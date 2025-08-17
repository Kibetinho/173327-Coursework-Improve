<?php

require_once __DIR__ . '/../config/connection.php';

class User
{
    public int $userId;
    public string $fullName;
    public string $email;
    public ?string $phoneNumber;
    public string $userName;
    public string $userType; // Super_User, Administrator, Author
    public ?string $profileImage;
    public ?string $address;
    public ?string $accessTime;

    public function __construct(array $row)
    {
        $this->userId = (int)($row['user_id'] ?? 0);
        $this->fullName = $row['full_name'] ?? '';
        $this->email = $row['email'] ?? '';
        $this->phoneNumber = $row['phone_number'] ?? null;
        $this->userName = $row['user_name'] ?? '';
        $this->userType = $row['user_type'] ?? USER_TYPE_AUTHOR;
        $this->profileImage = $row['profile_image'] ?? null;
        $this->address = $row['address'] ?? null;
        $this->accessTime = $row['access_time'] ?? null;
    }

    public static function findByUserName(string $userName): ?User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_name = :u LIMIT 1');
        $stmt->execute([':u' => $userName]);
        $row = $stmt->fetch();
        return $row ? new User($row) : null;
    }

    public static function findById(int $userId): ?User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ? new User($row) : null;
    }

    public static function listAll(?string $onlyUserType = null): array
    {
        $pdo = Database::getConnection();
        if ($onlyUserType) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE user_type = :t ORDER BY user_id DESC');
            $stmt->execute([':t' => $onlyUserType]);
        } else {
            $stmt = $pdo->query('SELECT * FROM users ORDER BY user_id DESC');
        }
        return array_map(fn($r) => new User($r), $stmt->fetchAll());
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $hash = password_hash($data['password'] ?? '', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone_number, user_name, password_hash, user_type, profile_image, address) VALUES (:full_name, :email, :phone_number, :user_name, :password_hash, :user_type, :profile_image, :address) RETURNING user_id');
        $stmt->execute([
            ':full_name' => $data['full_name'] ?? '',
            ':email' => $data['email'] ?? '',
            ':phone_number' => $data['phone_number'] ?? null,
            ':user_name' => $data['user_name'] ?? '',
            ':password_hash' => $hash,
            ':user_type' => $data['user_type'] ?? USER_TYPE_AUTHOR,
            ':profile_image' => $data['profile_image'] ?? null,
            ':address' => $data['address'] ?? null,
        ]);
        $newId = $stmt->fetchColumn();
        return (int)$newId;
    }

    public static function update(int $userId, array $data, bool $canChangeUserName = false): void
    {
        $pdo = Database::getConnection();
        $fields = [
            'full_name' => ':full_name',
            'email' => ':email',
            'phone_number' => ':phone_number',
            'user_type' => ':user_type',
            'profile_image' => ':profile_image',
            'address' => ':address',
        ];
        $params = [
            ':full_name' => $data['full_name'] ?? '',
            ':email' => $data['email'] ?? '',
            ':phone_number' => $data['phone_number'] ?? null,
            ':user_type' => $data['user_type'] ?? USER_TYPE_AUTHOR,
            ':profile_image' => $data['profile_image'] ?? null,
            ':address' => $data['address'] ?? null,
            ':user_id' => $userId,
        ];

        if (!empty($data['new_password'])) {
            $fields['password_hash'] = ':password_hash';
            $params[':password_hash'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }

        if ($canChangeUserName && isset($data['user_name'])) {
            $fields['user_name'] = ':user_name';
            $params[':user_name'] = $data['user_name'];
        }

        $set = implode(', ', array_map(fn($k, $v) => "$k = $v", array_keys($fields), $fields));
        $sql = "UPDATE users SET $set WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $userId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = :id');
        $stmt->execute([':id' => $userId]);
    }

    public static function verifyPassword(string $userName, string $password): ?User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_name = :u LIMIT 1');
        $stmt->execute([':u' => $userName]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if (!password_verify($password, $row['password_hash'] ?? '')) {
            return null;
        }
        // update access time
        $pdo->prepare('UPDATE users SET access_time = NOW() WHERE user_id = :id')->execute([':id' => $row['user_id']]);
        return new User($row);
    }
}


