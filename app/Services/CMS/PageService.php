<?php

namespace App\Services\CMS;

use App\Core\Database;

class PageService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all pages
     * 
     * @return array
     */
    public function getAllPages()
    {
        $stmt = $this->db->query("
            SELECT p.*, u.first_name, u.last_name 
            FROM pages p
            LEFT JOIN users u ON p.created_by = u.id
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get page by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getPageById($id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.first_name, u.last_name 
            FROM pages p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get page by slug
     * 
     * @param string $slug
     * @return array|null
     */
    public function getPageBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create new page
     * 
     * @param array $data
     * @return int|false Page ID or false on failure
     */
    public function createPage($data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        $stmt = $this->db->prepare("
            INSERT INTO pages 
            (title, slug, content, meta_description, meta_keywords, status, is_homepage, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['content'],
            $data['meta_description'],
            $data['meta_keywords'],
            $data['status'],
            $data['is_homepage'],
            $data['created_by']
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update page
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePage($id, $data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        $stmt = $this->db->prepare("
            UPDATE pages 
            SET title = ?, slug = ?, content = ?, meta_description = ?, 
                meta_keywords = ?, status = ?, is_homepage = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['content'],
            $data['meta_description'],
            $data['meta_keywords'],
            $data['status'],
            $data['is_homepage'],
            $id
        ]);
    }

    /**
     * Delete page
     * 
     * @param int $id
     * @return bool
     */
    public function deletePage($id)
    {
        $stmt = $this->db->prepare("DELETE FROM pages WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Publish page
     * 
     * @param int $id
     * @return bool
     */
    public function publishPage($id)
    {
        $stmt = $this->db->prepare("
            UPDATE pages 
            SET status = 'published', published_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Get homepage
     * 
     * @return array|null
     */
    public function getHomepage()
    {
        $stmt = $this->db->query("
            SELECT * FROM pages 
            WHERE is_homepage = 1 AND status = 'published' 
            LIMIT 1
        ");
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Generate slug from title
     * 
     * @param string $title
     * @return string
     */
    private function generateSlug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug;
    }

    /**
     * Check if slug exists
     * 
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
    public function slugExists($slug, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM pages WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM pages WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        
        return $stmt->fetchColumn() > 0;
    }
}
