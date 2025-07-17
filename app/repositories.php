<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\User\MySQLUserRepository;
use App\Domain\Story\StoryRepository;
use App\Infrastructure\Persistence\Story\InMemoryStoryRepository;
use App\Infrastructure\Persistence\Story\MySQLStoryRepository;
use App\Infrastructure\Utils\FlashMessage;
// Controllers
use App\Application\Controllers\Web\HomeController;
use App\Application\Controllers\Auth\AuthController;
use App\Application\Controllers\Auth\AuthWebController;
use App\Application\Controllers\UserController;
use App\Application\Controllers\StoryController;
use App\Application\Controllers\Admin\AdminController;
use App\Application\Controllers\User\UserStoryController;
use App\Application\Controllers\User\UserStoryFormController;
use App\Application\Controllers\User\UserStoryViewController;
// Middleware
use App\Application\Middleware\AuthMiddleware;
use App\Application\Middleware\GuestMiddleware;
use App\Application\Middleware\AdminMiddleware;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(MySQLUserRepository::class),
        StoryRepository::class => \DI\autowire(MySQLStoryRepository::class),
        FlashMessage::class => \DI\autowire(FlashMessage::class),
        PDO::class => function () {
            return new PDO('mysql:host=localhost;dbname=cerita_app', 'root', '');
        },
        
        // Controllers
        HomeController::class => \DI\autowire(HomeController::class),
        AuthController::class => \DI\autowire(AuthController::class),
        AuthWebController::class => \DI\autowire(AuthWebController::class),
        UserController::class => \DI\autowire(UserController::class),
        StoryController::class => \DI\autowire(StoryController::class),
        AdminController::class => \DI\autowire(AdminController::class),
        UserStoryController::class => \DI\autowire(UserStoryController::class),
        UserStoryFormController::class => \DI\autowire(UserStoryFormController::class),
        UserStoryViewController::class => \DI\autowire(UserStoryViewController::class),
        
        // Middleware
        AuthMiddleware::class => \DI\autowire(AuthMiddleware::class),
        GuestMiddleware::class => \DI\autowire(GuestMiddleware::class),
        AdminMiddleware::class => \DI\autowire(AdminMiddleware::class),
    ]);
};
