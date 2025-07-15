<?php

declare(strict_types=1);

namespace App\Application\Controllers\User;

use App\Domain\Story\StoryRepository;
use App\Application\Helpers\AuthHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserStoryViewController
{
    private StoryRepository $storyRepository;

    public function __construct(StoryRepository $storyRepository)
    {
        $this->storyRepository = $storyRepository;
    }

    /**
     * Show edit story form
     */
    public function showEditForm(Request $request, Response $response, array $args): Response
    {
        $user = AuthHelper::getCurrentUser();
        if (!$user) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $storyId = (int)$args['id'];
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

        // Render view
        $viewFile = __DIR__ . '/../../../../resources/views/user/edit.php';
        if (file_exists($viewFile)) {
            ob_start();
            $currentPage = 'user';
            include $viewFile;
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        }

        $response->getBody()->write('Edit form not found');
        return $response->withStatus(404);
    }

    /**
     * Show create story form
     */
    public function showCreateForm(Request $request, Response $response): Response
    {
        $user = AuthHelper::getCurrentUser();
        if (!$user) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        // Render view
        $viewFile = __DIR__ . '/../../../../resources/views/user/create.php';
        if (file_exists($viewFile)) {
            ob_start();
            $currentPage = 'user';
            include $viewFile;
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        }

        $response->getBody()->write('Create form not found');
        return $response->withStatus(404);
    }
}
