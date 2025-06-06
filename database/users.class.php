<?php

declare(strict_types=1);

class User {
    private int $id;
    private string $username;
    private string $name;
    private string $email;
    private string $district;
    private ?string $phone;
    private int $photoId;
    private ?string $createdAt;

    public function __construct(
        int $id,
        string $username,
        string $name,
        string $email,
        string $district,
        ?string $phone,
        int $photoId,
        ?string $createdAt,

    ) {
        $this->id = $id;
        $this->username = $username;
        $this->name = $name;
        $this->email = $email;
        $this->district = $district;
        $this->phone = $phone;
        $this->photoId = $photoId;
        $this->createdAt = $createdAt;

    }

    public function getId(): int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getDistrict(): string {
        return $this->district;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function getPhotoId(): int {
        return $this->photoId;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public static function getById(PDO $db, int $id): ?User {
        $stmt = $db->prepare('
            SELECT
                user_id,
                username,
                name,
                email,
                district,
                phone_number,
                photo_id,
                created_at
            FROM Users
            WHERE user_id = ?
        ');
        $stmt->execute([$id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData === false) {
            return null;
        }

        return new User(
            (int)$userData['user_id'],
            $userData['username'] ?? '',
            $userData['name'] ?? '',
            $userData['email'] ?? '',
            $userData['district'] ?? '',
            $userData['phone_number'] ?? null,
            (int)($userData['photo_id'] ?? 0),
            $userData['created_at'] ?? null
        );
    }

    public static function getByUsername(PDO $db, string $username): ?User {
        $stmt = $db->prepare('
            SELECT
                user_id,
                username,
                name,
                email,
                district,
                phone_number,
                photo_id,
                created_at
                FROM Users
            WHERE username = ?
        ');
        $stmt->execute([$username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData === false) {
            return null;
        }

        return new User(
            (int)$userData['user_id'],
            $userData['username'] ?? '',
            $userData['name'] ?? '',
            $userData['email'] ?? '',
            $userData['district'] ?? '',
            $userData['phone_number'] ?? null,
            (int)($userData['photo_id'] ?? 0),
            $userData['created_at'] ?? null
        );
    }

    public static function isUserAdmin(PDO $db, int $userId): bool {
        $stmt = $db->prepare('SELECT is_admin FROM users WHERE user_id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user && (int)$user['is_admin'] === 1;
    }

    public static function updateUserAdminStatus(PDO $db, int $userIdToUpdate, int $newAdminStatus): bool {
        $newAdminStatus = ($newAdminStatus === 1) ? 1 : 0;

        $stmt = $db->prepare('UPDATE users SET is_admin = ? WHERE rowid = ?');
        return $stmt->execute([$newAdminStatus, $userIdToUpdate]);
    }


    public static function getAllUsers(PDO $db): array {
        $stmt = $db->prepare('SELECT user_id, username, name, is_admin FROM users');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteUser(PDO $db, int $userId): bool {
        try {
            $stmt = $db->prepare('DELETE FROM Users WHERE user_id = ?');
            $stmt->execute([$userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro PDO ao eliminar utilizador: " . $e->getMessage());
            return false;
        }
    }
}
