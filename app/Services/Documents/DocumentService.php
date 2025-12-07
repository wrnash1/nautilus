<?php

namespace App\Services\Documents;

use App\Core\Database;
use PDO;
use App\Core\Logger;

/**
 * Document Service
 * Handles document storage and management
 */
class DocumentService
{
    private PDO $db;
    private Logger $logger;
    private string $uploadPath;

    public function __construct()
    {
        $this->db = Database::getPdo();
        $this->logger = new Logger();
        $this->uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/../storage/documents';

        // Ensure upload directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload and create a new document
     */
    public function create(array $fileData, array $documentData): int
    {
        try {
            // Validate file upload
            if (!isset($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
                throw new \Exception('Invalid file upload');
            }

            // Generate unique filename
            $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            $filename = uniqid('doc_') . '_' . time() . '.' . $extension;
            $filePath = $this->uploadPath . '/' . $filename;

            // Move uploaded file
            if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
                throw new \Exception('Failed to move uploaded file');
            }

            // Insert document record
            $sql = "INSERT INTO documents (
                        document_type, title, description, file_path, file_name,
                        file_size, mime_type, parent_id, tags, uploaded_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $documentData['document_type'],
                $documentData['title'],
                $documentData['description'] ?? null,
                $filePath,
                $fileData['name'],
                $fileData['size'],
                $fileData['type'],
                $documentData['parent_id'] ?? null,
                isset($documentData['tags']) ? json_encode($documentData['tags']) : null,
                $documentData['uploaded_by']
            ]);

            $documentId = (int)$this->db->lastInsertId();

            $this->logger->info('Document uploaded', [
                'document_id' => $documentId,
                'filename' => $fileData['name']
            ]);

            return $documentId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload document', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update document metadata
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $values = [];

            $allowedFields = ['document_type', 'title', 'description', 'tags', 'parent_id'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if ($field === 'tags' && is_array($data[$field])) {
                        $fields[] = "$field = ?";
                        $values[] = json_encode($data[$field]);
                    } else {
                        $fields[] = "$field = ?";
                        $values[] = $data[$field];
                    }
                }
            }

            if (empty($fields)) {
                return false;
            }

            $values[] = $id;

            $sql = "UPDATE documents SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            $this->logger->info('Document updated', ['document_id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update document', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get document by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT d.*,
                       CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                FROM documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.id = ? AND d.is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $document = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($document && $document['tags']) {
            $document['tags'] = json_decode($document['tags'], true);
        }

        return $document ?: null;
    }

    /**
     * Get all documents with filters
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT d.*,
                       CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                FROM documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.is_active = 1";

        $params = [];

        if (!empty($filters['document_type'])) {
            $sql .= " AND d.document_type = ?";
            $params[] = $filters['document_type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (d.title LIKE ? OR d.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['uploaded_by'])) {
            $sql .= " AND d.uploaded_by = ?";
            $params[] = $filters['uploaded_by'];
        }

        if (!empty($filters['parent_id'])) {
            $sql .= " AND d.parent_id = ?";
            $params[] = $filters['parent_id'];
        }

        $sql .= " ORDER BY d.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Parse tags
        foreach ($documents as &$doc) {
            if ($doc['tags']) {
                $doc['tags'] = json_decode($doc['tags'], true);
            }
        }

        return $documents;
    }

    /**
     * Get document types with counts
     */
    public function getDocumentTypes(): array
    {
        $sql = "SELECT document_type, COUNT(*) as count
                FROM documents
                WHERE is_active = 1
                GROUP BY document_type
                ORDER BY count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Search documents
     */
    public function search(string $query, ?string $documentType = null): array
    {
        $sql = "SELECT d.*,
                       CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name,
                       MATCH(d.title, d.description) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.is_active = 1
                AND MATCH(d.title, d.description) AGAINST(? IN NATURAL LANGUAGE MODE)";

        $params = [$query, $query];

        if ($documentType) {
            $sql .= " AND d.document_type = ?";
            $params[] = $documentType;
        }

        $sql .= " ORDER BY relevance DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Parse tags
        foreach ($documents as &$doc) {
            if ($doc['tags']) {
                $doc['tags'] = json_decode($doc['tags'], true);
            }
        }

        return $documents;
    }

    /**
     * Delete a document (soft delete)
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "UPDATE documents SET is_active = 0, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $this->logger->info('Document deleted (soft)', ['document_id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete document', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Permanently delete a document and its file
     */
    public function permanentlyDelete(int $id): bool
    {
        try {
            $document = $this->getById($id);

            if (!$document) {
                return false;
            }

            // Delete file from filesystem
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }

            // Delete database record
            $sql = "DELETE FROM documents WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $this->logger->info('Document permanently deleted', ['document_id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to permanently delete document', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Download a document
     */
    public function download(int $id): void
    {
        $document = $this->getById($id);

        if (!$document || !file_exists($document['file_path'])) {
            http_response_code(404);
            echo 'Document not found';
            return;
        }

        header('Content-Type: ' . $document['mime_type']);
        header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
        header('Content-Length: ' . $document['file_size']);

        readfile($document['file_path']);
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        $sql = "SELECT
                    COUNT(*) as total_documents,
                    SUM(file_size) as total_size,
                    AVG(file_size) as avg_size,
                    MAX(file_size) as max_size
                FROM documents
                WHERE is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new version of a document
     */
    public function createVersion(int $parentId, array $fileData, array $documentData): int
    {
        $parent = $this->getById($parentId);

        if (!$parent) {
            throw new \Exception('Parent document not found');
        }

        // Inherit document type and other metadata from parent
        $documentData['document_type'] = $documentData['document_type'] ?? $parent['document_type'];
        $documentData['title'] = $documentData['title'] ?? $parent['title'];
        $documentData['parent_id'] = $parentId;

        // Increment version
        $version = ((int)$parent['version']) + 1;

        $documentId = $this->create($fileData, $documentData);

        // Update version number
        $this->db->prepare("UPDATE documents SET version = ? WHERE id = ?")
                 ->execute([$version, $documentId]);

        return $documentId;
    }
}
