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
    
    public function edit(int $id)
    {
        if (!hasPermission('trips.edit')) {
            header('Location: /trips');
            exit;
        }
        
        $trip = $this->tripService->getTripById($id);
        
        if (!$trip) {
            $_SESSION['flash_error'] = 'Trip not found';
            header('Location: /trips');
            exit;
        }
        
        $pageTitle = 'Edit Trip';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/edit.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function update(int $id)
    {
        if (!hasPermission('trips.edit')) {
            header('Location: /trips');
            exit;
        }
        
        $this->tripService->updateTrip($id, $_POST);
        
        $_SESSION['flash_success'] = 'Trip updated successfully!';
        header('Location: /trips/' . $id);
        exit;
    }
    
    public function delete(int $id)
    {
        if (!hasPermission('trips.delete')) {
            header('Location: /trips');
            exit;
        }
        
        $this->tripService->deleteTrip($id);
        
        $_SESSION['flash_success'] = 'Trip deleted successfully!';
        header('Location: /trips');
        exit;
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
    
    public function createSchedule()
    {
        if (!hasPermission('trips.create')) {
            header('Location: /trips/schedules');
            exit;
        }
        
        $trips = $this->tripService->getTripList([]);
        
        $pageTitle = 'Create Trip Schedule';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/schedules/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function storeSchedule()
    {
        if (!hasPermission('trips.create')) {
            header('Location: /trips/schedules');
            exit;
        }
        
        $id = $this->tripService->createSchedule($_POST);
        
        $_SESSION['flash_success'] = 'Trip schedule created successfully!';
        header('Location: /trips/schedules/' . $id);
        exit;
    }
    
    public function showSchedule(int $id)
    {
        if (!hasPermission('trips.view')) {
            header('Location: /trips/schedules');
            exit;
        }
        
        $schedule = $this->tripService->getScheduleById($id);
        
        if (!$schedule) {
            $_SESSION['flash_error'] = 'Schedule not found';
            header('Location: /trips/schedules');
            exit;
        }
        
        $bookings = $this->tripService->getBookingList(['schedule_id' => $id]);
        
        $pageTitle = 'Trip Schedule';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/schedules/show.php';
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
    
    public function createBooking()
    {
        if (!hasPermission('trips.create')) {
            header('Location: /trips/bookings');
            exit;
        }
        
        $schedules = $this->tripService->getScheduleList(['status' => 'scheduled']);
        
        $pageTitle = 'Create Trip Booking';
        $activeMenu = 'trips';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/trips/bookings/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function storeBooking()
    {
        if (!hasPermission('trips.create')) {
            header('Location: /trips/bookings');
            exit;
        }
        
        $id = $this->tripService->createBooking($_POST);
        
        $_SESSION['flash_success'] = 'Trip booking created successfully!';
        header('Location: /trips/bookings/' . $id);
        exit;
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
    
    public function confirmBooking(int $id)
    {
        if (!hasPermission('trips.edit')) {
            header('Location: /trips/bookings/' . $id);
            exit;
        }
        
        $this->tripService->updateBookingStatus($id, 'confirmed');
        
        $_SESSION['flash_success'] = 'Booking confirmed successfully!';
        header('Location: /trips/bookings/' . $id);
        exit;
    }
    
    public function cancelBooking(int $id)
    {
        if (!hasPermission('trips.edit')) {
            header('Location: /trips/bookings/' . $id);
            exit;
        }
        
        $booking = $this->tripService->getBookingById($id);
        
        \App\Core\Database::execute(
            "UPDATE trip_schedules SET current_bookings = current_bookings - ? WHERE id = ?",
            [$booking['number_of_participants'], $booking['schedule_id']]
        );
        
        $this->tripService->updateBookingStatus($id, 'cancelled');
        
        $_SESSION['flash_success'] = 'Booking cancelled successfully!';
        header('Location: /trips/bookings/' . $id);
        exit;
    }
}
