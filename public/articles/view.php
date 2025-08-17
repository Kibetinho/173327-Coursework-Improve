<?php
require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../../services/Auth.php';
require_once __DIR__ . '/../../models/Article.php';

Auth::requireLogin();
$articles = Article::latest(6);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Latest Articles</title>
<style> body{font-family:Arial;margin:24px;} .card{background:#fff;border:1px solid #ddd;border-radius:6px;padding:16px;margin-bottom:12px} .meta{color:#666;font-size:12px} a.btn{padding:6px 10px;background:#1976d2;color:#fff;text-decoration:none;border-radius:4px}</style>
</head>
<body>
    <h2>Latest Articles</h2>
    <p><a class="btn" href="<?= BASE_URL ?>/auth/dashboard.php">Back</a></p>
    <?php foreach ($articles as $a): ?>
        <div class="card">
            <h3><?= htmlspecialchars($a->articleTitle) ?></h3>
            <div class="meta">Posted: <?= htmlspecialchars($a->articleCreatedDate) ?></div>
            <p><?= nl2br(htmlspecialchars(mb_strimwidth($a->articleFullText, 0, 400, '...'))) ?></p>
        </div>
    <?php endforeach; ?>
</body>
</html>


