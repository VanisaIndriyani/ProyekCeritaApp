<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get token from various sources
        $token = $this->getTokenFromRequest($request);
        
        if (!$token || !$this->isValidToken($token)) {
            // Redirect to login for web pages
            if ($this->isWebRequest($request)) {
                return $this->redirectToLogin();
            }
            
            // Return JSON error for API requests
            return $this->unauthorizedResponse();
        }
        
        // Add user info to request attributes for use in controllers
        $userInfo = $this->getUserInfoFromToken($token);
        $request = $request->withAttribute('user', $userInfo);
        
        // Extract userId for easier access in controllers
        $userId = $this->getUserIdFromToken($token);
        $request = $request->withAttribute('userId', $userId);
        
        return $handler->handle($request);
    }
    
    private function getTokenFromRequest(Request $request): ?string
    {
        // Check Authorization header first
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        // Check cookie (for web sessions) - prioritize this for web requests
        $cookies = $request->getCookieParams();
        if (isset($cookies['authToken']) && !empty($cookies['authToken'])) {
            return $cookies['authToken'];
        }
        
        // Check query parameter
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['token']) && !empty($queryParams['token'])) {
            return $queryParams['token'];
        }
        
        return null;
    }
    
    private function isValidToken(string $token): bool
    {
        // Enhanced token validation
        if (empty($token) || strlen($token) < 3) {
            return false;
        }
        
        // Allow test token for development
        if ($token === 'test_token_123') {
            return true;
        }
        
        // Check if it's our simple "logged_in" token or a more complex token
        if ($token === 'logged_in') {
            return true;
        }
        
        // For base64 encoded tokens, check if they decode properly
        if (strlen($token) >= 10) {
            $decoded = base64_decode($token, true);
            return $decoded !== false;
        }
        
        return false;
    }
    
    private function getUserInfoFromToken(string $token): array
    {
        // Handle test token
        if ($token === 'test_token_123') {
            return [
                'username' => 'user1',
                'loginTime' => time(),
                'token' => $token,
                'userId' => 2
            ];
        }
        
        // Handle simple token
        if ($token === 'logged_in') {
            return [
                'username' => 'user',
                'loginTime' => time(),
                'token' => $token
            ];
        }
        
        // Decode token to get user info
        // This is a simple implementation - use JWT in production
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return [
                'username' => 'unknown',
                'loginTime' => time(),
                'token' => $token
            ];
        }
        
        $parts = explode('|', $decoded);
        
        return [
            'username' => $parts[0] ?? 'unknown',
            'loginTime' => $parts[1] ?? time(),
            'token' => $token
        ];
    }
    
    private function getUserIdFromToken(string $token): ?int
    {
        // Handle test token
        if ($token === 'test_token_123') {
            return 2; // Test user ID
        }
        
        // Handle simple token
        if ($token === 'logged_in') {
            return 2; // Default test user ID
        }
        
        // Decode token to get user info
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }
        
        $parts = explode('|', $decoded);
        
        // For our current token format: username|timestamp
        // We need to lookup user ID from username
        $username = $parts[0] ?? null;
        if ($username === 'user1') {
            return 2; // From database
        } elseif ($username === 'admin') {
            return 1; // From database
        }
        
        return null;
    }
    
    private function isWebRequest(Request $request): bool
    {
        $accept = $request->getHeaderLine('Accept');
        return strpos($accept, 'text/html') !== false || 
               strpos($accept, 'application/json') === false;
    }
    
    private function redirectToLogin(): Response
    {
        $response = new SlimResponse();
        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
    
    private function unauthorizedResponse(): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'statusCode' => 401,
            'error' => [
                'type' => 'UNAUTHORIZED',
                'description' => 'Authentication required'
            ]
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
