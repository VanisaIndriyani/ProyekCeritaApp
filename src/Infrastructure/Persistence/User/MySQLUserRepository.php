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
    
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? $this->rowToUser($row) : null;
    }
    
    public function create(array $userData): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (nama, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $userData['nama'],
            $userData['email'],
            $userData['username'],
            $userData['password'],
            $userData['role']
        ]);
        return (int)$this->pdo->lastInsertId();
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

    public function saveResetToken(int $userId, string $token, string $expires): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $userId]);

        return $stmt->rowCount() > 0;
    }
    
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        if (isset($data['nama'])) {
            $fields[] = 'nama = ?';
            $params[] = $data['nama'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $params[] = $data['email'];
        }
        if (isset($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[] = $data['password'];
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    private function rowToUser(array $row): User
    {
        return new User(
            (int)$row['id'],
            $row['username'],
            $row['nama'] ?? '',
            '', // lastName (kosong karena nama sudah fullname)
            $row['role'] ?? 'user',
            $row['email'] ?? '',
            $row['password_hash'] ?? ''
        );
    }
} 