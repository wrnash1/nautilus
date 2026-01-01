<?php

namespace App\Services\CRM;

use App\Models\Customer;
use App\Core\Database;

class CustomerService
{
    public function createCustomer(array $data): int
    {
        $this->validateCustomerData($data);

        $customer = Customer::create($data);
        return $customer->id;
    }

    public function updateCustomer(int $id, array $data): bool
    {
        $this->validateCustomerData($data, $id);

        $customer = Customer::findOrFail($id);
        return $customer->update($data);
    }

    public function search(string $query): array
    {
        // Eloquent search returns Collection
        $customers = Customer::search($query);

        // Enrich with certification info
        $results = [];
        foreach ($customers as $customerModel) {
            $customer = $customerModel->toArray();

            // Get highest certification (using relationship if possible, but complex query originally)
            // Or just use query builder for speed/complexity handling
            $highestCert = Database::fetchOne("
                SELECT
                    c.name as certification_name,
                    c.level as certification_level,
                    ca.id as agency_id,
                    ca.name as agency_name,
                    ca.logo_path
                FROM customer_certifications cc
                JOIN certifications c ON cc.certification_id = c.id
                JOIN certification_agencies ca ON c.agency_id = ca.id
                WHERE cc.customer_id = ?
                ORDER BY c.level DESC, cc.issue_date DESC
                LIMIT 1
            ", [$customer['id']]);

            $customer['certification'] = $highestCert ?: null;

            // Add placeholder photo if missing
            if (empty($customer['photo_url'])) {
                $customer['photo_url'] = '/assets/img/default-avatar.png';
            }
            $results[] = $customer;
        }

        return $results;
    }

    public function getCustomer360(int $id): array
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return [];
        }

        // Convert customer to array for view compatibility (views might use array access)
        // Eloquent model supports array access, but toArray() is rigorous.
        $customerArray = $customer->toArray();

        // Use relationships for simple fetchs
        $addresses = $customer->addresses()->orderBy('is_default', 'desc')->get()->toArray();
        $transactions = $customer->transactions()->orderBy('created_at', 'desc')->get()->toArray();
        $certifications = $customer->certifications()
            ->leftJoin('certifications as c', 'customer_certifications.certification_id', '=', 'c.id')
            ->leftJoin('certification_agencies as ca', 'c.agency_id', '=', 'ca.id')
            ->select('customer_certifications.*', 'ca.name as agency_name', 'ca.abbreviation as agency_code', 'c.name as certification_name')
            ->orderBy('issue_date', 'desc')
            ->get()
            ->toArray();

        // Get highest certification with agency info
        // Keeping raw query for complex join logic unless simple
        $highestCert = null;
        if (!empty($certifications)) {
            $highestCert = Database::fetchOne("
                SELECT
                    cc.*,
                    c.name as certification_name,
                    c.level as certification_level,
                    c.code as certification_code,
                    ca.name as agency_name,
                    ca.abbreviation as agency_abbreviation,
                    ca.logo_path,
                    ca.primary_color
                FROM customer_certifications cc
                JOIN certifications c ON cc.certification_id = c.id
                JOIN certification_agencies ca ON c.agency_id = ca.id
                WHERE cc.customer_id = ?
                AND cc.verification_status IN ('verified', 'pending')
                ORDER BY c.level DESC, cc.issue_date DESC
                LIMIT 1
            ", [$id]);
        }

        $phones = $customer->phones()->orderBy('is_default', 'desc')->orderBy('phone_type')->get()->toArray();
        $emails = $customer->emails()->orderBy('is_default', 'desc')->orderBy('email_type')->get()->toArray();
        $contacts = $customer->contacts()->orderBy('is_primary_emergency', 'desc')->orderBy('last_name')->get()->toArray();

        // Tags - keeping Database query as no relationship defined yet
        $customerTags = Database::fetchAll("
            SELECT t.*, cta.assigned_at, cta.notes,
                   CONCAT(u.first_name, ' ', u.last_name) as assigned_by_name
            FROM customer_tag_assignments cta
            INNER JOIN customer_tags t ON cta.tag_id = t.id
            LEFT JOIN users u ON cta.assigned_by = u.id
            WHERE cta.customer_id = ?
            ORDER BY t.display_order, t.name
        ", [$id]);

        return [
            'customer' => $customerArray,
            'addresses' => $addresses,
            'transactions' => $transactions,
            'certifications' => $certifications,
            'highestCert' => $highestCert,
            'phones' => $phones,
            'emails' => $emails,
            'contacts' => $contacts,
            'customerTags' => $customerTags,
            'equipment' => $customer->equipment()->orderBy('serial_number')->get()->toArray()
        ];
    }

    private function validateCustomerData(array $data, ?int $id = null): void
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }

        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }

            // Replaced raw query with Eloquent count
            $query = Customer::where('email', $data['email'])
                ->where('is_active', 1);

            if ($id) {
                $query->where('id', '!=', $id);
            }

            if ($query->count() > 0) {
                $errors[] = 'Email already exists';
            }
        }

        if (($data['customer_type'] ?? 'B2C') === 'B2B' && empty($data['company_name'])) {
            $errors[] = 'Company name is required for B2B customers';
        }

        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }
    }

    public function getCustomerStatus(int $id): array
    {
        // 1. Outstanding Balance (Pending transactions)
        // Using Eloquent sum
        $outstanding = \App\Models\Transaction::where('customer_id', $id)
            ->where('status', 'pending')
            ->sum('total');

        // 2. Active Work Orders
        $workOrders = Database::fetchAll("
            SELECT id, work_order_number, equipment_type, status, estimated_cost
            FROM work_orders
            WHERE customer_id = ? 
            AND status NOT IN ('completed', 'cancelled', 'picked_up')
        ", [$id]);

        // 3. Upcoming Courses
        $courses = Database::fetchAll("
            SELECT cs.id, c.name, cs.start_date, ce.status
            FROM course_enrollments ce
            JOIN course_schedules cs ON ce.schedule_id = cs.id
            JOIN courses c ON cs.course_id = c.id
            WHERE ce.student_id = ?
            AND cs.start_date >= CURDATE()
            AND ce.status IN ('enrolled', 'confirmed')
            ORDER BY cs.start_date ASC
        ", [$id]);

        // 4. Upcoming Trips
        $trips = Database::fetchAll("
            SELECT ts.id, t.name, ts.departure_date, tb.status
            FROM trip_bookings tb
            JOIN trip_schedules ts ON tb.trip_schedule_id = ts.id
            JOIN trips t ON ts.trip_id = t.id
            WHERE tb.customer_id = ?
            AND ts.departure_date >= CURDATE()
            AND tb.status IN ('confirmed', 'paid')
            ORDER BY ts.departure_date ASC
        ", [$id]);

        return [
            'outstanding_balance' => (float) $outstanding,
            'work_orders' => $workOrders ?? [],
            'upcoming_courses' => $courses ?? [],
            'upcoming_trips' => $trips ?? []
        ];
    }
}
