<?php

require_once __DIR__ . '/../config/connection.php';

class Article
{
    public int $articleId;
    public int $authorId;
    public string $articleTitle;
    public string $articleFullText;
    public string $articleCreatedDate;
    public ?string $articleLastUpdate;
    public bool $articleDisplay;
    public int $articleOrder;

    public function __construct(array $row)
    {
        $this->articleId = (int)($row['article_id'] ?? 0);
        $this->authorId = (int)($row['author_id'] ?? 0);
        $this->articleTitle = $row['article_title'] ?? '';
        $this->articleFullText = $row['article_full_text'] ?? '';
        $this->articleCreatedDate = $row['article_created_date'] ?? '';
        $this->articleLastUpdate = $row['article_last_update'] ?? null;
        $this->articleDisplay = (bool)($row['article_display'] ?? false);
        $this->articleOrder = (int)($row['article_order'] ?? 0);
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO articles (author_id, article_title, article_full_text, article_display, article_order) VALUES (:author_id, :title, :full_text, :display, :ord) RETURNING article_id');
        $stmt->execute([
            ':author_id' => (int)$data['author_id'],
            ':title' => $data['article_title'] ?? '',
            ':full_text' => $data['article_full_text'] ?? '',
            ':display' => !empty($data['article_display']),
            ':ord' => (int)($data['article_order'] ?? 0),
        ]);
        return (int)$stmt->fetchColumn();
    }

    public static function update(int $articleId, array $data): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE articles SET article_title = :title, article_full_text = :full_text, article_last_update = NOW(), article_display = :display, article_order = :ord WHERE article_id = :id');
        $stmt->execute([
            ':title' => $data['article_title'] ?? '',
            ':full_text' => $data['article_full_text'] ?? '',
            ':display' => !empty($data['article_display']),
            ':ord' => (int)($data['article_order'] ?? 0),
            ':id' => $articleId,
        ]);
    }

    public static function delete(int $articleId): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare('DELETE FROM articles WHERE article_id = :id')->execute([':id' => $articleId]);
    }

    public static function findById(int $articleId): ?Article
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM articles WHERE article_id = :id');
        $stmt->execute([':id' => $articleId]);
        $row = $stmt->fetch();
        return $row ? new Article($row) : null;
    }

    public static function listAll(?int $onlyAuthorId = null): array
    {
        $pdo = Database::getConnection();
        if ($onlyAuthorId) {
            $stmt = $pdo->prepare('SELECT * FROM articles WHERE author_id = :aid ORDER BY article_created_date DESC');
            $stmt->execute([':aid' => $onlyAuthorId]);
        } else {
            $stmt = $pdo->query('SELECT * FROM articles ORDER BY article_created_date DESC');
        }
        return array_map(fn($r) => new Article($r), $stmt->fetchAll());
    }

    public static function latest(int $limit = 6): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM articles WHERE article_display = true ORDER BY article_created_date DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($r) => new Article($r), $stmt->fetchAll());
    }
}


