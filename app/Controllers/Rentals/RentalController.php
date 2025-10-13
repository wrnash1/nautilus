<?php

namespace App\Controllers\Rentals;

use App\Services\Rentals\RentalService;
use App\Services\CRM\CustomerService;

class RentalController
{
    private RentalService $rentalService;
    private CustomerService $customerService;
    
    public function __construct()
    {
        $this->rentalService = new RentalService();
        $this->customerService = new CustomerService();
    }
    
    public function index()
    {
        if (!hasPermission('rentals.view')) {
            header('Location: /');
            exit;
        }
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $equipment = $this->rentalService->getEquipmentList($filters);
        $categories = $this->rentalService->getAllCategories();
        
        $pageTitle = 'Rental Equipment';
        $activeMenu = 'rentals';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/rentals/equipment/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function createEquipment()
    {
        if (!hasPermission('rentals.create')) {
            header('Location: /rentals');
            exit;
        }
        
        $categories = $this->rentalService->getAllCategories();
        
        $pageTitle = 'Add Equipment';
        $activeMenu = 'rentals';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/rentals/equipment/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function storeEquipment()
    {
        if (!hasPermission('rentals.create')) {
            header('Location: /rentals');
            exit;
        }
        
        $id = $this->rentalService->createEquipment($_POST);
        
        $_SESSION['flash_success'] = 'Equipment added successfully!';
        header('Location: /rentals/equipment/' . $id);
        exit;
    }
    
    public function showEquipment(int $id)
    {
        if (!hasPermission('rentals.view')) {
            header('Location: /rentals');
            exit;
        }
        
        $equipment = $this->rentalService->getEquipmentById($id);
        
        if (!$equipment) {
            $_SESSION['flash_error'] = 'Equipment not found';
            header('Location: /rentals');
            exit;
        }
        
        $pageTitle = $equipment['name'];
        $activeMenu = 'rentals';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/rentals/equipment/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function editEquipment(int $id)
    {
        if (!hasPermission('rentals.edit')) {
            header('Location: /rentals');
            exit;
        }
        
        $equipment = $this->rentalService->getEquipmentById($id);
        
        if (!$equipment) {
            $_SESSION['flash_error'] = 'Equipment not found';
            header('Location: /rentals');
            exit;
        }
        
        $categories = $this->rentalService->getAllCategories();
        
        $pageTitle = 'Edit Equipment';
        $activeMenu = 'rentals';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/rentals/equipment/edit.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function updateEquipment(int $id)
    {
        if (!hasPermission('rentals.edit')) {
            header('Location: /rentals');
            exit;
        }
        
        $this->rentalService->updateEquipment($id, $_POST);
        
        $_SESSION['flash_success'] = 'Equipment updated successfully!';
        header('Location: /rentals/equipment/' . $id);
        exit;
    }
    
    public function deleteEquipment(int $id)
    {
        if (!hasPermission('rentals.delete')) {
            header('Location: /rentals');
            exit;
        }
        
        $this->rentalService->deleteEquipment($id);
        
        $_SESSION['flash_success'] = 'Equipment deleted successfully!';
        header('Location: /rentals');
        exit;
    }
    
    public function reservations()
    {
        if (!hasPermission('rentals.view')) {
            header('Location: /');
            exit;
        }
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $reservations = $this->rentalService->getReservationList($filters);
        
        $pageTitle = 'Reservations';
        $activeMenu = 'rentals';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/rentals/reservations/index.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function createReservation()
    {
        if (!hasPermission('rentals.create')) {
            header('Location: /rentals/reservations');
            exit;
        }
        
        $categories = $this->rentalService->getAllCategories();
        
        $pageTitle = 'Create Reservation';
        $activeMenu = 'rentals';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/rentals/reservations/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function showReservation(int $id)
    {
        if (!hasPermission('rentals.view')) {
            header('Location: /rentals/reservations');
            exit;
        }
        
        $reservation = $this->rentalService->getReservationById($id);
        
        if (!$reservation) {
            $_SESSION['flash_error'] = 'Reservation not found';
            header('Location: /rentals/reservations');
            exit;
        }
        
        $items = $this->rentalService->getReservationItems($id);
        
        $checkout = \App\Core\Database::fetchOne(
            "SELECT * FROM rental_checkouts WHERE reservation_id = ? ORDER BY id DESC LIMIT 1",
            [$id]
        );
        
        if ($checkout) {
            $reservation['checkout_id'] = $checkout['id'];
        }
        
        $pageTitle = 'Reservation ' . $reservation['reservation_number'];
        $activeMenu = 'rentals';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/rentals/reservations/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function searchAvailableEquipment()
    {
        if (!hasPermission('rentals.view')) {
            echo json_encode([]);
            exit;
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+1 day'));
        $categoryId = $_GET['category_id'] ?? null;
        
        $equipment = $this->rentalService->getAvailableEquipment($startDate, $endDate, $categoryId);
        
        header('Content-Type: application/json');
        echo json_encode($equipment);
        exit;
    }
    
    public function checkout(int $id)
    {
        if (!hasPermission('rentals.edit')) {
            header('Location: /rentals/reservations/' . $id);
            exit;
        }
        
        $this->rentalService->checkoutEquipment($id);
        
        $_SESSION['flash_success'] = 'Equipment checked out successfully!';
        header('Location: /rentals/reservations/' . $id);
        exit;
    }
    
    public function checkin(int $id)
    {
        if (!hasPermission('rentals.edit')) {
            header('Location: /rentals/reservations/' . $id);
            exit;
        }
        
        $checkoutId = $_POST['checkout_id'];
        $condition = [
            'condition' => $_POST['condition'] ?? 'good',
            'notes' => $_POST['notes'] ?? null
        ];
        
        $this->rentalService->checkinEquipment($checkoutId, $condition);
        
        $_SESSION['flash_success'] = 'Equipment checked in successfully!';
        header('Location: /rentals/reservations/' . $id);
        exit;
    }
}
