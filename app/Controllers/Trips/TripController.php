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
            redirect('/store');
            return;
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

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    public function create()
    {
        if (!hasPermission('trips.create')) {
            redirect('/trips');
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
            redirect('/trips');
        }

        $id = $this->tripService->createTrip($_POST);

        $_SESSION['flash_success'] = 'Trip created successfully!';
        redirect('/trips/' . $id);
    }

    public function show(int $id)
    {
        if (!hasPermission('trips.view')) {
            redirect('/trips');
        }

        $trip = $this->tripService->getTripById($id);

        if (!$trip) {
            $_SESSION['flash_error'] = 'Trip not found';
            redirect('/trips');
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
            redirect('/trips');
        }

        $trip = $this->tripService->getTripById($id);

        if (!$trip) {
            $_SESSION['flash_error'] = 'Trip not found';
            redirect('/trips');
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
            redirect('/trips');
        }

        $this->tripService->updateTrip($id, $_POST);

        $_SESSION['flash_success'] = 'Trip updated successfully!';
        redirect('/trips/' . $id);
    }

    public function delete(int $id)
    {
        if (!hasPermission('trips.delete')) {
            redirect('/trips');
        }

        $this->tripService->deleteTrip($id);

        $_SESSION['flash_success'] = 'Trip deleted successfully!';
        redirect('/trips');
    }

    public function schedules()
    {
        if (!hasPermission('trips.view')) {
            redirect('/');
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
            redirect('/trips/schedules');
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
            redirect('/trips/schedules');
        }

        $id = $this->tripService->createSchedule($_POST);

        $_SESSION['flash_success'] = 'Trip schedule created successfully!';
        redirect('/trips/schedules/' . $id);
    }

    public function showSchedule(int $id)
    {
        if (!hasPermission('trips.view')) {
            redirect('/trips/schedules');
        }

        $schedule = $this->tripService->getScheduleById($id);

        if (!$schedule) {
            $_SESSION['flash_error'] = 'Schedule not found';
            redirect('/trips/schedules');
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
            redirect('/');
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
            redirect('/trips/bookings');
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
            redirect('/trips/bookings');
        }

        $id = $this->tripService->createBooking($_POST);

        $_SESSION['flash_success'] = 'Trip booking created successfully!';
        redirect('/trips/bookings/' . $id);
    }

    public function showBooking(int $id)
    {
        if (!hasPermission('trips.view')) {
            redirect('/trips/bookings');
        }

        $booking = $this->tripService->getBookingById($id);

        if (!$booking) {
            $_SESSION['flash_error'] = 'Booking not found';
            redirect('/trips/bookings');
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
            redirect('/trips/bookings/' . $id);
        }

        $this->tripService->updateBookingStatus($id, 'confirmed');

        $_SESSION['flash_success'] = 'Booking confirmed successfully!';
        redirect('/trips/bookings/' . $id);
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
        redirect('/trips/bookings/' . $id);
    }
}
