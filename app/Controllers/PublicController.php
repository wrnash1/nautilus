<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;

/**
 * Public Storefront Controller
 * 
 * Handles public-facing pages (no authentication required)
 */
class PublicController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Homepage - Public storefront
     */
    public function index()
    {
        $company = getCompanyInfo();
        
        // Get featured products
        $stmt = $this->db->query("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN product_categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.is_featured = 1
            ORDER BY p.created_at DESC
            LIMIT 8
        ");
        $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get upcoming courses
        $stmt = $this->db->query("
            SELECT cs.*, c.name as course_name, c.description
            FROM course_schedules cs
            JOIN courses c ON cs.course_id = c.id
            WHERE cs.start_date >= CURDATE()
            AND cs.status = 'scheduled'
            ORDER BY cs.start_date ASC
            LIMIT 6
        ");
        $upcomingCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get upcoming trips
        $stmt = $this->db->query("
            SELECT ts.*, t.name as trip_name, t.description, t.destination
            FROM trip_schedules ts
            JOIN trips t ON ts.trip_id = t.id
            WHERE ts.departure_date >= CURDATE()
            AND ts.status IN ('scheduled', 'confirmed')
            ORDER BY ts.departure_date ASC
            LIMIT 6
        ");
        $upcomingTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require BASE_PATH . '/app/Views/public/index.php';
    }

    /**
     * Shop - Product catalog
     */
    public function shop()
    {
        $company = getCompanyInfo();
        
        // Get all active products with pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN product_categories c ON p.category_id = c.id
            WHERE p.is_active = 1
            ORDER BY p.name ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $stmt = $this->db->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
        $totalProducts = $stmt->fetchColumn();
        $totalPages = ceil($totalProducts / $perPage);

        // Get categories for filter
        $stmt = $this->db->query("
            SELECT id, name, (SELECT COUNT(*) FROM products WHERE category_id = product_categories.id AND is_active = 1) as product_count
            FROM product_categories
            WHERE is_active = 1
            ORDER BY name ASC
        ");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require BASE_PATH . '/app/Views/public/shop.php';
    }

    /**
     * Courses - Course catalog
     */
    public function courses()
    {
        $company = getCompanyInfo();
        
        // Get all active courses
        $stmt = $this->db->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM course_schedules WHERE course_id = c.id AND start_date >= CURDATE()) as upcoming_count
            FROM courses c
            WHERE c.is_active = 1
            ORDER BY c.name ASC
        ");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require BASE_PATH . '/app/Views/public/courses.php';
    }

    /**
     * Trips - Trip catalog
     */
    public function trips()
    {
        $company = getCompanyInfo();
        
        // Get all active trips
        $stmt = $this->db->query("
            SELECT t.*,
                   (SELECT COUNT(*) FROM trip_schedules WHERE trip_id = t.id AND departure_date >= CURDATE()) as upcoming_count
            FROM trips t
            WHERE t.is_active = 1
            ORDER BY t.destination ASC
        ");
        $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require BASE_PATH . '/app/Views/public/trips.php';
    }

    /**
     * About page
     */
    public function about()
    {
        $company = getCompanyInfo();
        require BASE_PATH . '/app/Views/public/about.php';
    }

    /**
     * Contact page
     */
    public function contact()
    {
        $company = getCompanyInfo();
        require BASE_PATH . '/app/Views/public/contact.php';
    }

    /**
     * Handle contact form submission
     */
    public function submitContact()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contact');
        }

        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');

        // Validate
        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['flash_error'] = 'Please fill in all required fields.';
            redirect('/contact');
        }

        // Save to database
        $stmt = $this->db->prepare("
            INSERT INTO contact_submissions (name, email, phone, message, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $name,
            $email,
            $phone,
            $message,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        // TODO: Send email notification to shop owner

        $_SESSION['flash_success'] = 'Thank you for contacting us! We will get back to you soon.';
        redirect('/contact');
    }
}
