<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use App\Application\Actions\ActionPayload;
use App\Application\Actions\ActionError;

abstract class BaseController
{
    protected LoggerInterface $logger;
    protected Request $request;
    protected Response $response;
    protected array $args;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->action();
    }

    abstract protected function action(): Response;

    /**
     * Get form data from request
     */
    protected function getFormData(): array
    {
        return $this->request->getParsedBody() ?? [];
    }

    /**
     * Get query parameters
     */
    protected function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    /**
     * Get route argument
     */
    protected function getArg(string $name): mixed
    {
        return $this->args[$name] ?? null;
    }

    /**
     * Respond with JSON data
     */
    protected function respondWithData(mixed $data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);
        return $this->respond($payload);
    }

    /**
     * Respond with error
     */
    protected function respondWithError(string $message, int $statusCode = 400): Response
    {
        $error = new ActionError(ActionError::BAD_REQUEST, $message);
        $payload = new ActionPayload($statusCode, null, $error);
        return $this->respond($payload);
    }

    /**
     * Respond with view (HTML)
     */
    protected function respondWithView(string $viewPath, array $data = []): Response
    {
        $viewFile = __DIR__ . '/../../../resources/views/' . $viewPath;
        
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        // Extract data for view
        extract($data);
        
        // Start output buffering
        ob_start();
        include $viewFile;
        $html = ob_get_clean();

        $this->response->getBody()->write($html);
        return $this->response->withHeader('Content-Type', 'text/html');
    }

    /**
     * Redirect response
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return $this->response->withHeader('Location', $url)->withStatus($statusCode);
    }

    /**
     * Send JSON response
     */
    private function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }

    /**
     * Log info message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Log error message
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
}
