<?php
require_once('config/connection.php');
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/Security.php';
$token = $_GET['csrf_token'] ?? null;
Security::verifyCsrf($token);

Auth::requireLogin();
$auth = Auth::user();
if ($auth['type'] === USER_TYPE_AUTHOR) { http_response_code(403); echo 'Forbidden'; exit; }

$id = (int)($_GET['id'] ?? 0);
$target = User::findById($id);
if (!$target) { http_response_code(404); echo 'Not found'; exit; }

if ($auth['type'] === USER_TYPE_ADMIN && $target->userType !== USER_TYPE_AUTHOR) {
    http_response_code(403); echo 'Forbidden'; exit;
}

if ($target->userType === USER_TYPE_SUPER && $auth['type'] !== USER_TYPE_SUPER) {
    http_response_code(403); echo 'Forbidden'; exit;
}

User::delete($id);
header('Location: list.php' . ($auth['type'] === USER_TYPE_ADMIN ? '?only=authors' : ''));
exit;
?>
<link rel="stylesheet" href="/css/general.css">
<link rel="stylesheet" href="/css/user.css">


