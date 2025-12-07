<?php

namespace App\Controllers\API;

use App\Services\Rentals\RentalService;

class RentalController
{
    private $rentalService;
    
    public function __construct()
    {
        $this->rentalService = new RentalService();
    }
    
    public function index()
    {
        $status = $_GET['status'] ?? null;
        
        $rentals = $this->rentalService->getAllReservations($status);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $rentals]);
    }
    
    public function show($id)
    {
        $rental = $this->rentalService->getReservationById($id);
        
        if (!$rental) {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'message' => 'Rental not found']);
            return;
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $rental]);
    }
    
    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $rentalId = $this->rentalService->createReservation($input);
        
        if ($rentalId) {
            $rental = $this->rentalService->getReservationById($rentalId);
            http_response_code(201);
            echo json_encode(['success' => true, 'data' => $rental]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to create rental']);
        }
    }
    
    public function checkin($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $success = $this->rentalService->checkinEquipment($id, $input);
        
        if ($success) {
            $rental = $this->rentalService->getReservationById($id);
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $rental]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to check in equipment']);
        }
    }
}
