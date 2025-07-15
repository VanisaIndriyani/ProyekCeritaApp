<?php

declare(strict_types=1);

namespace App\Application\Controllers\User;

use App\Application\Controllers\BaseController;
use App\Application\Helpers\AuthHelper;
use App\Domain\Story\Story;
use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class UserStoryController extends BaseController
{
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }

    /**
     * Get user's stories
     */
    public function getUserStories(Request $request, Response $response): Response
    {
        try {
            $this->request = $request;
            $this->response = $response;
            
            // Get current user
            $user = AuthHelper::getCurrentUser();
            if (!$user) {
                return $this->respondWithError('User not authenticated', 401);
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
                    'created_at' => $story->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $story->getUpdatedAt()->format('Y-m-d H:i:s')
                ];
            }, $paginatedStories);

            return $this->respondWithData([
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

            return $this->respondWithError('Failed to get stories', 500);
        }
    }

    /**
     * Get single story by ID (only if owned by user)
     */
    public function getStory(Request $request, Response $response, array $args): Response
    {
        try {
            $this->request = $request;
            $this->response = $response;
            $this->args = $args;
            
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $this->respondWithError('User not authenticated', 401);
            }

            $story = $this->storyRepository->findById($storyId);
            
            if (!$story) {
                return $this->respondWithError('Story not found', 404);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                return $this->respondWithError('Access denied', 403);
            }

            $formattedStory = [
                'id' => $story->getId(),
                'title' => $story->getTitle(),
                'content' => $story->getContent(),
                'category' => $story->getCategory(),
                'status' => $story->getStatus(),
                'cover_image' => $story->getCoverImage(),
                'created_at' => $story->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $story->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            return $this->respondWithData(['story' => $formattedStory]);

        } catch (\Exception $e) {
            $this->logger->error('Error getting story', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            return $this->respondWithError('Failed to get story', 500);
        }
    }

    /**
     * Create new story
     */
    public function createStory(Request $request, Response $response): Response
    {
        try {
            $this->request = $request;
            $this->response = $response;
            
            $user = AuthHelper::getCurrentUser();
            if (!$user) {
                return $this->respondWithError('User not authenticated', 401);
            }

            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            // Validate required fields
            if (empty($data['title']) || empty($data['content']) || empty($data['category'])) {
                return $this->respondWithError('Title, content, and category are required', 400);
            }

            // Handle image upload
            $coverImage = null;
            if (isset($uploadedFiles['coverImage']) && $uploadedFiles['coverImage']->getError() === UPLOAD_ERR_OK) {
                $coverImage = $this->handleImageUpload($uploadedFiles['coverImage']);
            }

            // Determine status
            $status = ($data['action'] ?? 'draft') === 'publish' ? 'pending' : 'draft';

            // Create story
            $story = new Story(
                null,
                $user['id'],
                $data['title'],
                $data['content'],
                $data['category'],
                $status,
                $coverImage
            );

            $storyId = $this->storyRepository->create($story);

            $this->logger->info('Story created', [
                'story_id' => $storyId,
                'user_id' => $user['id'],
                'title' => $data['title']
            ]);

            return $this->respondWithData([
                'message' => 'Story created successfully',
                'story' => ['id' => $storyId]
            ], 201);

        } catch (\Exception $e) {
            $this->logger->error('Error creating story', [
                'error' => $e->getMessage(),
                'user_id' => $user['id'] ?? 'unknown'
            ]);

            return $this->respondWithError('Failed to create story', 500);
        }
    }

    /**
     * Update story
     */
    public function updateStory(Request $request, Response $response, array $args): Response
    {
        try {
            $this->request = $request;
            $this->response = $response;
            $this->args = $args;
            
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $this->respondWithError('User not authenticated', 401);
            }

            $story = $this->storyRepository->findById($storyId);
            if (!$story) {
                return $this->respondWithError('Story not found', 404);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                return $this->respondWithError('Access denied', 403);
            }

            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            // Handle image upload
            $coverImage = $story->getCoverImage(); // Keep existing image
            if (isset($uploadedFiles['coverImage']) && $uploadedFiles['coverImage']->getError() === UPLOAD_ERR_OK) {
                $coverImage = $this->handleImageUpload($uploadedFiles['coverImage']);
            }

            // Determine status
            $status = ($data['action'] ?? 'draft') === 'publish' ? 'pending' : 'draft';

            // Update story properties
            $updatedStory = new Story(
                $storyId,
                $user['id'],
                $data['title'] ?? $story->getTitle(),
                $data['content'] ?? $story->getContent(),
                $data['category'] ?? $story->getCategory(),
                $status,
                $coverImage,
                $story->getCreatedAt(),
                new \DateTime()
            );

            $this->storyRepository->update($updatedStory);

            $this->logger->info('Story updated', [
                'story_id' => $storyId,
                'user_id' => $user['id']
            ]);

            return $this->respondWithData(['message' => 'Story updated successfully']);

        } catch (\Exception $e) {
            $this->logger->error('Error updating story', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            return $this->respondWithError('Failed to update story', 500);
        }
    }

    /**
     * Delete story
     */
    public function deleteStory(Request $request, Response $response, array $args): Response
    {
        try {
            $this->request = $request;
            $this->response = $response;
            $this->args = $args;
            
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $this->respondWithError('User not authenticated', 401);
            }

            $story = $this->storyRepository->findById($storyId);
            if (!$story) {
                return $this->respondWithError('Story not found', 404);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                return $this->respondWithError('Access denied', 403);
            }

            $this->storyRepository->delete($storyId);

            $this->logger->info('Story deleted', [
                'story_id' => $storyId,
                'user_id' => $user['id']
            ]);

            return $this->respondWithData(['message' => 'Story deleted successfully']);

        } catch (\Exception $e) {
            $this->logger->error('Error deleting story', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            return $this->respondWithError('Failed to delete story', 500);
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
}
