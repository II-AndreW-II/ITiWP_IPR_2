<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-API-Key");

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/Auth.php';
require_once __DIR__ . '/models/CommentModel.php';

// Проверка аутентификации для всех запросов
$auth = new Auth();
$auth->validateApiKey();

// Получение метода запроса и пути
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Удаление базового пути, если есть
$path = str_replace('/index.php', '', $path);
$path = trim($path, '/');

// Разбор пути для получения ресурса и ID
$path_parts = explode('/', $path);

// Проверка структуры пути: /api/comments или /api/comments/{id}
if (!isset($path_parts[0]) || $path_parts[0] !== 'api') {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Endpoint не найден. Используйте /api/comments"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($path_parts[1]) || $path_parts[1] !== 'comments') {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Endpoint не найден. Используйте /api/comments"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Получение ID из пути (если есть)
$id = isset($path_parts[2]) && !empty($path_parts[2]) ? intval($path_parts[2]) : null;

$commentModel = new CommentModel();

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                // GET /api/comments/{id} - получить один комментарий
                $comment = $commentModel->getById($id);
                
                if ($comment) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "data" => $comment
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Комментарий с ID {$id} не найден"
                    ], JSON_UNESCAPED_UNICODE);
                }
            } else {
                // GET /api/comments - получить все комментарии
                $comments = $commentModel->getAll();
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "data" => $comments,
                    "count" => count($comments)
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'POST':
            // POST /api/comments - создать новый комментарий
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Неверный формат JSON или пустое тело запроса"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $comment = $commentModel->create($input);
            
            if ($comment) {
                http_response_code(201);
                echo json_encode([
                    "success" => true,
                    "message" => "Комментарий успешно создан",
                    "data" => $comment
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Ошибка при создании комментария"
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'PUT':
            // PUT /api/comments/{id} - обновить комментарий
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "ID комментария не указан"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Неверный формат JSON или пустое тело запроса"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $comment = $commentModel->update($id, $input);
            
            if ($comment) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Комментарий успешно обновлен",
                    "data" => $comment
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "message" => "Комментарий с ID {$id} не найден"
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'DELETE':
            // DELETE /api/comments/{id} - удалить комментарий
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "ID комментария не указан"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $result = $commentModel->delete($id);
            
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Комментарий успешно удален"
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "message" => "Комментарий с ID {$id} не найден"
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                "success" => false,
                "message" => "Метод {$method} не поддерживается"
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

