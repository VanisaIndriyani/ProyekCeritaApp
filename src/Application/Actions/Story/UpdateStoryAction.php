<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use App\Domain\Story\Story;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateStoryAction extends StoryAction
{
    protected function action(): Response
    {
        $id = (int)$this->resolveArg('id');
        $data = $this->getFormData();
        $story = new Story(
            $id,
            $data['userId'],
            $data['title'],
            $data['content'],
            $data['category'],
            $data['coverImage'] ?? null,
            $data['createdAt'] ?? date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        );
        $updated = $this->storyRepository->update($story);
        $this->logger->info("Story with id $id updated.");
        return $this->respondWithData($updated);
    }
} 