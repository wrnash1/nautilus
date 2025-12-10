<?php

namespace App\Services\Travel;

use App\Core\Database;
use PDO;

class TravelPacketService
{
    /**
     * Create travel packet for trip
     */
    public function createTravelPacket(array $data, int $createdBy): int
    {
        $packetNumber = $this->generatePacketNumber();

        $sql = "
            INSERT INTO travel_packets (
                packet_number, trip_booking_id, destination_name, destination_contact_name,
                destination_email, destination_phone, departure_date, return_date,
                status, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)
        ";

        Database::query($sql, [
            $packetNumber,
            $data['trip_booking_id'] ?? null,
            $data['destination_name'],
            $data['destination_contact_name'] ?? null,
            $data['destination_email'] ?? null,
            $data['destination_phone'] ?? null,
            $data['departure_date'],
            $data['return_date'],
            $data['notes'] ?? null,
            $createdBy
        ]);

        return Database::lastInsertId();
    }

    /**
     * Add participant to travel packet
     */
    public function addParticipant(int $packetId, int $customerId, array $options = []): int
    {
        $sql = "
            INSERT INTO travel_packet_participants (
                travel_packet_id, customer_id, include_passport, include_medical,
                include_certifications, include_insurance, flight_number, arrival_time,
                departure_flight, departure_time, special_requests
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        Database::query($sql, [
            $packetId,
            $customerId,
            $options['include_passport'] ?? true,
            $options['include_medical'] ?? true,
            $options['include_certifications'] ?? true,
            $options['include_insurance'] ?? true,
            $options['flight_number'] ?? null,
            $options['arrival_time'] ?? null,
            $options['departure_flight'] ?? null,
            $options['departure_time'] ?? null,
            $options['special_requests'] ?? null
        ]);

        return Database::lastInsertId();
    }

    /**
     * Generate complete travel packet data
     */
    public function generatePacketData(int $packetId): array
    {
        // Get packet info
        $packet = $this->getPacket($packetId);

        if (!$packet) {
            throw new \Exception('Travel packet not found');
        }

        // Get all participants
        $participants = $this->getPacketParticipants($packetId);

        $packetData = [
            'packet_number' => $packet['packet_number'],
            'destination' => [
                'name' => $packet['destination_name'],
                'contact' => $packet['destination_contact_name'],
                'email' => $packet['destination_email'],
                'phone' => $packet['destination_phone']
            ],
            'travel_dates' => [
                'departure' => $packet['departure_date'],
                'return' => $packet['return_date']
            ],
            'participants' => []
        ];

        foreach ($participants as $participant) {
            $customerData = $this->compileCustomerData(
                $participant['customer_id'],
                $participant
            );

            $packetData['participants'][] = $customerData;
        }

        // Save packet data
        $this->savePacketData($packetId, $packetData);

        return $packetData;
    }

    /**
     * Compile complete customer data for travel
     */
    private function compileCustomerData(int $customerId, array $options): array
    {
        $customer = $this->getCustomer($customerId);

        $data = [
            'personal_info' => [
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'birth_date' => $customer['birth_date'],
                'photo' => $customer['photo_path']
            ],
            'emergency_contact' => [
                'name' => $customer['emergency_contact_name'],
                'phone' => $customer['emergency_contact_phone']
            ],
            'flight_info' => [
                'arrival_flight' => $options['flight_number'],
                'arrival_time' => $options['arrival_time'],
                'departure_flight' => $options['departure_flight'],
                'departure_time' => $options['departure_time']
            ],
            'special_requests' => $options['special_requests']
        ];

        // Add passport info if requested
        if ($options['include_passport']) {
            $data['passport'] = $this->getPassportInfo($customerId);
        }

        // Add medical info if requested
        if ($options['include_medical']) {
            $data['medical'] = $this->getMedicalInfo($customerId);
        }

        // Add certifications if requested
        if ($options['include_certifications']) {
            $data['certifications'] = $this->getCertifications($customerId);
        }

        // Add insurance if requested
        if ($options['include_insurance']) {
            $data['insurance'] = $this->getInsuranceInfo($customerId);
        }

        return $data;
    }

    /**
     * Get customer passport information
     */
    private function getPassportInfo(int $customerId): ?array
    {
        $sql = "
            SELECT *
            FROM customer_travel_documents
            WHERE customer_id = ?
            AND document_type = 'passport'
            AND (expiry_date IS NULL OR expiry_date > CURDATE())
            ORDER BY issue_date DESC
            LIMIT 1
        ";

        $passport = Database::fetchOne($sql, [$customerId]);

        if (!$passport) {
            return null;
        }

        return [
            'number' => $passport['document_number'],
            'issue_date' => $passport['issue_date'],
            'expiry_date' => $passport['expiry_date'],
            'issuing_country' => $passport['issuing_country']
        ];
    }

    /**
     * Get customer medical information
     */
    private function getMedicalInfo(int $customerId): ?array
    {
        $sql = "SELECT * FROM customer_medical_info WHERE customer_id = ?";
        $medical = Database::fetchOne($sql, [$customerId]);

        if (!$medical) {
            return null;
        }

        return [
            'blood_type' => $medical['blood_type'],
            'allergies' => $medical['allergies'],
            'medications' => $medical['medications'],
            'medical_conditions' => $medical['medical_conditions'],
            'physician_name' => $medical['physician_name'],
            'physician_phone' => $medical['physician_phone'],
            'medical_clearance_date' => $medical['medical_clearance_date'],
            'fitness_to_dive' => $medical['fitness_to_dive']
        ];
    }

    /**
     * Get customer certifications
     */
    private function getCertifications(int $customerId): array
    {
        $sql = "
            SELECT
                cc.certification_number,
                cc.issue_date,
                cc.expiry_date,
                cc.instructor_name,
                c.name as certification_name,
                c.level,
                c.code,
                ca.name as agency_name,
                ca.abbreviation as agency_code,
                ca.logo_path as agency_logo
            FROM customer_certifications cc
            JOIN certifications c ON cc.certification_id = c.id
            JOIN certification_agencies ca ON c.agency_id = ca.id
            WHERE cc.customer_id = ?
            AND cc.verification_status = 'verified'
            ORDER BY c.level DESC
        ";

        return Database::fetchAll($sql, [$customerId]);
    }

    /**
     * Get customer insurance information
     */
    private function getInsuranceInfo(int $customerId): ?array
    {
        $sql = "
            SELECT *
            FROM customer_travel_documents
            WHERE customer_id = ?
            AND document_type = 'travel_insurance'
            AND (expiry_date IS NULL OR expiry_date > CURDATE())
            ORDER BY issue_date DESC
            LIMIT 1
        ";

        $insurance = Database::fetchOne($sql, [$customerId]);

        if (!$insurance) {
            return null;
        }

        return [
            'policy_number' => $insurance['document_number'],
            'issue_date' => $insurance['issue_date'],
            'expiry_date' => $insurance['expiry_date'],
            'provider' => $insurance['notes']
        ];
    }

    /**
     * Generate PDF travel packet
     */
    public function generatePDF(int $packetId): string
    {
        $packetData = $this->generatePacketData($packetId);

        // TODO: Implement PDF generation using TCPDF
        // This would create a formatted PDF with all participant information

        $pdfPath = "storage/travel_packets/packet_{$packetId}.pdf";

        // Placeholder - would use TCPDF to generate actual PDF
        // $pdf = new \TCPDF();
        // ... PDF generation logic ...

        return $pdfPath;
    }

    /**
     * Send travel packet to destination
     */
    public function sendPacket(int $packetId): bool
    {
        $packet = $this->getPacket($packetId);

        if (!$packet || !$packet['destination_email']) {
            throw new \Exception('Cannot send packet - no destination email');
        }

        // Generate PDF
        $pdfPath = $this->generatePDF($packetId);

        // Send email with PDF attachment
        $subject = "Travel Packet - {$packet['packet_number']}";
        $body = $this->generateEmailBody($packet);

        // TODO: Implement email sending with attachment
        $sent = $this->sendEmailWithAttachment(
            $packet['destination_email'],
            $subject,
            $body,
            $pdfPath
        );

        if ($sent) {
            // Update packet status
            $sql = "UPDATE travel_packets SET status = 'sent', sent_at = NOW() WHERE id = ?";
            Database::query($sql, [$packetId]);
        }

        return $sent;
    }

    /**
     * Generate email body
     */
    private function generateEmailBody(array $packet): string
    {
        $participantCount = count($this->getPacketParticipants($packet['id']));

        return "
            <h2>Travel Packet: {$packet['packet_number']}</h2>
            <p>Dear {$packet['destination_contact_name']},</p>
            <p>Please find attached the travel packet for our upcoming group traveling to {$packet['destination_name']}.</p>
            <p><strong>Travel Dates:</strong> {$packet['departure_date']} to {$packet['return_date']}</p>
            <p><strong>Number of Travelers:</strong> {$participantCount}</p>
            <p>This packet includes all necessary information including:</p>
            <ul>
                <li>Personal information for each traveler</li>
                <li>Passport details</li>
                <li>Diving certifications and numbers</li>
                <li>Medical information</li>
                <li>Flight information</li>
                <li>Emergency contacts</li>
            </ul>
            <p>If you have any questions or need additional information, please don't hesitate to contact us.</p>
            <p>Best regards,<br>{$_ENV['APP_NAME']}</p>
        ";
    }

    /**
     * Send email with attachment (placeholder)
     */
    private function sendEmailWithAttachment(string $to, string $subject, string $body, string $attachmentPath): bool
    {
        // TODO: Implement with PHPMailer
        return true; // Placeholder
    }

    /**
     * Get packet
     */
    private function getPacket(int $packetId): ?array
    {
        $sql = "SELECT * FROM travel_packets WHERE id = ?";
        return Database::fetchOne($sql, [$packetId]);
    }

    /**
     * Get packet participants
     */
    private function getPacketParticipants(int $packetId): array
    {
        $sql = "
            SELECT tpp.*, c.first_name, c.last_name, c.email, c.phone
            FROM travel_packet_participants tpp
            JOIN customers c ON tpp.customer_id = c.id
            WHERE tpp.travel_packet_id = ?
        ";

        return Database::fetchAll($sql, [$packetId]);
    }

    /**
     * Get customer
     */
    private function getCustomer(int $customerId): ?array
    {
        $sql = "SELECT * FROM customers WHERE id = ?";
        return Database::fetchOne($sql, [$customerId]);
    }

    /**
     * Save packet data as JSON
     */
    private function savePacketData(int $packetId, array $data): void
    {
        $sql = "UPDATE travel_packets SET packet_data = ? WHERE id = ?";
        Database::query($sql, [json_encode($data), $packetId]);
    }

    /**
     * Generate unique packet number
     */
    private function generatePacketNumber(): string
    {
        return 'PKT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
}
