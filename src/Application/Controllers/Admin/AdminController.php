<?php

declare(strict_types=1);

namespace App\Application\Controllers\Admin;

use App\Application\Controllers\BaseController;
use App\Domain\Story\StoryRepository;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class AdminController extends BaseController
{
    private StoryRepository $storyRepository;
    private UserRepository $userRepository;

    public function __construct(
        LoggerInterface $logger, 
        StoryRepository $storyRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($logger);
        $this->storyRepository = $storyRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * List all stories for admin
     */
    public function listStories(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        // Handle approve (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
            $approveId = (int)$_POST['approve_id'];
            $story = $this->storyRepository->findStoryOfId($approveId);
            if ($story) {
                $story->setStatus('published');
                $this->storyRepository->update($story);
                $_SESSION['success'] = 'Cerita berhasil dipublish!';
            } else {
                $_SESSION['error'] = 'Cerita tidak ditemukan.';
            }
            header('Location: /admin/stories');
            exit;
        }

        $stories = $this->storyRepository->findAllAdmin();
        extract(['stories' => $stories]);
        ob_start();
        include __DIR__ . '/../../../../resources/views/admin/stories.php';
        $html = ob_get_clean();
        $this->response->getBody()->write($html);
        return $this->response->withHeader('Content-Type', 'text/html');
    }

    /**
     * Publish story
     */
    public function publishStory(): Response
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
            $story->setStatus('published');
            $this->storyRepository->update($story);
            
            $this->logInfo("Story published", ['story_id' => $storyId]);
            
            return $this->respondWithData([
                'message' => 'Story berhasil dipublish'
            ]);
            
        } catch (\Exception $e) {
            $this->logError("Failed to publish story", ['story_id' => $storyId, 'error' => $e->getMessage()]);
            return $this->respondWithError('Gagal mempublish story', 500);
        }
    }

    /**
     * Delete story (admin)
     */
    public function deleteStory(): Response
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
            
            $this->logInfo("Story deleted by admin", ['story_id' => $storyId]);
            
            return $this->respondWithData([
                'message' => 'Story berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            $this->logError("Failed to delete story", ['story_id' => $storyId, 'error' => $e->getMessage()]);
            return $this->respondWithError('Gagal menghapus story', 500);
        }
    }

    /**
     * List all users for admin
     */
    public function listUsers(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $users = $this->userRepository->findAll();
        
        return $this->respondWithData([
            'users' => $users
        ]);
    }

    /**
     * Delete user (admin)
     */
    public function deleteUser(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $userId = (int) $this->getArg('id');
        
        $user = $this->userRepository->findUserOfId($userId);
        if (!$user) {
            return $this->respondWithError('User not found', 404);
        }

        try {
            $this->userRepository->delete($userId);
            
            $this->logInfo("User deleted by admin", ['user_id' => $userId]);
            
            return $this->respondWithData([
                'message' => 'User berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            $this->logError("Failed to delete user", ['user_id' => $userId, 'error' => $e->getMessage()]);
            return $this->respondWithError('Gagal menghapus user', 500);
        }
    }

    /**
     * Get about content
     */
    public function getAbout(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $file = __DIR__ . '/../../../../var/about.txt';
        $content = is_file($file) ? file_get_contents($file) : '';
        
        return $this->respondWithData([
            'content' => $content
        ]);
    }

    /**
     * Update about content
     */
    public function updateAbout(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $data = $this->getFormData();
        $content = $data['content'] ?? '';
        
        $file = __DIR__ . '/../../../../var/about.txt';
        file_put_contents($file, $content);
        
        $this->logInfo("About content updated");
        
        return $this->respondWithData([
            'success' => true,
            'message' => 'About content berhasil diupdate'
        ]);
    }

    public function showStory(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $storyId = $this->args['id'] ?? null;
        if (!$storyId) {
            $this->response->getBody()->write('ID cerita tidak ditemukan');
            return $this->response->withStatus(404)->withHeader('Content-Type', 'text/html');
        }
        $story = $this->storyRepository->findStoryOfId((int)$storyId);
        if (!$story) {
            $this->response->getBody()->write('Cerita tidak ditemukan');
            return $this->response->withStatus(404)->withHeader('Content-Type', 'text/html');
        }
        extract(['story' => $story]);
        ob_start();
        include __DIR__ . '/../../../../resources/views/admin/story-detail.php';
        $html = ob_get_clean();
        $this->response->getBody()->write($html);
        return $this->response->withHeader('Content-Type', 'text/html');
    }

    protected function action(): Response
    {
        // This method won't be called directly
        return $this->response;
    }
}
