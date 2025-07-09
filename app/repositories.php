<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\User\MySQLUserRepository;
use App\Domain\Story\StoryRepository;
use App\Infrastructure\Persistence\Story\InMemoryStoryRepository;
use App\Infrastructure\Persistence\Story\MySQLStoryRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(MySQLUserRepository::class),
        StoryRepository::class => \DI\autowire(MySQLStoryRepository::class),
    ]);
};
