<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Services\Search\GlobalSearchService;

class SearchController
{
    private GlobalSearchService $searchService;

    public function __construct()
    {
        $this->searchService = new GlobalSearchService();
    }

    /**
     * Global search page
     */
    public function index()
    {
        $query = $_GET['q'] ?? '';
        $modules = $_GET['modules'] ?? [];

        if (empty($query)) {
            require __DIR__ . '/../Views/search/index.php';
            return;
        }

        // Perform search
        $results = $this->searchService->search($query, $modules);
        $totalResults = $this->searchService->getResultCount($results);

        require __DIR__ . '/../Views/search/results.php';
    }

    /**
     * AJAX autocomplete suggestions
     */
    public function suggestions()
    {
        $query = $_GET['q'] ?? '';

        if (empty($query) || strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }

        $suggestions = $this->searchService->getSuggestions($query, 10);

        header('Content-Type: application/json');
        echo json_encode($suggestions);
    }

    /**
     * Quick find (by ID)
     */
    public function quickFind()
    {
        $type = $_GET['type'] ?? '';
        $id = $_GET['id'] ?? '';

        if (empty($type) || empty($id)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing parameters']);
            return;
        }

        $result = $this->searchService->quickFind($type, $id);

        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Not found']);
        }
    }

    /**
     * Advanced search page
     */
    public function advanced()
    {
        require __DIR__ . '/../Views/search/advanced.php';
    }
}
