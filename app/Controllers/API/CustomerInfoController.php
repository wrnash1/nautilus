<?php

namespace App\Controllers\API;

use App\Core\Database;
use App\Core\Auth;

class CustomerInfoController
{
    /**
     * Get customer info for POS display
     * Returns photo and certifications
     */
    public function getPosInfo($id)
    {
        header('Content-Type: application/json');

        try {
            $db = Database::getInstance();

            // Get customer basic info with photo
            $stmt = $db->prepare("
                SELECT
                    id,
                    first_name,
                    last_name,
                    email,
                    phone,
                    photo_path
                FROM customers
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$customer) {
                http_response_code(404);
                echo json_encode(['error' => 'Customer not found']);
                return;
            }

            // Get customer certifications with agency info
            $stmt = $db->prepare("
                SELECT
                    cc.certification_number,
                    c.name as cert_name,
                    c.level as cert_level,
                    ca.name as agency_name,
                    ca.abbreviation as agency_abbreviation,
                    ca.primary_color as agency_color,
                    cc.issue_date,
                    cc.verification_status
                FROM customer_certifications cc
                INNER JOIN certifications c ON cc.certification_id = c.id
                INNER JOIN certification_agencies ca ON c.agency_id = ca.id
                WHERE cc.customer_id = ?
                AND cc.verification_status = 'verified'
                ORDER BY c.level DESC, cc.issue_date DESC
                LIMIT 5
            ");
            $stmt->execute([$id]);
            $certifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Return combined data
            echo json_encode([
                'id' => $customer['id'],
                'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'photo_path' => $customer['photo_path'],
                'certifications' => $certifications
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to fetch customer info',
                'message' => $e->getMessage()
            ]);
        }
    }
}
