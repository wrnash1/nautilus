<?php

namespace App\Controllers\Storefront;

class ServicesController extends StorefrontController
{
    /**
     * Services overview page
     */
    public function index(): void
    {
        $data = [
            'page_title' => 'Our Services'
        ];

        $this->renderStorefront('storefront/services/index', $data);
    }
}
