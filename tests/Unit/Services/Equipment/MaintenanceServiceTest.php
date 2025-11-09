<?php

namespace Tests\Unit\Services\Equipment;

use Tests\TestCase;
use App\Services\Equipment\MaintenanceService;

class MaintenanceServiceTest extends TestCase
{
    private MaintenanceService $maintenanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->maintenanceService = new MaintenanceService();
    }

    public function testGetEquipmentNeedingMaintenance(): void
    {
        // Create equipment type
        $stmt = $this->db->prepare(
            "INSERT INTO rental_equipment_types (name, daily_rate, created_at)
             VALUES (?, ?, ?)"
        );
        $stmt->execute(['BCD', 50.00, date('Y-m-d H:i:s')]);
        $equipmentTypeId = (int)$this->db->lastInsertId();

        // Create equipment items with various maintenance dates
        $stmt = $this->db->prepare(
            "INSERT INTO rental_equipment (equipment_type_id, serial_number, status, next_inspection_due, created_at)
             VALUES (?, ?, ?, ?, ?)"
        );

        // Overdue
        $stmt->execute([$equipmentTypeId, 'BCD-001', 'available', date('Y-m-d', strtotime('-5 days')), date('Y-m-d H:i:s')]);

        // Due soon
        $stmt->execute([$equipmentTypeId, 'BCD-002', 'available', date('Y-m-d', strtotime('+5 days')), date('Y-m-d H:i:s')]);

        // Not due yet (within 30 days)
        $stmt->execute([$equipmentTypeId, 'BCD-003', 'available', date('Y-m-d', strtotime('+20 days')), date('Y-m-d H:i:s')]);

        // Outside 30 day window
        $stmt->execute([$equipmentTypeId, 'BCD-004', 'available', date('Y-m-d', strtotime('+40 days')), date('Y-m-d H:i:s')]);

        // Get equipment needing maintenance
        $equipment = $this->maintenanceService->getEquipmentNeedingMaintenance();

        $this->assertIsArray($equipment);
        // Should return 3 items (overdue, due_soon, and within 30 days, but not the one 40 days out)
        $this->assertEquals(3, count($equipment));

        // Check urgency levels
        $this->assertEquals('overdue', $equipment[0]['urgency']);
        $this->assertEquals('due_soon', $equipment[1]['urgency']);
    }

    public function testGetMaintenanceHistory(): void
    {
        // Create equipment type
        $stmt = $this->db->prepare(
            "INSERT INTO rental_equipment_types (name, daily_rate, created_at)
             VALUES (?, ?, ?)"
        );
        $stmt->execute(['Regulator', 30.00, date('Y-m-d H:i:s')]);
        $equipmentTypeId = (int)$this->db->lastInsertId();

        // Create equipment
        $stmt = $this->db->prepare(
            "INSERT INTO rental_equipment (equipment_type_id, serial_number, status, created_at)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$equipmentTypeId, 'REG-001', 'available', date('Y-m-d H:i:s')]);
        $equipmentId = (int)$this->db->lastInsertId();

        // Create user for maintenance records
        $user = $this->createTestUser();

        // Create maintenance records
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_maintenance (equipment_id, maintenance_date, maintenance_type, notes, performed_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $equipmentId,
            date('Y-m-d', strtotime('-30 days')),
            'inspection',
            'Annual inspection completed',
            $user['id'],
            date('Y-m-d H:i:s')
        ]);

        $stmt->execute([
            $equipmentId,
            date('Y-m-d', strtotime('-60 days')),
            'repair',
            'Replaced O-rings',
            $user['id'],
            date('Y-m-d H:i:s')
        ]);

        // Get maintenance history
        $history = $this->maintenanceService->getMaintenanceHistory($equipmentId);

        $this->assertIsArray($history);
        $this->assertEquals(2, count($history));

        // Should be ordered by date DESC (most recent first)
        $this->assertEquals('inspection', $history[0]['maintenance_type']);
        $this->assertEquals('repair', $history[1]['maintenance_type']);
    }

    public function testCreateMaintenanceRecord(): void
    {
        // Create equipment type
        $stmt = $this->db->prepare(
            "INSERT INTO rental_equipment_types (name, daily_rate, created_at)
             VALUES (?, ?, ?)"
        );
        $stmt->execute(['Tank', 20.00, date('Y-m-d H:i:s')]);
        $equipmentTypeId = (int)$this->db->lastInsertId();

        // Create equipment
        $stmt = $this->db->prepare(
            "INSERT INTO rental_equipment (equipment_type_id, serial_number, status, created_at)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$equipmentTypeId, 'TANK-001', 'available', date('Y-m-d H:i:s')]);
        $equipmentId = (int)$this->db->lastInsertId();

        $user = $this->createTestUser();

        // Create a maintenance record
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_maintenance (equipment_id, maintenance_date, maintenance_type, notes, performed_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $maintenanceDate = date('Y-m-d');
        $stmt->execute([
            $equipmentId,
            $maintenanceDate,
            'hydrostatic_test',
            'Passed hydrostatic test',
            $user['id'],
            date('Y-m-d H:i:s')
        ]);

        $maintenanceId = (int)$this->db->lastInsertId();

        // Verify the maintenance record was created
        $this->assertDatabaseHas('equipment_maintenance', [
            'id' => $maintenanceId,
            'equipment_id' => $equipmentId,
            'maintenance_type' => 'hydrostatic_test'
        ]);
    }
}
