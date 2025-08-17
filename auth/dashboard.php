<?php
require_once('config/connection.php');
require_once __DIR__ . '/../services/Auth.php';

Auth::requireLogin();
$user = Auth::user();

function linkTo(string $path, string $label): string {
    return '<p><a href="' . BASE_URL . $path . '">' . htmlspecialchars($label) . '</a></p>';
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars(APP_NAME) ?> - Dashboard</title>
    <style> body{font-family:Arial;margin:24px;} a{display:inline-block;padding:10px 14px;margin:6px;background:#1976d2;color:#fff;text-decoration:none;border-radius:4px;} a:hover{background:#125aa0}</style>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['type']) ?>)</h2>
    <?= linkTo('/auth/profile.php', 'Update My Profile') ?>
    <?php if ($user['type'] === USER_TYPE_SUPER): ?>
        <?= linkTo('/public/users/list.php', 'Manage Other Users') ?>
        <?= linkTo('/public/articles/view.php', 'View Articles') ?>
    <?php elseif ($user['type'] === USER_TYPE_ADMIN): ?>
        <?= linkTo('/public/users/list.php?only=authors', 'Manage Authors') ?>
        <?= linkTo('/public/articles/list.php', 'Manage Articles') ?>
    <?php elseif ($user['type'] === USER_TYPE_AUTHOR): ?>
        <?= linkTo('/public/articles/list.php?mine=1', 'Manage My Articles') ?>
        <?= linkTo('/public/articles/view.php', 'View Articles') ?>
    <?php endif; ?>
    <?= linkTo('/auth/logout.php', 'Logout') ?>
</body>
</html>


