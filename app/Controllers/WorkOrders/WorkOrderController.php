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
            redirect('/');
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
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function create()
    {
        if (!hasPermission('workorders.create')) {
            redirect('/workorders');
        }
        
        $pageTitle = 'Create Work Order';
        $activeMenu = 'workorders';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/workorders/create.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function store()
    {
        if (!hasPermission('workorders.create')) {
            redirect('/workorders');
        }
        
        $id = $this->workOrderService->createWorkOrder($_POST);
        
        $_SESSION['flash_success'] = 'Work order created successfully!';
        redirect('/workorders/' . $id);
    }
    
    public function show(int $id)
    {
        if (!hasPermission('workorders.view')) {
            redirect('/workorders');
        }
        
        $workOrder = $this->workOrderService->getWorkOrderById($id);
        
        if (!$workOrder) {
            $_SESSION['flash_error'] = 'Work order not found';
            redirect('/workorders');
        }
        
        $notes = $this->workOrderService->getWorkOrderNotes($id);
        $staff = $this->workOrderService->getAvailableStaff();
        
        $pageTitle = 'Work Order ' . $workOrder['work_order_number'];
        $activeMenu = 'workorders';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/workorders/show.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function edit(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/workorders');
        }
        
        $workOrder = $this->workOrderService->getWorkOrderById($id);
        
        if (!$workOrder) {
            $_SESSION['flash_error'] = 'Work order not found';
            redirect('/workorders');
        }
        
        $pageTitle = 'Edit Work Order';
        $activeMenu = 'workorders';
        $user = $_SESSION['user'] ?? [];
        
        ob_start();
        require __DIR__ . '/../../Views/workorders/edit.php';
        $content = ob_get_clean();
        
        require __DIR__ . '/../../Views/layouts/app.php';
    }
    
    public function update(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/workorders');
        }
        
        $this->workOrderService->updateWorkOrder($id, $_POST);
        
        $_SESSION['flash_success'] = 'Work order updated successfully!';
        redirect('/workorders/' . $id);
    }
    
    public function updateStatus(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/workorders');
        }
        
        $this->workOrderService->updateStatus($id, $_POST['status']);
        
        $_SESSION['flash_success'] = 'Status updated successfully!';
        redirect('/workorders/' . $id);
    }
    
    public function delete(int $id)
    {
        if (!hasPermission('workorders.delete')) {
            redirect('/workorders');
        }
        
        $this->workOrderService->deleteWorkOrder($id);
        
        $_SESSION['flash_success'] = 'Work order deleted successfully!';
        redirect('/workorders');
    }
    
    public function addNote(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/workorders');
        }
        
        $this->workOrderService->addNote($id, $_POST);
        
        $_SESSION['flash_success'] = 'Note added successfully!';
        redirect('/workorders/' . $id);
    }
    
    public function assign(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            redirect('/workorders/' . $id);
        }
        
        $assignedTo = $_POST['assigned_to'] ?? null;
        
        if ($assignedTo) {
            $this->workOrderService->assignWorkOrder($id, $assignedTo);
            $_SESSION['flash_success'] = 'Work order assigned successfully!';
        }
        
        redirect('/workorders/' . $id);
    }
}
