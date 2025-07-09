<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use Psr\Http\Message\ResponseInterface as Response;

class ListStoriesAction extends StoryAction
{
    protected function action(): Response
    {
        $stories = $this->storyRepository->findAll();
        $this->logger->info("Stories list was viewed.");
        return $this->respondWithData($stories);
    }
} 