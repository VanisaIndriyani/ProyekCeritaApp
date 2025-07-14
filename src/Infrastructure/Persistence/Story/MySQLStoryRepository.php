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
        $stmt = $this->pdo->query("SELECT * FROM stories WHERE status = 'published' ORDER BY createdAt DESC");
        $rows = $stmt->fetchAll();
        return array_map([$this, 'rowToStory'], $rows);
    }

    public function findStoryOfId(int $id): Story
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) throw new \Exception('Story not found');
        return $this->rowToStory($row);
    }

    public function save(Story $story): Story
    {
        $stmt = $this->pdo->prepare("INSERT INTO stories (userId, title, content, category, coverImage, createdAt, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $story->getUserId(),
            $story->getTitle(),
            $story->getContent(),
            $story->getCategory(),
            $story->getCoverImage(),
            $story->getCreatedAt(),
            $story->getStatus() ?? 'pending'
        ]);
        $id = (int)$this->pdo->lastInsertId();
        return new Story($id, $story->getUserId(), $story->getTitle(), $story->getContent(), $story->getCategory(), $story->getCoverImage(), $story->getCreatedAt(), null, $story->getStatus() ?? 'pending');
    }

    public function update(Story $story): Story
    {
        $stmt = $this->pdo->prepare("
            UPDATE stories SET
                userId = :userId,
                title = :title,
                content = :content,
                category = :category,
                coverImage = :coverImage,
                updatedAt = :updatedAt,
                status = :status
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $story->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':userId', $story->getUserId(), PDO::PARAM_INT);
        $stmt->bindValue(':title', $story->getTitle());
        $stmt->bindValue(':content', $story->getContent());
        $stmt->bindValue(':category', $story->getCategory());
        $stmt->bindValue(':coverImage', $story->getCoverImage());
        $stmt->bindValue(':updatedAt', $story->getUpdatedAt());
        $stmt->bindValue(':status', $story->getStatus() ?? 'pending');
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
        $stmt = $this->pdo->query("SELECT * FROM stories ORDER BY createdAt DESC");
        $rows = $stmt->fetchAll();
        return array_map([$this, 'rowToStory'], $rows);
    }

    private function rowToStory(array $row): Story
    {
        return new Story(
            (int)$row['id'],
            (int)$row['userId'],
            $row['title'],
            $row['content'],
            $row['category'],
            $row['coverImage'],
            $row['createdAt'],
            $row['updatedAt'] ?? null,
            $row['status'] ?? null
        );
    }
} 