<?php

require_once __DIR__ . '/../config/connection.php';

class Security
{
    public static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function csrfToken(): string
    {
        self::ensureSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): void
    {
        self::ensureSession();
        if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            exit;
        }
    }

    public static function flash(string $type, string $message): void
    {
        self::ensureSession();
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function consumeFlash(): array
    {
        self::ensureSession();
        $msgs = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $msgs;
    }
}


