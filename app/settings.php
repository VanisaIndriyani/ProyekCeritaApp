<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return [
    'displayErrorDetails' => true,
    'db' => [
        'host' => 'localhost',
        'dbname' => 'cerita_app',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ],
];
