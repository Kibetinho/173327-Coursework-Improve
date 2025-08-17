<?php
require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/Security.php';

Auth::requireLogin();
$auth = Auth::user();

// Determine scope
$only = $_GET['only'] ?? '';
$limitToAuthors = $only === 'authors' || $auth['type'] === USER_TYPE_ADMIN;

// Access rules
if ($auth['type'] === USER_TYPE_AUTHOR) {
    http_response_code(403); echo 'Forbidden'; exit;
}

// Super can manage all; Admin manages only Authors and cannot touch Super_User

$users = $limitToAuthors ? User::listAll(USER_TYPE_AUTHOR) : User::listAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Users</title>
    <style> body{font-family:Arial;margin:24px;} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ccc;padding:8px;text-align:left} a.btn{padding:6px 10px;background:#1976d2;color:#fff;text-decoration:none;border-radius:4px;margin-right:6px} </style>
</head>
<body>
    <h2><?= $limitToAuthors ? 'Manage Authors' : 'Manage Users' ?></h2>
    <p><a class="btn" href="add.php<?= $limitToAuthors ? '?type=Author' : '' ?>">Add <?= $limitToAuthors ? 'Author' : 'User' ?></a>
    <a class="btn" href="<?= BASE_URL ?>/auth/dashboard.php">Back</a></p>
    <table>
        <tr>
            <th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Type</th><th>Actions</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <?php if ($auth['type'] === USER_TYPE_ADMIN && $u->userType !== USER_TYPE_AUTHOR) continue; ?>
            <tr>
                <td><?= $u->userId ?></td>
                <td><?= htmlspecialchars($u->userName) ?></td>
                <td><?= htmlspecialchars($u->fullName) ?></td>
                <td><?= htmlspecialchars($u->email) ?></td>
                <td><?= htmlspecialchars($u->userType) ?></td>
                <td>
                    <a class="btn" href="edit.php?id=<?= $u->userId ?>">Edit</a>
                    <?php if (!($u->userType === USER_TYPE_SUPER && $auth['type'] !== USER_TYPE_SUPER)): ?>
                        <a class="btn" href="delete.php?id=<?= $u->userId ?>&csrf_token=<?= urlencode(Security::csrfToken()) ?>" onclick="return confirm('Delete this user?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>


