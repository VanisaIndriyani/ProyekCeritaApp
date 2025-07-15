<?php

declare(strict_types=1);

namespace App\Application\Controllers\User;

use App\Domain\Story\Story;
use App\Domain\Story\StoryRepository;
use App\Application\Helpers\AuthHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as SlimResponse;

class UserStoryFormController
{
    protected LoggerInterface $logger;
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, StoryRepository $storyRepository)
    {
        $this->logger = $logger;
        $this->storyRepository = $storyRepository;
    }

    /**
     * Handle form submission for creating story (POST)
     */
    public function createStorySubmit(Request $request, Response $response): Response
    {
        try {
            $user = AuthHelper::getCurrentUser();
            if (!$user) {
                return $response->withHeader('Location', '/login')->withStatus(302);
            }

            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            // Validate required fields
            if (empty($data['title']) || empty($data['content'])) {
                $_SESSION['error'] = 'Title dan content wajib diisi';
                return $response->withHeader('Location', '/user/create')->withStatus(302);
            }

            // Handle image upload
            $coverImage = '';
            if (isset($uploadedFiles['coverImage']) && $uploadedFiles['coverImage']->getError() === UPLOAD_ERR_OK) {
                $coverImage = $this->handleImageUpload($uploadedFiles['coverImage']);
            }

            // Create story
            $story = new Story(
                null,                           // id
                $user['id'],                   // userId
                $data['title'],                // title
                $data['content'],              // content
                $data['category'] ?? '',       // category
                $coverImage,                   // coverImage
                date('Y-m-d H:i:s'),          // createdAt
                null,                          // updatedAt
                'pending'                    // status
            );

            $storyId = $this->storyRepository->create($story);

            $this->logger->info('Story created via form', [
                'story_id' => $storyId,
                'user_id' => $user['id'],
                'title' => $data['title']
            ]);

            $_SESSION['success'] = 'Story berhasil dibuat!';
            return $response->withHeader('Location', '/user')->withStatus(302);

        } catch (\Exception $e) {
            $this->logger->error('Error creating story via form', [
                'error' => $e->getMessage(),
                'user_id' => $user['id'] ?? 'unknown'
            ]);

            $_SESSION['error'] = 'Gagal membuat story: ' . $e->getMessage();
            return $response->withHeader('Location', '/user/create')->withStatus(302);
        }
    }

    /**
     * Handle form submission for updating story (POST)
     */
    public function updateStorySubmit(Request $request, Response $response, array $args): Response
    {
        try {
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $response->withHeader('Location', '/login')->withStatus(302);
            }

            $story = $this->storyRepository->findById($storyId);
            if (!$story) {
                $_SESSION['error'] = 'Story tidak ditemukan';
                return $response->withHeader('Location', '/user')->withStatus(302);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                $_SESSION['error'] = 'Anda tidak memiliki akses untuk edit story ini';
                return $response->withHeader('Location', '/user')->withStatus(302);
            }

            $data = $request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            // Validate required fields
            if (empty($data['title']) || empty($data['content'])) {
                $_SESSION['error'] = 'Title dan content wajib diisi';
                return $response->withHeader('Location', "/user/edit/$storyId")->withStatus(302);
            }

            // Handle image upload
            $coverImage = $story->getCoverImage(); // Keep existing image
            if (isset($uploadedFiles['coverImage']) && $uploadedFiles['coverImage']->getError() === UPLOAD_ERR_OK) {
                $coverImage = $this->handleImageUpload($uploadedFiles['coverImage']);
            }

            // Create updated story object
            $updatedStory = new Story(
                $storyId,                      // id
                $user['id'],                   // userId
                $data['title'],                // title
                $data['content'],              // content
                $data['category'] ?? '',       // category
                $coverImage,                   // coverImage
                $story->getCreatedAt(),        // createdAt (keep original)
                date('Y-m-d H:i:s'),          // updatedAt
                'pending'                    // status
            );

            // Update in database
            $this->storyRepository->update($updatedStory);

            $this->logger->info('Story updated via form', [
                'story_id' => $storyId,
                'user_id' => $user['id'],
                'title' => $data['title']
            ]);

            $_SESSION['success'] = 'Story berhasil diupdate!';
            return $response->withHeader('Location', '/user')->withStatus(302);

        } catch (\Exception $e) {
            $this->logger->error('Error updating story via form', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            $_SESSION['error'] = 'Gagal update story: ' . $e->getMessage();
            return $response->withHeader('Location', "/user/edit/{$args['id']}")->withStatus(302);
        }
    }

    /**
     * Handle delete story (POST)
     */
    public function deleteStorySubmit(Request $request, Response $response, array $args): Response
    {
        try {
            $storyId = (int)$args['id'];
            $user = AuthHelper::getCurrentUser();
            
            if (!$user) {
                return $response->withHeader('Location', '/login')->withStatus(302);
            }

            $story = $this->storyRepository->findById($storyId);
            if (!$story) {
                $_SESSION['error'] = 'Story tidak ditemukan';
                return $response->withHeader('Location', '/user')->withStatus(302);
            }

            // Check if story belongs to current user
            if ($story->getUserId() !== $user['id']) {
                $_SESSION['error'] = 'Anda tidak memiliki akses untuk hapus story ini';
                return $response->withHeader('Location', '/user')->withStatus(302);
            }

            $this->storyRepository->delete($storyId);

            $this->logger->info('Story deleted via form', [
                'story_id' => $storyId,
                'user_id' => $user['id']
            ]);

            $_SESSION['success'] = 'Story berhasil dihapus!';
            return $response->withHeader('Location', '/user')->withStatus(302);

        } catch (\Exception $e) {
            $this->logger->error('Error deleting story via form', [
                'error' => $e->getMessage(),
                'story_id' => $args['id'] ?? 'unknown'
            ]);

            $_SESSION['error'] = 'Gagal hapus story: ' . $e->getMessage();
            return $response->withHeader('Location', '/user')->withStatus(302);
        }
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($uploadedFile): string
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return '';
        }

        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
        $directory = __DIR__ . '/../../../../public/uploads';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }
}
