<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use Psr\Http\Message\ResponseInterface as Response;

class ListStoriesAction extends StoryAction
{
    protected function action(): Response
    {
        $queryParams = $this->request->getQueryParams();
        $userId = $queryParams['userId'] ?? null;

        if ($userId !== null) {
            // Jika userId ada, panggil findByUserId dari repository
            $stories = $this->storyRepository->findByUserId((int)$userId);
            $this->logger->info("Stories list for user ID {$userId} was viewed.");
        } else {
            // Jika userId tidak ada, panggil findAll (hanya cerita yang dipublikasikan)
            $stories = $this->storyRepository->findAll();
            $this->logger->info("Stories list was viewed.");
        }
        
        return $this->respondWithData($stories);
    }
}