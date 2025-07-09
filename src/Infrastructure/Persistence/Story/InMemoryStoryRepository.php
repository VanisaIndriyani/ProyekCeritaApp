<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Story;

use App\Domain\Story\Story;
use App\Domain\Story\StoryRepository;

class InMemoryStoryRepository implements StoryRepository
{
    /**
     * @var Story[]
     */
    private array $stories;

    public function __construct(array $stories = null)
    {
        $this->stories = $stories ?? [
            1 => new Story(1, 1, 'Judul Cerita 1', 'Isi cerita 1', 'fiksi', null, date('Y-m-d H:i:s')),
            2 => new Story(2, 2, 'Judul Cerita 2', 'Isi cerita 2', 'pengalaman', null, date('Y-m-d H:i:s')),
        ];
    }

    public function findAll(): array
    {
        return array_values($this->stories);
    }

    public function findStoryOfId(int $id): Story
    {
        if (!isset($this->stories[$id])) {
            throw new \Exception('Story not found');
        }
        return $this->stories[$id];
    }

    public function save(Story $story): Story
    {
        $id = count($this->stories) + 1;
        $storyReflection = new \ReflectionClass($story);
        $idProperty = $storyReflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($story, $id);
        $this->stories[$id] = $story;
        return $story;
    }

    public function update(Story $story): Story
    {
        $id = $story->getId();
        if (!isset($this->stories[$id])) {
            throw new \Exception('Story not found');
        }
        $this->stories[$id] = $story;
        return $story;
    }

    public function delete(int $id): void
    {
        unset($this->stories[$id]);
    }

    public function findByUserId(int $userId): array
    {
        return array_values(array_filter($this->stories, fn($story) => $story->getUserId() === $userId));
    }
} 