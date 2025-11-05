<?php

namespace App\Controllers\API;

use App\Services\Courses\EnrollmentService;

class CourseScheduleController
{
    private EnrollmentService $enrollmentService;

    public function __construct()
    {
        $this->enrollmentService = new EnrollmentService();
    }

    /**
     * Get available schedules for a course
     * GET /store/api/courses/{id}/schedules
     */
    public function getAvailableSchedules(int $courseId): void
    {
        if (!hasPermission('pos.view')) {
            jsonResponse(['error' => 'Access denied'], 403);
            return;
        }

        try {
            $schedules = $this->enrollmentService->getAvailableSchedules($courseId);
            jsonResponse($schedules);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
