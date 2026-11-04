<?php
declare(strict_types=1);

$host = '127.0.0.1';
$db   = 'premier_samgyupsal';
$user = 'root';
$pass = ''; // Default Laragon MySQL password is usually blank.

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        $options
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check config/database.php and make sure Laragon MySQL is running. Details: ' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
