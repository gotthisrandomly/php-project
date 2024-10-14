<?php

class ApiController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));

        if ($pathParts[0] !== 'api') {
            $this->sendJsonResponse(['error' => 'Not Found'], 404);
            return;
        }

        if (!isset($pathParts[1])) {
            $this->sendJsonResponse(['error' => 'Invalid API endpoint'], 400);
            return;
        }

        switch ($pathParts[1]) {
            case 'users':
                $this->handleUsersRequest($method, $pathParts);
                break;
            default:
                $this->sendJsonResponse(['error' => 'Invalid API endpoint'], 400);
        }
    }

    private function handleUsersRequest($method, $pathParts) {
        if (count($pathParts) > 3) {
            $this->sendJsonResponse(['error' => 'Invalid API endpoint'], 400);
            return;
        }

        $userId = isset($pathParts[2]) ? $pathParts[2] : null;

        switch ($method) {
            case 'GET':
                if ($userId) {
                    $this->getUser($userId);
                } else {
                    $this->getUsers();
                }
                break;
            case 'POST':
                $this->createUser();
                break;
            case 'PUT':
                if ($userId) {
                    $this->updateUser($userId);
                } else {
                    $this->sendJsonResponse(['error' => 'User ID is required for update'], 400);
                }
                break;
            case 'DELETE':
                if ($userId) {
                    $this->deleteUser($userId);
                } else {
                    $this->sendJsonResponse(['error' => 'User ID is required for deletion'], 400);
                }
                break;
            default:
                $this->sendJsonResponse(['error' => 'Method not allowed'], 405);
        }
    }

    private function getUsers() {
        $stmt = $this->db->prepare("SELECT id, username, email FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $this->sendJsonResponse($users);
    }

    private function getUser($userId) {
        $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $this->sendJsonResponse($user);
        } else {
            $this->sendJsonResponse(['error' => 'User not found'], 404);
        }
    }

    private function createUser() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
            $this->sendJsonResponse(['error' => 'Missing required fields'], 400);
            return;
        }

        $username = $data['username'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $this->sendJsonResponse(['id' => $userId, 'username' => $username, 'email' => $email], 201);
        } else {
            $this->sendJsonResponse(['error' => 'Failed to create user'], 500);
        }
    }

    private function updateUser($userId) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) {
            $this->sendJsonResponse(['error' => 'No data provided for update'], 400);
            return;
        }

        $allowedFields = ['username', 'email', 'password'];
        $updates = [];
        $types = "";
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $types .= "s";
                $values[] = $field === 'password' ? password_hash($data[$field], PASSWORD_DEFAULT) : $data[$field];
            }
        }

        if (empty($updates)) {
            $this->sendJsonResponse(['error' => 'No valid fields provided for update'], 400);
            return;
        }

        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $types .= "i";
        $values[] = $userId;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $this->getUser($userId);
        } else {
            $this->sendJsonResponse(['error' => 'Failed to update user'], 500);
        }
    }

    private function deleteUser($userId) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            $this->sendJsonResponse(['message' => 'User deleted successfully']);
        } else {
            $this->sendJsonResponse(['error' => 'Failed to delete user'], 500);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}