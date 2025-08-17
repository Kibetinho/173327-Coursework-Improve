<?php
require_once('config/connection.php');
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/Article.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../services/Mailer.php';
require_once __DIR__ . '/../../services/Security.php';
require_once __DIR__ . '/../../services/Validator.php';

Auth::requireLogin();
$auth = Auth::user();

$flash = Security::consumeFlash();
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf_token'] ?? null);
    try {
        $errors = [];
        Validator::nonEmpty($_POST['article_title'] ?? '', 'Title', $errors);
        Validator::nonEmpty($_POST['article_full_text'] ?? '', 'Full Text', $errors);
        if (isset($_POST['article_order'])) {
            Validator::intRange($_POST['article_order'], -100000, 100000, 'Order', $errors);
        }
        if ($errors) {
            throw new RuntimeException(implode('\n', $errors));
        }
        $authorId = $auth['id'];
        if ($auth['type'] !== USER_TYPE_AUTHOR && !empty($_POST['author_id'])) {
            $authorId = (int)$_POST['author_id'];
        }
        $newId = Article::create([
            'author_id' => $authorId,
            'article_title' => trim($_POST['article_title'] ?? ''),
            'article_full_text' => trim($_POST['article_full_text'] ?? ''),
            'article_display' => isset($_POST['article_display']),
            'article_order' => (int)($_POST['article_order'] ?? 0),
        ]);
        // Notify all Administrators
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT email FROM users WHERE user_type = :t");
        $stmt->execute([':t' => USER_TYPE_ADMIN]);
        $emails = array_column($stmt->fetchAll(), 'email');
        if ($emails) {
            $subject = 'New Article Posted: ' . ($_POST['article_title'] ?? '');
            $body = '<p>A new article has been posted.</p><p>Title: <strong>' . htmlspecialchars($_POST['article_title'] ?? '') . '</strong></p>';
            foreach ($emails as $email) {
                Mailer::send($email, $subject, $body);
            }
        }
        Security::flash('success', 'Article created with ID ' . $newId);
        header('Location: list.php' . ($auth['type'] === USER_TYPE_AUTHOR ? '?mine=1' : ''));
        exit;
    } catch (Throwable $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch authors for selection if Admin or Super
$authors = [];
if ($auth['type'] !== USER_TYPE_AUTHOR) {
    $authors = User::listAll(USER_TYPE_AUTHOR);
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Add Article</title>
<link rel="stylesheet" href="/css/general.css">
<link rel="stylesheet" href="/css/article.css">
</head>
<body>
    <h2>Add Article</h2>
    <?php foreach ($flash as $msg): ?><p class="msg"><?= htmlspecialchars($msg['message']) ?></p><?php endforeach; ?>
    <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post">
        <?php if ($auth['type'] !== USER_TYPE_AUTHOR): ?>
            <label>Author</label>
            <select name="author_id">
                <?php foreach ($authors as $a): ?>
                    <option value="<?= $a->userId ?>"><?= htmlspecialchars($a->fullName) ?> (<?= htmlspecialchars($a->userName) ?>)</option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
        <label>Title</label>
        <input type="text" name="article_title" required>
        <label>Full Text</label>
        <textarea name="article_full_text" required></textarea>
        <label><input type="checkbox" name="article_display" checked> Display</label>
        <label>Order</label>
        <input type="number" name="article_order" value="0">
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <button type="submit">Create</button>
    </form>
    <p><a href="list.php<?= $auth['type'] === USER_TYPE_AUTHOR ? '?mine=1' : '' ?>">Back</a></p>
</body>
</html>


