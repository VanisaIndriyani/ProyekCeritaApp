<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Actions\Story\ListStoriesAction;
use App\Application\Actions\Story\ViewStoryAction;
use App\Application\Actions\Story\CreateStoryAction;
use App\Application\Actions\Story\UpdateStoryAction;
use App\Application\Actions\Story\DeleteStoryAction;
use App\Application\Actions\Auth\RegisterAction;
use App\Application\Actions\Auth\LoginAction;
use App\Application\Actions\Admin\ListStoriesAdminAction;
use App\Application\Actions\Admin\PublishStoryAction;
use App\Application\Actions\Admin\DeleteStoryAdminAction;
use App\Application\Actions\Admin\ListUsersAdminAction;
use App\Application\Actions\Admin\DeleteUserAdminAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->group('/stories', function (Group $group) {
        $group->get('', ListStoriesAction::class);
        $group->get('/{id}', ViewStoryAction::class);
        $group->post('', CreateStoryAction::class);
        $group->map(['PUT', 'POST'], '/{id}', UpdateStoryAction::class);
        $group->delete('/{id}', DeleteStoryAction::class);
    });

    $app->post('/register', RegisterAction::class);
    $app->post('/login', LoginAction::class);

    $app->group('/admin', function (\Slim\Interfaces\RouteCollectorProxyInterface $group) {
        $group->get('/stories', ListStoriesAdminAction::class);
        $group->post('/stories/{id}/publish', PublishStoryAction::class);
        $group->delete('/stories/{id}', DeleteStoryAdminAction::class);
        $group->get('/users', ListUsersAdminAction::class);
        $group->delete('/users/{id}', DeleteUserAdminAction::class);
        // Tentang Kami endpoints
        $group->get('/about', function ($request, $response) {
            $file = __DIR__ . '/../var/about.txt';
            $content = is_file($file) ? file_get_contents($file) : '';
            $response->getBody()->write(json_encode(['content' => $content]));
            return $response->withHeader('Content-Type', 'application/json');
        });
        $group->post('/about', function ($request, $response) {
            $data = $request->getParsedBody();
            $content = $data['content'] ?? '';
            $file = __DIR__ . '/../var/about.txt';
            file_put_contents($file, $content);
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');
        });
    });
};
