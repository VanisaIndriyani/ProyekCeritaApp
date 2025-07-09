<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use App\Domain\Story\StoryRepository;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

abstract class StoryAction extends Action
{
    protected StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }
} 