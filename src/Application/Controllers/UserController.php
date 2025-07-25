<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Controllers\BaseController;
use App\Domain\User\UserRepository;
use App\Domain\Story\StoryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class UserController extends BaseController
{
    private UserRepository $userRepository;
    private StoryRepository $storyRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository, StoryRepository $storyRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
        $this->storyRepository = $storyRepository;
    }

    /**
     * List all users
     */
    public function index(): Response
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
     * Show single user
     */
    public function show(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $userId = (int) $this->getArg('id');
        $user = $this->userRepository->findUserOfId($userId);

        if (!$user) {
            return $this->respondWithError('User not found', 404);
        }

        return $this->respondWithData([
            'user' => $user
        ]);
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $userId = $this->request->getAttribute('userId');
        if (!$userId) {
            return $this->respondWithError('Unauthorized', 401);
        }

        try {
            // Get user's stories
            $userStories = $this->storyRepository->findByUserId($userId);
            
            // Calculate stats
            $totalStories = count($userStories);
            $totalViews = 0; // We don't have views tracking yet
            $totalLikes = 0; // We don't have likes tracking yet  
            $totalComments = 0; // We don't have comments tracking yet
            
            $stats = [
                'totalStories' => $totalStories,
                'totalViews' => $totalViews,
                'totalLikes' => $totalLikes,
                'totalComments' => $totalComments
            ];

            return $this->respondWithData([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->logError("Failed to get user stats", ['error' => $e->getMessage()]);
            return $this->respondWithError('Gagal mengambil statistik user', 500);
        }
    }

    /**
     * Get user stories
     */
    public function getUserStories(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $userId = $this->request->getAttribute('userId');
        if (!$userId) {
            return $this->respondWithError('Unauthorized', 401);
        }

        try {
            // Get user's stories
            $userStories = $this->storyRepository->findByUserId($userId);
            
            // Convert to array format for JSON response
            $storiesData = [];
            foreach ($userStories as $story) {
                $storiesData[] = [
                    'id' => $story->getId(),
                    'title' => $story->getTitle(),
                    'content' => $story->getContent(),
                    'category' => $story->getCategory(),
                    'status' => $story->getStatus(),
                    'image' => $story->getImage(),
                    'created_at' => $story->getCreatedAt(),
                    'updated_at' => $story->getUpdatedAt()
                ];
            }

            return $this->respondWithData([
                'success' => true,
                'data' => $storiesData
            ]);
        } catch (\Exception $e) {
            $this->logError("Failed to get user stories", ['error' => $e->getMessage()]);
            return $this->respondWithError('Gagal mengambil cerita user', 500);
        }
    }

    public function profile(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

        $user = \App\Application\Helpers\AuthHelper::getCurrentUser();
        if (!$user) {
            return $this->response->withHeader('Location', '/login')->withStatus(302);
        }

        // Proses update profil jika POST
        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->getParsedBody();
            $nama = $data['nama'] ?? $user['nama'];
            $email = $data['email'] ?? $user['email'];
            $password = $data['password'] ?? null;

            // Update user di database
            $userId = $user['id'];
            $updateData = [
                'nama' => $nama,
                'email' => $email
            ];
            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->userRepository->update($userId, $updateData);

            // Set notifikasi sukses ke session
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['profile_success'] = 'Profil berhasil diperbarui!';

            // Redirect agar tidak resubmit form
            return $this->response->withHeader('Location', '/user/profile')->withStatus(302);
        }

        // Ambil notifikasi jika ada
        if (session_status() === PHP_SESSION_NONE) session_start();
        $notif = $_SESSION['profile_success'] ?? null;
        unset($_SESSION['profile_success']);

        extract(['user' => $user, 'notif' => $notif]);
        ob_start();
        $viewPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'profile.php';
        if (!file_exists($viewPath)) {
            die('❌ File view tidak ditemukan: ' . $viewPath);
        }
        include $viewPath;
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
