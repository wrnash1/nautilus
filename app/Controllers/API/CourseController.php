<?php

namespace App\Controllers\API;

use App\Services\Courses\CourseService;

class CourseController
{
    private $courseService;
    
    public function __construct()
    {
        $this->courseService = new CourseService();
    }
    
    public function index()
    {
        $courses = $this->courseService->getAllCourses();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $courses]);
    }
    
    public function show($id)
    {
        $course = $this->courseService->getCourseById($id);
        
        if (!$course) {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'message' => 'Course not found']);
            return;
        }
        
        $schedules = $this->courseService->getCourseSchedules($id);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => array_merge($course, ['schedules' => $schedules])]);
    }
    
    public function enroll($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $enrollmentId = $this->courseService->enrollStudent($id, $input['schedule_id'] ?? 0, $input['customer_id'] ?? 0);
        
        if ($enrollmentId) {
            http_response_code(201);
            echo json_encode(['success' => true, 'enrollment_id' => $enrollmentId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to enroll in course']);
        }
    }
}
