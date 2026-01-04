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

        $page = (int) ($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = sanitizeInput($_GET['search'] ?? '');

        if (!empty($search)) {
            $customers = Customer::search($search, $limit);
            $total = count($customers);
        } else {
            $customers = Customer::where('is_active', 1)
                ->orderBy('last_name', 'ASC')
                ->orderBy('first_name', 'ASC')
                ->offset($offset)
                ->limit($limit)
                ->get();
            $total = Customer::where('is_active', 1)->count();
        }

        $totalPages = ceil($total / $limit);

        require __DIR__ . '/../../Views/customers/index.php';
    }

    public function create()
    {
        if (!hasPermission('customers.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/customers');
        }

        $certificationAgencies = \App\Models\CertificationAgency::where('is_active', 1)->orderBy('name')->get();

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
                'credit_limit' => (float) ($_POST['credit_limit'] ?? 0),
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

            if (!empty($_POST['certification_agency_id'])) {
                try {
                    $customer = Customer::find($customerId);
                    $customer->certifications()->create([
                        'certification_agency_id' => (int) $_POST['certification_agency_id'],
                        'certification_level' => sanitizeInput($_POST['certification_level'] ?? ''),
                        'certification_number' => sanitizeInput($_POST['certification_number'] ?? ''),
                        'issue_date' => !empty($_POST['certification_issue_date']) ? $_POST['certification_issue_date'] : null,
                        'notes' => sanitizeInput($_POST['certification_notes'] ?? '')
                    ]);
                } catch (\Exception $e) {
                    error_log("Failed to add initial certification: " . $e->getMessage());
                }
            }

            $_SESSION['flash_success'] = 'Customer created successfully';

            $returnTo = $_POST['return_to'] ?? '';
            if ($returnTo === 'pos') {
                $_SESSION['pos_customer_id'] = $customerId;
                redirect('/store/pos');
            }

            redirect("/store/customers/{$customerId}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/store/customers/create');
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
            redirect('/store/customers');
        }

        extract($data, EXTR_SKIP);

        require __DIR__ . '/../../Views/customers/show.php';
    }

    public function edit(int $id)
    {
        if (!hasPermission('customers.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/customers');
        }

        $customer = Customer::find($id);

        if (!$customer) {
            $_SESSION['flash_error'] = 'Customer not found';
            redirect('/store/customers');
        }

        $address = $customer->addresses()->where('is_default', 1)->first();

        $certificationAgencies = \App\Models\CertificationAgency::where('is_active', 1)->orderBy('name')->get();

        $certifications = $customer->certifications()
            ->leftJoin('certification_agencies as ca', 'customer_certifications.certification_agency_id', '=', 'ca.id')
            ->select('customer_certifications.*', 'ca.name as agency_name', 'ca.code as agency_code')
            ->orderBy('issue_date', 'DESC')
            ->get();

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
                'credit_limit' => (float) ($_POST['credit_limit'] ?? 0),
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
            redirect("/store/customers/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/store/customers/{$id}/edit");
        }
    }

    public function delete(int $id)
    {
        if (!hasPermission('customers.delete')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/customers');
        }

        $customer = Customer::findOrFail($id);
        $customer->update(['is_active' => 0]);

        $_SESSION['flash_success'] = 'Customer deleted successfully';
        redirect('/store/customers');
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

    public function transactions(int $id)
    {
        if (!hasPermission('customers.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        $customer = Customer::findOrFail($id);
        $transactions = $customer->transactions()
            ->select('id', 'transaction_number', 'created_at', 'payment_method', 'status', 'total')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();

        jsonResponse($transactions);
    }

    public function exportCsv()
    {
        if (!hasPermission('customers.export')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/customers');
        }

        $customers = Customer::limit(10000)->get();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customers-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'ID',
            'Type',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Company',
            'Credit Limit',
            'Created At'
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

            $customer = Customer::findOrFail($id);
            if ($data['is_default']) {
                $customer->addresses()->update(['is_default' => 0]);
            }
            $customer->addresses()->create($data);

            $_SESSION['flash_success'] = 'Address added successfully';
            redirect("/store/customers/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/store/customers/{$id}");
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

            if ($data['is_default']) {
                \App\Models\CustomerAddress::where('customer_id', $id)->update(['is_default' => 0]);
            }

            $address = \App\Models\CustomerAddress::where('id', $address_id)->where('customer_id', $id)->firstOrFail();
            $address->update($data);

            $_SESSION['flash_success'] = 'Address updated successfully';
            redirect("/store/customers/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/store/customers/{$id}");
        }
    }

    public function deleteAddress(int $id, int $address_id)
    {
        if (!hasPermission('customers.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect("/store/customers/{$id}");
        }

        try {
            $address = \App\Models\CustomerAddress::where('id', $address_id)->where('customer_id', $id)->firstOrFail();
            $address->delete();

            $_SESSION['flash_success'] = 'Address deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        redirect("/store/customers/{$id}");
    }

    // ========== Phone Number Management ==========

    public function addPhone(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $customer = Customer::findOrFail($id);
            $customer->phones()->create([
                'phone_type' => sanitizeInput($_POST['phone_type'] ?? 'mobile'),
                'phone_number' => sanitizeInput($_POST['phone_number'] ?? ''),
                'extension' => sanitizeInput($_POST['extension'] ?? ''),
                'is_default' => isset($_POST['is_default']) ? 1 : 0,
                'can_sms' => isset($_POST['can_sms']) ? 1 : 0,
                'can_call' => isset($_POST['can_call']) ? 1 : 0,
                'notes' => sanitizeInput($_POST['notes'] ?? ''),
                'label' => sanitizeInput($_POST['label'] ?? '')
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
            $phone = \App\Models\CustomerPhone::where('id', $phoneId)->where('customer_id', $id)->firstOrFail();
            $phone->update([
                'phone_type' => sanitizeInput($_POST['phone_type'] ?? 'mobile'),
                'phone_number' => sanitizeInput($_POST['phone_number'] ?? ''),
                'extension' => sanitizeInput($_POST['extension'] ?? ''),
                'is_default' => isset($_POST['is_default']) ? 1 : 0,
                'can_sms' => isset($_POST['can_sms']) ? 1 : 0,
                'can_call' => isset($_POST['can_call']) ? 1 : 0,
                'notes' => sanitizeInput($_POST['notes'] ?? ''),
                'label' => sanitizeInput($_POST['label'] ?? '')
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
            \App\Models\CustomerPhone::where('id', $phoneId)->where('customer_id', $id)->delete();
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
            $customer = Customer::findOrFail($id);
            $customer->emails()->create([
                'email_type' => sanitizeInput($_POST['email_type'] ?? 'personal'),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'is_default' => isset($_POST['is_default']) ? 1 : 0,
                'can_market' => isset($_POST['can_market']) ? 1 : 0,
                'notes' => sanitizeInput($_POST['notes'] ?? ''),
                'label' => sanitizeInput($_POST['label'] ?? '')
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
            $email = \App\Models\CustomerEmail::where('id', $emailId)->where('customer_id', $id)->firstOrFail();
            $email->update([
                'email_type' => sanitizeInput($_POST['email_type'] ?? 'personal'),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'is_default' => isset($_POST['is_default']) ? 1 : 0,
                'can_market' => isset($_POST['can_market']) ? 1 : 0,
                'notes' => sanitizeInput($_POST['notes'] ?? ''),
                'label' => sanitizeInput($_POST['label'] ?? '')
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
            \App\Models\CustomerEmail::where('id', $emailId)->where('customer_id', $id)->delete();
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
            $customer = Customer::findOrFail($id);
            $customer->contacts()->create([
                'contact_type' => sanitizeInput($_POST['contact_type'] ?? 'emergency'),
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'relationship' => sanitizeInput($_POST['relationship'] ?? ''),
                'is_primary_emergency' => isset($_POST['is_primary_emergency']) ? 1 : 0,
                'notes' => sanitizeInput($_POST['notes'] ?? '')
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
            $contact = \App\Models\CustomerContact::where('id', $contactId)->where('customer_id', $id)->firstOrFail();
            $contact->update([
                'contact_type' => sanitizeInput($_POST['contact_type'] ?? 'emergency'),
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'relationship' => sanitizeInput($_POST['relationship'] ?? ''),
                'is_primary_emergency' => isset($_POST['is_primary_emergency']) ? 1 : 0,
                'notes' => sanitizeInput($_POST['notes'] ?? '')
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
            \App\Models\CustomerContact::where('id', $contactId)->where('customer_id', $id)->delete();
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
            $customer = Customer::findOrFail($id);
            $customer->certifications()->create([
                'certification_agency_id' => (int) ($_POST['certification_agency_id'] ?? 0),
                'certification_level' => sanitizeInput($_POST['certification_level'] ?? ''),
                'certification_number' => sanitizeInput($_POST['certification_number'] ?? ''),
                'issue_date' => !empty($_POST['issue_date']) ? $_POST['issue_date'] : null,
                'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null,
                'instructor_name' => sanitizeInput($_POST['instructor_name'] ?? ''),
                'notes' => sanitizeInput($_POST['notes'] ?? '')
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
            \App\Models\CustomerCertification::where('id', $certId)->where('customer_id', $id)->delete();
            jsonResponse(['success' => true, 'message' => 'Certification deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    // ========== Equipment Management ==========

    public function addEquipment(int $id)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $customer = Customer::findOrFail($id);
            $customer->equipment()->create([
                'serial_number' => sanitizeInput($_POST['serial_number'] ?? ''),
                'manufacturer' => sanitizeInput($_POST['manufacturer'] ?? ''),
                'model' => sanitizeInput($_POST['model'] ?? ''),
                'size' => sanitizeInput($_POST['size'] ?? ''),
                'material' => sanitizeInput($_POST['material'] ?? ''),
                'last_vip_date' => !empty($_POST['last_vip_date']) ? $_POST['last_vip_date'] : null,
                'last_hydro_date' => !empty($_POST['last_hydro_date']) ? $_POST['last_hydro_date'] : null,
                'notes' => sanitizeInput($_POST['notes'] ?? '')
            ]);

            $_SESSION['flash_success'] = 'Equipment added successfully';
            jsonResponse(['success' => true, 'message' => 'Equipment added successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function deleteEquipment(int $id, int $equipId)
    {
        if (!hasPermission('customers.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            // Optional: Check if used in air fills? For now just delete.
            // Ideally we soft delete or block if used.
            \App\Models\CustomerEquipment::where('id', $equipId)->where('customer_id', $id)->delete();

            jsonResponse(['success' => true, 'message' => 'Equipment deleted successfully']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
