<?php
// Router для встроенного PHP сервера
// Использование: php -S localhost:8000 router.php

// Пропускаем статические файлы
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|svg)$/', $_SERVER["REQUEST_URI"])) {
    return false; // serve the requested resource as-is.
}

// Все остальные запросы перенаправляем на index.php
require __DIR__ . '/index.php';


