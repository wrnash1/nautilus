<?php

namespace Tests\Unit\Services\Courses;

use Tests\TestCase;
use App\Services\Courses\CourseService;

class CourseServiceTest extends TestCase
{
    private CourseService $courseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->courseService = new CourseService();
    }

    public function testCreateCourse(): void
    {
        $user = $this->createTestUser();
        $_SESSION['user_id'] = $user['id'];

        $courseData = [
            'course_code' => 'OW101',
            'name' => 'Open Water Diver',
            'description' => 'Entry level scuba certification',
            'duration_days' => 3,
            'max_students' => 8,
            'prerequisites' => null,
            'price' => 399.00,
            'created_by' => $user['id']
        ];

        $courseId = $this->courseService->createCourse($courseData);

        $this->assertIsInt($courseId);
        $this->assertGreaterThan(0, $courseId);

        $this->assertDatabaseHas('courses', [
            'id' => $courseId,
            'course_code' => 'OW101',
            'name' => 'Open Water Diver'
        ]);
    }

    public function testGetCourseById(): void
    {
        $user = $this->createTestUser();

        // Create a test course
        $stmt = $this->db->prepare(
            "INSERT INTO courses (course_code, name, description, duration_days, max_students, price, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            'AOW101',
            'Advanced Open Water',
            'Advanced diving skills',
            2,
            6,
            349.00,
            $user['id'],
            date('Y-m-d H:i:s')
        ]);

        $courseId = (int)$this->db->lastInsertId();

        // Retrieve the course
        $course = $this->courseService->getCourseById($courseId);

        $this->assertIsArray($course);
        $this->assertEquals($courseId, $course['id']);
        $this->assertEquals('AOW101', $course['course_code']);
        $this->assertEquals('Advanced Open Water', $course['name']);
    }

    public function testGetCourseList(): void
    {
        $user = $this->createTestUser();

        // Create multiple courses
        $courses = [
            ['course_code' => 'OW101', 'name' => 'Open Water Diver'],
            ['course_code' => 'AOW101', 'name' => 'Advanced Open Water'],
            ['course_code' => 'RES101', 'name' => 'Rescue Diver']
        ];

        foreach ($courses as $course) {
            $stmt = $this->db->prepare(
                "INSERT INTO courses (course_code, name, duration_days, max_students, price, created_by, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $course['course_code'],
                $course['name'],
                3,
                8,
                399.00,
                $user['id'],
                date('Y-m-d H:i:s')
            ]);
        }

        // Get all courses
        $courseList = $this->courseService->getCourseList();

        $this->assertIsArray($courseList);
        $this->assertGreaterThanOrEqual(3, count($courseList));
    }

    public function testSearchCourses(): void
    {
        $user = $this->createTestUser();

        // Create test courses
        $stmt = $this->db->prepare(
            "INSERT INTO courses (course_code, name, duration_days, max_students, price, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute(['OW101', 'Open Water Diver', 3, 8, 399.00, $user['id'], date('Y-m-d H:i:s')]);
        $stmt->execute(['AOW101', 'Advanced Open Water', 2, 6, 349.00, $user['id'], date('Y-m-d H:i:s')]);
        $stmt->execute(['RES101', 'Rescue Diver', 3, 8, 449.00, $user['id'], date('Y-m-d H:i:s')]);

        // Search for courses with "Open Water"
        $results = $this->courseService->getCourseList(['search' => 'Open Water']);

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results));

        foreach ($results as $course) {
            $this->assertStringContainsStringIgnoringCase('Open Water', $course['name']);
        }
    }
}
