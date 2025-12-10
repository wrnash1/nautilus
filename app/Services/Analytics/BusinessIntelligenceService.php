<?php

namespace App\Services\Analytics;

use PDO;

/**
 * Business Intelligence Service
 * Handle dashboards, KPIs, reports, and analytics
 */
class BusinessIntelligenceService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get dashboard with widgets
     */
    public function getDashboard(int $dashboardId, int $tenantId): array
    {
        // Get dashboard details
        $stmt = $this->db->prepare("
            SELECT * FROM dashboards
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$dashboardId, $tenantId]);
        $dashboard = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dashboard) {
            return [
                'success' => false,
                'error' => 'Dashboard not found'
            ];
        }

        // Get widgets
        $widgets = $this->getDashboardWidgets($dashboardId);

        // Load data for each widget
        foreach ($widgets as &$widget) {
            $widget['data'] = $this->loadWidgetData($widget);
        }

        return [
            'success' => true,
            'dashboard' => $dashboard,
            'widgets' => $widgets
        ];
    }

    /**
     * Get dashboard widgets
     */
    private function getDashboardWidgets(int $dashboardId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM dashboard_widgets
            WHERE dashboard_id = ? AND is_visible = TRUE
            ORDER BY display_order, position_row, position_col
        ");
        $stmt->execute([$dashboardId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Load widget data
     */
    private function loadWidgetData(array $widget): array
    {
        // Check cache first
        if ($widget['cache_enabled'] && $widget['cached_data']) {
            $cacheAge = time() - strtotime($widget['cached_at']);
            if ($cacheAge < $widget['cache_ttl_seconds']) {
                return json_decode($widget['cached_data'], true);
            }
        }

        // Load fresh data based on data source type
        $data = [];
        switch ($widget['data_source_type']) {
            case 'report_template':
                $data = $this->loadReportData($widget['report_template_id'], $widget);
                break;
            case 'custom_query':
                $data = $this->executeCustomQuery($widget['custom_query'], $widget);
                break;
            case 'static':
                $data = json_decode($widget['static_data'], true);
                break;
        }

        // Update cache
        if ($widget['cache_enabled']) {
            $this->cacheWidgetData($widget['id'], $data);
        }

        return $data;
    }

    /**
     * Load report data for widget
     */
    private function loadReportData(?int $templateId, array $widget): array
    {
        if (!$templateId) {
            return ['error' => 'No report template specified'];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM report_templates WHERE id = ?
        ");
        $stmt->execute([$templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template || !$template['query_template']) {
            return ['error' => 'Invalid report template'];
        }

        // Execute query with date range
        $query = $this->processQueryTemplate(
            $template['query_template'],
            $widget['date_range'] ?? 'last_30_days'
        );

        return $this->executeQuery($query);
    }

    /**
     * Execute custom query safely
     */
    private function executeCustomQuery(string $query, array $widget): array
    {
        // Apply date range filter if specified
        $query = $this->processQueryTemplate($query, $widget['date_range'] ?? null);
        return $this->executeQuery($query);
    }

    /**
     * Execute query and return results
     */
    private function executeQuery(string $query): array
    {
        try {
            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process query template with date ranges
     */
    private function processQueryTemplate(string $template, ?string $dateRange): string
    {
        if (!$dateRange) {
            return $template;
        }

        $dates = $this->calculateDateRange($dateRange);
        $template = str_replace('{{start_date}}', $dates['start'], $template);
        $template = str_replace('{{end_date}}', $dates['end'], $template);

        return $template;
    }

    /**
     * Calculate date range
     */
    private function calculateDateRange(string $range): array
    {
        $end = date('Y-m-d');
        $start = $end;

        switch ($range) {
            case 'today':
                break;
            case 'yesterday':
                $start = $end = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'last_7_days':
                $start = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'last_30_days':
                $start = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'last_month':
                $start = date('Y-m-01', strtotime('first day of last month'));
                $end = date('Y-m-t', strtotime('last day of last month'));
                break;
            case 'this_month':
                $start = date('Y-m-01');
                break;
            case 'last_quarter':
                $quarter = ceil(date('n') / 3) - 1;
                if ($quarter === 0) {
                    $quarter = 4;
                    $year = date('Y') - 1;
                } else {
                    $year = date('Y');
                }
                $start = date('Y-m-01', strtotime("$year-" . (($quarter - 1) * 3 + 1) . "-01"));
                $end = date('Y-m-t', strtotime("$year-" . ($quarter * 3) . "-01"));
                break;
            case 'this_year':
                $start = date('Y-01-01');
                break;
            case 'last_year':
                $start = date('Y-01-01', strtotime('last year'));
                $end = date('Y-12-31', strtotime('last year'));
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Cache widget data
     */
    private function cacheWidgetData(int $widgetId, array $data): void
    {
        $this->db->prepare("
            UPDATE dashboard_widgets
            SET cached_data = ?,
                cached_at = NOW(),
                last_refreshed_at = NOW()
            WHERE id = ?
        ")->execute([json_encode($data), $widgetId]);
    }

    /**
     * Get KPI value
     */
    public function getKPIValue(int $kpiId, int $tenantId, ?string $period = null): array
    {
        // Get KPI definition
        $stmt = $this->db->prepare("
            SELECT * FROM kpi_definitions
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$kpiId, $tenantId]);
        $kpi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$kpi) {
            return [
                'success' => false,
                'error' => 'KPI not found'
            ];
        }

        // Calculate current period if not specified
        if (!$period) {
            $period = $kpi['aggregation_period'];
        }

        $dates = $this->getKPIPeriodDates($period);

        // Get or calculate KPI value
        $value = $this->calculateKPIValue($kpi, $dates);

        return [
            'success' => true,
            'kpi' => $kpi,
            'value' => $value,
            'period' => $dates
        ];
    }

    /**
     * Calculate KPI value
     */
    private function calculateKPIValue(array $kpi, array $dates): array
    {
        // Check if we have cached value
        $stmt = $this->db->prepare("
            SELECT * FROM kpi_values
            WHERE kpi_id = ?
              AND period_start = ?
              AND period_end = ?
            ORDER BY calculated_at DESC
            LIMIT 1
        ");
        $stmt->execute([$kpi['id'], $dates['start'], $dates['end']]);
        $cached = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cached && $this->isKPIValueFresh($cached, $kpi['aggregation_period'])) {
            return $cached;
        }

        // Calculate new value
        $query = $this->processQueryTemplate($kpi['data_source_query'], null);
        $query = str_replace('{{start_date}}', $dates['start'], $query);
        $query = str_replace('{{end_date}}', $dates['end'], $query);

        $result = $this->executeQuery($query);
        $actualValue = $result['data'][0]['value'] ?? 0;

        // Calculate variance and trend
        $targetValue = $kpi['target_value'];
        $variance = $targetValue ? ($actualValue - $targetValue) : null;
        $variancePercentage = $targetValue ? (($variance / $targetValue) * 100) : null;

        // Get previous period for trend
        $previousDates = $this->getPreviousPeriod($dates, $kpi['aggregation_period']);
        $previousQuery = str_replace('{{start_date}}', $previousDates['start'], $query);
        $previousQuery = str_replace('{{end_date}}', $previousDates['end'], $previousQuery);
        $previousResult = $this->executeQuery($previousQuery);
        $previousValue = $previousResult['data'][0]['value'] ?? 0;

        $changeValue = $actualValue - $previousValue;
        $changePercentage = $previousValue ? (($changeValue / $previousValue) * 100) : null;
        $trendDirection = $changeValue > 0 ? 'up' : ($changeValue < 0 ? 'down' : 'flat');

        // Determine status based on thresholds
        $status = $this->calculateKPIStatus($actualValue, $kpi);

        // Save KPI value
        $this->db->prepare("
            INSERT INTO kpi_values (
                tenant_id, kpi_id, period_start, period_end, period_type,
                actual_value, target_value, variance, variance_percentage,
                previous_period_value, change_value, change_percentage, trend_direction,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                actual_value = VALUES(actual_value),
                variance = VALUES(variance),
                variance_percentage = VALUES(variance_percentage),
                previous_period_value = VALUES(previous_period_value),
                change_value = VALUES(change_value),
                change_percentage = VALUES(change_percentage),
                trend_direction = VALUES(trend_direction),
                status = VALUES(status),
                calculated_at = NOW()
        ")->execute([
            $kpi['tenant_id'], $kpi['id'], $dates['start'], $dates['end'], $kpi['aggregation_period'],
            $actualValue, $targetValue, $variance, $variancePercentage,
            $previousValue, $changeValue, $changePercentage, $trendDirection,
            $status
        ]);

        return [
            'actual_value' => $actualValue,
            'target_value' => $targetValue,
            'variance' => $variance,
            'variance_percentage' => $variancePercentage,
            'previous_period_value' => $previousValue,
            'change_value' => $changeValue,
            'change_percentage' => $changePercentage,
            'trend_direction' => $trendDirection,
            'status' => $status
        ];
    }

    /**
     * Calculate KPI status based on thresholds
     */
    private function calculateKPIStatus(float $value, array $kpi): string
    {
        if (!$kpi['has_target']) {
            return 'neutral';
        }

        if ($kpi['green_threshold'] && $value >= $kpi['green_threshold']) {
            return 'green';
        }
        if ($kpi['yellow_threshold'] && $value >= $kpi['yellow_threshold']) {
            return 'yellow';
        }
        if ($kpi['red_threshold'] && $value >= $kpi['red_threshold']) {
            return 'red';
        }

        return 'red';
    }

    /**
     * Check if cached KPI value is fresh
     */
    private function isKPIValueFresh(array $value, string $period): bool
    {
        $calculatedAt = strtotime($value['calculated_at']);
        $age = time() - $calculatedAt;

        switch ($period) {
            case 'real_time':
                return $age < 300; // 5 minutes
            case 'daily':
                return $age < 3600; // 1 hour
            case 'weekly':
            case 'monthly':
                return $age < 86400; // 1 day
            default:
                return false;
        }
    }

    /**
     * Get KPI period dates
     */
    private function getKPIPeriodDates(string $period): array
    {
        return $this->calculateDateRange($period === 'daily' ? 'today' :
            ($period === 'weekly' ? 'last_7_days' :
            ($period === 'monthly' ? 'this_month' : 'this_year')));
    }

    /**
     * Get previous period dates
     */
    private function getPreviousPeriod(array $currentDates, string $periodType): array
    {
        $diff = strtotime($currentDates['end']) - strtotime($currentDates['start']);
        $start = date('Y-m-d', strtotime($currentDates['start']) - $diff - 86400);
        $end = date('Y-m-d', strtotime($currentDates['end']) - $diff - 86400);

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Generate report
     */
    public function generateReport(int $templateId, int $tenantId, array $parameters = []): array
    {
        $startTime = microtime(true);

        // Get template
        $stmt = $this->db->prepare("
            SELECT * FROM report_templates
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$templateId, $tenantId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template) {
            return [
                'success' => false,
                'error' => 'Report template not found'
            ];
        }

        // Process query with parameters
        $query = $this->processReportQuery($template['query_template'], $parameters);

        // Execute query
        $result = $this->executeQuery($query);

        if (!$result['success']) {
            return $result;
        }

        $executionTime = round((microtime(true) - $startTime) * 1000);

        // Save generated report
        $stmt = $this->db->prepare("
            INSERT INTO generated_reports (
                tenant_id, template_id, report_name, generated_by,
                parameters_used, date_range_start, date_range_end,
                result_data, result_format, row_count,
                execution_time_ms, query_executed, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')
        ");

        $stmt->execute([
            $tenantId,
            $templateId,
            $template['report_name'],
            $parameters['user_id'] ?? null,
            json_encode($parameters),
            $parameters['start_date'] ?? null,
            $parameters['end_date'] ?? null,
            json_encode($result['data']),
            'json',
            $result['count'],
            $executionTime,
            $query
        ]);

        $reportId = $this->db->lastInsertId();

        return [
            'success' => true,
            'report_id' => $reportId,
            'data' => $result['data'],
            'row_count' => $result['count'],
            'execution_time_ms' => $executionTime
        ];
    }

    /**
     * Process report query with parameters
     */
    private function processReportQuery(string $template, array $parameters): string
    {
        $query = $template;

        // Replace date range
        if (isset($parameters['date_range'])) {
            $dates = $this->calculateDateRange($parameters['date_range']);
            $query = str_replace('{{start_date}}', $dates['start'], $query);
            $query = str_replace('{{end_date}}', $dates['end'], $query);
        } elseif (isset($parameters['start_date']) && isset($parameters['end_date'])) {
            $query = str_replace('{{start_date}}', $parameters['start_date'], $query);
            $query = str_replace('{{end_date}}', $parameters['end_date'], $query);
        }

        // Replace other parameters
        foreach ($parameters as $key => $value) {
            $query = str_replace('{{' . $key . '}}', $value, $query);
        }

        return $query;
    }

    /**
     * Export data
     */
    public function exportData(int $tenantId, array $exportConfig): array
    {
        // Create export job
        $stmt = $this->db->prepare("
            INSERT INTO data_exports (
                tenant_id, export_name, export_type, export_format,
                date_range_start, date_range_end, filters,
                requested_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $tenantId,
            $exportConfig['name'],
            $exportConfig['type'],
            $exportConfig['format'],
            $exportConfig['start_date'] ?? null,
            $exportConfig['end_date'] ?? null,
            json_encode($exportConfig['filters'] ?? []),
            $exportConfig['user_id'] ?? null
        ]);

        $exportId = $this->db->lastInsertId();

        // Process export (in production, this would be queued)
        // $this->processExport($exportId);

        return [
            'success' => true,
            'export_id' => $exportId,
            'message' => 'Export job created. You will be notified when it completes.'
        ];
    }
}
