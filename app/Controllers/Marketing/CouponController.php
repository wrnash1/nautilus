<?php

namespace App\Controllers\Marketing;

use App\Core\Auth;
use App\Services\Marketing\CouponService;

class CouponController
{
    private $couponService;

    public function __construct()
    {
        $this->couponService = new CouponService();
    }

    /**
     * Display coupons list
     */
    public function index()
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/store');
            return;
        }

        $coupons = $this->couponService->getAllCoupons();
        $pageTitle = 'Coupons';

        ob_start();
        require __DIR__ . '/../../Views/marketing/coupons/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show create coupon form
     */
    public function create()
    {
        if (!Auth::hasPermission('marketing.create')) {
            redirect('/store');
            return;
        }

        $pageTitle = 'Create Coupon';

        ob_start();
        require __DIR__ . '/../../Views/marketing/coupons/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Store new coupon
     */
    public function store()
    {
        if (!Auth::hasPermission('marketing.create')) {
            redirect('/store');
            return;
        }

        $data = [
            'code' => strtoupper($_POST['code'] ?? ''),
            'description' => $_POST['description'] ?? '',
            'discount_type' => $_POST['discount_type'] ?? 'percentage',
            'discount_value' => $_POST['discount_value'] ?? 0,
            'min_purchase_amount' => $_POST['min_purchase_amount'] ?? null,
            'max_discount_amount' => $_POST['max_discount_amount'] ?? null,
            'usage_limit' => $_POST['usage_limit'] ?? null,
            'usage_limit_per_customer' => $_POST['usage_limit_per_customer'] ?? null,
            'valid_from' => $_POST['valid_from'] ?? null,
            'valid_until' => $_POST['valid_until'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $couponId = $this->couponService->createCoupon($data);

        if ($couponId) {
            $_SESSION['success'] = 'Coupon created successfully.';
            redirect('/store/marketing/coupons/' . $couponId);
        } else {
            $_SESSION['error'] = 'Failed to create coupon.';
            redirect('/store/marketing/coupons/create');
        }
    }

    /**
     * Display coupon details
     */
    public function show($id)
    {
        if (!Auth::hasPermission('marketing.view')) {
            redirect('/store');
            return;
        }

        $coupon = $this->couponService->getCouponById($id);
        if (!$coupon) {
            $_SESSION['error'] = 'Coupon not found.';
            redirect('/store/marketing/coupons');
            return;
        }

        $usage = $this->couponService->getCouponUsage($id);
        $pageTitle = 'Coupon: ' . $coupon['code'];

        ob_start();
        require __DIR__ . '/../../Views/marketing/coupons/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Show edit coupon form
     */
    public function edit($id)
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/store');
            return;
        }

        $coupon = $this->couponService->getCouponById($id);
        if (!$coupon) {
            $_SESSION['error'] = 'Coupon not found.';
            redirect('/store/marketing/coupons');
            return;
        }

        $pageTitle = 'Edit Coupon';

        ob_start();
        require __DIR__ . '/../../Views/marketing/coupons/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../../Views/layouts/app.php';
    }

    /**
     * Update coupon
     */
    public function update($id)
    {
        if (!Auth::hasPermission('marketing.edit')) {
            redirect('/store');
            return;
        }

        $data = [
            'code' => strtoupper($_POST['code'] ?? ''),
            'description' => $_POST['description'] ?? '',
            'discount_type' => $_POST['discount_type'] ?? 'percentage',
            'discount_value' => $_POST['discount_value'] ?? 0,
            'min_purchase_amount' => $_POST['min_purchase_amount'] ?? null,
            'max_discount_amount' => $_POST['max_discount_amount'] ?? null,
            'usage_limit' => $_POST['usage_limit'] ?? null,
            'usage_limit_per_customer' => $_POST['usage_limit_per_customer'] ?? null,
            'valid_from' => $_POST['valid_from'] ?? null,
            'valid_until' => $_POST['valid_until'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $success = $this->couponService->updateCoupon($id, $data);

        if ($success) {
            $_SESSION['success'] = 'Coupon updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update coupon.';
        }

        redirect('/store/marketing/coupons/' . $id);
    }

    /**
     * Delete coupon
     */
    public function delete($id)
    {
        if (!Auth::hasPermission('marketing.delete')) {
            redirect('/store');
            return;
        }

        $success = $this->couponService->deleteCoupon($id);

        if ($success) {
            $_SESSION['success'] = 'Coupon deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete coupon.';
        }

        redirect('/store/marketing/coupons');
    }

    /**
     * Validate coupon via AJAX
     */
    public function validate()
    {
        $code = $_POST['code'] ?? '';
        $customerId = $_POST['customer_id'] ?? null;
        $cartTotal = $_POST['cart_total'] ?? 0;

        $result = $this->couponService->validateCoupon($code, $customerId, $cartTotal);

        jsonResponse($result);
    }
}
