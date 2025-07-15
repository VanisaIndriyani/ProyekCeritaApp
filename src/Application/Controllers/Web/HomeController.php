<?php

declare(strict_types=1);

namespace App\Application\Controllers\Web;

use App\Application\Controllers\BaseController;
use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class HomeController extends BaseController
{
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }

    protected function action(): Response
    {
        try {
            // Get latest published stories for home page
            $allStories = $this->storyRepository->findAll();
            
            // Filter published stories only
            $publishedStories = array_filter($allStories, function($story) {
                return $story->getStatus() === 'published';
            });

            // Sort by creation date (newest first)
            usort($publishedStories, function($a, $b) {
                return strtotime($b->getCreatedAt()) - strtotime($a->getCreatedAt());
            });

            // Limit to 6 stories for home page
            $stories = array_slice($publishedStories, 0, 6);

            // Convert to array format for view
            $storiesData = [];
            foreach ($stories as $story) {
                $storiesData[] = [
                    'id' => $story->getId(),
                    'judul' => $story->getTitle(),
                    'konten' => $story->getContent(),
                    'kategori' => $story->getCategory(),
                    'author_name' => $story->getUserName(),
                    'userName' => $story->getUserName(),
                    'created_at' => $story->getCreatedAt(),
                    'status' => $story->getStatus()
                ];
            }

            return $this->respondWithView('home.php', [
                'stories' => $storiesData,
                'hasError' => false,
                'errorMessage' => ''
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to load stories for home page', [
                'error' => $e->getMessage()
            ]);

            return $this->respondWithView('home.php', [
                'stories' => [],
                'hasError' => true,
                'errorMessage' => 'Gagal memuat cerita. Silakan coba lagi nanti.'
            ]);
        }
    }
}
