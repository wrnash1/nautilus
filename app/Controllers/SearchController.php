<?php

namespace App\Controllers;

use App\Services\Search\SearchService;

class SearchController
{
    private SearchService $searchService;

    public function __construct()
    {
        $this->searchService = new SearchService();
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
        // Pass specific modules if selected, otherwise service defaults to likely all relevant
        $options = [];
        if (!empty($modules)) {
            $options['entities'] = $modules;
        }

        $searchResponse = $this->searchService->universalSearch($query, $options);
        
        $results = $searchResponse['results'] ?? [];
        $totalResults = $searchResponse['total_results'] ?? 0;

        require __DIR__ . '/../Views/search/results.php';
    }

    /**
     * AJAX autocomplete suggestions
     */
    public function suggestions()
    {
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? 'products'; // Default to products if not specified

        if (empty($query) || strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }

        // New service expects (query, entity, limit)
        // If type is not one supported by suggestions, it might return empty or error.
        // The view JS seems to query ?q=... without type, need to check that.
        // View JS: fetch(`/store/search/suggestions?q=${encodeURIComponent(query)}`)
        // It provides a single list. The new service provides suggestions per entity.
        // To maintain compatibility with the view which expects a mixed list, we might need to query multiple.
        
        $suggestions = [];
        
        // If type is specified, query just that.
        if (isset($_GET['type'])) {
             $result = $this->searchService->getSearchSuggestions($query, $type, 10);
             if ($result['success']) {
                 $suggestions = $result['suggestions'];
             }
        } else {
            // Aggregate from common types if no type specified (legacy behavior compat)
            $types = ['products', 'customers', 'courses'];
            foreach ($types as $t) {
                $res = $this->searchService->getSearchSuggestions($query, $t, 3);
                if ($res['success']) {
                    // Add type label if not present in suggestion data
                    foreach ($res['suggestions'] as &$s) {
                        $s['type'] = $t; 
                    }
                    $suggestions = array_merge($suggestions, $res['suggestions']);
                }
            }
        }

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
