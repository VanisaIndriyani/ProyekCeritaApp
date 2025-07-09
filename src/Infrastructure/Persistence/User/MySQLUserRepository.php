<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use PDO;

class MySQLUserRepository implements UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $rows = $stmt->fetchAll();
        return array_map([$this, 'rowToUser'], $rows);
    }

    public function findUserOfId(int $id): User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) throw new \Exception('User not found');
        return $this->rowToUser($row);
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ? $this->rowToUser($row) : null;
    }

    public function save(User $user, string $passwordHash): User
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password_hash, nama, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user->getUsername(),
            $passwordHash,
            $user->getFirstName(),
            $user->getLastName(),
            'user'
        ]);
        $id = (int)$this->pdo->lastInsertId();
        return new User($id, $user->getUsername(), $user->getFirstName(), $user->getLastName());
    }

    public function verifyLogin(string $username, string $password): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            return $this->rowToUser($row);
        }
        return null;
    }

    private function rowToUser(array $row): User
    {
        return new User(
            (int)$row['id'],
            $row['username'],
            $row['nama'] ?? '',
            $row['email'] ?? '',
            $row['role'] ?? 'user'
        );
    }
} 