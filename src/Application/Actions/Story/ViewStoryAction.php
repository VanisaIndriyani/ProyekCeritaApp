<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use Psr\Http\Message\ResponseInterface as Response;

class ViewStoryAction extends StoryAction
{
    protected function action(): Response
    {
        $id = (int)$this->resolveArg('id');
        $story = $this->storyRepository->findStoryOfId($id);
        $this->logger->info("Story with id $id was viewed.");
        return $this->respondWithData($story);
    }
} 