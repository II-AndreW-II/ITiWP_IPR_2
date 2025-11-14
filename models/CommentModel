<?php
require_once __DIR__ . '/../config/Database.php';

class CommentModel {
    private $conn;
    private $table_name = "comments";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Получить все комментарии
     */
    public function getAll() {
        $query = "SELECT id, post_id, author_name, content, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Получить комментарий по ID
     */
    public function getById($id) {
        $query = "SELECT id, post_id, author_name, content, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Создать новый комментарий
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (post_id, author_name, content, created_at) 
                  VALUES (:post_id, :author_name, :content, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Валидация и санитизация данных
        $post_id = filter_var($data['post_id'], FILTER_VALIDATE_INT);
        $author_name = trim(htmlspecialchars(strip_tags($data['author_name'])));
        $content = trim(htmlspecialchars(strip_tags($data['content'])));
        
        if (!$post_id || $post_id <= 0) {
            throw new Exception("post_id должен быть положительным числом");
        }
        
        if (empty($author_name)) {
            throw new Exception("author_name обязателен для заполнения");
        }
        
        if (empty($content)) {
            throw new Exception("content обязателен для заполнения");
        }
        
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':author_name', $author_name);
        $stmt->bindParam(':content', $content);
        
        if ($stmt->execute()) {
            return [
                'id' => $this->conn->lastInsertId(),
                'post_id' => $post_id,
                'author_name' => $author_name,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return false;
    }

    /**
     * Обновить комментарий
     */
    public function update($id, $data) {
        // Проверяем существование комментария
        $existing = $this->getById($id);
        if (!$existing) {
            return false;
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET post_id = :post_id, 
                      author_name = :author_name, 
                      content = :content 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Валидация и санитизация данных
        $post_id = filter_var($data['post_id'], FILTER_VALIDATE_INT);
        $author_name = trim(htmlspecialchars(strip_tags($data['author_name'])));
        $content = trim(htmlspecialchars(strip_tags($data['content'])));
        
        if (!$post_id || $post_id <= 0) {
            throw new Exception("post_id должен быть положительным числом");
        }
        
        if (empty($author_name)) {
            throw new Exception("author_name обязателен для заполнения");
        }
        
        if (empty($content)) {
            throw new Exception("content обязателен для заполнения");
        }
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':author_name', $author_name);
        $stmt->bindParam(':content', $content);
        
        if ($stmt->execute()) {
            return $this->getById($id);
        }
        
        return false;
    }

    /**
     * Удалить комментарий
     */
    public function delete($id) {
        // Проверяем существование комментария
        $existing = $this->getById($id);
        if (!$existing) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}


