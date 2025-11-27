<?php

namespace App\Services\Integration;

use PDO;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\PeopleService;
use Google\Service\PeopleService\Person;
use Google\Service\PeopleService\Name;
use Google\Service\PeopleService\EmailAddress;
use Google\Service\PeopleService\PhoneNumber;
use Google\Service\PeopleService\Address;
use Google\Service\PeopleService\Birthday;
use Google\Service\PeopleService\UserDefined;

/**
 * Google Contacts Synchronization Service
 * 
 * Handles two-way sync between Nautilus customers and Google Contacts
 * using the Google People API.
 */
class GoogleContactsService
{
    private PDO $db;
    private int $tenantId;
    private ?GoogleClient $googleClient = null;
    private ?PeopleService $peopleService = null;
    private array $config;
    private array $fieldMappings;
    
    public function __construct(PDO $db, int $tenantId)
    {
        $this->db = $db;
        $this->tenantId = $tenantId;
        $this->loadConfig();
        $this->loadFieldMappings();
    }
    
    /**
     * Load sync configuration from database
     */
    private function loadConfig(): void
    {
        $stmt = $this->db->prepare("
            SELECT * FROM google_contacts_sync_config 
            WHERE tenant_id = ? AND is_active = 1
        ");
        $stmt->execute([$this->tenantId]);
        $this->config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    
    /**
     * Load field mappings from database
     */
    private function loadFieldMappings(): void
    {
        $stmt = $this->db->prepare("
            SELECT * FROM google_contacts_field_mapping 
            WHERE tenant_id = ? AND is_active = 1 AND sync_enabled = 1
            ORDER BY priority_order
        ");
        $stmt->execute([$this->tenantId]);
        $this->fieldMappings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Initialize Google API client
     */
    private function initializeGoogleClient(): void
    {
        if ($this->googleClient !== null) {
            return;
        }
        
        if (empty($this->config['google_client_id']) || empty($this->config['access_token'])) {
            throw new Exception('Google Contacts not configured. Please connect your Google account first.');
        }
        
        $this->googleClient = new GoogleClient();
        $this->googleClient->setClientId($this->config['google_client_id']);
        $this->googleClient->setClientSecret($this->decryptToken($this->config['google_client_secret']));
        $this->googleClient->setAccessToken($this->decryptToken($this->config['access_token']));
        
        // Refresh token if expired
        if ($this->googleClient->isAccessTokenExpired()) {
            $refreshToken = $this->decryptToken($this->config['refresh_token']);
            $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);
            $newToken = $this->googleClient->getAccessToken();
            $this->saveAccessToken($newToken);
        }
        
        $this->peopleService = new PeopleService($this->googleClient);
    }
    
    /**
     * Perform full synchronization
     */
    public function performFullSync(int $triggeredByUserId = null): array
    {
        $logId = $this->startSyncLog('full', 'two_way', $triggeredByUserId);
        
        try {
            $this->initializeGoogleClient();
            
            $direction = $this->config['sync_direction'] ?? 'two_way';
            $results = [
                'exported' => 0,
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'conflicts' => 0,
                'errors' => 0
            ];
            
            // Export Nautilus customers to Google
            if (in_array($direction, ['two_way', 'export_only'])) {
                $exportResults = $this->exportAllCustomers();
                $results['exported'] = $exportResults['exported'];
                $results['updated'] += $exportResults['updated'];
                $results['errors'] += $exportResults['errors'];
            }
            
            // Import Google Contacts to Nautilus
            if (in_array($direction, ['two_way', 'import_only'])) {
                $importResults = $this->importAllContacts();
                $results['imported'] = $importResults['imported'];
                $results['updated'] += $importResults['updated'];
                $results['errors'] += $importResults['errors'];
            }
            
            $this->completeSyncLog($logId, 'completed', $results);
            $this->updateLastSyncTime('full');
            
            return $results;
            
        } catch (Exception $e) {
            $this->completeSyncLog($logId, 'failed', [], $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Perform incremental sync (only changed records)
     */
    public function performIncrementalSync(int $triggeredByUserId = null): array
    {
        $logId = $this->startSyncLog('incremental', 'two_way', $triggeredByUserId);
        
        try {
            $this->initializeGoogleClient();
            
            $results = [
                'exported' => 0,
                'imported' => 0,
                'updated' => 0,
                'conflicts' => 0
            ];
            
            // Get customers modified since last sync
            $lastSync = $this->config['last_sync_at'] ?? null;
            $changedCustomers = $this->getChangedCustomers($lastSync);
            
            foreach ($changedCustomers as $customer) {
                $result = $this->syncSingleCustomer($customer['id']);
                if ($result['status'] === 'exported') $results['exported']++;
                if ($result['status'] === 'updated') $results['updated']++;
                if ($result['status'] === 'conflict') $results['conflicts']++;
            }
            
            $this->completeSyncLog($logId, 'completed', $results);
            $this->updateLastSyncTime('incremental');
            
            return $results;
            
        } catch (Exception $e) {
            $this->completeSyncLog($logId, 'failed', [], $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sync a single customer
     */
    public function syncSingleCustomer(int $customerId): array
    {
        $this->initializeGoogleClient();
        
        // Get customer data
        $customer = $this->getCustomerData($customerId);
        if (!$customer) {
            return ['status' => 'error', 'message' => 'Customer not found'];
        }
        
        // Check if customer already mapped to Google Contact
        $mapping = $this->getMapping($customerId);
        
        if ($mapping) {
            // Update existing contact
            return $this->updateGoogleContact($customer, $mapping);
        } else {
            // Create new contact
            return $this->createGoogleContact($customer);
        }
    }
    
    /**
     * Export all customers to Google Contacts
     */
    private function exportAllCustomers(): array
    {
        $results = ['exported' => 0, 'updated' => 0, 'errors' => 0];
        
        $customers = $this->getCustomersForSync();
        $batchSize = $this->config['batch_size'] ?? 50;
        
        foreach (array_chunk($customers, $batchSize) as $batch) {
            foreach ($batch as $customer) {
                try {
                    $result = $this->syncSingleCustomer($customer['id']);
                    if ($result['status'] === 'created') $results['exported']++;
                    if ($result['status'] === 'updated') $results['updated']++;
                } catch (Exception $e) {
                    $results['errors']++;
                    error_log("Error syncing customer {$customer['id']}: " . $e->getMessage());
                }
            }
            
            // Rate limiting
            usleep(100000); // 100ms delay between batches
        }
        
        return $results;
    }
    
    /**
     * Import all Google Contacts
     */
    private function importAllContacts(): array
    {
        $results = ['imported' => 0, 'updated' => 0, 'errors' => 0];
        
        try {
            $pageToken = null;
            
            do {
                $params = [
                    'pageSize' => 100,
                    'personFields' => 'names,emailAddresses,phoneNumbers,addresses,birthdays,biographies,userDefined'
                ];
                
                if ($pageToken) {
                    $params['pageToken'] = $pageToken;
                }
                
                $response = $this->peopleService->people_connections->listPeopleConnections('people/me', $params);
                
                foreach ($response->getConnections() as $person) {
                    try {
                        $result = $this->importGoogleContact($person);
                        if ($result['status'] === 'created') $results['imported']++;
                        if ($result['status'] === 'updated') $results['updated']++;
                    } catch (Exception $e) {
                        $results['errors']++;
                        error_log("Error importing contact: " . $e->getMessage());
                    }
                }
                
                $pageToken = $response->getNextPageToken();
                
            } while ($pageToken);
            
        } catch (Exception $e) {
            error_log("Error fetching Google Contacts: " . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * Create new Google Contact from Nautilus customer
     */
    private function createGoogleContact(array $customer): array
    {
        $person = $this->nautilusToGoogleFormat($customer);
        
        try {
            $createdPerson = $this->peopleService->people->createContact($person);
            
            // Save mapping
            $this->saveMapping($customer['id'], $createdPerson->getResourceName(), $createdPerson->getEtag());
            
            return [
                'status' => 'created',
                'google_resource_name' => $createdPerson->getResourceName()
            ];
            
        } catch (Exception $e) {
            error_log("Error creating Google contact: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update existing Google Contact
     */
    private function updateGoogleContact(array $customer, array $mapping): array
    {
        // Check for conflicts
        if ($this->detectConflict($customer, $mapping)) {
            return $this->handleConflict($customer, $mapping);
        }
        
        $person = $this->nautilusToGoogleFormat($customer);
        $person->setResourceName($mapping['google_resource_name']);
        $person->setEtag($mapping['google_etag']);
        
        try {
            $updatedPerson = $this->peopleService->people->updateContact(
                $mapping['google_resource_name'],
                $person,
                ['updatePersonFields' => 'names,emailAddresses,phoneNumbers,addresses,birthdays,biographies']
            );
            
            // Update mapping
            $this->updateMapping($customer['id'], $updatedPerson->getEtag());
            
            return [
                'status' => 'updated',
                'google_resource_name' => $updatedPerson->getResourceName()
            ];
            
        } catch (Exception $e) {
            error_log("Error updating Google contact: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Import Google Contact to Nautilus
     */
    private function importGoogleContact(Person $person): array
    {
        $resourceName = $person->getResourceName();
        
        // Check if already mapped
        $existingMapping = $this->getMappingByGoogleResource($resourceName);
        
        $customerData = $this->googleToNautilusFormat($person);
        
        if ($existingMapping) {
            // Update existing customer
            $this->updateCustomer($existingMapping['customer_id'], $customerData);
            return ['status' => 'updated', 'customer_id' => $existingMapping['customer_id']];
        } else {
            // Create new customer
            $customerId = $this->createCustomer($customerData);
            $this->saveMapping($customerId, $resourceName, $person->getEtag());
            return ['status' => 'created', 'customer_id' => $customerId];
        }
    }
    
    /**
     * Convert Nautilus customer to Google Person format
     */
    private function nautilusToGoogleFormat(array $customer): Person
    {
        $person = new Person();
        
        // Name
        $name = new Name();
        $name->setGivenName($customer['first_name']);
        $name->setFamilyName($customer['last_name']);
        $person->setNames([$name]);
        
        // Email
        if (!empty($customer['email'])) {
            $email = new EmailAddress();
            $email->setValue($customer['email']);
            $email->setType('work');
            $person->setEmailAddresses([$email]);
        }
        
        // Phone numbers
        $phoneNumbers = [];
        if (!empty($customer['phone'])) {
            $phone = new PhoneNumber();
            $phone->setValue($customer['phone']);
            $phone->setType('work');
            $phoneNumbers[] = $phone;
        }
        if (!empty($customer['mobile'])) {
            $mobile = new PhoneNumber();
            $mobile->setValue($customer['mobile']);
            $mobile->setType('mobile');
            $phoneNumbers[] = $mobile;
        }
        if (!empty($phoneNumbers)) {
            $person->setPhoneNumbers($phoneNumbers);
        }
        
        // Address (from customer_addresses table)
        $addressData = $this->getCustomerAddress($customer['id']);
        if ($addressData) {
            $address = new Address();
            $address->setStreetAddress($addressData['address_line1']);
            $address->setCity($addressData['city']);
            $address->setRegion($addressData['state']);
            $address->setPostalCode($addressData['postal_code']);
            $address->setCountry($addressData['country']);
            $person->setAddresses([$address]);
        }
        
        // Birthday
        if (!empty($customer['birth_date'])) {
            $birthday = new Birthday();
            $date = new \Google\Service\PeopleService\Date();
            list($year, $month, $day) = explode('-', $customer['birth_date']);
            $date->setYear((int)$year);
            $date->setMonth((int)$month);
            $date->setDay((int)$day);
            $birthday->setDate($date);
            $person->setBirthdays([$birthday]);
        }
        
        // Custom fields (loyalty info, customer since, etc.)
        $userDefined = [];
        if (!empty($customer['customer_since'])) {
            $field = new UserDefined();
            $field->setKey('Customer Since');
            $field->setValue($customer['customer_since']);
            $userDefined[] = $field;
        }
        if (!empty($customer['loyalty_tier'])) {
            $field = new UserDefined();
            $field->setKey('Loyalty Tier');
            $field->setValue($customer['loyalty_tier']);
            $userDefined[] = $field;
        }
        if (!empty($userDefined)) {
            $person->setUserDefined($userDefined);
        }
        
        return $person;
    }
    
    /**
     * Convert Google Person to Nautilus customer format
     */
    private function googleToNautilusFormat(Person $person): array
    {
        $data = [];
        
        // Name
        $names = $person->getNames();
        if (!empty($names)) {
            $data['first_name'] = $names[0]->getGivenName() ?? '';
            $data['last_name'] = $names[0]->getFamilyName() ?? '';
        }
        
        // Email
        $emails = $person->getEmailAddresses();
        if (!empty($emails)) {
            $data['email'] = $emails[0]->getValue();
        }
        
        // Phone numbers
        $phones = $person->getPhoneNumbers();
        foreach ($phones ?? [] as $phone) {
            if ($phone->getType() === 'mobile') {
                $data['mobile'] = $phone->getValue();
            } else {
                $data['phone'] = $phone->getValue();
            }
        }
        
        // Address
        $addresses = $person->getAddresses();
        if (!empty($addresses)) {
            $addr = $addresses[0];
            $data['address'] = [
                'address_line1' => $addr->getStreetAddress(),
                'city' => $addr->getCity(),
                'state' => $addr->getRegion(),
                'postal_code' => $addr->getPostalCode(),
                'country' => $addr->getCountry() ?? 'US'
            ];
        }
        
        // Birthday
        $birthdays = $person->getBirthdays();
        if (!empty($birthdays)) {
            $date = $birthdays[0]->getDate();
            if ($date) {
                $data['birth_date'] = sprintf('%04d-%02d-%02d', 
                    $date->getYear(), $date->getMonth(), $date->getDay());
            }
        }
        
        return $data;
    }
    
    /**
     * Detect conflicts between Nautilus and Google data
     */
    private function detectConflict(array $customer, array $mapping): bool
    {
        // Compare modification timestamps
        $nautilusModified = strtotime($customer['updated_at']);
        $lastSynced = strtotime($mapping['last_synced_at']);
        
        // If Nautilus was modified after last sync, check Google too
        if ($nautilusModified > $lastSynced) {
            // Fetch current Google contact
            try {
                $person = $this->peopleService->people->get(
                    $mapping['google_resource_name'],
                    ['personFields' => 'metadata']
                );
                
                $metadata = $person->getMetadata();
                $googleModified = strtotime($metadata->getSources()[0]->getUpdateTime());
                
                // Conflict if both were modified after last sync
                return $googleModified > $lastSynced;
                
            } catch (Exception $e) {
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Handle sync conflict
     */
    private function handleConflict(array $customer, array $mapping): array
    {
        $strategy = $this->config['conflict_strategy'] ?? 'last_modified_wins';
        
        switch ($strategy) {
            case 'nautilus_wins':
                return $this->updateGoogleContact($customer, $mapping);
                
            case 'google_wins':
                // Fetch and import Google data
                $person = $this->peopleService->people->get(
                    $mapping['google_resource_name'],
                    ['personFields' => 'names,emailAddresses,phoneNumbers,addresses,birthdays']
                );
                return $this->importGoogleContact($person);
                
            case 'last_modified_wins':
                // Compare timestamps and use most recent
                $nautilusTime = strtotime($customer['updated_at']);
                $person = $this->peopleService->people->get($mapping['google_resource_name'], ['personFields' => 'metadata']);
                $googleTime = strtotime($person->getMetadata()->getSources()[0]->getUpdateTime());
                
                if ($nautilusTime > $googleTime) {
                    return $this->updateGoogleContact($customer, $mapping);
                } else {
                    return $this->importGoogleContact($person);
                }
                
            case 'manual':
                // Mark for manual review
                $this->markConflictForReview($customer['id'], $mapping);
                return ['status' => 'conflict', 'message' => 'Marked for manual review'];
                
            default:
                return ['status' => 'conflict', 'message' => 'Unknown conflict strategy'];
        }
    }
    
    // Database Helper Methods
    
    private function getCustomerData(int $customerId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM customers WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getCustomersForSync(): array
    {
        $conditions = ["c.is_active = 1"];
        $params = [$this->tenantId];
        
        // Apply filters from config
        if (!empty($this->config['sync_customer_types'])) {
            $types = json_decode($this->config['sync_customer_types'], true);
            $placeholders = str_repeat('?,', count($types) - 1) . '?';
            $conditions[] = "c.customer_type IN ($placeholders)";
            $params = array_merge($params, $types);
        }
        
        if (!empty($this->config['min_lifetime_value'])) {
            $conditions[] = "c.lifetime_value >= ?";
            $params[] = $this->config['min_lifetime_value'];
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $stmt = $this->db->prepare("
            SELECT c.* FROM customers c
            WHERE c.tenant_id = ? AND $whereClause
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getCustomerAddress(int $customerId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM customer_addresses 
            WHERE customer_id = ? AND is_default = 1 
            LIMIT 1
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getMapping(int $customerId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM google_contacts_sync_mapping 
            WHERE tenant_id = ? AND customer_id = ?
        ");
        $stmt->execute([$this->tenantId, $customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getMappingByGoogleResource(string $resourceName): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM google_contacts_sync_mapping 
            WHERE tenant_id = ? AND google_resource_name = ?
        ");
        $stmt->execute([$this->tenantId, $resourceName]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function saveMapping(int $customerId, string $resourceName, string $etag): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO google_contacts_sync_mapping 
            (tenant_id, customer_id, google_resource_name, google_etag, last_synced_at, sync_status)
            VALUES (?, ?, ?, ?, NOW(), 'synced')
        ");
        $stmt->execute([$this->tenantId, $customerId, $resourceName, $etag]);
    }
    
    private function updateMapping(int $customerId, string $etag): void
    {
        $stmt = $this->db->prepare("
            UPDATE google_contacts_sync_mapping 
            SET google_etag = ?, last_synced_at = NOW(), sync_status = 'synced'
            WHERE tenant_id = ? AND customer_id = ?
        ");
        $stmt->execute([$etag, $this->tenantId, $customerId]);
    }
    
    private function createCustomer(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO customers (tenant_id, first_name, last_name, email, phone, mobile, birth_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->tenantId,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? null,
            $data['mobile'] ?? null,
            $data['birth_date'] ?? null
        ]);
        
        $customerId = (int)$this->db->lastInsertId();
        
        // Create address if provided
        if (!empty($data['address'])) {
            $this->createCustomerAddress($customerId, $data['address']);
        }
        
        return $customerId;
    }
    
    private function updateCustomer(int $customerId, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE customers 
            SET first_name = ?, last_name = ?, email = ?, phone = ?, mobile = ?, birth_date = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? null,
            $data['mobile'] ?? null,
            $data['birth_date'] ?? null,
            $customerId
        ]);
        
        if (!empty($data['address'])) {
            $this->updateCustomerAddress($customerId, $data['address']);
        }
    }
    
    private function createCustomerAddress(int $customerId, array $address): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO customer_addresses 
            (customer_id, address_line1, city, state, postal_code, country, is_default)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $customerId,
            $address['address_line1'] ?? '',
            $address['city'] ?? '',
            $address['state'] ?? '',
            $address['postal_code'] ?? '',
            $address['country'] ?? 'US'
        ]);
    }
    
    private function updateCustomerAddress(int $customerId, array $address): void
    {
        // Check if address exists
        $stmt = $this->db->prepare("
            SELECT id FROM customer_addresses WHERE customer_id = ? AND is_default = 1
        ");
        $stmt->execute([$customerId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE customer_addresses 
                SET address_line1 = ?, city = ?, state = ?, postal_code = ?, country = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $address['address_line1'] ?? '',
                $address['city'] ?? '',
                $address['state'] ?? '',
                $address['postal_code'] ?? '',
                $address['country'] ?? 'US',
                $existing['id']
            ]);
        } else {
            $this->createCustomerAddress($customerId, $address);
        }
    }
    
    private function getChangedCustomers(?string $since): array
    {
        if (!$since) {
            return $this->getCustomersForSync();
        }
        
        $stmt = $this->db->prepare("
            SELECT c.* FROM customers c
            WHERE c.tenant_id = ? AND c.is_active = 1 
            AND c.updated_at > ?
        ");
        $stmt->execute([$this->tenantId, $since]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function startSyncLog(string $type, string $direction, ?int $userId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO google_contacts_sync_log 
            (tenant_id, sync_type, sync_direction, triggered_by, triggered_by_user_id, started_at, status)
            VALUES (?, ?, ?, ?, ?, NOW(), 'in_progress')
        ");
        $stmt->execute([
            $this->tenantId,
            $type,
            $direction,
            $userId ? 'user' : 'scheduler',
            $userId
        ]);
        return (int)$this->db->lastInsertId();
    }
    
    private function completeSyncLog(int $logId, string $status, array $results = [], string $error = null): void
    {
        $stmt = $this->db->prepare("
            UPDATE google_contacts_sync_log 
            SET completed_at = NOW(),
                status = ?,
                customers_exported = ?,
                customers_imported = ?,
                customers_updated = ?,
                conflicts_detected = ?,
                errors_count = ?,
                error_message = ?,
                duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW())
            WHERE id = ?
        ");
        $stmt->execute([
            $status,
            $results['exported'] ?? 0,
            $results['imported'] ?? 0,
            $results['updated'] ?? 0,
            $results['conflicts'] ?? 0,
            $results['errors'] ?? 0,
            $error,
            $logId
        ]);
    }
    
    private function updateLastSyncTime(string $syncType): void
    {
        $stmt = $this->db->prepare("
            UPDATE google_contacts_sync_config 
            SET last_sync_at = NOW(),
                last_full_sync_at = IF(? = 'full', NOW(), last_full_sync_at),
                next_sync_at = DATE_ADD(NOW(), INTERVAL sync_frequency_minutes MINUTE),
                total_syncs = total_syncs + 1
            WHERE tenant_id = ?
        ");
        $stmt->execute([$syncType, $this->tenantId]);
    }
    
    private function markConflictForReview(int $customerId, array $mapping): void
    {
        $stmt = $this->db->prepare("
            UPDATE google_contacts_sync_mapping 
            SET has_conflict = 1,
                conflict_detected_at = NOW(),
                sync_status = 'conflict'
            WHERE tenant_id = ? AND customer_id = ?
        ");
        $stmt->execute([$this->tenantId, $customerId]);
    }
    
    private function decryptToken(string $encrypted): string
    {
        // Simple base64 decoding - in production use proper encryption
        return base64_decode($encrypted);
    }
    
    private function saveAccessToken(array $token): void
    {
        $stmt = $this->db->prepare("
            UPDATE google_contacts_sync_config 
            SET access_token = ?,
                token_expires_at = FROM_UNIXTIME(?)
            WHERE tenant_id = ?
        ");
        $stmt->execute([
            base64_encode(json_encode($token)),
            $token['expires_in'] + time(),
            $this->tenantId
        ]);
    }
}
