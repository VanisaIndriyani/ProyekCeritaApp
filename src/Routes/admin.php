<?php

declare(strict_types=1);

use App\Application\Controllers\Admin\AdminController;
use App\Application\Middleware\AdminMiddleware;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (\Slim\App $app) {
    
    // Admin API Routes (Require admin authentication)
    $app->group('/admin', function (Group $group) {
        // Story Management
        // $group->get('/stories', [AdminController::class, 'listStories']);
        $group->post('/stories/{id}/publish', [AdminController::class, 'publishStory']);
        $group->delete('/stories/{id}', [AdminController::class, 'deleteStory']);
        
        // User Management
        // $group->get('/users', [AdminController::class, 'listUsers']);
        $group->delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // About Content Management
        // $group->get('/about', [AdminController::class, 'getAbout']);
        // $group->post('/about', [AdminController::class, 'updateAbout']);
    })->add(AdminMiddleware::class);
};
