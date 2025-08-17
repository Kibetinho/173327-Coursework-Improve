<?php
require_once('config/connection.php');
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/Article.php';
require_once __DIR__ . '/../../services/Security.php';
$token = $_GET['csrf_token'] ?? null;
Security::verifyCsrf($token);

Auth::requireLogin();
$auth = Auth::user();

$id = (int)($_GET['id'] ?? 0);
$article = Article::findById($id);
if (!$article) { http_response_code(404); echo 'Not found'; exit; }

if ($auth['type'] === USER_TYPE_AUTHOR && $article->authorId !== $auth['id']) {
    http_response_code(403); echo 'Forbidden'; exit;
}

Article::delete($id);
header('Location: list.php' . ($auth['type'] === USER_TYPE_AUTHOR ? '?mine=1' : ''));
exit;


