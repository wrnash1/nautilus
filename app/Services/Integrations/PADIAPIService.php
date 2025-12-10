<?php

namespace App\Services\Integrations;

/**
 * PADI API Integration Service
 * Handles automated submissions to PADI systems
 */
class PADIAPIService
{
    private string $apiKey;
    private string $storeNumber;
    private string $baseUrl = 'https://api.padi.com/v1'; // Example endpoint
    private bool $sandboxMode;

    public function __construct()
    {
        $this->apiKey = $_ENV['PADI_API_KEY'] ?? '';
        $this->storeNumber = $_ENV['PADI_STORE_NUMBER'] ?? '';
        $this->sandboxMode = ($_ENV['PADI_SANDBOX_MODE'] ?? 'true') === 'true';

        if ($this->sandboxMode) {
            $this->baseUrl = 'https://sandbox-api.padi.com/v1';
        }
    }

    /**
     * Submit training completion to PADI
     */
    public function submitTrainingCompletion(array $studentData, array $courseData): array
    {
        $payload = [
            'store_number' => $this->storeNumber,
            'instructor' => [
                'instructor_number' => $courseData['instructor_number'],
                'name' => $courseData['instructor_name']
            ],
            'student' => [
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'email' => $studentData['email'],
                'date_of_birth' => $studentData['date_of_birth'],
                'address' => $studentData['address'] ?? null,
                'phone' => $studentData['phone'] ?? null
            ],
            'course' => [
                'course_code' => $courseData['course_code'],
                'completion_date' => $courseData['completion_date'],
                'start_date' => $courseData['start_date'],
                'certification_number' => $courseData['certification_number'] ?? null
            ],
            'skills_completed' => $courseData['skills_completed'] ?? [],
            'dive_sites' => $courseData['dive_sites'] ?? [],
            'elearning_completed' => $courseData['elearning_completed'] ?? false
        ];

        return $this->makeRequest('POST', '/certifications/submit', $payload);
    }

    /**
     * Verify existing PADI certification
     */
    public function verifyCertification(string $certNumber, string $lastName): array
    {
        $payload = [
            'certification_number' => $certNumber,
            'last_name' => $lastName
        ];

        return $this->makeRequest('POST', '/certifications/verify', $payload);
    }

    /**
     * Request eCard issuance
     */
    public function requestECard(string $certNumber, string $email): array
    {
        $payload = [
            'certification_number' => $certNumber,
            'email' => $email,
            'format' => 'ecard'
        ];

        return $this->makeRequest('POST', '/ecards/issue', $payload);
    }

    /**
     * Get instructor status
     */
    public function getInstructorStatus(string $instructorNumber): array
    {
        return $this->makeRequest('GET', "/instructors/{$instructorNumber}/status");
    }

    /**
     * Submit quality assurance questionnaire
     */
    public function submitQualityAssurance(array $qaData): array
    {
        $payload = [
            'store_number' => $this->storeNumber,
            'course_id' => $qaData['course_id'],
            'student_id' => $qaData['student_id'],
            'responses' => $qaData['responses'],
            'satisfaction_rating' => $qaData['satisfaction_rating'],
            'submitted_date' => date('Y-m-d')
        ];

        return $this->makeRequest('POST', '/quality-assurance/submit', $payload);
    }

    /**
     * Get course materials inventory
     */
    public function getCourseMaterialsInventory(): array
    {
        return $this->makeRequest('GET', "/stores/{$this->storeNumber}/materials");
    }

    /**
     * Order course materials
     */
    public function orderCourseMaterials(array $materials): array
    {
        $payload = [
            'store_number' => $this->storeNumber,
            'materials' => $materials,
            'shipping_address' => $materials['shipping_address'] ?? null
        ];

        return $this->makeRequest('POST', '/materials/order', $payload);
    }

    /**
     * Submit incident report (Form 10120)
     */
    public function submitIncidentReport(array $incidentData): array
    {
        $payload = [
            'store_number' => $this->storeNumber,
            'incident_type' => $incidentData['incident_type'],
            'incident_date' => $incidentData['incident_date'],
            'location' => $incidentData['location'],
            'diver_info' => [
                'name' => $incidentData['diver_name'],
                'certification_number' => $incidentData['cert_number'] ?? null,
                'experience_level' => $incidentData['experience_level'] ?? null
            ],
            'incident_description' => $incidentData['description'],
            'injuries' => $incidentData['injuries'] ?? [],
            'equipment_issues' => $incidentData['equipment_issues'] ?? [],
            'witness_statements' => $incidentData['witnesses'] ?? [],
            'actions_taken' => $incidentData['actions_taken'] ?? null,
            'medical_treatment' => $incidentData['medical_treatment'] ?? false,
            'reported_by' => $incidentData['reported_by']
        ];

        return $this->makeRequest('POST', '/incidents/report', $payload);
    }

    /**
     * Get store account status
     */
    public function getStoreAccountStatus(): array
    {
        return $this->makeRequest('GET', "/stores/{$this->storeNumber}/status");
    }

    /**
     * Sync certifications batch
     */
    public function batchSyncCertifications(array $certifications): array
    {
        $payload = [
            'store_number' => $this->storeNumber,
            'certifications' => $certifications
        ];

        return $this->makeRequest('POST', '/certifications/batch-sync', $payload);
    }

    /**
     * Make HTTP request to PADI API
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'PADI API key not configured',
                'sandbox_mode' => $this->sandboxMode
            ];
        }

        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'X-PADI-Store-Number: ' . $this->storeNumber
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL error: ' . $error,
                'http_code' => $httpCode
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $responseData,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'API request failed',
                'http_code' => $httpCode,
                'response' => $responseData
            ];
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        return $this->makeRequest('GET', '/health');
    }

    /**
     * Get available courses from PADI catalog
     */
    public function getCourseCatalog(): array
    {
        return $this->makeRequest('GET', '/courses/catalog');
    }

    /**
     * Log API request for audit trail
     */
    private function logAPIRequest(string $endpoint, string $method, array $data, array $response): void
    {
        // Log to database or file
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'method' => $method,
            'request_data' => $data,
            'response' => $response,
            'http_code' => $response['http_code'] ?? null,
            'success' => $response['success'] ?? false
        ];

        // Could store in database table: padi_api_log
        error_log('PADI API: ' . json_encode($logEntry));
    }
}
