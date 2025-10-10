<?php

namespace App\Controllers\Courses;

use App\Services\Courses\CourseService;

class CourseController
{
    private CourseService $courseService;
    
    public function __construct()
    {
        $this->courseService = new CourseService();
    }
    
    public function index()
    {
        
    }
    
    public function schedule(int $courseId)
    {
        
    }
    
    public function enroll()
    {
        
    }
    
    public function attendance(int $enrollmentId)
    {
        
    }
    
    public function certify(int $enrollmentId)
    {
        
    }
}
