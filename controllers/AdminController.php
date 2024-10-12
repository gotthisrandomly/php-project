<?php

class AdminController {
    private $adminUsername = 'admin';
    private $adminPassword = 'securePa$$w0rd';

    public function index() {
        session_start();
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $this->showDashboard();
        } else {
            $this->showLoginForm();
        }
    }

    private function showLoginForm($error = '') {
        require_once __DIR__ . '/../views/admin_login.php';
    }

    private function showDashboard() {
        // Fetch all player accounts and money accounts
        $players = $this->getAllPlayers();
        $pendingCashouts = $this->getPendingCashouts();
        require_once __DIR__ . '/../views/admin_dashboard.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($username === $this->adminUsername && $password === $this->adminPassword) {
                session_start();
                $_SESSION['admin_logged_in'] = true;
                header('Location: /admin');
                exit;
            } else {
                $this->showLoginForm('Invalid username or password');
            }
        } else {
            header('Location: /admin');
            exit;
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /admin');
        exit;
    }

    public function approveCashout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cashoutId = $_POST['cashout_id'] ?? '';
            // Process the cashout approval
            // Update the database to mark the cashout as approved
            header('Location: /admin');
            exit;
        }
    }

    public function blockCashout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cashoutId = $_POST['cashout_id'] ?? '';
            // Process the cashout blocking
            // Update the database to mark the cashout as blocked
            header('Location: /admin');
            exit;
        }
    }

    private function getAllPlayers() {
        // Fetch all player accounts from the database
        // This is a placeholder, replace with actual database query
        return [
            ['id' => 1, 'username' => 'player1', 'balance' => 1000],
            ['id' => 2, 'username' => 'player2', 'balance' => 500],
        ];
    }

    private function getPendingCashouts() {
        // Fetch all pending cashouts from the database
        // This is a placeholder, replace with actual database query
        return [
            ['id' => 1, 'player_id' => 1, 'amount' => 200],
            ['id' => 2, 'player_id' => 2, 'amount' => 100],
        ];
    }
}