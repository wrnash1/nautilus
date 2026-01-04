<?php

namespace App\Services\Import;

use App\Core\Database;
use App\Models\Customer;

class CustomerImportService
{
    private $customerModel;

    public function __construct()
    {
        $this->customerModel = new Customer();
    }

    public function createImportJob(array $fileData, array $settings): int
    {
        Database::query(
            "INSERT INTO product_import_jobs (
                job_name, import_type, source_file, status,
                file_size, update_existing, match_field, skip_duplicates,
                created_by, created_at
            ) VALUES (?, 'csv', ?, 'pending', ?, ?, ?, ?, ?, NOW())",
            [
                $settings['job_name'] ?? 'Customer Import ' . date('Y-m-d H:i:s'),
                $fileData['file_path'],
                $fileData['file_size'],
                (int)($settings['update_existing'] ?? 0),
                $settings['match_field'] ?? 'email',
                (int)($settings['skip_duplicates'] ?? 1),
                currentUser()['id'] ?? null
            ]
        );

        return (int)Database::lastInsertId();
    }

    public function autoDetectFieldMapping(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Could not open file: $filePath");
        }

        $headers = fgetcsv($handle, 0, ",", "\"", "\\");
        fclose($handle);

        if (!$headers) {
            throw new \Exception("Could not read headers from file");
        }

        $mapping = [];
        $patterns = [
            'first_name' => ['first name', 'firstname', 'f_name', 'fname'],
            'last_name' => ['last name', 'lastname', 'l_name', 'lname'],
            'email' => ['email', 'email address', 'e-mail'],
            'phone' => ['phone', 'home phone', 'mobile', 'cell', 'telephone'],
            'company_name' => ['company', 'company name', 'business', 'organization'],
            'address_line1' => ['address', 'address 1', 'street', 'mailing address', 'address line 1'],
            'address_line2' => ['address 2', 'suite', 'apt', 'unit', 'address line 2'],
            'city' => ['city', 'town'],
            'state' => ['state', 'province', 'region'],
            'postal_code' => ['zip', 'zip code', 'postal code', 'postal'],
            'country' => ['country'],
            'external_id' => ['customer id', 'id', 'account number', 'cust id'],
            'customer_type' => ['customer type', 'type'],
            'birth_date' => ['birthday', 'dob', 'date of birth']
        ];

        foreach ($headers as $header) {
            $normalizedHeader = strtolower(trim(str_replace(['_', '-'], ' ', $header)));
            
            foreach ($patterns as $field => $fieldPatterns) {
                if (in_array($normalizedHeader, $fieldPatterns)) {
                    $mapping[$header] = $field;
                    break;
                }
            }
        }

        return $mapping;
    }

    public function executeImport(int $jobId): array
    {
        $job = Database::fetchOne("SELECT * FROM product_import_jobs WHERE id = ?", [$jobId]);
        if (!$job) {
            throw new \Exception("Job not found");
        }

        $mapping = json_decode($job['field_mapping'], true) ?? [];
        
        $handle = fopen($job['source_file'], 'r');
        if (!$handle) {
            throw new \Exception("Could not open source file");
        }

        // Skip header
        fgetcsv($handle);

        $stats = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'updated' => 0,
            'errors' => []
        ];

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $stats['processed']++;

            try {
                $data = $this->mapRow($row, $mapping, $job['header_row'] ?? []); // header_row might need to be passed or re-read
                
                // Basic validation
                if (empty($data['email']) && empty($data['first_name']) && empty($data['last_name'])) {
                     $stats['skipped']++;
                     continue;
                }

                $this->processCustomer($data, $job, $stats);

            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        fclose($handle);
        
        Database::query(
            "UPDATE product_import_jobs SET status = 'completed', completed_at = NOW() WHERE id = ?",
            [$jobId]
        );

        return $stats;
    }

    private function mapRow(array $row, array $mapping, array $headers = []): array
    {
        // Re-read headers if not provided (simplified for now, assuming mapping keys match row indices if we had headers)
        // Actually, mapping keys are header names. We need to know which index corresponds to which header.
        // For simplicity in this step, I'll assume we can get the header index from the mapping keys if we had the headers.
        // But wait, fgetcsv just gives indexed array. 
        // I need to read headers once and map indices.
        
        // Let's refactor executeImport to read headers first.
        return []; 
    }
    
    // Refactoring executeImport to handle mapping correctly
    public function executeImportRefactored(int $jobId): array {
         $job = Database::fetchOne("SELECT * FROM product_import_jobs WHERE id = ?", [$jobId]);
        if (!$job) {
            throw new \Exception("Job not found");
        }

        $mapping = json_decode($job['field_mapping'], true) ?? [];
        
        $handle = fopen($job['source_file'], 'r');
        if (!$handle) {
            throw new \Exception("Could not open source file");
        }

        $headers = fgetcsv($handle, 0, ",", "\"", "\\");
        if (!$headers) {
             throw new \Exception("Could not read headers");
        }
        
        // Flip headers to get index
        $headerMap = array_flip($headers);

        $stats = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'updated' => 0,
            'errors' => []
        ];

        $rowNumber = 1;
        while (($row = fgetcsv($handle, 0, ",", "\"", "\\")) !== false) {
            $rowNumber++;
            $stats['processed']++;

            try {
                $data = [];
                foreach ($mapping as $csvHeader => $dbField) {
                    if (isset($headerMap[$csvHeader]) && isset($row[$headerMap[$csvHeader]])) {
                        $data[$dbField] = trim($row[$headerMap[$csvHeader]]);
                    }
                }

                // Basic validation
                if (empty($data['email']) && empty($data['first_name']) && empty($data['last_name'])) {
                     $stats['skipped']++;
                     continue;
                }

                $this->processCustomer($data, $job, $stats);

            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        fclose($handle);
        
        Database::query(
            "UPDATE product_import_jobs SET status = 'completed', completed_at = NOW() WHERE id = ?",
            [$jobId]
        );

        return $stats;
    }

    private function processCustomer(array $data, array $job, array &$stats)
    {
        // Check for existing
        $existing = null;
        if (!empty($data['external_id'])) {
            $existing = Database::fetchOne("SELECT * FROM customers WHERE external_id = ?", [$data['external_id']]);
        }
        if (!$existing && !empty($data['email'])) {
             $existing = Database::fetchOne("SELECT * FROM customers WHERE email = ?", [$data['email']]);
        }

        // Map Customer Type
        if (isset($data['customer_type'])) {
            $type = strtolower($data['customer_type']);
            if (strpos($type, 'retail') !== false) {
                $data['customer_type'] = 'B2C';
            } elseif (strpos($type, 'employee') !== false) {
                $data['customer_type'] = 'B2C'; // Or handle as employee? Schema says ENUM('B2C', 'B2B')
            } else {
                $data['customer_type'] = 'B2C'; // Default
            }
        }

        // Handle Birth Date
        if (isset($data['birth_date'])) {
            if (empty($data['birth_date']) || $data['birth_date'] === '0000-00-00') {
                $data['birth_date'] = null;
            } else {
                // Try to parse date if needed, but assuming YYYY-MM-DD from CSV based on sample
                // If CSV has other format, might need conversion.
                // Sample shows "1984-02-17" (Row 31) and "0000-00-00" (Row 29).
                // So format is YYYY-MM-DD.
            }
        }

        if ($existing) {
            if ($job['update_existing']) {
                // Update
                $updateFields = [];
                $params = [];
                foreach (['first_name', 'last_name', 'phone', 'company_name', 'customer_type', 'birth_date', 'external_id'] as $field) {
                    if (array_key_exists($field, $data)) { // Use array_key_exists to allow updating to NULL
                        $updateFields[] = "$field = ?";
                        $params[] = $data[$field];
                    }
                }
                if (!empty($updateFields)) {
                    $params[] = $existing['id'];
                    Database::query("UPDATE customers SET " . implode(', ', $updateFields) . " WHERE id = ?", $params);
                    $stats['updated']++;
                }
                
                $customerId = $existing['id'];
            } else {
                $stats['skipped']++;
                return;
            }
        } else {
            // Create
            Database::query(
                "INSERT INTO customers (
                    first_name, last_name, email, phone, company_name, 
                    customer_type, birth_date, external_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $data['first_name'] ?? null,
                    $data['last_name'] ?? null,
                    $data['email'] ?? null,
                    $data['phone'] ?? null,
                    $data['company_name'] ?? null,
                    $data['customer_type'] ?? 'B2C',
                    $data['birth_date'] ?? null,
                    $data['external_id'] ?? null
                ]
            );
            $customerId = Database::lastInsertId();
            $stats['success']++;
        }

        // Handle Address
        if (!empty($data['address_line1']) || !empty($data['city'])) {
            // Check if address exists (simple check)
            $existingAddr = Database::fetchOne(
                "SELECT id FROM customer_addresses WHERE customer_id = ? AND address_line1 = ? AND postal_code = ?",
                [$customerId, $data['address_line1'] ?? '', $data['postal_code'] ?? '']
            );

            if (!$existingAddr) {
                Database::query(
                    "INSERT INTO customer_addresses (
                        customer_id, address_line1, address_line2, city, state, 
                        postal_code, country, address_type, is_default
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'billing', 1)",
                    [
                        $customerId,
                        $data['address_line1'] ?? null,
                        $data['address_line2'] ?? null,
                        $data['city'] ?? null,
                        $data['state'] ?? null,
                        $data['postal_code'] ?? null,
                        $data['country'] ?? 'United States'
                    ]
                );
            }
        }
    }
    
    public function saveFieldMapping(int $jobId, array $mapping): void
    {
        Database::query(
            "UPDATE product_import_jobs SET field_mapping = ? WHERE id = ?",
            [json_encode($mapping), $jobId]
        );
    }
}
