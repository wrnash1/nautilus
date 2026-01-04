<?php

namespace App\Controllers\Customer;

use App\Core\CustomerAuth;
use App\Core\Database;
use App\Models\Customer;

class AccountController
{
    public function dashboard()
    {
        $customerData = CustomerAuth::customer();

        $orders = \App\Models\Order::where('customer_id', $customerData['id'])
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->toArray();

        require __DIR__ . '/../../Views/customer/dashboard.php';
    }

    public function orders()
    {
        $customerData = CustomerAuth::customer();

        $orders = \App\Models\Order::where('customer_id', $customerData['id'])
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();

        require __DIR__ . '/../../Views/customer/orders.php';
    }

    public function orderDetail(int $id)
    {
        $customerData = CustomerAuth::customer();

        $order = \App\Models\Order::where('id', $id)
            ->where('customer_id', $customerData['id'])
            ->first();

        if (!$order) {
            $_SESSION['flash_error'] = 'Order not found';
            redirect('/account/orders');
        }

        $order = $order->toArray();

        $orderItems = \App\Models\OrderItem::where('order_id', $id)
            ->get()
            ->toArray();

        require __DIR__ . '/../../Views/customer/order-detail.php';
    }

    public function profile()
    {
        $customer = CustomerAuth::customer();
        require __DIR__ . '/../../Views/customer/profile.php';
    }

    public function updateProfile()
    {
        $customerData = CustomerAuth::customer();

        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($email)) {
            $_SESSION['flash_error'] = 'Please fill in all required fields';
            redirect('/account/profile');
        }

        // Check email uniqueness
        $existingCustomer = Customer::where('email', $email)->where('is_active', 1)->first();
        if ($existingCustomer && $existingCustomer->id != $customerData['id']) {
            $_SESSION['flash_error'] = 'Email already in use';
            redirect('/account/profile');
        }

        $customer = Customer::findOrFail($customerData['id']);
        $customer->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone
        ]);

        // Update session
        $_SESSION['customer'] = $customer->toArray();

        $_SESSION['flash_success'] = 'Profile updated successfully!';
        redirect('/account/profile');
    }

    public function addresses()
    {
        $customerData = CustomerAuth::customer();

        $addresses = \App\Models\CustomerAddress::where('customer_id', $customerData['id'])
            ->orderBy('is_default', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();

        require __DIR__ . '/../../Views/customer/addresses.php';
    }

    public function createAddress()
    {
        $customerData = CustomerAuth::customer();

        $customer = Customer::findOrFail($customerData['id']);

        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        if ($isDefault) {
            $customer->addresses()->update(['is_default' => 0]);
        }

        $customer->addresses()->create([
            'address_type' => $_POST['address_type'] ?? 'both',
            'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
            'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
            'city' => sanitizeInput($_POST['city'] ?? ''),
            'state' => sanitizeInput($_POST['state'] ?? ''),
            'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
            'country' => sanitizeInput($_POST['country'] ?? 'US'),
            'is_default' => $isDefault
        ]);

        $_SESSION['flash_success'] = 'Address added successfully!';
        redirect('/account/addresses');
    }

    public function updateAddress(int $id)
    {
        $customerData = CustomerAuth::customer();

        $address = \App\Models\CustomerAddress::where('id', $id)
            ->where('customer_id', $customerData['id'])
            ->first();

        if (!$address) {
            $_SESSION['flash_error'] = 'Address not found';
            redirect('/account/addresses');
        }

        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        if ($isDefault) {
            \App\Models\CustomerAddress::where('customer_id', $customerData['id'])->update(['is_default' => 0]);
        }

        $address->update([
            'address_type' => $_POST['address_type'] ?? 'both',
            'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
            'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
            'city' => sanitizeInput($_POST['city'] ?? ''),
            'state' => sanitizeInput($_POST['state'] ?? ''),
            'postal_code' => sanitizeInput($_POST['postal_code'] ?? ''),
            'country' => sanitizeInput($_POST['country'] ?? 'US'),
            'is_default' => $isDefault
        ]);

        $_SESSION['flash_success'] = 'Address updated successfully!';
        redirect('/account/addresses');
    }

    public function deleteAddress(int $id)
    {
        $customerData = CustomerAuth::customer();

        \App\Models\CustomerAddress::where('id', $id)
            ->where('customer_id', $customerData['id'])
            ->delete();

        $_SESSION['flash_success'] = 'Address deleted successfully!';
        redirect('/account/addresses');
    }
}
