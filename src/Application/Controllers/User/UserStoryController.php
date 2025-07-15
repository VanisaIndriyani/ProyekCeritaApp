<?php

declare(strict_types=1);

namespace App\Application\Controllers\User;

use App\Application\Helpers\AuthHelper;
use App\Domain\Story\Story;
use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as SlimResponse;

class UserStoryController
{
    protected LoggerInterface $logger;
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        $this->logger = $logger;
        $this->storyRepository = $storyRepository;
    }

    /**
     * Get user's stories
     */
    public function getUserStories(Request $request, Response $response): Response
    {
        try {
            // Get current user
            $user = AuthHelper::getCurrentUser();
            if (!$user) {
                return $this->jsonErrorResponse($response, 'User not authenticated', 401);
            }

            $userId = $user['id'];
            $queryParams = $request->getQueryParams();
            
            // Get filter parameters
            $status = $queryParams['status'] ?? null;
            $category = $queryParams['category'] ?? null;
            $search = $queryParams['search'] ?? null;
            $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
            $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;

            // Get all user stories
            $allStories = $this->storyRepository->findByUserId($userId);

            // Apply filters
            $filteredStories = $this->applyFilters($allStories, $status, $category, $search);

            // Apply pagination
            $offset = ($page - 1) * $limit;
            $paginatedStories = array_slice($filteredStories, $offset, $limit);

            // Format stories for response
            $formattedStories = array_map(function($story) {
                return [
                    'id' => $story->getId(),
                    'title' => $story->getTitle(),
                    'content' => $story->getContent(),
                    'category' => $story->getCategory(),
                    'status' => $story->getStatus(),
                    'cover_image' => $story->getCoverImage(),
                    'created_at' => $this->formatDate($story->getCreatedAt()),
                    'updated_at' => $this->formatDate($story->getUpdatedAt())
                ];
            }, $paginatedStories);

            return $this->jsonResponse($response, [
                'stories' => $formattedStories,
                'total' => count($filteredStories),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil(count($filteredStories) / $limit)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error getting user stories', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->jsonErrorResponse($response, 'Failed to get stories', 500);
        }
    }

    /**
     * Get single story by ID (only if owned by user)
     */
    public function getStory(Request $request, Response $response, array $args): Response
    {
        try {
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $this->jsonErrorResponse($response, 'User not authenticated', 401);
            }

            $story = $this->storyRepository->findById($storyId);
            
            if (!$story) {
                return $this->jsonErrorResponse($response, 'Story not found', 404);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                return $this->jsonErrorResponse($response, 'Access denied', 403);
            }

            $formattedStory = [
                'id' => $story->getId(),
                'title' => $story->getTitle(),
                'content' => $story->getContent(),
                'category' => $story->getCategory(),
                'status' => $story->getStatus(),
                'cover_image' => $story->getCoverImage(),
                'created_at' => $this->formatDate($story->getCreatedAt()),
                'updated_at' => $this->formatDate($story->getUpdatedAt())
            ];

            return $this->jsonResponse($response, ['story' => $formattedStory]);

        } catch (\Exception $e) {
            $this->logger->error('Error getting story', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            return $this->jsonErrorResponse($response, 'Failed to get story', 500);
        }
    }

    /**
     * Create new story
     */
    public function createStory(Request $request, Response $response): Response
    {
        try {
            $user = AuthHelper::getCurrentUser();
            if (!$user) {
                return $this->jsonErrorResponse($response, 'User not authenticated', 401);
            }

            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            // Validate required fields
            if (empty($data['title']) || empty($data['content']) || empty($data['category'])) {
                return $this->jsonErrorResponse($response, 'Title, content, and category are required', 400);
            }

            // Handle image upload
            $coverImage = null;
            if (isset($uploadedFiles['coverImage']) && $uploadedFiles['coverImage']->getError() === UPLOAD_ERR_OK) {
                $coverImage = $this->handleImageUpload($uploadedFiles['coverImage']);
            }

            // Set status to published directly (no draft)
            $status = 'published';

            // Create story
            $story = new Story(
                null,                           // id
                $user['id'],                   // userId
                $data['title'],                // title
                $data['content'],              // content
                $data['category'],             // category
                $coverImage,                   // coverImage
                date('Y-m-d H:i:s'),          // createdAt
                null,                          // updatedAt
                $status                        // status
            );

            $storyId = $this->storyRepository->create($story);

            $this->logger->info('Story created', [
                'story_id' => $storyId,
                'user_id' => $user['id'],
                'title' => $data['title']
            ]);

            return $this->jsonResponse($response, [
                'message' => 'Story created successfully',
                'story' => ['id' => $storyId]
            ], 201);

        } catch (\Exception $e) {
            $this->logger->error('Error creating story', [
                'error' => $e->getMessage(),
                'user_id' => $user['id'] ?? 'unknown'
            ]);

            return $this->jsonErrorResponse($response, 'Failed to create story', 500);
        }
    }

    /**
     * Update story
     */
    public function updateStory(Request $request, Response $response, array $args): Response
    {
        try {
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $this->jsonErrorResponse($response, 'User not authenticated', 401);
            }

            $story = $this->storyRepository->findById($storyId);
            if (!$story) {
                return $this->jsonErrorResponse($response, 'Story not found', 404);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                return $this->jsonErrorResponse($response, 'Access denied', 403);
            }

            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            // Handle image upload
            $coverImage = $story->getCoverImage(); // Keep existing image
            if (isset($uploadedFiles['coverImage']) && $uploadedFiles['coverImage']->getError() === UPLOAD_ERR_OK) {
                $coverImage = $this->handleImageUpload($uploadedFiles['coverImage']);
            }

            // Set status to published directly (no draft)
            $status = 'published';

            // Update story properties
            $updatedStory = new Story(
                $storyId,                                    // id
                $user['id'],                                // userId
                $data['title'] ?? $story->getTitle(),      // title
                $data['content'] ?? $story->getContent(),  // content
                $data['category'] ?? $story->getCategory(), // category
                $coverImage,                                // coverImage
                $story->getCreatedAt(),                     // createdAt
                date('Y-m-d H:i:s'),                       // updatedAt
                $status                                     // status
            );

            $this->storyRepository->update($updatedStory);

            $this->logger->info('Story updated', [
                'story_id' => $storyId,
                'user_id' => $user['id']
            ]);

            return $this->jsonResponse($response, ['message' => 'Story updated successfully']);

        } catch (\Exception $e) {
            $this->logger->error('Error updating story', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            return $this->jsonErrorResponse($response, 'Failed to update story', 500);
        }
    }

    /**
     * Delete story
     */
    public function deleteStory(Request $request, Response $response, array $args): Response
    {
        try {
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $this->jsonErrorResponse($response, 'User not authenticated', 401);
            }

            $story = $this->storyRepository->findById($storyId);
            if (!$story) {
                return $this->jsonErrorResponse($response, 'Story not found', 404);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                return $this->jsonErrorResponse($response, 'Access denied', 403);
            }

            $this->storyRepository->delete($storyId);

            $this->logger->info('Story deleted', [
                'story_id' => $storyId,
                'user_id' => $user['id']
            ]);

            return $this->jsonResponse($response, ['message' => 'Story deleted successfully']);

        } catch (\Exception $e) {
            $this->logger->error('Error deleting story', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            return $this->jsonErrorResponse($response, 'Failed to delete story', 500);
        }
    }

    /**
     * Apply filters to stories
     */
    private function applyFilters(array $stories, ?string $status, ?string $category, ?string $search): array
    {
        return array_filter($stories, function($story) use ($status, $category, $search) {
            // Status filter
            if ($status && $story->getStatus() !== $status) {
                return false;
            }

            // Category filter
            if ($category && $story->getCategory() !== $category) {
                return false;
            }

            // Search filter
            if ($search) {
                $searchLower = strtolower($search);
                if (strpos(strtolower($story->getTitle()), $searchLower) === false &&
                    strpos(strtolower($story->getContent()), $searchLower) === false) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($uploadedFile): ?string
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadsDir = __DIR__ . '/../../../../public/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $filename = uniqid() . '.' . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filepath = $uploadsDir . '/' . $filename;

        $uploadedFile->moveTo($filepath);

        return '/uploads/' . $filename;
    }

    /**
     * Helper method to return JSON response
     */
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    /**
     * Helper method to return JSON error response
     */
    private function jsonErrorResponse(Response $response, string $message, int $status = 400): Response
    {
        $response->getBody()->write(json_encode(['error' => $message]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    /**
     * Helper method to format date safely
     */
    private function formatDate($date): string
    {
        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d H:i:s');
        }
        
        if (is_string($date)) {
            try {
                $dateTime = new \DateTime($date);
                return $dateTime->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return $date; // Return as is if can't parse
            }
        }
        
        return ''; // Return empty string if null or invalid
    }
}
