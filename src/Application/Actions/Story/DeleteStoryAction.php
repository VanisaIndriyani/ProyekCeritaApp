<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use Psr\Http\Message\ResponseInterface as Response;

class DeleteStoryAction extends StoryAction
{
    protected function action(): Response
    {
        $id = (int)$this->resolveArg('id');
        $this->storyRepository->delete($id);
        $this->logger->info("Story with id $id deleted.");
        return $this->respondWithData(['message' => 'Story deleted']);
    }
} 