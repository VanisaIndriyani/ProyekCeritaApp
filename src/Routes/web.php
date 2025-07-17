<?php

declare(strict_types=1);

use App\Application\Controllers\Web\HomeController;
use App\Application\Controllers\Web\StoriesController;
use App\Application\Controllers\Web\StoryDetailController;
use App\Application\Controllers\Web\LogoutController;
use App\Application\Controllers\Auth\AuthWebController;
use App\Application\Controllers\StoryController;
use App\Application\Controllers\User\UserStoryFormController;
use App\Application\Controllers\User\UserStoryViewController;
use App\Application\Middleware\GuestMiddleware;
use App\Application\Middleware\AuthMiddleware;
use App\Application\Middleware\AdminMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (\Slim\App $app) {
    // CORS Options
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    // Public Web Routes (No authentication required)
    $app->get('/', HomeController::class);
    
    // Stories List Page (Public access)
    $app->get('/stories', [StoriesController::class, 'index']);
    
    // Auth Pages (Guest only - redirect if already logged in)
    $app->group('', function (Group $group) {
        // Show login/register forms
        $group->get('/login', [AuthWebController::class, 'showLogin']);
        $group->get('/register', [AuthWebController::class, 'showRegister']);
        
        // Process login/register forms
        $group->post('/login', [AuthWebController::class, 'processLogin']);
        $group->post('/register', [AuthWebController::class, 'processRegister']);

        $group->get('/forgot-password', function (Request $request, Response $response) {
        $viewFile = __DIR__ . '/../../resources/views/auth/forgot-password.php';
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        }
        $response->getBody()->write('<h2>Halaman lupa password tidak ditemukan.</h2>');
        return $response->withHeader('Content-Type', 'text/html');
        });
    })->add(GuestMiddleware::class);

    $app->get('/reset-password', function (Request $request, Response $response) {
    $token = $request->getQueryParams()['token'] ?? null;

    if (!$token) {
        $response->getBody()->write('<h2>Token tidak valid.</h2>');
        return $response->withHeader('Content-Type', 'text/html')->withStatus(400);
    }

    $viewFile = __DIR__ . '/../../resources/views/auth/reset-password.php';
    if (file_exists($viewFile)) {
        ob_start();
        $resetToken = $token;
        include $viewFile;
        $html = ob_get_clean();
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
    }

    $response->getBody()->write('<h2>Halaman reset password tidak ditemukan.</h2>');
    return $response->withHeader('Content-Type', 'text/html')->withStatus(404);
    });
    
    // Logout handling
    // Note: Using direct logout.php for full PHP session handling instead of Slim routes
    // $app->get('/logout', LogoutController::class); // Disabled: using logout.php
    // $app->post('/logout', [AuthWebController::class, 'logout'])->add(AuthMiddleware::class); // Disabled: using logout.php
    
    // Static Pages (Public access)
    $app->get('/about', function (Request $request, Response $response) {
        $viewFile = __DIR__ . '/../../resources/views/about.php';
        if (file_exists($viewFile)) {
            ob_start();
            $currentPage = 'about'; // Set current page for navigation highlighting
            include $viewFile;
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        }
        
        // Fallback ke file lama jika view baru belum ada
        $html = file_get_contents(__DIR__ . '/../../public/tentang.html');
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    });
    
    $app->get('/categories', function (Request $request, Response $response) {
        $viewFile = __DIR__ . '/../../resources/views/categories.php';
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $html = ob_get_clean();
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        }
        
        // Fallback ke file lama jika view baru belum ada
        $html = file_get_contents(__DIR__ . '/../../public/kategori.html');
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    });
    
    // Story Detail Page (Public access)
    $app->get('/story-detail', [StoryDetailController::class, 'show']);
    
    // Alternative story detail route - redirect to main route
    $app->get('/story/{id}', function (Request $request, Response $response, array $args) {
        $storyId = $args['id'] ?? '';
        return $response->withHeader('Location', "/story-detail?id={$storyId}")->withStatus(302);
    });
    
    
    // Protected Pages (Require authentication)
    $app->group('', function (Group $group) {
        // User dashboard
        $group->get('/user', function (Request $request, Response $response) {
            $viewFile = __DIR__ . '/../../resources/views/user/profile-php.php';
            if (file_exists($viewFile)) {
                ob_start();
                $currentPage = 'user'; // Set current page for navigation highlighting
                include $viewFile;
                $html = ob_get_clean();
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html');
            }
            
            // Fallback ke file lama jika view baru belum ada
            $html = file_get_contents(__DIR__ . '/../../public/user.html');
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        });
        
        // Create story page
        $group->get('/user/create', [UserStoryViewController::class, 'showCreateForm']);
        
        // Edit story page
        $group->get('/user/edit/{id}', [UserStoryViewController::class, 'showEditForm']);
        
        // Form submission routes
        $group->post('/user/create', [UserStoryFormController::class, 'createStorySubmit']);
        $group->post('/user/edit/{id}', [UserStoryFormController::class, 'updateStorySubmit']);
        $group->post('/user/delete/{id}', [UserStoryFormController::class, 'deleteStorySubmit']);
    })->add(AuthMiddleware::class);
    
    $app->get('/user/profile', [\App\Application\Controllers\UserController::class, 'profile']);
    $app->post('/user/profile', [\App\Application\Controllers\UserController::class, 'profile']);
    
    // Admin Pages (Require admin authentication)
    $app->group('', function (Group $group) {
        $group->get('/admin', function (Request $request, Response $response) {
            $viewFile = __DIR__ . '/../../resources/views/admin/dashboard.php';
            if (file_exists($viewFile)) {
                ob_start();
                include $viewFile;
                $html = ob_get_clean();
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html');
            }
            $html = file_get_contents(__DIR__ . '/../../public/admin.html');
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        });
        $group->get('/admin/users', function (Request $request, Response $response) {
            $viewFile = __DIR__ . '/../../resources/views/admin/users.php';
            if (file_exists($viewFile)) {
                ob_start();
                include $viewFile;
                $html = ob_get_clean();
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html');
            }
            $response->getBody()->write('<h2>Halaman Kelola User tidak ditemukan.</h2>');
            return $response->withHeader('Content-Type', 'text/html');
        });
        $group->get('/admin/stories', function (Request $request, Response $response) {
            $viewFile = __DIR__ . '/../../resources/views/admin/stories.php';
            if (file_exists($viewFile)) {
                ob_start();
                include $viewFile;
                $html = ob_get_clean();
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html');
            }
            $response->getBody()->write('<h2>Halaman Kelola Cerita tidak ditemukan.</h2>');
            return $response->withHeader('Content-Type', 'text/html');
        });
        $group->map(['GET', 'POST'], '/admin/about', function (Request $request, Response $response) {
            $viewFile = __DIR__ . '/../../resources/views/admin/about.php';
            if (file_exists($viewFile)) {
                ob_start();
                include $viewFile;
                $html = ob_get_clean();
                $response->getBody()->write($html);
                return $response->withHeader('Content-Type', 'text/html');
            }
            $response->getBody()->write('<h2>Halaman Kelola Tim tidak ditemukan.</h2>');
            return $response->withHeader('Content-Type', 'text/html');
        });
        // Route detail cerita admin
        $group->get('/admin/stories/show/{id}', [\App\Application\Controllers\Admin\AdminController::class, 'showStory']);
        $group->post('/admin/stories', [\App\Application\Controllers\Admin\AdminController::class, 'listStories']);
    })->add(AdminMiddleware::class);
};
