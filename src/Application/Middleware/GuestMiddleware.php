<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class GuestMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Allow POST requests to pass through (form submissions)
        if ($request->getMethod() === 'POST') {
            return $handler->handle($request);
        }
        
        // Get token from various sources
        $token = $this->getTokenFromRequest($request);
        
        if ($token && $this->isValidToken($token)) {
            // User is already logged in, redirect to appropriate page
            $userInfo = $this->getUserInfoFromToken($token);
            return $this->redirectToDashboard($userInfo);
        }
        
        // User is not logged in, continue to login/register page
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
        // Handle simple token
        if ($token === 'logged_in') {
            return [
                'username' => 'user',
                'loginTime' => time(),
                'token' => $token,
                'role' => 'user'
            ];
        }
        
        // Decode token to get user info
        // This is a simple implementation - use JWT in production
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return [
                'username' => 'unknown',
                'loginTime' => time(),
                'token' => $token,
                'role' => 'user'
            ];
        }
        
        $parts = explode('|', $decoded);
        
        return [
            'username' => $parts[0] ?? 'unknown',
            'loginTime' => $parts[1] ?? time(),
            'token' => $token,
            'role' => $parts[2] ?? 'user'
        ];
    }
    
    private function redirectToDashboard(array $userInfo): Response
    {
        $response = new SlimResponse();
        
        // Redirect based on user role
        $redirectUrl = '/'; // Default to homepage
        if (isset($userInfo['role']) && $userInfo['role'] === 'admin') {
            $redirectUrl = '/admin';
        }
        
        return $response
            ->withHeader('Location', $redirectUrl)
            ->withStatus(302);
    }
}
