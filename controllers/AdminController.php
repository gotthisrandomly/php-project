<?php

class AdminController {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    private function isAdminLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    private function requireAdminLogin() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: /admin/login');
            exit();
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // In a real-world scenario, you would hash the password and compare it with a stored hash
            if ($username === 'admin' && $password === 'secretpassword') {
                $_SESSION['admin_logged_in'] = true;
                header('Location: /admin');
                exit();
            } else {
                $error = "Invalid username or password";
            }
        }

        include 'views/admin/login.php';
    }

    public function logout() {
        unset($_SESSION['admin_logged_in']);
        session_destroy();
        header('Location: /admin/login');
        exit();
    }

    public function dashboard() {
        $this->requireAdminLogin();
        $playerAccounts = $this->getPlayerAccounts();
        $pendingCashouts = $this->getPendingCashouts();
        $gameSettings = $this->getGameSettings();

        include 'views/admin/dashboard.php';
    }

    public function managePlayers() {
        $this->requireAdminLogin();
        $playerAccounts = $this->getPlayerAccounts();
        include 'views/admin/manage_players.php';
    }

    public function editPlayer($playerId) {
        $this->requireAdminLogin();
        $player = $this->getPlayerById($playerId);
        $transactionHistory = $this->getPlayerTransactionHistory($playerId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updatePlayerInformation($playerId, $_POST);
            header('Location: /admin/manage-players');
            exit();
        }

        include 'views/admin/edit_player.php';
    }

    public function manageSettings() {
        $this->requireAdminLogin();
        $gameSettings = $this->getGameSettings();
        include 'views/admin/manage_settings.php';
    }

    private function getPlayerAccounts() {
        $stmt = $this->pdo->query("SELECT * FROM players");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPendingCashouts() {
        $stmt = $this->pdo->query("SELECT * FROM cashouts WHERE status = 'pending'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGameSettings() {
        $stmt = $this->pdo->query("SELECT * FROM game_settings");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateGameSetting($settingName, $settingValue) {
        $this->requireAdminLogin();
        $stmt = $this->pdo->prepare("UPDATE game_settings SET value = ? WHERE name = ?");
        return $stmt->execute([$settingValue, $settingName]);
    }

    private function getPlayerById($playerId) {
        $stmt = $this->pdo->prepare("SELECT * FROM players WHERE id = ?");
        $stmt->execute([$playerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getPlayerTransactionHistory($playerId) {
        $stmt = $this->pdo->prepare("SELECT * FROM transactions WHERE player_id = ? ORDER BY timestamp DESC LIMIT 50");
        $stmt->execute([$playerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updatePlayerInformation($playerId, $data) {
        $stmt = $this->pdo->prepare("UPDATE players SET username = ?, email = ?, balance = ? WHERE id = ?");
        return $stmt->execute([$data['username'], $data['email'], $data['balance'], $playerId]);
    }
}
?>