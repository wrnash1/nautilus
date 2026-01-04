<?php

namespace App\Controllers\Profile;

use App\Core\Database;

/**
 * ProfileController - Handles user profile viewing
 * Redirects to user details page for the current logged-in user
 */
class ProfileController
{
    /**
     * Show user profile - redirects to user details page
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['flash_error'] = 'You must be logged in to view your profile';
            redirect('/store/login');
            return;
        }

        // Redirect to user details page
        redirect("/store/admin/users/{$userId}");
    }
}
