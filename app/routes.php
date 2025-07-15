<?php

declare(strict_types=1);

use Slim\App;

return function (App $app) {
    // Load Web Routes (Pages)
    $webRoutes = require __DIR__ . '/../src/Routes/web.php';
    $webRoutes($app);
    
    // Load API Routes
    $apiRoutes = require __DIR__ . '/../src/Routes/api.php';
    $apiRoutes($app);
    
    // Load Admin Routes
    $adminRoutes = require __DIR__ . '/../src/Routes/admin.php';
    $adminRoutes($app);
};
