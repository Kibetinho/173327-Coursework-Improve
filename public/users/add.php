<?php
require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/Security.php';
require_once __DIR__ . '/../../services/Validator.php';

Auth::requireLogin();
$auth = Auth::user();
if ($auth['type'] === USER_TYPE_AUTHOR) { http_response_code(403); echo 'Forbidden'; exit; }

$defaultType = $_GET['type'] ?? ($auth['type'] === USER_TYPE_ADMIN ? USER_TYPE_AUTHOR : USER_TYPE_AUTHOR);
if ($auth['type'] === USER_TYPE_SUPER) {
    $allowedTypes = [USER_TYPE_SUPER, USER_TYPE_ADMIN, USER_TYPE_AUTHOR];
} else { // Admin
    $allowedTypes = [USER_TYPE_AUTHOR];
}

$flash = Security::consumeFlash();
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf_token'] ?? null);
    $userType = $_POST['user_type'] ?? USER_TYPE_AUTHOR;
    $errors = [];
    Validator::nonEmpty($_POST['user_name'] ?? '', 'Username', $errors);
    Validator::minLen($_POST['password'] ?? '', 6, 'Password', $errors);
    Validator::nonEmpty($_POST['full_name'] ?? '', 'Full Name', $errors);
    Validator::email($_POST['email'] ?? '', 'Email', $errors);
    if (!in_array($userType, $allowedTypes, true)) {
        $error = 'Not allowed to create this user type';
    } elseif (!$errors) {
        try {
            $newId = User::create([
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'user_name' => trim($_POST['user_name'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'user_type' => $userType,
                'profile_image' => trim($_POST['profile_image'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
            ]);
            Security::flash('success', 'User created with ID ' . $newId);
            header('Location: list.php' . ($auth['type'] === USER_TYPE_ADMIN ? '?only=authors' : ''));
            exit;
        } catch (Throwable $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    } else {
        $error = implode('\n', $errors);
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Add User</title>
<style> body{font-family:Arial;margin:24px;} label{display:block;margin-top:8px} input,select{width:360px;padding:8px} button{margin-top:12px;padding:10px 14px} .msg{color:green}.err{color:#b00020}</style>
</head>
<body>
    <h2>Add <?= $auth['type'] === USER_TYPE_ADMIN ? 'Author' : 'User' ?></h2>
    <?php foreach ($flash as $msg): ?><p class="msg"><?= htmlspecialchars($msg['message']) ?></p><?php endforeach; ?>
    <?php if ($error): ?><pre class="err" style="white-space:pre-wrap;"><?= htmlspecialchars($error) ?></pre><?php endif; ?>
    <form method="post">
        <label>Username</label>
        <input type="text" name="user_name" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Full Name</label>
        <input type="text" name="full_name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Phone Number</label>
        <input type="text" name="phone_number">
        <label>Profile Image</label>
        <input type="text" name="profile_image">
        <label>Address</label>
        <input type="text" name="address">
        <?php if ($auth['type'] === USER_TYPE_SUPER): ?>
            <label>User Type</label>
            <select name="user_type">
                <?php foreach ($allowedTypes as $t): ?>
                    <option value="<?= $t ?>" <?= $t === $defaultType ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input type="hidden" name="user_type" value="Author">
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <button type="submit">Create</button>
    </form>
    <p><a href="list.php<?= $auth['type'] === USER_TYPE_ADMIN ? '?only=authors' : '' ?>">Back</a></p>
</body>
</html>


