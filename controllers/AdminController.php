<?php
require_once 'includes/db_connection.php';

class AdminController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            return true;
        }
        return false;
    }

    public function getPlayerAccounts() {
        $stmt = $this->pdo->query("SELECT * FROM players");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingCashouts() {
        $stmt = $this->pdo->query("SELECT * FROM cashouts WHERE status = 'pending'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveCashout($cashoutId) {
        $stmt = $this->pdo->prepare("UPDATE cashouts SET status = 'approved' WHERE id = ?");
        return $stmt->execute([$cashoutId]);
    }

    public function blockCashout($cashoutId) {
        $stmt = $this->pdo->prepare("UPDATE cashouts SET status = 'blocked' WHERE id = ?");
        return $stmt->execute([$cashoutId]);
    }

    private function validateInput($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    public function createAdmin($username, $password) {
        $username = $this->validateInput($username);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        return $stmt->execute([$username, $hashedPassword]);
    }
}
?>