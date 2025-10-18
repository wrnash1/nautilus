<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\Admin\UserService;

class UserController
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function index()
    {
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';

        $users = $this->service->getUsers($search, $role, $status);
        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY name");

        require __DIR__ . '/../../Views/admin/users/index.php';
    }

    public function create()
    {
        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY name");
        require __DIR__ . '/../../Views/admin/users/create.php';
    }

    public function store()
    {
        try {
            $data = [
                'email' => $_POST['email'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'phone' => $_POST['phone'] ?? '',
                'role_id' => $_POST['role_id'],
                'password' => $_POST['password'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $userId = $this->service->createUser($data);

            setFlashMessage('success', 'User created successfully!');
            header('Location: /admin/users/' . $userId);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to create user: ' . $e->getMessage());
            header('Location: /admin/users/create');
            exit;
        }
    }

    public function show(int $id)
    {
        $user = $this->service->getUserById($id);
        if (!$user) {
            setFlashMessage('error', 'User not found');
            header('Location: /admin/users');
            exit;
        }

        $activityLog = $this->service->getUserActivity($id, 20);
        require __DIR__ . '/../../Views/admin/users/show.php';
    }

    public function edit(int $id)
    {
        $user = $this->service->getUserById($id);
        if (!$user) {
            setFlashMessage('error', 'User not found');
            header('Location: /admin/users');
            exit;
        }

        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY name");
        require __DIR__ . '/../../Views/admin/users/edit.php';
    }

    public function update(int $id)
    {
        try {
            $data = [
                'email' => $_POST['email'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'phone' => $_POST['phone'] ?? '',
                'role_id' => $_POST['role_id'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }

            $this->service->updateUser($id, $data);

            setFlashMessage('success', 'User updated successfully!');
            header('Location: /admin/users/' . $id);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to update user: ' . $e->getMessage());
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        }
    }

    public function delete(int $id)
    {
        try {
            // Cannot delete yourself
            if ($id == currentUser()['id']) {
                throw new \Exception('Cannot delete your own account');
            }

            $this->service->deleteUser($id);

            setFlashMessage('success', 'User deleted successfully!');
            header('Location: /admin/users');
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to delete user: ' . $e->getMessage());
            header('Location: /admin/users');
            exit;
        }
    }

    public function resetPassword(int $id)
    {
        try {
            $newPassword = $this->service->resetUserPassword($id);

            setFlashMessage('success', 'Password reset. New password: ' . $newPassword);
            header('Location: /admin/users/' . $id);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to reset password: ' . $e->getMessage());
            header('Location: /admin/users/' . $id);
            exit;
        }
    }

    public function toggleStatus(int $id)
    {
        try {
            // Cannot deactivate yourself
            if ($id == currentUser()['id']) {
                throw new \Exception('Cannot deactivate your own account');
            }

            $this->service->toggleUserStatus($id);

            setFlashMessage('success', 'User status updated successfully!');
            header('Location: /admin/users/' . $id);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to update status: ' . $e->getMessage());
            header('Location: /admin/users/' . $id);
            exit;
        }
    }
}
