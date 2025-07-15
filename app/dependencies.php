<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Application\Controllers\User\UserStoryFormController;
use App\Application\Controllers\Web\HomeController;
use App\Domain\Story\StoryRepository;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'] ?? [
                'name' => 'slim-app',
                'path' => __DIR__ . '/../logs/app.log',
                'level' => \Monolog\Logger::DEBUG,
            ];
            $logger = new Logger($loggerSettings['name']);
            $processor = new UidProcessor();
            $logger->pushProcessor($processor);
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);
            return $logger;
        },
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $db = $settings['db'];
            $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
            return new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        },
        UserStoryFormController::class => function (ContainerInterface $c) {
            return new UserStoryFormController(
                $c->get(LoggerInterface::class),
                $c->get(StoryRepository::class)
            );
        },
        HomeController::class => function (ContainerInterface $c) {
            return new HomeController(
                $c->get(LoggerInterface::class),
                $c->get(StoryRepository::class)
            );
        },
    ]);
};
