<?php

namespace App\Services\Integration;

class GoogleWorkspaceService
{
    public function syncCalendar(array $event): bool
    {
        
        return false;
    }
    
    public function sendEmail(array $emailData): bool
    {
        
        return false;
    }
    
    public function uploadToDrive(string $filePath, string $folderId): ?string
    {
        
        return null;
    }
}
