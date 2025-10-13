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
        if (!hasPermission('courses.view')) {
            redirect('/');
        }
        
        $filters = [
            'search' => $_GET['search'] ?? ''
        ];
        
        $courses = $this->courseService->getCourseList($filters);
        
        $pageTitle = 'Courses';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function create()
    {
        if (!hasPermission('courses.create')) {
            redirect('/courses');
        }
        
        $pageTitle = 'Create Course';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function store()
    {
        if (!hasPermission('courses.create')) {
            redirect('/courses');
        }
        
        $id = $this->courseService->createCourse($_POST);
        
        $_SESSION['flash_success'] = 'Course created successfully!';
        redirect('/courses/' . $id);
    }
    
    public function show(int $id)
    {
        if (!hasPermission('courses.view')) {
            redirect('/courses');
        }
        
        $course = $this->courseService->getCourseById($id);
        
        if (!$course) {
            $_SESSION['flash_error'] = 'Course not found';
            redirect('/courses');
        }
        
        $schedules = $this->courseService->getScheduleList(['course_id' => $id]);
        
        $pageTitle = $course['name'];
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function edit(int $id)
    {
        if (!hasPermission('courses.edit')) {
            redirect('/courses');
        }
        
        $course = $this->courseService->getCourseById($id);
        
        if (!$course) {
            $_SESSION['flash_error'] = 'Course not found';
            redirect('/courses');
        }
        
        $pageTitle = 'Edit Course';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/edit.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function update(int $id)
    {
        if (!hasPermission('courses.edit')) {
            redirect('/courses');
        }
        
        $this->courseService->updateCourse($id, $_POST);
        
        $_SESSION['flash_success'] = 'Course updated successfully!';
        redirect('/courses/' . $id);
    }
    
    public function delete(int $id)
    {
        if (!hasPermission('courses.delete')) {
            redirect('/courses');
        }
        
        $this->courseService->deleteCourse($id);
        
        $_SESSION['flash_success'] = 'Course deleted successfully!';
        redirect('/courses');
    }
    
    public function schedules()
    {
        if (!hasPermission('courses.view')) {
            redirect('/');
        }
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $schedules = $this->courseService->getScheduleList($filters);
        $courses = $this->courseService->getCourseList([]);
        
        $pageTitle = 'Course Schedules';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/schedules/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function createSchedule()
    {
        if (!hasPermission('courses.create')) {
            redirect('/courses/schedules');
        }
        
        $courses = $this->courseService->getCourseList([]);
        
        $pageTitle = 'Create Schedule';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/schedules/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function storeSchedule()
    {
        if (!hasPermission('courses.create')) {
            redirect('/courses/schedules');
        }
        
        $id = $this->courseService->createSchedule($_POST);
        
        $_SESSION['flash_success'] = 'Schedule created successfully!';
        redirect('/courses/schedules/' . $id);
    }
    
    public function showSchedule(int $id)
    {
        if (!hasPermission('courses.view')) {
            redirect('/courses/schedules');
        }
        
        $schedule = $this->courseService->getScheduleById($id);
        
        if (!$schedule) {
            $_SESSION['flash_error'] = 'Schedule not found';
            redirect('/courses/schedules');
        }
        
        $enrollments = $this->courseService->getScheduleEnrollments($id);
        
        $pageTitle = 'Course Schedule';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/schedules/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function enrollments()
    {
        if (!hasPermission('courses.view')) {
            redirect('/');
        }
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $enrollments = $this->courseService->getEnrollmentList($filters);
        
        $pageTitle = 'Enrollments';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/enrollments/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function showEnrollment(int $id)
    {
        if (!hasPermission('courses.view')) {
            redirect('/courses/enrollments');
        }
        
        $enrollment = $this->courseService->getEnrollmentById($id);
        
        if (!$enrollment) {
            $_SESSION['flash_error'] = 'Enrollment not found';
            redirect('/courses/enrollments');
        }
        
        $attendance = $this->courseService->getEnrollmentAttendance($id);
        
        $pageTitle = 'Enrollment Details';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/enrollments/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function markAttendance(int $id)
    {
        if (!hasPermission('courses.edit')) {
            redirect('/courses/enrollments/' . $id);
        }
        
        $this->courseService->markAttendance($id, $_POST);
        
        $_SESSION['flash_success'] = 'Attendance marked successfully!';
        redirect('/courses/enrollments/' . $id);
    }
    
    public function updateGrade(int $id)
    {
        if (!hasPermission('courses.edit')) {
            redirect('/courses/enrollments/' . $id);
        }
        
        $this->courseService->updateGrade($id, $_POST['grade'], $_POST['cert_number'] ?? null);
        
        $_SESSION['flash_success'] = 'Grade updated successfully!';
        redirect('/courses/enrollments/' . $id);
    }
}
