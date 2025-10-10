<?php

namespace App\Services\Courses;

class CourseService
{
    public function enrollStudent(int $scheduleId, int $customerId): int
    {
        
        return 0;
    }
    
    public function recordAttendance(int $enrollmentId, array $data): bool
    {
        
        return false;
    }
    
    public function submitCertification(int $enrollmentId): bool
    {
        
        return false;
    }
}
