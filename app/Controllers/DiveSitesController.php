<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\DiveSites\WeatherService;

class DiveSitesController extends Controller
{
    private WeatherService $weatherService;

    public function __construct()
    {
        parent::__construct();
        $this->weatherService = new WeatherService();
    }

    /**
     * List all dive sites
     */
    public function index(): void
    {
        $this->checkPermission('dive_sites.view');

        $sql = "SELECT ds.*, COUNT(dsc.id) as condition_count
                FROM dive_sites ds
                LEFT JOIN dive_site_conditions dsc ON ds.id = dsc.dive_site_id
                GROUP BY ds.id
                ORDER BY ds.name";

        $stmt = $this->db->getConnection()->query($sql);
        $sites = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('dive_sites/index', [
            'title' => 'Dive Sites',
            'sites' => $sites
        ]);
    }

    /**
     * Show individual dive site with weather
     */
    public function show(int $id): void
    {
        $this->checkPermission('dive_sites.view');

        // Get dive site
        $sql = "SELECT * FROM dive_sites WHERE id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        $site = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$site) {
            $_SESSION['error'] = 'Dive site not found';
            $this->redirect('/dive-sites');
            return;
        }

        // Get current weather
        $currentWeather = $this->weatherService->getCurrentWeather($id);

        // Get forecast
        $forecast = $this->weatherService->getForecast($id, 7);

        // Get dive conditions rating
        $conditionsRating = null;
        if ($currentWeather) {
            $conditionsRating = $this->weatherService->getDiveConditionsRating($currentWeather);
        }

        // Get recent conditions history
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $history = $this->weatherService->getHistoricalConditions($id, $startDate, $endDate);

        // Get associated trips
        $sql = "SELECT ts.*, t.name as trip_name
                FROM trip_dive_sites tds
                JOIN trip_schedules ts ON tds.trip_schedule_id = ts.id
                JOIN trips t ON ts.trip_id = t.id
                WHERE tds.dive_site_id = ?
                AND ts.departure_date >= CURDATE()
                ORDER BY ts.departure_date
                LIMIT 5";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        $upcomingTrips = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('dive_sites/show', [
            'title' => $site['name'],
            'site' => $site,
            'current_weather' => $currentWeather,
            'forecast' => $forecast,
            'conditions_rating' => $conditionsRating,
            'history' => $history,
            'upcoming_trips' => $upcomingTrips
        ]);
    }

    /**
     * Create new dive site
     */
    public function create(): void
    {
        $this->checkPermission('dive_sites.create');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        $this->view('dive_sites/create', [
            'title' => 'Add Dive Site'
        ]);
    }

    /**
     * Store new dive site
     */
    private function store(): void
    {
        try {
            $sql = "INSERT INTO dive_sites
                    (name, location, country, latitude, longitude, max_depth, average_depth,
                     difficulty_level, description, best_season, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([
                $_POST['name'],
                $_POST['location'] ?? null,
                $_POST['country'] ?? null,
                $_POST['latitude'] ?? null,
                $_POST['longitude'] ?? null,
                $_POST['max_depth'] ?? null,
                $_POST['average_depth'] ?? null,
                $_POST['difficulty_level'] ?? 'beginner',
                $_POST['description'] ?? null,
                $_POST['best_season'] ?? null
            ]);

            $siteId = (int)$this->db->getConnection()->lastInsertId();

            $_SESSION['success'] = 'Dive site added successfully';
            $this->redirect('/dive-sites/' . $siteId);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to add dive site: ' . $e->getMessage();
            $this->redirect('/dive-sites/create');
        }
    }

    /**
     * Get weather data via AJAX
     */
    public function weather(int $id): void
    {
        header('Content-Type: application/json');

        $type = $_GET['type'] ?? 'current';

        try {
            switch ($type) {
                case 'current':
                    $data = $this->weatherService->getCurrentWeather($id);
                    if ($data) {
                        $data['rating'] = $this->weatherService->getDiveConditionsRating($data);
                    }
                    break;

                case 'forecast':
                    $days = (int)($_GET['days'] ?? 7);
                    $data = $this->weatherService->getForecast($id, $days);
                    break;

                case 'marine':
                    $data = $this->weatherService->getMarineData($id);
                    break;

                default:
                    throw new \Exception('Invalid weather type');
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update weather for all sites (can be called by cron)
     */
    public function updateAllWeather(): void
    {
        $this->checkPermission('dive_sites.manage');

        $results = $this->weatherService->updateAllSites();

        echo json_encode([
            'success' => true,
            'results' => $results
        ]);
    }
}
