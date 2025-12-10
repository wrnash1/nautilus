<?php

namespace App\Controllers\Integrations;

use App\Controllers\BaseController;
use App\Services\Integration\GoogleContactsService;
use Google\Client as GoogleClient;
use Exception;

class GoogleContactsController extends BaseController
{
    private GoogleContactsService $contactsService;
    
    public function __construct()
    {
        parent::__construct();
        $this->contactsService = new GoogleContactsService($this->db, $this->tenantId);
    }
    
    /**
     * Display Google Contacts integration page
     */
    public function index()
    {
        $config = $this->getConfig();
        $stats = $this->getStats();
        $recentLogs = $this->getRecentLogs(10);
        
        return $this->view('admin/integrations/google-contacts', [
            'config' => $config,
            'stats' => $stats,
            'logs' => $recentLogs,
            'isConnected' => !empty($config['access_token'])
        ]);
    }
    
    /**
     * Initiate OAuth connection flow
     */
    public function connect()
    {
        try {
            $client = new GoogleClient();
            $client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
            $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
            $client->setRedirectUri($this->getRedirectUri());
            $client->addScope('https://www.googleapis.com/auth/contacts');
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            
            // Handle OAuth callback
            if (isset($_GET['code'])) {
                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                
                if (isset($token['error'])) {
                    throw new Exception('OAuth error: ' . $token['error_description']);
                }
                
                $this->saveOAuthToken($token);
                
                $this->redirect('/admin/integrations/google-contacts?success=connected');
                return;
            }
            
            // Redirect to Google OAuth
            $authUrl = $client->createAuthUrl();
            header('Location: ' . $authUrl);
            exit;
            
        } catch (Exception $e) {
            $this->redirect('/admin/integrations/google-contacts?error=' . urlencode($e->getMessage()));
        }
    }
    
    /**
     * Disconnect from Google
     */
    public function disconnect()
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE google_contacts_sync_config 
                SET access_token = NULL,
                    refresh_token = NULL,
                    sync_enabled = 0,
                    sync_status = 'idle'
                WHERE tenant_id = ?
            ");
            $stmt->execute([$this->tenantId]);
            
            $this->jsonResponse(['success' => true]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Trigger manual sync
     */
    public function manualSync()
    {
        try {
            $syncType = $_POST['sync_type'] ?? 'incremental';
            
            if ($syncType === 'full') {
                $results = $this->contactsService->performFullSync($this->userId);
            } else {
                $results = $this->contactsService->performIncrementalSync($this->userId);
            }
            
            $this->jsonResponse([
                'success' => true,
                'results' => $results
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save sync configuration
     */
    public function saveConfig()
    {
        try {
            $config = [
                'sync_enabled' => isset($_POST['sync_enabled']),
                'sync_direction' => $_POST['sync_direction'] ?? 'two_way',
                'sync_frequency_minutes' => (int)($_POST['sync_frequency_minutes'] ?? 15),
                'sync_only_active' => isset($_POST['sync_only_active']),
                'conflict_strategy' => $_POST['conflict_strategy'] ?? 'last_modified_wins',
                'batch_size' => (int)($_POST['batch_size'] ?? 200)
            ];
            
            $stmt = $this->db->prepare("
                UPDATE google_contacts_sync_config 
                SET sync_enabled = ?,
                    sync_direction = ?,
                    sync_frequency_minutes = ?,
                    sync_only_active = ?,
                    conflict_strategy = ?,
                    batch_size = ?,
                    next_sync_at = IF(? = 1, DATE_ADD(NOW(), INTERVAL ? MINUTE), NULL)
                WHERE tenant_id = ?
            ");
            
            $stmt->execute([
                $config['sync_enabled'],
                $config['sync_direction'],
                $config['sync_frequency_minutes'],
                $config['sync_only_active'],
                $config['conflict_strategy'],
                $config['batch_size'],
                $config['sync_enabled'],
                $config['sync_frequency_minutes'],
                $this->tenantId
            ]);
            
            $this->jsonResponse(['success' => true]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * View sync logs
     */
    public function viewLogs()
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->prepare("
            SELECT * FROM google_contacts_sync_log 
            WHERE tenant_id = ?
            ORDER BY started_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$this->tenantId, $perPage, $offset]);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return $this->view('admin/integrations/google-contacts-logs', [
            'logs' => $logs,
            'page' => $page
        ]);
    }
    
    // Helper methods
    
    private function getConfig(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM google_contacts_sync_config WHERE tenant_id = ?
        ");
        $stmt->execute([$this->tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }
    
    private function getStats(): array
    {
        $config = $this->getConfig();
        
        // Get mapping count
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_mapped,
                SUM(has_conflict) as conflicts,
                SUM(sync_status = 'error') as errors
            FROM google_contacts_sync_mapping 
            WHERE tenant_id = ?
        ");
        $stmt->execute([$this->tenantId]);
        $mappingStats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return [
            'total_synced' => $config['total_exports'] + $config['total_imports'],
            'total_exports' => $config['total_exports'] ?? 0,
            'total_imports' => $config['total_imports'] ?? 0,
            'total_mapped' => $mappingStats['total_mapped'] ?? 0,
            'conflicts' => $mappingStats['conflicts'] ?? 0,
            'errors' => $mappingStats['errors'] ?? 0,
            'last_sync' => $config['last_sync_at'] ?? null,
            'next_sync' => $config['next_sync_at'] ?? null
        ];
    }
    
    private function getRecentLogs(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM google_contacts_sync_log 
            WHERE tenant_id = ?
            ORDER BY started_at DESC
            LIMIT ?
        ");
        $stmt->execute([$this->tenantId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function saveOAuthToken(array $token): void
    {
        // Check if config exists
        $stmt = $this->db->prepare("
            SELECT id FROM google_contacts_sync_config WHERE tenant_id = ?
        ");
        $stmt->execute([$this->tenantId]);
        $exists = $stmt->fetch();
        
        $accessToken = base64_encode(json_encode($token));
        $refreshToken = base64_encode($token['refresh_token'] ?? '');
        $expiresAt = date('Y-m-d H:i:s', time() + ($token['expires_in'] ?? 3600));
        
        if ($exists) {
            $stmt = $this->db->prepare("
                UPDATE google_contacts_sync_config 
                SET access_token = ?,
                    refresh_token = ?,
                    token_expires_at = ?,
                    authorized_at = NOW(),
                    authorized_by = ?
                WHERE tenant_id = ?
            ");
            $stmt->execute([$accessToken, $refreshToken, $expiresAt, $this->userId, $this->tenantId]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO google_contacts_sync_config 
                (tenant_id, access_token, refresh_token, token_expires_at, authorized_at, authorized_by)
                VALUES (?, ?, ?, ?, NOW(), ?)
            ");
            $stmt->execute([$this->tenantId, $accessToken, $refreshToken, $expiresAt, $this->userId]);
        }
    }
    
    private function getRedirectUri(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host/admin/integrations/google-contacts/connect";
    }
}
