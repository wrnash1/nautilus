<?php

namespace App\Services\Courses;

use App\Core\Database;
use PDO;

class PrerequisiteService
{
    /**
     * Check if customer meets course prerequisites
     *
     * @param int $customerId
     * @param int $courseId
     * @return array ['meets_requirements' => bool, 'missing_requirements' => array, 'details' => array]
     */
    public function checkPrerequisites(int $customerId, int $courseId): array
    {
        $course = $this->getCourse($courseId);

        if (!$course) {
            return [
                'meets_requirements' => false,
                'missing_requirements' => ['Course not found'],
                'details' => []
            ];
        }

        // If no prerequisites, customer can enroll
        if (empty($course['prerequisites'])) {
            return [
                'meets_requirements' => true,
                'missing_requirements' => [],
                'details' => []
            ];
        }

        $prerequisites = json_decode($course['prerequisites'], true);
        if (!is_array($prerequisites)) {
            $prerequisites = [];
        }

        $missingRequirements = [];
        $details = [];

        // Get customer's certifications
        $customerCerts = $this->getCustomerCertifications($customerId);

        foreach ($prerequisites as $prereq) {
            if (isset($prereq['certification'])) {
                // Check certification requirement
                $hasCert = $this->hasRequiredCertification(
                    $customerCerts,
                    $prereq['certification'],
                    $prereq['level'] ?? null
                );

                if (!$hasCert) {
                    $missingRequirements[] = $prereq['certification'];
                    $details[] = [
                        'type' => 'certification',
                        'requirement' => $prereq['certification'],
                        'status' => 'missing'
                    ];
                } else {
                    $details[] = [
                        'type' => 'certification',
                        'requirement' => $prereq['certification'],
                        'status' => 'met'
                    ];
                }
            }

            if (isset($prereq['logged_dives'])) {
                // Check logged dives requirement
                $loggedDives = $this->getCustomerLoggedDives($customerId);

                if ($loggedDives < $prereq['logged_dives']) {
                    $missingRequirements[] = "{$prereq['logged_dives']} logged dives (has {$loggedDives})";
                    $details[] = [
                        'type' => 'logged_dives',
                        'required' => $prereq['logged_dives'],
                        'actual' => $loggedDives,
                        'status' => 'missing'
                    ];
                } else {
                    $details[] = [
                        'type' => 'logged_dives',
                        'required' => $prereq['logged_dives'],
                        'actual' => $loggedDives,
                        'status' => 'met'
                    ];
                }
            }

            if (isset($prereq['age'])) {
                // Check age requirement
                $age = $this->getCustomerAge($customerId);

                if ($age === null) {
                    $missingRequirements[] = "Age verification required (must be {$prereq['age']}+)";
                    $details[] = [
                        'type' => 'age',
                        'required' => $prereq['age'],
                        'actual' => 'unknown',
                        'status' => 'missing'
                    ];
                } elseif ($age < $prereq['age']) {
                    $missingRequirements[] = "Must be at least {$prereq['age']} years old (current age: {$age})";
                    $details[] = [
                        'type' => 'age',
                        'required' => $prereq['age'],
                        'actual' => $age,
                        'status' => 'missing'
                    ];
                } else {
                    $details[] = [
                        'type' => 'age',
                        'required' => $prereq['age'],
                        'actual' => $age,
                        'status' => 'met'
                    ];
                }
            }

            if (isset($prereq['specialties_count'])) {
                // Check specialty certifications count
                $specialtiesCount = $this->getCustomerSpecialtiesCount($customerId);

                if ($specialtiesCount < $prereq['specialties_count']) {
                    $missingRequirements[] = "{$prereq['specialties_count']} specialty certifications (has {$specialtiesCount})";
                    $details[] = [
                        'type' => 'specialties',
                        'required' => $prereq['specialties_count'],
                        'actual' => $specialtiesCount,
                        'status' => 'missing'
                    ];
                } else {
                    $details[] = [
                        'type' => 'specialties',
                        'required' => $prereq['specialties_count'],
                        'actual' => $specialtiesCount,
                        'status' => 'met'
                    ];
                }
            }

            if (isset($prereq['medical_clearance'])) {
                // Check medical clearance
                $hasMedicalClearance = $this->hasMedicalClearance($customerId);

                if (!$hasMedicalClearance) {
                    $missingRequirements[] = "Current medical clearance required";
                    $details[] = [
                        'type' => 'medical_clearance',
                        'status' => 'missing'
                    ];
                } else {
                    $details[] = [
                        'type' => 'medical_clearance',
                        'status' => 'met'
                    ];
                }
            }
        }

        return [
            'meets_requirements' => empty($missingRequirements),
            'missing_requirements' => $missingRequirements,
            'details' => $details,
            'course_name' => $course['name']
        ];
    }

    /**
     * Get course details
     */
    private function getCourse(int $courseId): ?array
    {
        $sql = "SELECT * FROM courses WHERE id = ?";
        return Database::fetchOne($sql, [$courseId]);
    }

    /**
     * Get customer's certifications
     */
    private function getCustomerCertifications(int $customerId): array
    {
        $sql = "
            SELECT cc.*, c.name, c.level, c.code, ca.abbreviation as agency
            FROM customer_certifications cc
            JOIN certifications c ON cc.certification_id = c.id
            JOIN certification_agencies ca ON c.agency_id = ca.id
            WHERE cc.customer_id = ?
            AND cc.verification_status IN ('verified', 'pending')
            ORDER BY c.level DESC
        ";

        return Database::fetchAll($sql, [$customerId]);
    }

    /**
     * Check if customer has required certification
     */
    private function hasRequiredCertification(array $customerCerts, string $requiredCert, ??int $minLevel = null): bool
    {
        foreach ($customerCerts as $cert) {
            // Check by certification name or code
            if (stripos($cert['name'], $requiredCert) !== false ||
                stripos($cert['code'], $requiredCert) !== false) {

                // If level is specified, check if customer's cert meets or exceeds level
                if ($minLevel !== null && $cert['level'] < $minLevel) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Get customer's total logged dives
     * Note: This would need a dive log table to be fully implemented
     */
    private function getCustomerLoggedDives(int $customerId): int
    {
        // For now, return 0 - would need dive log implementation
        // Future: COUNT from dive_log table
        $sql = "
            SELECT COUNT(*) as count FROM trip_participants tp
            JOIN trip_schedules ts ON tp.trip_schedule_id = ts.id
            WHERE tp.customer_id = ?
            AND ts.status = 'completed'
        ";

        $result = Database::fetchOne($sql, [$customerId]);
        return $result['count'] ?? 0;
    }

    /**
     * Get customer's age
     */
    private function getCustomerAge(int $customerId): ?int
    {
        $sql = "SELECT birth_date FROM customers WHERE id = ?";
        $customer = Database::fetchOne($sql, [$customerId]);

        if (!$customer || !$customer['birth_date']) {
            return null;
        }

        $birthDate = new \DateTime($customer['birth_date']);
        $today = new \DateTime();
        $age = $today->diff($birthDate)->y;

        return $age;
    }

    /**
     * Get customer's specialty certifications count
     */
    private function getCustomerSpecialtiesCount(int $customerId): int
    {
        // Specialty certifications typically have level 3-5 and are not core certs
        $sql = "
            SELECT COUNT(*) as count
            FROM customer_certifications cc
            JOIN certifications c ON cc.certification_id = c.id
            WHERE cc.customer_id = ?
            AND cc.verification_status IN ('verified', 'pending')
            AND c.level BETWEEN 3 AND 5
            AND c.name NOT LIKE '%Advanced%'
            AND c.name NOT LIKE '%Rescue%'
            AND c.name NOT LIKE '%Master%'
        ";

        $result = Database::fetchOne($sql, [$customerId]);
        return $result['count'] ?? 0;
    }

    /**
     * Check if customer has current medical clearance
     */
    private function hasMedicalClearance(int $customerId): bool
    {
        $sql = "
            SELECT medical_clearance_date, fitness_to_dive
            FROM customer_medical_info
            WHERE customer_id = ?
        ";

        $medicalInfo = Database::fetchOne($sql, [$customerId]);

        if (!$medicalInfo) {
            return false;
        }

        if (!$medicalInfo['fitness_to_dive']) {
            return false;
        }

        if (!$medicalInfo['medical_clearance_date']) {
            return false;
        }

        // Check if medical clearance is within last 12 months
        $clearanceDate = new \DateTime($medicalInfo['medical_clearance_date']);
        $today = new \DateTime();
        $monthsDiff = $today->diff($clearanceDate)->m + ($today->diff($clearanceDate)->y * 12);

        return $monthsDiff <= 12;
    }

    /**
     * Get prerequisite-friendly course list for a customer
     */
    public function getAvailableCoursesForCustomer(int $customerId): array
    {
        $sql = "SELECT * FROM courses WHERE is_active = 1 ORDER BY name";
        $courses = Database::fetchAll($sql);

        $availableCourses = [];

        foreach ($courses as $course) {
            $check = $this->checkPrerequisites($customerId, $course['id']);
            $course['can_enroll'] = $check['meets_requirements'];
            $course['missing_requirements'] = $check['missing_requirements'];
            $course['prerequisite_details'] = $check['details'];

            $availableCourses[] = $course;
        }

        return $availableCourses;
    }

    /**
     * Get highest certification level for customer
     */
    public function getHighestCertificationLevel(int $customerId): array
    {
        $sql = "
            SELECT cc.*, c.name, c.level, c.code, ca.abbreviation as agency, ca.name as agency_name, ca.logo_path
            FROM customer_certifications cc
            JOIN certifications c ON cc.certification_id = c.id
            JOIN certification_agencies ca ON c.agency_id = ca.id
            WHERE cc.customer_id = ?
            AND cc.verification_status IN ('verified', 'pending')
            ORDER BY c.level DESC
            LIMIT 1
        ";

        return Database::fetchOne($sql, [$customerId]) ?? [];
    }
}
