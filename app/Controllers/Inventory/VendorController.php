<?php

namespace App\Controllers\Inventory;

use App\Models\Vendor;

class VendorController
{
    public function index()
    {
        if (!hasPermission('products.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $page = (int) ($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $vendors = Vendor::where('is_active', 1)->orderBy('name', 'ASC')->offset($offset)->limit($limit)->get();
        $total = Vendor::where('is_active', 1)->count();
        $totalPages = ceil($total / $limit);

        require __DIR__ . '/../../Views/vendors/index.php';
    }

    public function create()
    {
        if (!hasPermission('products.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/vendors');
        }

        require __DIR__ . '/../../Views/vendors/create.php';
    }

    public function store()
    {
        if (!hasPermission('products.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'contact_name' => sanitizeInput($_POST['contact_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'website' => sanitizeInput($_POST['website'] ?? ''),
                'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
                'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
                'city' => sanitizeInput($_POST['city'] ?? ''),
                'state' => sanitizeInput($_POST['state'] ?? ''),
                'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
                'country' => sanitizeInput($_POST['country'] ?? 'US'),
                'payment_terms' => sanitizeInput($_POST['payment_terms'] ?? ''),
                'notes' => sanitizeInput($_POST['notes'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (empty($data['name'])) {
                throw new \Exception('Vendor name is required');
            }

            $vendor = Vendor::create($data);
            $vendorId = $vendor->id;

            $_SESSION['flash_success'] = 'Vendor created successfully';
            redirect("/vendors/{$vendorId}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/vendors/create');
        }
    }

    public function show(int $id)
    {
        if (!hasPermission('products.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $vendor = Vendor::find($id);

        if (!$vendor) {
            $_SESSION['flash_error'] = 'Vendor not found';
            redirect('/vendors');
        }

        require __DIR__ . '/../../Views/vendors/show.php';
    }

    public function edit(int $id)
    {
        if (!hasPermission('products.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/vendors');
        }

        $vendor = Vendor::find($id);

        if (!$vendor) {
            $_SESSION['flash_error'] = 'Vendor not found';
            redirect('/vendors');
        }

        require __DIR__ . '/../../Views/vendors/edit.php';
    }

    public function update(int $id)
    {
        if (!hasPermission('products.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'contact_name' => sanitizeInput($_POST['contact_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'website' => sanitizeInput($_POST['website'] ?? ''),
                'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
                'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
                'city' => sanitizeInput($_POST['city'] ?? ''),
                'state' => sanitizeInput($_POST['state'] ?? ''),
                'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
                'country' => sanitizeInput($_POST['country'] ?? 'US'),
                'payment_terms' => sanitizeInput($_POST['payment_terms'] ?? ''),
                'notes' => sanitizeInput($_POST['notes'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (empty($data['name'])) {
                throw new \Exception('Vendor name is required');
            }

            $vendor = Vendor::findOrFail($id);
            $vendor->update($data);

            $_SESSION['flash_success'] = 'Vendor updated successfully';
            redirect("/vendors/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/vendors/{$id}/edit");
        }
    }

    public function delete(int $id)
    {
        if (!hasPermission('products.delete')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/vendors');
        }

        $vendor = Vendor::findOrFail($id);
        $vendor->update(['is_active' => 0]);

        $_SESSION['flash_success'] = 'Vendor deleted successfully';
        redirect('/vendors');
    }
}
