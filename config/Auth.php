<?php
require_once __DIR__ . '/Database.php';

class Auth {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Проверка API-ключа из заголовка запроса
     * @return bool|array Возвращает данные пользователя или false при ошибке
     */
    public function validateApiKey() {
        $headers = getallheaders();
        $apiKey = null;

        // Проверяем заголовок X-API-Key
        if (isset($headers['X-API-Key'])) {
            $apiKey = $headers['X-API-Key'];
        } elseif (isset($headers['x-api-key'])) {
            $apiKey = $headers['x-api-key'];
        } elseif (isset($_SERVER['HTTP_X_API_KEY'])) {
            $apiKey = $_SERVER['HTTP_X_API_KEY'];
        }

        if (!$apiKey) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "API-ключ не предоставлен. Укажите заголовок X-API-Key."
            ]);
            exit;
        }

        // Поиск ключа в базе данных
        $query = "SELECT id, user_id, is_active FROM api_keys WHERE api_key = :api_key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':api_key', $apiKey);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Неверный API-ключ."
            ]);
            exit;
        }

        $row = $stmt->fetch();
        
        if (!$row['is_active']) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "API-ключ неактивен."
            ]);
            exit;
        }

        return $row;
    }
}


