<?php

namespace App\Controllers\Portal;

use App\Core\Controller;

class PortalController extends Controller
{
    /**
     * Customer portal dashboard
     */
    public function index()
    {
        // For now, show a simple portal page
        // TODO: Add customer authentication
        require __DIR__ . '/../../Views/portal/index.php';
    }
    
    /**
     * Customer certifications
     */
    public function certifications()
    {
        require __DIR__ . '/../../Views/portal/certifications.php';
    }
    
    /**
     * Customer bookings
     */
    public function bookings()
    {
        require __DIR__ . '/../../Views/portal/bookings.php';
    }
}
