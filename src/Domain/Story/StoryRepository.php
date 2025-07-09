<?php

declare(strict_types=1);

namespace App\Domain\Story;

interface StoryRepository
{
    /**
     * @return Story[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Story
     */
    public function findStoryOfId(int $id): Story;

    /**
     * @param Story $story
     * @return Story
     */
    public function save(Story $story): Story;

    /**
     * @param Story $story
     * @return Story
     */
    public function update(Story $story): Story;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;

    /**
     * @param int $userId
     * @return Story[]
     */
    public function findByUserId(int $userId): array;
} 