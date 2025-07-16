<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Story;

use App\Domain\Story\Story;
use App\Domain\Story\StoryRepository;
use PDO;

class MySQLStoryRepository implements StoryRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT s.*, u.nama as userName FROM stories s 
                LEFT JOIN users u ON s.userId = u.id 
                WHERE s.status = 'published' 
                ORDER BY s.createdAt DESC";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'rowToStory'], $rows);
    }

    public function findById(int $id): ?Story
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return $this->rowToStory($row);
    }

    public function create(Story $story): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO stories (userId, title, content, category, coverImage, createdAt, status, admin_comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $story->getUserId(),
            $story->getTitle(),
            $story->getContent(),
            $story->getCategory(),
            $story->getCoverImage(),
            $story->getCreatedAt(),
            $story->getStatus() ?? 'pending',
            $story->getAdminComment()
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findStoryOfId(int $id): Story
    {
        $story = $this->findById($id);
        if (!$story) throw new \Exception('Story not found');
        return $story;
    }

    public function save(Story $story): Story
    {
        $stmt = $this->pdo->prepare("INSERT INTO stories (userId, title, content, category, coverImage, createdAt, status, admin_comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $story->getUserId(),
            $story->getTitle(),
            $story->getContent(),
            $story->getCategory(),
            $story->getCoverImage(),
            $story->getCreatedAt(),
            $story->getStatus() ?? 'pending',
            $story->getAdminComment()
        ]);
        $id = (int)$this->pdo->lastInsertId();
        return new Story($id, $story->getUserId(), $story->getTitle(), $story->getContent(), $story->getCategory(), $story->getCoverImage(), $story->getCreatedAt(), null, $story->getStatus() ?? 'pending', null, $story->getAdminComment());
    }

    public function update(Story $story): Story
    {
        $stmt = $this->pdo->prepare("
            UPDATE stories SET
                title = :title,
                content = :content,
                category = :category,
                coverImage = :coverImage,
                updatedAt = :updatedAt,
                status = :status,
                admin_comment = :admin_comment
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $story->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':title', $story->getTitle());
        $stmt->bindValue(':content', $story->getContent());
        $stmt->bindValue(':category', $story->getCategory());
        $stmt->bindValue(':coverImage', $story->getCoverImage());
        $stmt->bindValue(':updatedAt', date('Y-m-d H:i:s'));
        $stmt->bindValue(':status', $story->getStatus() ?? 'pending');
        $stmt->bindValue(':admin_comment', $story->getAdminComment());
        $stmt->execute();
        return $story;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM stories WHERE id=?");
        $stmt->execute([$id]);
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stories WHERE userId=?");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'rowToStory'], $rows);
    }

    public function findAllAdmin(): array
    {
        $stmt = $this->pdo->query("SELECT s.*, u.nama as userName FROM stories s LEFT JOIN users u ON s.userId = u.id ORDER BY s.createdAt DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'rowToStory'], $rows);
    }

    private function rowToStory(array $row): Story
    {
        return new Story(
            (int)$row['id'],
            (int)$row['userId'],
            $row['title'],
            $row['content'],
            $row['category'] ?? null,
            $row['coverImage'] ?? null,
            $row['createdAt'] ?? null,
            $row['updatedAt'] ?? null,
            $row['status'] ?? 'pending',
            $row['userName'] ?? null,
            $row['admin_comment'] ?? null
        );
    }
} 