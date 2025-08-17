<?php
require_once __DIR__ . '/constants.php';

// Create a connection to the PostgreSQL database server and assign to a variable
// Using PDO for OOP and prepared statements

try {
    $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', DB_HOST_NAME, DB_PORT, DB_NAME);
    $pdo = new PDO($dsn, DB_USER_NAME, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
    // If the connection fails because the seed user might not exist, instructions are in schema.sql
    exit;
}

class Database
{
    public static function getConnection(): PDO
    {
        global $pdo; // as required, connection assigned to a variable
        return $pdo;
    }
}


