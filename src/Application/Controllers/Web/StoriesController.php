<?php

declare(strict_types=1);

namespace App\Application\Controllers\Web;

use App\Application\Controllers\BaseController;
use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class StoriesController extends BaseController
{
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }

    protected function action(): Response
    {
        return $this->index($this->request, $this->response);
    }

    public function index(Request $request, Response $response): Response
    {
        try {
            // Get query parameters
            $queryParams = $request->getQueryParams();
            $search = $queryParams['search'] ?? '';
            $category = $queryParams['category'] ?? '';
            $sort = $queryParams['sort'] ?? 'newest';
            $page = (int)($queryParams['page'] ?? 1);
            $perPage = 12;

            // Get all published stories
            $allStories = $this->storyRepository->findAll();
            
            // Filter published stories only
            $stories = array_filter($allStories, function($story) {
                return $story->getStatus() === 'published';
            });

            // Apply search filter
            if (!empty($search)) {
                $searchLower = strtolower($search);
                $stories = array_filter($stories, function($story) use ($searchLower) {
                    return strpos(strtolower($story->getTitle()), $searchLower) !== false ||
                           strpos(strtolower($story->getContent()), $searchLower) !== false;
                });
            }

            // Apply category filter
            if (!empty($category)) {
                $stories = array_filter($stories, function($story) use ($category) {
                    return strtolower($story->getCategory()) === strtolower($category);
                });
            }

            // Sort stories
            $this->sortStories($stories, $sort);

            // Calculate pagination
            $totalStories = count($stories);
            $totalPages = ceil($totalStories / $perPage);
            $offset = ($page - 1) * $perPage;
            $paginatedStories = array_slice($stories, $offset, $perPage);

            // Convert Story objects to arrays for view
            $storiesArray = array_map([$this, 'convertStoryToArray'], $paginatedStories);

            // Prepare data for view
            $viewData = [
                'stories' => $storiesArray,
                'totalStories' => $totalStories,
                'currentPageNum' => $page,
                'totalPages' => $totalPages,
                'search' => $search,
                'category' => $category,
                'sort' => $sort,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1,
                'categories' => $this->getAvailableCategories($allStories)
            ];

            // Render view
            ob_start();
            $title = 'Semua Cerita - Cerita Mahasiswa';
            $additionalCSS = ['/stories-list.css'];
            $currentPage = 'stories';
            extract($viewData);
            include __DIR__ . '/../../../../resources/views/pages/stories.php';
            $html = ob_get_clean();

            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');

        } catch (\Exception $e) {
            $this->logError("Failed to load stories page", ['error' => $e->getMessage()]);
            
            // Return error page
            ob_start();
            $title = 'Error - Cerita Mahasiswa';
            $errorMessage = 'Terjadi kesalahan saat memuat halaman cerita.';
            include __DIR__ . '/../../../resources/views/errors/500.php';
            $html = ob_get_clean();

            $response->getBody()->write($html);
            return $response->withStatus(500)->withHeader('Content-Type', 'text/html');
        }
    }

    private function sortStories(array &$stories, string $sort): void
    {
        switch ($sort) {
            case 'newest':
                usort($stories, function($a, $b) {
                    return strtotime($b->getCreatedAt()) - strtotime($a->getCreatedAt());
                });
                break;
            case 'oldest':
                usort($stories, function($a, $b) {
                    return strtotime($a->getCreatedAt()) - strtotime($b->getCreatedAt());
                });
                break;
            case 'title':
                usort($stories, function($a, $b) {
                    return strcmp($a->getTitle(), $b->getTitle());
                });
                break;
        }
    }

    private function getAvailableCategories(array $stories): array
    {
        // Return predefined categories mapping
        return [
            'akademik' => 'Akademik',
            'karir' => 'Karir',
            'kehidupan' => 'Kehidupan',
            'teknologi' => 'Teknologi',
            'organisasi' => 'Organisasi',
            'magang' => 'Magang',
            'kompetisi' => 'Kompetisi',
            'wisuda' => 'Wisuda'
        ];
    }

    private function getCategoryName(string $category): string
    {
        $categoryNames = [
            'akademik' => 'Akademik',
            'karir' => 'Karir',
            'kehidupan' => 'Kehidupan',
            'teknologi' => 'Teknologi',
            'organisasi' => 'Organisasi',
            'magang' => 'Magang',
            'kompetisi' => 'Kompetisi',
            'wisuda' => 'Wisuda'
        ];
        
        return $categoryNames[strtolower($category)] ?? ucfirst($category);
    }

    private function formatDate(string $dateString): string
    {
        if (empty($dateString)) return 'Baru saja';
        
        $date = new \DateTime($dateString);
        $now = new \DateTime();
        $diff = $now->diff($date);
        
        if ($diff->days === 0) return 'Hari ini';
        if ($diff->days === 1) return 'Kemarin';
        if ($diff->days < 7) return $diff->days . ' hari lalu';
        if ($diff->days < 30) return floor($diff->days / 7) . ' minggu lalu';
        
        return $date->format('d F Y');
    }

    private function getExcerpt(string $content, int $maxLength = 120): string
    {
        if (empty($content)) return 'Tidak ada preview tersedia.';
        if (strlen($content) <= $maxLength) return $content;
        
        $truncated = substr($content, 0, $maxLength);
        $lastSpace = strrpos($truncated, ' ');
        
        return ($lastSpace > 0 ? substr($truncated, 0, $lastSpace) : $truncated) . '...';
    }

    private function convertStoryToArray($story): array
    {
        return [
            'id' => $story->getId(),
            'title' => $story->getTitle(),
            'content' => $story->getContent(),
            'category' => $story->getCategory(),
            'userName' => $story->getUserName() ?? 'Anonymous',
            'author' => $story->getUserName() ?? 'Anonymous',
            'createdAt' => $story->getCreatedAt(),
            'status' => $story->getStatus()
        ];
    }
}
