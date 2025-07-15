<?php

declare(strict_types=1);

namespace App\Application\Controllers\Web;

use App\Application\Controllers\BaseController;
use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class StoryDetailController extends BaseController
{
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }

    protected function action(): Response
    {
        return $this->show($this->request, $this->response);
    }

    public function show(Request $request, Response $response): Response
    {
        try {
            // Get story ID from query params or URL args
            $queryParams = $request->getQueryParams();
            $urlArgs = $this->args ?? [];
            
            $storyId = 0;
            if (isset($urlArgs['id'])) {
                // From URL path like /story/{id}
                $storyId = (int)$urlArgs['id'];
            } elseif (isset($queryParams['id'])) {
                // From query string like /story-detail?id=123
                $storyId = (int)$queryParams['id'];
            }

            if ($storyId <= 0) {
                return $this->renderNotFound($response);
            }

            // Get story by ID
            $story = $this->storyRepository->findStoryOfId($storyId);
            
            if (!$story || $story->getStatus() !== 'published') {
                return $this->renderNotFound($response);
            }

            // Convert story to array
            $storyData = $this->convertStoryToArray($story);

            // Get related stories (same category, exclude current story)
            $allStories = $this->storyRepository->findAll();
            $relatedStories = $this->getRelatedStories($allStories, $story, 3);

            // Prepare data for view
            $viewData = [
                'story' => $storyData,
                'relatedStories' => $relatedStories,
                'categories' => $this->getAvailableCategories($allStories)
            ];

            // Render view
            ob_start();
            $title = $storyData['title'] . ' - Cerita Mahasiswa';
            $additionalCSS = ['/story-detail.css'];
            $currentPage = 'story-detail';
            extract($viewData);
            include __DIR__ . '/../../../../resources/views/pages/story-detail.php';
            $html = ob_get_clean();

            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');

        } catch (\Exception $e) {
            $this->logger->error("Failed to load story detail page", [
                'error' => $e->getMessage(),
                'storyId' => $storyId ?? 'unknown'
            ]);
            
            return $this->renderNotFound($response);
        }
    }

    private function renderNotFound(Response $response): Response
    {
        ob_start();
        $title = 'Cerita Tidak Ditemukan - Cerita Mahasiswa';
        $additionalCSS = ['/story-detail.css'];
        $currentPage = 'story-detail';
        $story = null;
        $relatedStories = [];
        $categories = [];
        include __DIR__ . '/../../../../resources/views/pages/story-detail.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response->withStatus(404)->withHeader('Content-Type', 'text/html');
    }

    private function convertStoryToArray($story): array
    {
        return [
            'id' => $story->getId(),
            'title' => $story->getTitle(),
            'content' => $story->getContent(),
            'category' => $story->getCategory(),
            'userName' => $story->getUserName() ?? 'Anonymous',
            'createdAt' => $story->getCreatedAt(),
            'status' => $story->getStatus(),
            'readTime' => $this->calculateReadTime($story->getContent())
        ];
    }

    private function getRelatedStories(array $allStories, $currentStory, int $limit = 3): array
    {
        $related = [];
        $currentCategory = $currentStory->getCategory();
        $currentId = $currentStory->getId();

        // Filter published stories with same category but different ID
        foreach ($allStories as $story) {
            if ($story->getStatus() === 'published' && 
                $story->getCategory() === $currentCategory && 
                $story->getId() !== $currentId) {
                $related[] = $this->convertStoryToArray($story);
            }
            
            if (count($related) >= $limit) {
                break;
            }
        }

        // If not enough related stories, add from other categories
        if (count($related) < $limit) {
            foreach ($allStories as $story) {
                if ($story->getStatus() === 'published' && 
                    $story->getId() !== $currentId) {
                    $storyArray = $this->convertStoryToArray($story);
                    
                    // Check if not already added
                    $alreadyAdded = false;
                    foreach ($related as $relatedStory) {
                        if ($relatedStory['id'] === $storyArray['id']) {
                            $alreadyAdded = true;
                            break;
                        }
                    }
                    
                    if (!$alreadyAdded) {
                        $related[] = $storyArray;
                    }
                    
                    if (count($related) >= $limit) {
                        break;
                    }
                }
            }
        }

        return $related;
    }

    private function getAvailableCategories(array $stories): array
    {
        return [
            'akademik' => 'Akademik',
            'karir' => 'Karir',
            'kehidupan' => 'Kehidupan',
            'teknologi' => 'Teknologi'
        ];
    }

    private function calculateReadTime(string $content): string
    {
        $wordCount = str_word_count(strip_tags($content));
        $readingSpeed = 200; // words per minute
        $minutes = ceil($wordCount / $readingSpeed);
        
        return $minutes . ' menit baca';
    }

    private function formatDate(string $dateString): string
    {
        if (empty($dateString)) return 'Baru saja';
        
        $date = new \DateTime($dateString);
        return $date->format('d F Y');
    }

    private function getCategoryName(string $category): string
    {
        $categories = [
            'akademik' => 'Akademik',
            'karir' => 'Karir',
            'kehidupan' => 'Kehidupan',
            'teknologi' => 'Teknologi'
        ];
        
        return $categories[strtolower($category)] ?? 'Akademik';
    }

    private function getExcerpt(string $content, int $maxLength = 120): string
    {
        if (empty($content)) return 'Tidak ada preview tersedia.';
        if (strlen($content) <= $maxLength) return $content;
        
        $truncated = substr($content, 0, $maxLength);
        $lastSpace = strrpos($truncated, ' ');
        
        return ($lastSpace > 0 ? substr($truncated, 0, $lastSpace) : $truncated) . '...';
    }
}
