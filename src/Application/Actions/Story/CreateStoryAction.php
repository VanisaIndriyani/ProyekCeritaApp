<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use App\Domain\Story\Story;
use Psr\Http\Message\ResponseInterface as Response;

class CreateStoryAction extends StoryAction
{
    protected function action(): Response
    {
        $data = $this->getFormData();
        $story = new Story(
            null,
            (int)$data['userId'],
            $data['title'],
            $data['content'],
            $data['category'],
            $data['coverImage'] ?? null,
            date('Y-m-d H:i:s')
        );
        $saved = $this->storyRepository->save($story);
        $this->logger->info("Story created.");
        return $this->respondWithData($saved, 201);
    }
} 