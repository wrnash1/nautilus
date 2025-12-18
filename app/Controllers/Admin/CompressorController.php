<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Middleware\AuthMiddleware;

class CompressorController
{
    public function index()
    {
        if (!hasPermission('admin.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $compressors = Database::fetchAll("SELECT * FROM compressors ORDER BY name");

        // Calculate statuses
        foreach ($compressors as &$comp) {
            $comp['oil_due_at'] = $comp['last_oil_change_hours'] + $comp['oil_change_interval'];
            $comp['filter_due_at'] = $comp['last_filter_change_hours'] + $comp['filter_change_interval'];
            
            $comp['oil_remaining'] = $comp['oil_due_at'] - $comp['current_hours'];
            $comp['filter_remaining'] = $comp['filter_due_at'] - $comp['current_hours'];
            
            $comp['maintenance_needed'] = ($comp['oil_remaining'] <= 0 || $comp['filter_remaining'] <= 0);
        }

        $logs = Database::fetchAll("
            SELECT cl.*, c.name as compressor_name, CONCAT(u.first_name, ' ', u.last_name) as user_name
            FROM compressor_logs cl
            JOIN compressors c ON cl.compressor_id = c.id
            JOIN users u ON cl.user_id = u.id
            ORDER BY cl.created_at DESC
            LIMIT 50
        ");

        require __DIR__ . '/../../Views/admin/compressors/index.php';
    }

    public function store()
    {
        if (!hasPermission('admin.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO compressors (name, model, serial_number, oil_change_interval, filter_change_interval)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                sanitizeInput($_POST['name']),
                sanitizeInput($_POST['model']),
                sanitizeInput($_POST['serial_number']),
                (int)$_POST['oil_change_interval'],
                (int)$_POST['filter_change_interval']
            ]);

            $_SESSION['flash_success'] = 'Compressor added successfully';
            redirect('/admin/compressors');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/admin/compressors');
        }
    }

    public function logMaintenance($id)
    {
        if (!hasPermission('admin.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            $type = sanitizeInput($_POST['type']); // 'oil_change', 'filter_change', 'check'
            $currentHours = (float)$_POST['current_hours'];
            $description = sanitizeInput($_POST['description']);

            // Insert log
            $stmt = $db->prepare("
                INSERT INTO compressor_logs (compressor_id, user_id, type, hours_recorded, description)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $_SESSION['user_id'],
                $type,
                $currentHours,
                $description
            ]);

            // Update compressor status
            $updates = ["current_hours = ?"];
            $params = [$currentHours];
            
            if ($type === 'oil_change') {
                $updates[] = "last_oil_change_hours = ?";
                $params[] = $currentHours;
            } elseif ($type === 'filter_change') {
                $updates[] = "last_filter_change_hours = ?";
                $params[] = $currentHours;
            } elseif ($type === 'maintenance') {
                // Full service
                $updates[] = "last_oil_change_hours = ?";
                $updates[] = "last_filter_change_hours = ?";
                $params[] = $currentHours;
                $params[] = $currentHours;
            }

            $params[] = $id;
            
            $sql = "UPDATE compressors SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($sql);
            $updateStmt->execute($params);

            $db->commit();
            $_SESSION['flash_success'] = 'Maintenance logged successfully';
            redirect('/admin/compressors');

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/admin/compressors');
        }
    }
}
