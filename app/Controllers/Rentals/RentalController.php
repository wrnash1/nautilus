<?php

namespace App\Controllers\Rentals;

use App\Services\Rentals\RentalService;

class RentalController
{
    private RentalService $rentalService;
    
    public function __construct()
    {
        $this->rentalService = new RentalService();
    }
    
    public function index()
    {
        
    }
    
    public function createReservation()
    {
        
    }
    
    public function checkout(int $reservationId)
    {
        
    }
    
    public function checkin(int $checkoutId)
    {
        
    }
    
    public function inspectEquipment(int $equipmentId)
    {
        
    }
}
