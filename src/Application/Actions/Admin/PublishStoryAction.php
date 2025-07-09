<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

class PublishStoryAction extends Action
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
        $story = $this->storyRepository->findStoryOfId($id);
        // Update status
        $storyReflection = new \ReflectionClass($story);
        $statusProp = $storyReflection->hasProperty('status') ? $storyReflection->getProperty('status') : null;
        if ($statusProp) {
            $statusProp->setAccessible(true);
            $statusProp->setValue($story, 'published');
        }
        $this->storyRepository->update($story);
        return $this->respondWithData(['message' => 'Cerita dipublish']);
    }
} 