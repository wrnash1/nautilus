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
            header('Location: /');
            exit;
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
            header('Location: /workorders');
            exit;
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
            header('Location: /workorders');
            exit;
        }
        
        $id = $this->workOrderService->createWorkOrder($_POST);
        
        $_SESSION['flash_success'] = 'Work order created successfully!';
        header('Location: /workorders/' . $id);
        exit;
    }
    
    public function show(int $id)
    {
        if (!hasPermission('workorders.view')) {
            header('Location: /workorders');
            exit;
        }
        
        $workOrder = $this->workOrderService->getWorkOrderById($id);
        
        if (!$workOrder) {
            $_SESSION['flash_error'] = 'Work order not found';
            header('Location: /workorders');
            exit;
        }
        
        $notes = $this->workOrderService->getWorkOrderNotes($id);
        
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
            header('Location: /workorders');
            exit;
        }
        
        $workOrder = $this->workOrderService->getWorkOrderById($id);
        
        if (!$workOrder) {
            $_SESSION['flash_error'] = 'Work order not found';
            header('Location: /workorders');
            exit;
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
            header('Location: /workorders');
            exit;
        }
        
        $this->workOrderService->updateWorkOrder($id, $_POST);
        
        $_SESSION['flash_success'] = 'Work order updated successfully!';
        header('Location: /workorders/' . $id);
        exit;
    }
    
    public function updateStatus(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            header('Location: /workorders');
            exit;
        }
        
        $this->workOrderService->updateStatus($id, $_POST['status']);
        
        $_SESSION['flash_success'] = 'Status updated successfully!';
        header('Location: /workorders/' . $id);
        exit;
    }
    
    public function delete(int $id)
    {
        if (!hasPermission('workorders.delete')) {
            header('Location: /workorders');
            exit;
        }
        
        $this->workOrderService->deleteWorkOrder($id);
        
        $_SESSION['flash_success'] = 'Work order deleted successfully!';
        header('Location: /workorders');
        exit;
    }
    
    public function addNote(int $id)
    {
        if (!hasPermission('workorders.edit')) {
            header('Location: /workorders');
            exit;
        }
        
        $this->workOrderService->addNote($id, $_POST);
        
        $_SESSION['flash_success'] = 'Note added successfully!';
        header('Location: /workorders/' . $id);
        exit;
    }
}
