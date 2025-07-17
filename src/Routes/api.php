<?php

declare(strict_types=1);

use App\Application\Controllers\Auth\AuthController;
use App\Application\Controllers\UserController;
use App\Application\Controllers\StoryController;
use App\Application\Controllers\User\UserStoryController;
use App\Application\Middleware\AuthMiddleware;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (\Slim\App $app) {
    
    // API Auth Routes (No authentication required)
    $app->group('/api/auth', function (Group $group) {
        $group->post('/register', [AuthController::class, 'register']);
        $group->post('/login', [AuthController::class, 'login']);
        $group->post('/forgot-password', [AuthController::class, 'forgotPassword']);
        $group->post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public API Routes (No authentication required for reading)
    $app->group('/api', function (Group $group) {
        // Public story routes (reading)
        $group->get('/stories', [StoryController::class, 'index']);
        $group->get('/stories/{id}', [StoryController::class, 'show']);
    });

    // Protected API Routes (Require authentication for writing)
    $app->group('/api', function (Group $group) {
        // User API Routes
        $group->group('/users', function (Group $userGroup) {
            $userGroup->get('', [UserController::class, 'index']);
            $userGroup->get('/{id}', [UserController::class, 'show']);
        });

        // User-specific routes
        $group->group('/user', function (Group $userGroup) {
            $userGroup->get('/stats', [UserController::class, 'getUserStats']);
            $userGroup->get('/stories', [UserStoryController::class, 'getUserStories']);
            $userGroup->get('/stories/{id}', [UserStoryController::class, 'getStory']);
            $userGroup->post('/stories', [UserStoryController::class, 'createStory']);
            $userGroup->map(['PUT', 'POST'], '/stories/{id}', [UserStoryController::class, 'updateStory']);
            $userGroup->delete('/stories/{id}', [UserStoryController::class, 'deleteStory']);
        });

        // Story write operations (require auth)
        $group->group('/stories', function (Group $storyGroup) {
            $storyGroup->post('', [StoryController::class, 'create']);
            $storyGroup->map(['PUT', 'POST'], '/{id}', [StoryController::class, 'update']);
            $storyGroup->delete('/{id}', [StoryController::class, 'delete']);
        });
    })->add(AuthMiddleware::class);
};
