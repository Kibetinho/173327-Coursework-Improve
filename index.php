<?php
require_once('config/connection.php');
require_once __DIR__ . '/Coursework/services/Auth.php';
require_once __DIR__ . '/Coursework/services/Security.php';

Auth::startSession();
if (!empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$flash = Security::consumeFlash();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf_token'] ?? null);
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (Auth::login($username, $password)) {
        header('Location: ' . BASE_URL . '/auth/dashboard.php');
        exit;
    }
    $error = 'Invalid credentials';
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars(APP_NAME) ?> - Sign In</title>
    <link rel="stylesheet" href="/css/general.css">
    <link rel="stylesheet" href="/css/user.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f5f5f5; }
        .card { width: 360px; margin: 10% auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        input, button { width: 100%; padding: 10px; margin: 6px 0; }
        .error { color: #b00020; margin-bottom: 8px; }
    </style>
    </head>
<body>
    <div class="card">
        <h2>Sign In</h2>
        <?php foreach ($flash as $msg): ?>
            <div class="<?= htmlspecialchars($msg['type']) ?>"><?= htmlspecialchars($msg['message']) ?></div>
        <?php endforeach; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>


