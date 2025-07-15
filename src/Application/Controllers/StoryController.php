<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Controllers\BaseController;
use App\Domain\Story\Story;
use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class StoryController extends BaseController
{
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
    }

    /**
     * List all stories
     */
    public function index(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $queryParams = $this->request->getQueryParams();
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : null;
        $search = $queryParams['search'] ?? null;

        $stories = $this->storyRepository->findAll();
        
        // Filter by search if provided
        if ($search) {
            $stories = array_filter($stories, function($story) use ($search) {
                return stripos($story->getTitle(), $search) !== false || 
                       stripos($story->getContent(), $search) !== false;
            });
        }

        // Apply limit if provided
        if ($limit) {
            $stories = array_slice($stories, 0, $limit);
        }

        // Convert to proper format for frontend
        $storiesData = array_map(function($story) {
            return [
                'id' => $story->getId(),
                'judul' => $story->getTitle(),
                'konten' => $story->getContent(),
                'kategori' => $story->getCategory(),
                'gambar' => $story->getCoverImage(),
                'status' => $story->getStatus(),
                'created_at' => $story->getCreatedAt(),
                'updated_at' => $story->getUpdatedAt(),
                'author_name' => $story->getUserName(),
                'userId' => $story->getUserId(),
            ];
        }, $stories);
        
        return $this->respondWithData([
            'success' => true,
            'data' => $storiesData
        ]);
    }

    /**
     * Show single story
     */
    public function show(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $storyId = (int) $this->getArg('id');
        $story = $this->storyRepository->findStoryOfId($storyId);

        if (!$story) {
            return $this->respondWithError('Story not found', 404);
        }

        return $this->respondWithData([
            'story' => $story
        ]);
    }

    /**
     * Create new story
     */
    public function create(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        // Get current user from JWT token
        $userId = $this->request->getAttribute('userId');
        if (!$userId) {
            return $this->respondWithError('Unauthorized', 401);
        }

        $data = $this->getFormData();
        
        // Validate required fields
        if (empty($data['title']) || empty($data['content'])) {
            return $this->respondWithError('Judul dan konten wajib diisi', 400);
        }

        try {
            // Handle file upload if present
            $uploadedFiles = $this->request->getUploadedFiles();
            $coverImage = null;
            
            if (isset($uploadedFiles['coverImage']) && $uploadedFiles['coverImage']->getError() === UPLOAD_ERR_OK) {
                $uploadedFile = $uploadedFiles['coverImage'];
                $filename = $this->moveUploadedFile($uploadedFile);
                $coverImage = $filename;
            }

            $story = new Story(
                null,
                (int)$userId,
                $data['title'],
                $data['content'],
                $data['category'] ?? 'lainnya',
                $coverImage,
                date('Y-m-d H:i:s'),
                null,
                $data['status'] ?? 'published'
            );

            $savedStory = $this->storyRepository->save($story);
            
            $this->logInfo("Story created", ['story_id' => $savedStory->getId()]);
            
            return $this->respondWithData([
                'success' => true,
                'message' => 'Cerita berhasil dibuat',
                'data' => $savedStory
            ], 201);
            
        } catch (\Exception $e) {
            $this->logError("Failed to create story", ['error' => $e->getMessage()]);
            return $this->respondWithError('Gagal membuat cerita: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update story
     */
    public function update(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $storyId = (int) $this->getArg('id');
        $data = $this->getFormData();

        $story = $this->storyRepository->findStoryOfId($storyId);
        if (!$story) {
            return $this->respondWithError('Story not found', 404);
        }

        try {
            // Update story fields
            if (isset($data['title'])) {
                $story->setTitle($data['title']);
            }
            if (isset($data['content'])) {
                $story->setContent($data['content']);
            }
            if (isset($data['category'])) {
                $story->setCategory($data['category']);
            }

            $updatedStory = $this->storyRepository->save($story);
            
            $this->logInfo("Story updated", ['story_id' => $storyId]);
            
            return $this->respondWithData([
                'message' => 'Story berhasil diupdate',
                'story' => $updatedStory
            ]);
            
        } catch (\Exception $e) {
            $this->logError("Failed to update story", ['story_id' => $storyId, 'error' => $e->getMessage()]);
            return $this->respondWithError('Gagal mengupdate story', 500);
        }
    }

    /**
     * Delete story
     */
    public function delete(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $storyId = (int) $this->getArg('id');
        
        $story = $this->storyRepository->findStoryOfId($storyId);
        if (!$story) {
            return $this->respondWithError('Story not found', 404);
        }

        try {
            $this->storyRepository->delete($storyId);
            
            $this->logInfo("Story deleted", ['story_id' => $storyId]);
            
            return $this->respondWithData([
                'message' => 'Story berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            $this->logError("Failed to delete story", ['story_id' => $storyId, 'error' => $e->getMessage()]);
            return $this->respondWithError('Gagal menghapus story', 500);
        }
    }

    /**
     * Move uploaded file to uploads directory
     */
    private function moveUploadedFile($uploadedFile): string
    {
        $uploadDir = __DIR__ . '/../../../../public/uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%s', $basename, $extension);

        $uploadedFile->moveTo($uploadDir . $filename);

        return $filename;
    }

    protected function action(): Response
    {
        // This method won't be called directly
        return $this->response;
    }
}
