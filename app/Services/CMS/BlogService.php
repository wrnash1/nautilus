<?php

namespace App\Services\CMS;

use App\Core\Database;

class BlogService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all blog posts
     *
     * @return array
     */
    public function getAllPosts()
    {
        try {
            $stmt = $this->db->query("
                SELECT bp.*, u.first_name, u.last_name,
                       COUNT(DISTINCT bpc.category_id) as category_count,
                       COUNT(DISTINCT bpt.tag_id) as tag_count
                FROM blog_posts bp
                LEFT JOIN users u ON bp.author_id = u.id
                LEFT JOIN blog_post_categories bpc ON bp.id = bpc.post_id
                LEFT JOIN blog_post_tags bpt ON bp.id = bpt.post_id
                GROUP BY bp.id
                ORDER BY bp.created_at DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get post by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getPostById($id)
    {
        $stmt = $this->db->prepare("
            SELECT bp.*, u.first_name, u.last_name 
            FROM blog_posts bp
            LEFT JOIN users u ON bp.author_id = u.id
            WHERE bp.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get post by slug
     * 
     * @param string $slug
     * @return array|null
     */
    public function getPostBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create new post
     * 
     * @param array $data
     * @param array $categoryIds
     * @param array $tagIds
     * @return int|false Post ID or false on failure
     */
    public function createPost($data, $categoryIds = [], $tagIds = [])
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO blog_posts 
                (title, slug, excerpt, content, featured_image, meta_description, 
                 meta_keywords, status, author_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['title'],
                $data['slug'],
                $data['excerpt'],
                $data['content'],
                $data['featured_image'],
                $data['meta_description'],
                $data['meta_keywords'],
                $data['status'],
                $data['author_id']
            ]);

            $postId = $this->db->lastInsertId();

            foreach ($categoryIds as $categoryId) {
                $stmt = $this->db->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
                $stmt->execute([$postId, $categoryId]);
            }

            foreach ($tagIds as $tagId) {
                $stmt = $this->db->prepare("INSERT INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)");
                $stmt->execute([$postId, $tagId]);
            }

            $this->db->commit();
            return $postId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Update post
     * 
     * @param int $id
     * @param array $data
     * @param array $categoryIds
     * @param array $tagIds
     * @return bool
     */
    public function updatePost($id, $data, $categoryIds = [], $tagIds = [])
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                UPDATE blog_posts 
                SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?,
                    meta_description = ?, meta_keywords = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['title'],
                $data['slug'],
                $data['excerpt'],
                $data['content'],
                $data['featured_image'],
                $data['meta_description'],
                $data['meta_keywords'],
                $data['status'],
                $id
            ]);

            $this->db->prepare("DELETE FROM blog_post_categories WHERE post_id = ?")->execute([$id]);
            foreach ($categoryIds as $categoryId) {
                $stmt = $this->db->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
                $stmt->execute([$id, $categoryId]);
            }

            $this->db->prepare("DELETE FROM blog_post_tags WHERE post_id = ?")->execute([$id]);
            foreach ($tagIds as $tagId) {
                $stmt = $this->db->prepare("INSERT INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)");
                $stmt->execute([$id, $tagId]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Delete post
     * 
     * @param int $id
     * @return bool
     */
    public function deletePost($id)
    {
        $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Publish post
     * 
     * @param int $id
     * @return bool
     */
    public function publishPost($id)
    {
        $stmt = $this->db->prepare("
            UPDATE blog_posts 
            SET status = 'published', published_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Get post categories
     * 
     * @param int $postId
     * @return array
     */
    public function getPostCategories($postId)
    {
        $stmt = $this->db->prepare("
            SELECT bc.* 
            FROM blog_categories bc
            JOIN blog_post_categories bpc ON bc.id = bpc.category_id
            WHERE bpc.post_id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get post tags
     * 
     * @param int $postId
     * @return array
     */
    public function getPostTags($postId)
    {
        $stmt = $this->db->prepare("
            SELECT bt.* 
            FROM blog_tags bt
            JOIN blog_post_tags bpt ON bt.id = bpt.tag_id
            WHERE bpt.post_id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all categories
     * 
     * @return array
     */
    public function getAllCategories()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM blog_categories ORDER BY name ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get all tags
     *
     * @return array
     */
    public function getAllTags()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM blog_tags ORDER BY name ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
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
}
