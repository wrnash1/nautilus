<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Services\Documents\DocumentService;

class DocumentsController
{
    private DocumentService $documentService;

    public function __construct()
    {
        $this->documentService = new DocumentService();
    }

    /**
     * Display all documents
     */
    public function index()
    {
        $filters = [
            'document_type' => $_GET['type'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $documents = $this->documentService->getAll($filters);
        $documentTypes = $this->documentService->getDocumentTypes();
        $stats = $this->documentService->getStorageStats();

        require __DIR__ . '/../Views/documents/index.php';
    }

    /**
     * Show upload form
     */
    public function create()
    {
        require __DIR__ . '/../Views/documents/create.php';
    }

    /**
     * Handle document upload
     */
    public function store()
    {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header('Location: /store/documents/create');
            exit;
        }

        try {
            // Validate file upload
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('File upload failed');
            }

            // Validate file size (max 50MB)
            $maxSize = 50 * 1024 * 1024;
            if ($_FILES['document']['size'] > $maxSize) {
                throw new \Exception('File size exceeds 50MB limit');
            }

            // Validate required fields
            if (empty($_POST['document_type']) || empty($_POST['title'])) {
                throw new \Exception('Document type and title are required');
            }

            $documentData = [
                'document_type' => $_POST['document_type'],
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? null,
                'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [],
                'uploaded_by' => Auth::userId()
            ];

            $id = $this->documentService->create($_FILES['document'], $documentData);

            $_SESSION['flash_success'] = 'Document uploaded successfully';
            header("Location: /store/documents/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            header('Location: /store/documents/create');
        }
    }

    /**
     * Show document details
     */
    public function show(int $id)
    {
        $document = $this->documentService->getById($id);

        if (!$document) {
            $_SESSION['flash_error'] = 'Document not found';
            header('Location: /store/documents');
            exit;
        }

        // Get versions if this is a parent document
        $versions = [];
        if (!$document['parent_id']) {
            $versions = $this->documentService->getAll(['parent_id' => $id]);
        }

        require __DIR__ . '/../Views/documents/show.php';
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        $document = $this->documentService->getById($id);

        if (!$document) {
            $_SESSION['flash_error'] = 'Document not found';
            header('Location: /store/documents');
            exit;
        }

        require __DIR__ . '/../Views/documents/edit.php';
    }

    /**
     * Update document metadata
     */
    public function update(int $id)
    {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header("Location: /store/documents/{$id}/edit");
            exit;
        }

        try {
            $data = [
                'document_type' => $_POST['document_type'] ?? null,
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'tags' => !empty($_POST['tags']) ? explode(',', $_POST['tags']) : []
            ];

            $data = array_filter($data, fn($value) => $value !== null);

            $this->documentService->update($id, $data);

            $_SESSION['flash_success'] = 'Document updated successfully';
            header("Location: /store/documents/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            header("Location: /store/documents/{$id}/edit");
        }
    }

    /**
     * Delete document
     */
    public function delete(int $id)
    {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header("Location: /store/documents/{$id}");
            exit;
        }

        try {
            $this->documentService->delete($id);
            $_SESSION['flash_success'] = 'Document deleted';
            header('Location: /store/documents');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            header("Location: /store/documents/{$id}");
        }
    }

    /**
     * Download document
     */
    public function download(int $id)
    {
        $this->documentService->download($id);
    }

    /**
     * Search documents
     */
    public function search()
    {
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? null;

        if (empty($query)) {
            header('Location: /store/documents');
            exit;
        }

        $documents = $this->documentService->search($query, $type);
        $documentTypes = $this->documentService->getDocumentTypes();

        require __DIR__ . '/../Views/documents/search.php';
    }

    /**
     * Upload new version
     */
    public function uploadVersion(int $id)
    {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header("Location: /store/documents/{$id}");
            exit;
        }

        try {
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('File upload failed');
            }

            $documentData = [
                'uploaded_by' => Auth::userId()
            ];

            $newId = $this->documentService->createVersion($id, $_FILES['document'], $documentData);

            $_SESSION['flash_success'] = 'New version uploaded successfully';
            header("Location: /store/documents/{$newId}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            header("Location: /store/documents/{$id}");
        }
    }
}
