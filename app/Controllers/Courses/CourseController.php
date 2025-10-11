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
            header('Location: /');
            exit;
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
            header('Location: /courses');
            exit;
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
            header('Location: /courses');
            exit;
        }
        
        $id = $this->courseService->createCourse($_POST);
        
        $_SESSION['flash_success'] = 'Course created successfully!';
        header('Location: /courses/' . $id);
        exit;
    }
    
    public function show(int $id)
    {
        if (!hasPermission('courses.view')) {
            header('Location: /courses');
            exit;
        }
        
        $course = $this->courseService->getCourseById($id);
        
        if (!$course) {
            $_SESSION['flash_error'] = 'Course not found';
            header('Location: /courses');
            exit;
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
            header('Location: /courses');
            exit;
        }
        
        $course = $this->courseService->getCourseById($id);
        
        if (!$course) {
            $_SESSION['flash_error'] = 'Course not found';
            header('Location: /courses');
            exit;
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
            header('Location: /courses');
            exit;
        }
        
        $this->courseService->updateCourse($id, $_POST);
        
        $_SESSION['flash_success'] = 'Course updated successfully!';
        header('Location: /courses/' . $id);
        exit;
    }
    
    public function delete(int $id)
    {
        if (!hasPermission('courses.delete')) {
            header('Location: /courses');
            exit;
        }
        
        $this->courseService->deleteCourse($id);
        
        $_SESSION['flash_success'] = 'Course deleted successfully!';
        header('Location: /courses');
        exit;
    }
    
    public function schedules()
    {
        if (!hasPermission('courses.view')) {
            header('Location: /');
            exit;
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
            header('Location: /courses/schedules');
            exit;
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
            header('Location: /courses/schedules');
            exit;
        }
        
        $id = $this->courseService->createSchedule($_POST);
        
        $_SESSION['flash_success'] = 'Schedule created successfully!';
        header('Location: /courses/schedules/' . $id);
        exit;
    }
    
    public function showSchedule(int $id)
    {
        if (!hasPermission('courses.view')) {
            header('Location: /courses/schedules');
            exit;
        }
        
        $schedule = $this->courseService->getScheduleById($id);
        
        if (!$schedule) {
            $_SESSION['flash_error'] = 'Schedule not found';
            header('Location: /courses/schedules');
            exit;
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
            header('Location: /');
            exit;
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
            header('Location: /courses/enrollments');
            exit;
        }
        
        $enrollment = $this->courseService->getEnrollmentById($id);
        
        if (!$enrollment) {
            $_SESSION['flash_error'] = 'Enrollment not found';
            header('Location: /courses/enrollments');
            exit;
        }
        
        $pageTitle = 'Enrollment Details';
        $activeMenu = 'courses';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/courses/enrollments/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
}
