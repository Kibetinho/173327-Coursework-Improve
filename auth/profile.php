<?php
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../services/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/Security.php';
require_once __DIR__ . '/../services/Upload.php';
require_once __DIR__ . '/../services/Validator.php';

Auth::requireLogin();
$authUser = Auth::user();
$user = User::findById($authUser['id']);

$flash = Security::consumeFlash();
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf_token'] ?? null);
    try {
        $errors = [];
        Validator::nonEmpty($_POST['full_name'] ?? '', 'Full Name', $errors);
        Validator::email($_POST['email'] ?? '', 'Email', $errors);
        if (!empty($_POST['new_password'])) {
            Validator::minLen($_POST['new_password'], 6, 'New Password', $errors);
        }
        if ($errors) {
            throw new RuntimeException(implode('\n', $errors));
        }
        $profileImagePath = $user->profileImage;
        if (!empty($_FILES['profile_image']['name'])) {
            $profileImagePath = Upload::saveProfileImage($_FILES['profile_image']);
        }
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'user_type' => $user->userType, // cannot change own role here
        'profile_image' => $profileImagePath,
        'address' => trim($_POST['address'] ?? ''),
    ];
    if (!empty($_POST['new_password'])) {
        $data['new_password'] = $_POST['new_password'];
    }
    User::update($user->userId, $data, false);
    Security::flash('success', 'Profile updated');
    header('Location: ' . BASE_URL . '/auth/profile.php');
    exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
    $user = User::findById($authUser['id']);
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Update Profile</title>
    <style> body{font-family:Arial;margin:24px;} label{display:block;margin-top:8px} input{width:360px;padding:8px} button{margin-top:12px;padding:10px 14px} .note{color: #666}</style>
</head>
<body>
    <h2>Update My Profile</h2>
    <?php foreach ($flash as $msg): ?><p style="color:<?= $msg['type']==='success'?'green':'#b00020' ?>;"><?= htmlspecialchars($msg['message']) ?></p><?php endforeach; ?>
    <?php if ($error): ?><p style="color:#b00020;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Username (not editable)</label>
        <input type="text" value="<?= htmlspecialchars($user->userName) ?>" disabled>

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user->fullName) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>

        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?= htmlspecialchars($user->phoneNumber ?? '') ?>">

        <label>Profile Image</label>
        <?php if ($user->profileImage): ?>
            <div><img src="<?= htmlspecialchars($user->profileImage) ?>" alt="Profile" style="max-width:120px;border:1px solid #ddd;border-radius:4px"></div>
        <?php endif; ?>
        <input type="file" name="profile_image" accept="image/*">

        <label>Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user->address ?? '') ?>">

        <label>New Password <span class="note">(leave blank to keep current)</span></label>
        <input type="password" name="new_password">

        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <button type="submit">Save</button>
    </form>
    <p><a href="<?= BASE_URL ?>/auth/dashboard.php">Back</a></p>
</body>
</html>


