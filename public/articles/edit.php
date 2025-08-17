<?php
require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/Article.php';
require_once __DIR__ . '/../../services/Security.php';
require_once __DIR__ . '/../../services/Validator.php';

Auth::requireLogin();
$auth = Auth::user();

$id = (int)($_GET['id'] ?? 0);
$article = Article::findById($id);
if (!$article) { http_response_code(404); echo 'Not found'; exit; }

if ($auth['type'] === USER_TYPE_AUTHOR && $article->authorId !== $auth['id']) {
    http_response_code(403); echo 'Forbidden'; exit;
}

$flash = Security::consumeFlash();
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf_token'] ?? null);
    $errors = [];
    Validator::nonEmpty($_POST['article_title'] ?? '', 'Title', $errors);
    Validator::nonEmpty($_POST['article_full_text'] ?? '', 'Full Text', $errors);
    if (isset($_POST['article_order'])) {
        Validator::intRange($_POST['article_order'], -100000, 100000, 'Order', $errors);
    }
    if ($errors) {
        $error = implode('\n', $errors);
    } else {
        Article::update($article->articleId, [
            'article_title' => trim($_POST['article_title'] ?? ''),
            'article_full_text' => trim($_POST['article_full_text'] ?? ''),
            'article_display' => isset($_POST['article_display']),
            'article_order' => (int)($_POST['article_order'] ?? 0),
        ]);
        Security::flash('success', 'Saved');
        header('Location: edit.php?id=' . $article->articleId);
        exit;
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Edit Article</title>
<style> body{font-family:Arial;margin:24px;} label{display:block;margin-top:8px} input,textarea{width:600px;padding:8px} textarea{height:160px} button{margin-top:12px;padding:10px 14px}</style>
</head>
<body>
    <h2>Edit Article #<?= $article->articleId ?></h2>
    <?php foreach ($flash as $msg): ?><p style="color:green;"><?= htmlspecialchars($msg['message']) ?></p><?php endforeach; ?>
    <?php if ($error): ?><pre style="color:#b00020;white-space:pre-wrap;"><?= htmlspecialchars($error) ?></pre><?php endif; ?>
    <form method="post">
        <label>Title</label>
        <input type="text" name="article_title" value="<?= htmlspecialchars($article->articleTitle) ?>" required>
        <label>Full Text</label>
        <textarea name="article_full_text" required><?= htmlspecialchars($article->articleFullText) ?></textarea>
        <label><input type="checkbox" name="article_display" <?= $article->articleDisplay ? 'checked' : '' ?>> Display</label>
        <label>Order</label>
        <input type="number" name="article_order" value="<?= $article->articleOrder ?>">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <button type="submit">Save</button>
    </form>
    <p><a href="list.php<?= $auth['type'] === USER_TYPE_AUTHOR ? '?mine=1' : '' ?>">Back</a></p>
</body>
</html>


