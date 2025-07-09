<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

class DeleteStoryAdminAction extends Action
{
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }

    protected function action(): Response
    {
        $id = (int)$this->resolveArg('id');
        $this->storyRepository->delete($id);
        return $this->respondWithData(['message' => 'Cerita dihapus']);
    }
} 