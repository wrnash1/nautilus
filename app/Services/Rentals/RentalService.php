<?php

namespace App\Services\Rentals;

class RentalService
{
    public function createReservation(array $data): int
    {
        
        return 0;
    }
    
    public function checkoutEquipment(int $reservationId): bool
    {
        
        return false;
    }
    
    public function checkinEquipment(int $checkoutId, array $condition): bool
    {
        
        return false;
    }
}
