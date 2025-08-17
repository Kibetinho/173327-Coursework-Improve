<?php

require_once __DIR__ . '/../models/User.php';

class Auth
{
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function login(string $userName, string $password): bool
    {
        self::startSession();
        $user = User::verifyPassword($userName, $password);
        if (!$user) {
            return false;
        }
        $_SESSION['user'] = [
            'id' => $user->userId,
            'name' => $user->fullName,
            'username' => $user->userName,
            'type' => $user->userType,
        ];
        return true;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(): void
    {
        self::startSession();
        if (empty($_SESSION['user'])) {
            header('Location: ' . ROOT_URL . '/index.php');
            exit;
        }
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function requireRole(array $allowedRoles): void
    {
        self::requireLogin();
        $u = self::user();
        if (!$u || !in_array($u['type'], $allowedRoles, true)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}


