<?php

namespace App\Controllers\CashDrawer;

use App\Core\Database;
use PDO;

class CashDrawerController
{
    /**
     * Dashboard - show all drawers and current status
     */
    public function index()
    {
        if (!hasPermission('pos.access')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store');
        }

        $db = Database::getInstance();

        // Get all drawers
        $stmt = $db->query("SELECT * FROM cash_drawers ORDER BY location, name");
        $drawers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get open sessions
        $stmt = $db->query("SELECT * FROM cash_drawer_sessions_open");
        $openSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Cash Drawer Management';
        $activeMenu = 'cash-drawer';
        $user = \App\Core\Auth::user();

        // Start output buffering for the view
        ob_start();
        require __DIR__ . '/../../Views/cash_drawer/index.php';
        $content = ob_get_clean();

        // Load layout
        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Open a new cash drawer session
     */
    public function open(int $drawerId)
    {
        if (!hasPermission('pos.access')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/cash-drawer');
        }

        $db = Database::getInstance();

        // Check if drawer already has an open session
        $stmt = $db->prepare("SELECT id FROM cash_drawer_sessions WHERE drawer_id = ? AND status = 'open'");
        $stmt->execute([$drawerId]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'This drawer already has an open session';
            redirect('/store/cash-drawer');
        }

        // Get drawer details
        $stmt = $db->prepare("SELECT * FROM cash_drawers WHERE id = ?");
        $stmt->execute([$drawerId]);
        $drawer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$drawer) {
            $_SESSION['flash_error'] = 'Drawer not found';
            redirect('/store/cash-drawer');
        }

        require __DIR__ . '/../../Views/cash_drawer/open.php';
    }

    /**
     * Process opening a drawer
     */
    public function processOpen()
    {
        if (!hasPermission('pos.access')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $drawerId = (int)($_POST['drawer_id'] ?? 0);
            $startingBalance = (float)($_POST['starting_balance'] ?? 0);
            $notes = sanitizeInput($_POST['starting_notes'] ?? '');

            // Bill counts
            $bills = [
                100 => (int)($_POST['bills_100'] ?? 0),
                50 => (int)($_POST['bills_50'] ?? 0),
                20 => (int)($_POST['bills_20'] ?? 0),
                10 => (int)($_POST['bills_10'] ?? 0),
                5 => (int)($_POST['bills_5'] ?? 0),
                2 => (int)($_POST['bills_2'] ?? 0),
                1 => (int)($_POST['bills_1'] ?? 0),
            ];

            // Coin counts
            $coins = [
                'dollar' => (int)($_POST['coins_dollar'] ?? 0),
                'quarter' => (int)($_POST['coins_quarter'] ?? 0),
                'dime' => (int)($_POST['coins_dime'] ?? 0),
                'nickel' => (int)($_POST['coins_nickel'] ?? 0),
                'penny' => (int)($_POST['coins_penny'] ?? 0),
            ];

            // Calculate total from counts
            $calculatedTotal = 0;
            $calculatedTotal += $bills[100] * 100;
            $calculatedTotal += $bills[50] * 50;
            $calculatedTotal += $bills[20] * 20;
            $calculatedTotal += $bills[10] * 10;
            $calculatedTotal += $bills[5] * 5;
            $calculatedTotal += $bills[2] * 2;
            $calculatedTotal += $bills[1] * 1;
            $calculatedTotal += $coins['dollar'] * 1.00;
            $calculatedTotal += $coins['quarter'] * 0.25;
            $calculatedTotal += $coins['dime'] * 0.10;
            $calculatedTotal += $coins['nickel'] * 0.05;
            $calculatedTotal += $coins['penny'] * 0.01;

            if (abs($calculatedTotal - $startingBalance) > 0.01) {
                throw new \Exception('Starting balance does not match bill/coin count');
            }

            $db = Database::getInstance();

            // Generate session number
            $sessionNumber = 'CS-' . date('Ymd') . '-' . str_pad($drawerId, 3, '0', STR_PAD_LEFT) . '-' . time();

            // Create session
            $stmt = $db->prepare("
                INSERT INTO cash_drawer_sessions (
                    session_number, drawer_id, user_id, starting_balance,
                    starting_bills_100, starting_bills_50, starting_bills_20, starting_bills_10,
                    starting_bills_5, starting_bills_2, starting_bills_1,
                    starting_coins_dollar, starting_coins_quarter, starting_coins_dime,
                    starting_coins_nickel, starting_coins_penny, starting_notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $sessionNumber, $drawerId, $_SESSION['user_id'], $startingBalance,
                $bills[100], $bills[50], $bills[20], $bills[10],
                $bills[5], $bills[2], $bills[1],
                $coins['dollar'], $coins['quarter'], $coins['dime'],
                $coins['nickel'], $coins['penny'], $notes
            ]);

            $sessionId = $db->lastInsertId();

            $_SESSION['flash_success'] = 'Cash drawer opened successfully';
            $_SESSION['cash_drawer_session_id'] = $sessionId;

            jsonResponse(['success' => true, 'session_id' => $sessionId]);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Show close drawer form
     */
    public function close(int $sessionId)
    {
        if (!hasPermission('pos.access')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/cash-drawer');
        }

        $db = Database::getInstance();

        // Get session details
        $stmt = $db->prepare("
            SELECT cds.*, cd.name as drawer_name, cd.location,
                   CONCAT(u.first_name, ' ', u.last_name) as opened_by_name
            FROM cash_drawer_sessions cds
            INNER JOIN cash_drawers cd ON cds.drawer_id = cd.id
            INNER JOIN users u ON cds.user_id = u.id
            WHERE cds.id = ?
        ");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            $_SESSION['flash_error'] = 'Session not found';
            redirect('/store/cash-drawer');
        }

        if ($session['status'] != 'open') {
            $_SESSION['flash_error'] = 'Session is already closed';
            redirect('/store/cash-drawer');
        }

        // Calculate expected balance
        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN transaction_type IN ('sale', 'deposit', 'till_payback') THEN amount ELSE 0 END), 0) as total_in,
                COALESCE(SUM(CASE WHEN transaction_type IN ('return', 'refund', 'withdrawal', 'payout', 'till_loan') THEN amount ELSE 0 END), 0) as total_out
            FROM cash_drawer_transactions
            WHERE session_id = ? AND payment_method = 'cash'
        ");
        $stmt->execute([$sessionId]);
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);

        $expectedBalance = $session['starting_balance'] + $totals['total_in'] - $totals['total_out'];

        require __DIR__ . '/../../Views/cash_drawer/close.php';
    }

    /**
     * Process closing a drawer
     */
    public function processClose(int $sessionId)
    {
        if (!hasPermission('pos.access')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $endingBalance = (float)($_POST['ending_balance'] ?? 0);
            $notes = sanitizeInput($_POST['ending_notes'] ?? '');
            $differenceReason = sanitizeInput($_POST['difference_reason'] ?? '');

            // Bill counts
            $bills = [
                100 => (int)($_POST['ending_bills_100'] ?? 0),
                50 => (int)($_POST['ending_bills_50'] ?? 0),
                20 => (int)($_POST['ending_bills_20'] ?? 0),
                10 => (int)($_POST['ending_bills_10'] ?? 0),
                5 => (int)($_POST['ending_bills_5'] ?? 0),
                2 => (int)($_POST['ending_bills_2'] ?? 0),
                1 => (int)($_POST['ending_bills_1'] ?? 0),
            ];

            // Coin counts
            $coins = [
                'dollar' => (int)($_POST['ending_coins_dollar'] ?? 0),
                'quarter' => (int)($_POST['ending_coins_quarter'] ?? 0),
                'dime' => (int)($_POST['ending_coins_dime'] ?? 0),
                'nickel' => (int)($_POST['ending_coins_nickel'] ?? 0),
                'penny' => (int)($_POST['ending_coins_penny'] ?? 0),
            ];

            $db = Database::getInstance();

            // Get session and calculate expected
            $stmt = $db->prepare("SELECT * FROM cash_drawer_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN transaction_type IN ('sale', 'deposit', 'till_payback') THEN amount ELSE 0 END), 0) as total_in,
                    COALESCE(SUM(CASE WHEN transaction_type IN ('return', 'refund', 'withdrawal', 'payout', 'till_loan') THEN amount ELSE 0 END), 0) as total_out,
                    COUNT(CASE WHEN transaction_type = 'sale' THEN 1 END) as sale_count,
                    COALESCE(SUM(CASE WHEN transaction_type = 'sale' THEN amount ELSE 0 END), 0) as total_sales,
                    COALESCE(SUM(CASE WHEN transaction_type IN ('return', 'refund') THEN amount ELSE 0 END), 0) as total_refunds,
                    COALESCE(SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
                    COALESCE(SUM(CASE WHEN transaction_type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals
                FROM cash_drawer_transactions
                WHERE session_id = ?
            ");
            $stmt->execute([$sessionId]);
            $totals = $stmt->fetch(PDO::FETCH_ASSOC);

            $expectedBalance = $session['starting_balance'] + $totals['total_in'] - $totals['total_out'];
            $difference = $endingBalance - $expectedBalance;

            // Determine status
            $status = 'closed';
            if (abs($difference) < 0.01) {
                $status = 'balanced';
            } elseif ($difference > 0) {
                $status = 'over';
            } else {
                $status = 'short';
            }

            // Update session
            $stmt = $db->prepare("
                UPDATE cash_drawer_sessions
                SET closed_at = NOW(), closed_by = ?, ending_balance = ?,
                    ending_bills_100 = ?, ending_bills_50 = ?, ending_bills_20 = ?, ending_bills_10 = ?,
                    ending_bills_5 = ?, ending_bills_2 = ?, ending_bills_1 = ?,
                    ending_coins_dollar = ?, ending_coins_quarter = ?, ending_coins_dime = ?,
                    ending_coins_nickel = ?, ending_coins_penny = ?, ending_notes = ?,
                    expected_balance = ?, difference = ?, difference_reason = ?,
                    total_sales = ?, total_refunds = ?, total_deposits = ?, total_withdrawals = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $_SESSION['user_id'], $endingBalance,
                $bills[100], $bills[50], $bills[20], $bills[10],
                $bills[5], $bills[2], $bills[1],
                $coins['dollar'], $coins['quarter'], $coins['dime'],
                $coins['nickel'], $coins['penny'], $notes,
                $expectedBalance, $difference, $differenceReason,
                $totals['total_sales'], $totals['total_refunds'],
                $totals['total_deposits'], $totals['total_withdrawals'],
                $status, $sessionId
            ]);

            // If significant variance, create variance record
            if (abs($difference) > 1.00) {
                $varianceType = $difference > 0 ? 'overage' : 'shortage';
                $stmt = $db->prepare("
                    INSERT INTO cash_variances (session_id, variance_type, amount, description, created_by)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $sessionId, $varianceType, abs($difference),
                    $differenceReason ?: 'Variance detected during cash drawer closing',
                    $_SESSION['user_id']
                ]);
            }

            unset($_SESSION['cash_drawer_session_id']);

            $_SESSION['flash_success'] = 'Cash drawer closed successfully';
            jsonResponse(['success' => true, 'status' => $status, 'difference' => $difference]);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Add a transaction to current session
     */
    public function addTransaction()
    {
        if (!hasPermission('pos.access')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $sessionId = $_SESSION['cash_drawer_session_id'] ?? null;
            if (!$sessionId) {
                throw new \Exception('No open cash drawer session');
            }

            $type = sanitizeInput($_POST['transaction_type'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'cash');
            $description = sanitizeInput($_POST['description'] ?? '');
            $notes = sanitizeInput($_POST['notes'] ?? '');
            $referenceType = sanitizeInput($_POST['reference_type'] ?? '');
            $referenceId = !empty($_POST['reference_id']) ? (int)$_POST['reference_id'] : null;

            if (empty($type) || $amount <= 0) {
                throw new \Exception('Transaction type and amount are required');
            }

            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO cash_drawer_transactions (
                    session_id, transaction_type, amount, payment_method,
                    description, notes, reference_type, reference_id, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $sessionId, $type, $amount, $paymentMethod,
                $description, $notes, $referenceType, $referenceId,
                $_SESSION['user_id']
            ]);

            jsonResponse(['success' => true, 'message' => 'Transaction recorded']);
        } catch (\Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * View session history
     */
    public function history()
    {
        if (!hasPermission('pos.access')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store');
        }

        $db = Database::getInstance();

        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get sessions
        $stmt = $db->prepare("
            SELECT * FROM cash_drawer_session_summary
            ORDER BY opened_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $stmt = $db->query("SELECT COUNT(*) as total FROM cash_drawer_sessions");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($total / $limit);

        require __DIR__ . '/../../Views/cash_drawer/history.php';
    }

    /**
     * View session details
     */
    public function viewSession(int $sessionId)
    {
        if (!hasPermission('pos.access')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/store/cash-drawer');
        }

        $db = Database::getInstance();

        // Get session
        $stmt = $db->prepare("SELECT * FROM cash_drawer_session_summary WHERE id = ?");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            $_SESSION['flash_error'] = 'Session not found';
            redirect('/store/cash-drawer/history');
        }

        // Get transactions
        $stmt = $db->prepare("
            SELECT cdt.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM cash_drawer_transactions cdt
            INNER JOIN users u ON cdt.created_by = u.id
            WHERE cdt.session_id = ?
            ORDER BY cdt.created_at DESC
        ");
        $stmt->execute([$sessionId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../Views/cash_drawer/view_session.php';
    }
}
