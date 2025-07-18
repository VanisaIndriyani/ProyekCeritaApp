<?php

declare(strict_types=1);

namespace App\Domain\Story;

use JsonSerializable;

class Story implements JsonSerializable
{
    private ?int $id;
    private int $userId;
    private string $title;
    private string $content;
    private string $category;
    private ?string $coverImage;
    private string $createdAt;
    private ?string $updatedAt;
    private ?string $status;
    private ?string $userName;
    private ?string $adminComment;

    public function __construct(
        ?int $id,
        int $userId,
        string $title,
        string $content,
        string $category,
        ?string $coverImage,
        string $createdAt,
        ?string $updatedAt = null,
        ?string $status = null,
        ?string $userName = null,
        ?string $adminComment = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->content = $content;
        $this->category = $category;
        $this->coverImage = $coverImage;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->status = $status;
        $this->userName = $userName;
        $this->adminComment = $adminComment;
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTitle(): string { return $this->title; }
    public function getContent(): string { return $this->content; }
    public function getCategory(): string { return $this->category; }
    public function getCoverImage(): ?string { return $this->coverImage; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getStatus(): ?string { return $this->status; }
    public function getUserName(): ?string { return $this->userName; }
    public function setStatus(?string $status): void { $this->status = $status; }
    public function getAdminComment(): ?string { return $this->adminComment; }
    public function setAdminComment(?string $comment): void { $this->adminComment = $comment; }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'coverImage' => $this->coverImage,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'status' => $this->status,
            'userName' => $this->userName,
            'adminComment' => $this->adminComment,
        ];
    }
} 