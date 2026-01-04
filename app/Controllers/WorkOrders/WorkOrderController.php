<?php

namespace App\Controllers\WorkOrders;

use App\Services\WorkOrders\WorkOrderService;

class WorkOrderController
{
    private WorkOrderService $workOrderService;

    public function __construct()
    {
        $this->workOrderService = new WorkOrderService();
    }

    public function index()
    {
        if (!hasPermission('workorders.view')) {
            setFlashMessage('error', 'You do not have permission to view work orders.');
            redirect('/store');
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? ''
        ];

        $workOrders = $this->workOrderService->getWorkOrderList($filters);

        $pageTitle = 'Work Orders';
        $activeMenu = 'workorders';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/workorders/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    public function create()
    {
        if (!hasPermission('workorders.create')) {
            setFlashMessage('error', 'You do not have permission to create work orders.');
            redirect('/store/workorders');
            return;
        }

        // Get customers and equipment for the form
        $customers = $this->workOrderService->getCustomers();
        $equipmentTypes = $this->workOrderService->getEquipmentTypes();
        $staff = $this->workOrderService->getAvailableStaff();

        $pageTitle = 'Create Work Order';
        $activeMenu = 'workorders';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/workorders/create.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    public function store()
    {
        if (!hasPermission('workorders.create')) {
            redirect('/store/workorders');
            return;
        }

        $id = $this->workOrderService->createWorkOrder($_POST);

        setFlashMessage('success', 'Work order created successfully!');
        redirect('/store/workorders/' . $id);
    }

    public function show(int $id)
    {
        if (!hasPermission('workorders.view')) {
            redirect('/store/workorders');
            return;
        }

        $workOrder = $this->workOrderService->getWorkOrderById($id);

        if (!$workOrder) {
            setFlashMessage('error', 'Work order not found');
            redirect('/store/workorders');
            return;
        }

        $notes = $this->workOrderService->getWorkOrderNotes($id);
        $staff = $this->workOrderService->getAvailableStaff();

        $pageTitle = 'Work Order ' . $workOrder['work_order_number'];
        $activeMenu = 'workorders';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/workorders/show.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    public function edit(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/store/workorders');
            return;
        }

        $workOrder = $this->workOrderService->getWorkOrderById($id);

        if (!$workOrder) {
            setFlashMessage('error', 'Work order not found');
            redirect('/store/workorders');
            return;
        }

        $customers = $this->workOrderService->getCustomers();
        $equipmentTypes = $this->workOrderService->getEquipmentTypes();
        $staff = $this->workOrderService->getAvailableStaff();

        $pageTitle = 'Edit Work Order';
        $activeMenu = 'workorders';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/workorders/edit.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    public function update(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/store/workorders');
            return;
        }

        $this->workOrderService->updateWorkOrder($id, $_POST);

        setFlashMessage('success', 'Work order updated successfully!');
        redirect('/store/workorders/' . $id);
    }

    public function updateStatus(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/store/workorders');
            return;
        }

        $this->workOrderService->updateStatus($id, $_POST['status']);

        setFlashMessage('success', 'Status updated successfully!');
        redirect('/store/workorders/' . $id);
    }

    public function delete(int $id)
    {
        if (!hasPermission('workorders.delete')) {
            redirect('/store/workorders');
            return;
        }

        $this->workOrderService->deleteWorkOrder($id);

        setFlashMessage('success', 'Work order deleted successfully!');
        redirect('/store/workorders');
    }

    public function addNote(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/store/workorders');
            return;
        }

        $this->workOrderService->addNote($id, $_POST);

        setFlashMessage('success', 'Note added successfully!');
        redirect('/store/workorders/' . $id);
    }

    public function assign(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/store/workorders/' . $id);
            return;
        }

        $assignedTo = $_POST['assigned_to'] ?? null;

        if ($assignedTo) {
            $this->workOrderService->assignWorkOrder($id, $assignedTo);
            setFlashMessage('success', 'Work order assigned successfully!');
        }

        redirect('/store/workorders/' . $id);
    }
}
