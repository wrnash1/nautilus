<?php

namespace App\Controllers\Trips;

use App\Services\Trips\TripService;

class TripController
{
    private TripService $tripService;
    
    public function __construct()
    {
        $this->tripService = new TripService();
    }
    
    public function index()
    {
        if (!hasPermission('trips.view')) {
            header('Location: /');
            exit;
        }
        
        $filters = [
            'search' => $_GET['search'] ?? ''
        ];
        
        $trips = $this->tripService->getTripList($filters);
        
        $pageTitle = 'Trips';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function create()
    {
        if (!hasPermission('trips.create')) {
            header('Location: /trips');
            exit;
        }
        
        $pageTitle = 'Create Trip';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function store()
    {
        if (!hasPermission('trips.create')) {
            header('Location: /trips');
            exit;
        }
        
        $id = $this->tripService->createTrip($_POST);
        
        $_SESSION['flash_success'] = 'Trip created successfully!';
        header('Location: /trips/' . $id);
        exit;
    }
    
    public function show(int $id)
    {
        if (!hasPermission('trips.view')) {
            header('Location: /trips');
            exit;
        }
        
        $trip = $this->tripService->getTripById($id);
        
        if (!$trip) {
            $_SESSION['flash_error'] = 'Trip not found';
            header('Location: /trips');
            exit;
        }
        
        $schedules = $this->tripService->getScheduleList(['trip_id' => $id]);
        
        $pageTitle = $trip['name'];
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function schedules()
    {
        if (!hasPermission('trips.view')) {
            header('Location: /');
            exit;
        }
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $schedules = $this->tripService->getScheduleList($filters);
        
        $pageTitle = 'Trip Schedules';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/schedules/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function bookings()
    {
        if (!hasPermission('trips.view')) {
            header('Location: /');
            exit;
        }
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $bookings = $this->tripService->getBookingList($filters);
        
        $pageTitle = 'Trip Bookings';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/bookings/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function showBooking(int $id)
    {
        if (!hasPermission('trips.view')) {
            header('Location: /trips/bookings');
            exit;
        }
        
        $booking = $this->tripService->getBookingById($id);
        
        if (!$booking) {
            $_SESSION['flash_error'] = 'Booking not found';
            header('Location: /trips/bookings');
            exit;
        }
        
        $pageTitle = 'Booking ' . $booking['booking_number'];
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/bookings/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
}
