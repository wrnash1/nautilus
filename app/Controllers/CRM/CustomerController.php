<?php

namespace App\Controllers\CRM;

use App\Services\CRM\CustomerService;
use App\Models\Customer;

class CustomerController
{
    private CustomerService $customerService;
    
    public function __construct()
    {
        $this->customerService = new CustomerService();
    }
    
    public function index()
    {
        if (!hasPermission('customers.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = sanitizeInput($_GET['search'] ?? '');
        
        if (!empty($search)) {
            $customers = Customer::search($search, $limit);
            $total = count($customers);
        } else {
            $customers = Customer::all($limit, $offset);
            $total = Customer::count();
        }
        
        $totalPages = ceil($total / $limit);
        
        require __DIR__ . '/../../Views/customers/index.php';
    }
    
    public function create()
    {
        if (!hasPermission('customers.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/customers');
        }
        
        require __DIR__ . '/../../Views/customers/create.php';
    }
    
    public function store()
    {
        if (!hasPermission('customers.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }
        
        try {
            $birthDate = sanitizeInput($_POST['birth_date'] ?? '');

            $data = [
                'customer_type' => sanitizeInput($_POST['customer_type'] ?? 'B2C'),
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'mobile' => sanitizeInput($_POST['mobile'] ?? ''),
                'company_name' => sanitizeInput($_POST['company_name'] ?? ''),
                'birth_date' => !empty($birthDate) ? $birthDate : null,
                'emergency_contact_name' => sanitizeInput($_POST['emergency_contact_name'] ?? ''),
                'emergency_contact_phone' => sanitizeInput($_POST['emergency_contact_phone'] ?? ''),
                'tax_exempt' => isset($_POST['tax_exempt']) ? 1 : 0,
                'tax_exempt_number' => sanitizeInput($_POST['tax_exempt_number'] ?? ''),
                'credit_limit' => (float)($_POST['credit_limit'] ?? 0),
                'credit_terms' => sanitizeInput($_POST['credit_terms'] ?? ''),
                'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
                'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
                'city' => sanitizeInput($_POST['city'] ?? ''),
                'state' => sanitizeInput($_POST['state'] ?? ''),
                'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
                'country' => sanitizeInput($_POST['country'] ?? 'US'),
                'notes' => sanitizeInput($_POST['notes'] ?? '')
            ];

            $customerId = $this->customerService->createCustomer($data);
            
            $_SESSION['flash_success'] = 'Customer created successfully';
            redirect("/customers/{$customerId}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/customers/create');
        }
    }
    
    public function show(int $id)
    {
        if (!hasPermission('customers.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }
        
        $data = $this->customerService->getCustomer360($id);

        if (empty($data)) {
            $_SESSION['flash_error'] = 'Customer not found';
            redirect('/customers');
        }

        // Use EXTR_SKIP to prevent overwriting existing variables (security measure)
        extract($data, EXTR_SKIP);

        require __DIR__ . '/../../Views/customers/show.php';
    }
    
    public function edit(int $id)
    {
        if (!hasPermission('customers.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/customers');
        }

        $customer = Customer::find($id);
        $address = Customer::getDefaultAddress($id);

        if (!$customer) {
            $_SESSION['flash_error'] = 'Customer not found';
            redirect('/customers');
        }

        // Load certification agencies
        $certificationAgencies = \App\Core\Database::fetchAll("SELECT id, name, code FROM certification_agencies WHERE is_active = 1 ORDER BY name");

        // Load customer certifications
        $certifications = \App\Core\Database::fetchAll("
            SELECT cc.*, ca.name as agency_name, ca.code as agency_code
            FROM customer_certifications cc
            LEFT JOIN certification_agencies ca ON cc.certification_agency_id = ca.id
            WHERE cc.customer_id = ?
            ORDER BY cc.issue_date DESC
        ", [$id]);

        require __DIR__ . '/../../Views/customers/edit.php';
    }
    
    public function update(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }
        
        try {
            $birthDate = sanitizeInput($_POST['birth_date'] ?? '');

            $data = [
                'customer_type' => sanitizeInput($_POST['customer_type'] ?? 'B2C'),
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'mobile' => sanitizeInput($_POST['mobile'] ?? ''),
                'company_name' => sanitizeInput($_POST['company_name'] ?? ''),
                'birth_date' => !empty($birthDate) ? $birthDate : null,
                'emergency_contact_name' => sanitizeInput($_POST['emergency_contact_name'] ?? ''),
                'emergency_contact_phone' => sanitizeInput($_POST['emergency_contact_phone'] ?? ''),
                'tax_exempt' => isset($_POST['tax_exempt']) ? 1 : 0,
                'tax_exempt_number' => sanitizeInput($_POST['tax_exempt_number'] ?? ''),
                'credit_limit' => (float)($_POST['credit_limit'] ?? 0),
                'credit_terms' => sanitizeInput($_POST['credit_terms'] ?? ''),
                'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
                'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
                'city' => sanitizeInput($_POST['city'] ?? ''),
                'state' => sanitizeInput($_POST['state'] ?? ''),
                'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
                'country' => sanitizeInput($_POST['country'] ?? 'US'),
                'notes' => sanitizeInput($_POST['notes'] ?? '')
            ];

            $this->customerService->updateCustomer($id, $data);
            
            $_SESSION['flash_success'] = 'Customer updated successfully';
            redirect("/customers/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/customers/{$id}/edit");
        }
    }
    
    public function delete(int $id)
    {
        if (!hasPermission('customers.delete')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/customers');
        }
        
        Customer::delete($id);
        
        $_SESSION['flash_success'] = 'Customer deleted successfully';
        redirect('/customers');
    }
    
    public function search()
    {
        if (!hasPermission('customers.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }
        
        $query = sanitizeInput($_GET['q'] ?? '');
        
        if (empty($query)) {
            jsonResponse([]);
        }
        
        $customers = $this->customerService->search($query);
        jsonResponse($customers);
    }
    
    public function exportCsv()
    {
        if (!hasPermission('customers.export')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/customers');
        }
        
        $customers = Customer::all(10000, 0);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customers-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, [
            'ID', 'Type', 'First Name', 'Last Name', 'Email', 'Phone', 
            'Company', 'Credit Limit', 'Created At'
        ]);
        
        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer['id'],
                $customer['customer_type'],
                $customer['first_name'],
                $customer['last_name'],
                $customer['email'],
                $customer['phone'],
                $customer['company_name'] ?? '',
                $customer['credit_limit'] ?? 0,
                $customer['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function createAddress(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }
        
        try {
            $data = [
                'address_type' => sanitizeInput($_POST['address_type'] ?? 'billing'),
                'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
                'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
                'city' => sanitizeInput($_POST['city'] ?? ''),
                'state' => sanitizeInput($_POST['state'] ?? ''),
                'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
                'country' => sanitizeInput($_POST['country'] ?? 'US'),
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];
            
            if (empty($data['address_line1'])) {
                throw new \Exception('Address line 1 is required');
            }
            
            Customer::createAddress($id, $data);
            
            $_SESSION['flash_success'] = 'Address added successfully';
            redirect("/customers/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/customers/{$id}");
        }
    }
    
    public function updateAddress(int $id, int $address_id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }
        
        try {
            $data = [
                'address_type' => sanitizeInput($_POST['address_type'] ?? 'billing'),
                'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
                'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
                'city' => sanitizeInput($_POST['city'] ?? ''),
                'state' => sanitizeInput($_POST['state'] ?? ''),
                'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
                'country' => sanitizeInput($_POST['country'] ?? 'US'),
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];
            
            if (empty($data['address_line1'])) {
                throw new \Exception('Address line 1 is required');
            }
            
            Customer::updateAddress($address_id, $data);
            
            $_SESSION['flash_success'] = 'Address updated successfully';
            redirect("/customers/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/customers/{$id}");
        }
    }
    
    public function deleteAddress(int $id, int $address_id)
    {
        if (!hasPermission('customers.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect("/customers/{$id}");
        }

        Customer::deleteAddress($address_id);

        $_SESSION['flash_success'] = 'Address deleted successfully';
        redirect("/customers/{$id}");
    }

    // ========== Phone Number Management ==========

    public function addPhone(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO customer_phones (customer_id, phone_type, phone_number, extension, is_default, can_sms, can_call, notes, label)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                sanitizeInput($_POST['phone_type'] ?? 'mobile'),
                sanitizeInput($_POST['phone_number'] ?? ''),
                sanitizeInput($_POST['extension'] ?? ''),
                isset($_POST['is_default']) ? 1 : 0,
                isset($_POST['can_sms']) ? 1 : 0,
                isset($_POST['can_call']) ? 1 : 0,
                sanitizeInput($_POST['notes'] ?? ''),
                sanitizeInput($_POST['label'] ?? '')
            ]);

            jsonResponse(['success' => true, 'message' => 'Phone added successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function updatePhone(int $id, int $phoneId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                UPDATE customer_phones
                SET phone_type = ?, phone_number = ?, extension = ?, is_default = ?, can_sms = ?, can_call = ?, notes = ?, label = ?
                WHERE id = ? AND customer_id = ?
            ");
            $stmt->execute([
                sanitizeInput($_POST['phone_type'] ?? 'mobile'),
                sanitizeInput($_POST['phone_number'] ?? ''),
                sanitizeInput($_POST['extension'] ?? ''),
                isset($_POST['is_default']) ? 1 : 0,
                isset($_POST['can_sms']) ? 1 : 0,
                isset($_POST['can_call']) ? 1 : 0,
                sanitizeInput($_POST['notes'] ?? ''),
                sanitizeInput($_POST['label'] ?? ''),
                $phoneId,
                $id
            ]);

            jsonResponse(['success' => true, 'message' => 'Phone updated successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function deletePhone(int $id, int $phoneId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("DELETE FROM customer_phones WHERE id = ? AND customer_id = ?");
            $stmt->execute([$phoneId, $id]);

            jsonResponse(['success' => true, 'message' => 'Phone deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // ========== Email Management ==========

    public function addEmail(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO customer_emails (customer_id, email_type, email, is_default, can_market, notes, label)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                sanitizeInput($_POST['email_type'] ?? 'personal'),
                sanitizeInput($_POST['email'] ?? ''),
                isset($_POST['is_default']) ? 1 : 0,
                isset($_POST['can_market']) ? 1 : 0,
                sanitizeInput($_POST['notes'] ?? ''),
                sanitizeInput($_POST['label'] ?? '')
            ]);

            jsonResponse(['success' => true, 'message' => 'Email added successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function updateEmail(int $id, int $emailId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                UPDATE customer_emails
                SET email_type = ?, email = ?, is_default = ?, can_market = ?, notes = ?, label = ?
                WHERE id = ? AND customer_id = ?
            ");
            $stmt->execute([
                sanitizeInput($_POST['email_type'] ?? 'personal'),
                sanitizeInput($_POST['email'] ?? ''),
                isset($_POST['is_default']) ? 1 : 0,
                isset($_POST['can_market']) ? 1 : 0,
                sanitizeInput($_POST['notes'] ?? ''),
                sanitizeInput($_POST['label'] ?? ''),
                $emailId,
                $id
            ]);

            jsonResponse(['success' => true, 'message' => 'Email updated successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function deleteEmail(int $id, int $emailId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("DELETE FROM customer_emails WHERE id = ? AND customer_id = ?");
            $stmt->execute([$emailId, $id]);

            jsonResponse(['success' => true, 'message' => 'Email deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // ========== Contact Management ==========

    public function addContact(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO customer_contacts (customer_id, contact_type, first_name, last_name, phone, email, relationship, is_primary_emergency, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                sanitizeInput($_POST['contact_type'] ?? 'emergency'),
                sanitizeInput($_POST['first_name'] ?? ''),
                sanitizeInput($_POST['last_name'] ?? ''),
                sanitizeInput($_POST['phone'] ?? ''),
                sanitizeInput($_POST['email'] ?? ''),
                sanitizeInput($_POST['relationship'] ?? ''),
                isset($_POST['is_primary_emergency']) ? 1 : 0,
                sanitizeInput($_POST['notes'] ?? '')
            ]);

            jsonResponse(['success' => true, 'message' => 'Contact added successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function updateContact(int $id, int $contactId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                UPDATE customer_contacts
                SET contact_type = ?, first_name = ?, last_name = ?, phone = ?, email = ?, relationship = ?, is_primary_emergency = ?, notes = ?
                WHERE id = ? AND customer_id = ?
            ");
            $stmt->execute([
                sanitizeInput($_POST['contact_type'] ?? 'emergency'),
                sanitizeInput($_POST['first_name'] ?? ''),
                sanitizeInput($_POST['last_name'] ?? ''),
                sanitizeInput($_POST['phone'] ?? ''),
                sanitizeInput($_POST['email'] ?? ''),
                sanitizeInput($_POST['relationship'] ?? ''),
                isset($_POST['is_primary_emergency']) ? 1 : 0,
                sanitizeInput($_POST['notes'] ?? ''),
                $contactId,
                $id
            ]);

            jsonResponse(['success' => true, 'message' => 'Contact updated successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function deleteContact(int $id, int $contactId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("DELETE FROM customer_contacts WHERE id = ? AND customer_id = ?");
            $stmt->execute([$contactId, $id]);

            jsonResponse(['success' => true, 'message' => 'Contact deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // ========== Certification Management ==========

    public function addCertification(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO customer_certifications (customer_id, certification_agency_id, certification_level, certification_number, issue_date, expiration_date, instructor_name, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                (int)($_POST['certification_agency_id'] ?? 0),
                sanitizeInput($_POST['certification_level'] ?? ''),
                sanitizeInput($_POST['certification_number'] ?? ''),
                sanitizeInput($_POST['issue_date'] ?? null),
                sanitizeInput($_POST['expiration_date'] ?? null),
                sanitizeInput($_POST['instructor_name'] ?? ''),
                sanitizeInput($_POST['notes'] ?? '')
            ]);

            $_SESSION['flash_success'] = 'Certification added successfully';
            jsonResponse(['success' => true, 'message' => 'Certification added successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function deleteCertification(int $id, int $certId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("DELETE FROM customer_certifications WHERE id = ? AND customer_id = ?");
            $stmt->execute([$certId, $id]);

            jsonResponse(['success' => true, 'message' => 'Certification deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
