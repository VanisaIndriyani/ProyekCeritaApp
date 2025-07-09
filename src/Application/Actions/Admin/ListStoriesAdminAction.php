<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

class ListStoriesAdminAction extends Action
{
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }

    protected function action(): Response
    {
        $stories = $this->storyRepository->findAll();
        return $this->respondWithData($stories);
    }
} 