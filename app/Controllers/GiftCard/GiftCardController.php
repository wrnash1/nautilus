<?php

namespace App\Controllers\GiftCard;

use App\Core\Controller;

class GiftCardController extends Controller
{
    /**
     * Display gift cards list
     */
    public function index(): void
    {
        $this->checkPermission('transactions.view');

        $status = $_GET['status'] ?? 'all';

        try {
            $sql = "
                SELECT gc.*, c.first_name, c.last_name
                FROM gift_cards gc
                LEFT JOIN customers c ON gc.customer_id = c.id
                WHERE 1=1";
            $params = [];

            if ($status !== 'all') {
                $sql .= " AND gc.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY gc.issued_at DESC LIMIT 100";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $cards = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Stats
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM gift_cards WHERE status = 'active'");
            $activeCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            $stmt = $this->db->query("SELECT SUM(current_balance) as total FROM gift_cards WHERE status = 'active'");
            $totalBalance = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $stmt = $this->db->query("SELECT SUM(initial_balance) as total FROM gift_cards");
            $totalIssued = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

        } catch (\Exception $e) {
            $cards = [];
            $activeCount = 0;
            $totalBalance = 0;
            $totalIssued = 0;
        }

        $this->view('giftcard/index', [
            'title' => 'Gift Cards',
            'cards' => $cards,
            'status' => $status,
            'stats' => [
                'active' => $activeCount,
                'total_balance' => $totalBalance,
                'total_issued' => $totalIssued
            ]
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->checkPermission('transactions.create');

        try {
            $stmt = $this->db->query("
                SELECT id, first_name, last_name, email
                FROM customers WHERE status = 'active'
                ORDER BY last_name, first_name
            ");
            $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $customers = [];
        }

        $this->view('giftcard/create', [
            'title' => 'Issue Gift Card',
            'customers' => $customers
        ]);
    }

    /**
     * Store new gift card
     */
    public function store(): void
    {
        $this->checkPermission('transactions.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/gift-cards');
            return;
        }

        $amount = (float)($_POST['amount'] ?? 0);
        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Please enter a valid amount';
            $this->redirect('/store/gift-cards/create');
            return;
        }

        try {
            // Generate card number
            $cardNumber = 'GC-' . strtoupper(bin2hex(random_bytes(8)));
            $pin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            $stmt = $this->db->prepare("
                INSERT INTO gift_cards (
                    card_number, pin, customer_id, initial_balance, current_balance,
                    card_type, status, expiry_date
                ) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)
            ");

            $expiryDate = $_POST['expiry_date'] ?: date('Y-m-d', strtotime('+1 year'));

            $stmt->execute([
                $cardNumber,
                $pin,
                $_POST['customer_id'] ?: null,
                $amount,
                $amount,
                $_POST['card_type'] ?? 'physical',
                $expiryDate
            ]);

            $cardId = $this->db->lastInsertId();

            // Record initial transaction
            $stmt = $this->db->prepare("
                INSERT INTO gift_card_transactions (
                    gift_card_id, transaction_type, amount, balance_before, balance_after
                ) VALUES (?, 'issue', ?, 0, ?)
            ");
            $stmt->execute([$cardId, $amount, $amount]);

            $_SESSION['flash_success'] = "Gift card {$cardNumber} created successfully (PIN: {$pin})";
            $this->redirect('/store/gift-cards/' . $cardId);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create gift card: ' . $e->getMessage();
            $this->redirect('/store/gift-cards/create');
        }
    }

    /**
     * Show gift card details
     */
    public function show(int $id): void
    {
        $this->checkPermission('transactions.view');

        try {
            $stmt = $this->db->prepare("
                SELECT gc.*, c.first_name, c.last_name, c.email
                FROM gift_cards gc
                LEFT JOIN customers c ON gc.customer_id = c.id
                WHERE gc.id = ?
            ");
            $stmt->execute([$id]);
            $card = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$card) {
                $_SESSION['flash_error'] = 'Gift card not found';
                $this->redirect('/store/gift-cards');
                return;
            }

            // Get transactions
            $stmt = $this->db->prepare("
                SELECT * FROM gift_card_transactions
                WHERE gift_card_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$id]);
            $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading gift card';
            $this->redirect('/store/gift-cards');
            return;
        }

        $this->view('giftcard/show', [
            'title' => 'Gift Card Details',
            'card' => $card,
            'transactions' => $transactions
        ]);
    }

    /**
     * Add balance (reload)
     */
    public function reload(int $id): void
    {
        $this->checkPermission('transactions.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/gift-cards/' . $id);
            return;
        }

        $amount = (float)($_POST['amount'] ?? 0);
        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Please enter a valid amount';
            $this->redirect('/store/gift-cards/' . $id);
            return;
        }

        try {
            // Get current balance
            $stmt = $this->db->prepare("SELECT current_balance FROM gift_cards WHERE id = ?");
            $stmt->execute([$id]);
            $card = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$card) {
                $_SESSION['flash_error'] = 'Gift card not found';
                $this->redirect('/store/gift-cards');
                return;
            }

            $balanceBefore = $card['current_balance'];
            $balanceAfter = $balanceBefore + $amount;

            // Update balance
            $stmt = $this->db->prepare("UPDATE gift_cards SET current_balance = ?, status = 'active' WHERE id = ?");
            $stmt->execute([$balanceAfter, $id]);

            // Record transaction
            $stmt = $this->db->prepare("
                INSERT INTO gift_card_transactions (
                    gift_card_id, transaction_type, amount, balance_before, balance_after
                ) VALUES (?, 'reload', ?, ?, ?)
            ");
            $stmt->execute([$id, $amount, $balanceBefore, $balanceAfter]);

            $_SESSION['flash_success'] = 'Gift card reloaded successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to reload gift card';
        }

        $this->redirect('/store/gift-cards/' . $id);
    }

    /**
     * Check balance (lookup)
     */
    public function checkBalance(): void
    {
        $this->checkPermission('transactions.view');

        $cardNumber = $_GET['card_number'] ?? '';
        $card = null;

        if ($cardNumber) {
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM gift_cards WHERE card_number = ?
                ");
                $stmt->execute([$cardNumber]);
                $card = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $card = null;
            }
        }

        $this->view('giftcard/check-balance', [
            'title' => 'Check Gift Card Balance',
            'cardNumber' => $cardNumber,
            'card' => $card
        ]);
    }

    /**
     * Deactivate card
     */
    public function deactivate(int $id): void
    {
        $this->checkPermission('transactions.void');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/gift-cards/' . $id);
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE gift_cards SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_success'] = 'Gift card deactivated';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to deactivate gift card';
        }

        $this->redirect('/store/gift-cards/' . $id);
    }
}
