<?php
require_once('config/connection.php');
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/Article.php';
require_once __DIR__ . '/../../services/Security.php';

Auth::requireLogin();
$auth = Auth::user();

$mine = isset($_GET['mine']);
$articles = Article::listAll($mine ? $auth['id'] : null);

// Author can only manage own articles; Admin can manage all; Super can view via view.php normally but can also manage here
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Articles</title>
    <style> body{font-family:Arial;margin:24px;} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ccc;padding:8px;text-align:left} a.btn{padding:6px 10px;background:#1976d2;color:#fff;text-decoration:none;border-radius:4px;margin-right:6px} </style>
</head>
<body>
    <h2><?= $mine ? 'My Articles' : 'All Articles' ?></h2>
    <p>
        <a class="btn" href="add.php">Add Article</a>
        <a class="btn" href="<?= BASE_URL ?>/auth/dashboard.php">Back</a>
    </p>
    <table>
        <tr>
            <th>ID</th><th>Title</th><th>Created</th><th>Display</th><th>Order</th><th>Author</th><th>Actions</th>
        </tr>
        <?php foreach ($articles as $a): ?>
            <?php if ($auth['type'] === USER_TYPE_AUTHOR && $a->authorId !== $auth['id']) continue; ?>
            <tr>
                <td><?= $a->articleId ?></td>
                <td><?= htmlspecialchars($a->articleTitle) ?></td>
                <td><?= htmlspecialchars($a->articleCreatedDate) ?></td>
                <td><?= $a->articleDisplay ? 'yes' : 'no' ?></td>
                <td><?= $a->articleOrder ?></td>
                <td><?= $a->authorId ?></td>
                <td>
                    <?php if ($auth['type'] !== USER_TYPE_AUTHOR || $a->authorId === $auth['id']): ?>
                        <a class="btn" href="edit.php?id=<?= $a->articleId ?>">Edit</a>
                        <a class="btn" href="delete.php?id=<?= $a->articleId ?>&csrf_token=<?= urlencode(Security::csrfToken()) ?>" onclick="return confirm('Delete this article?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>


