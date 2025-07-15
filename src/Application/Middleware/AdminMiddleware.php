<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class AdminMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get token from various sources
        $token = $this->getTokenFromRequest($request);
        
        if (!$token || !$this->isValidToken($token)) {
            return $this->unauthorizedResponse();
        }
        
        $userInfo = $this->getUserInfoFromToken($token);
        
        // Check if user is admin
        if (!$this->isAdmin($userInfo)) {
            return $this->forbiddenResponse();
        }
        
        // Add user info to request attributes
        $request = $request->withAttribute('user', $userInfo);
        
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
                'username' => 'admin',
                'loginTime' => time(),
                'token' => $token,
                'role' => 'admin'
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
            'role' => $this->getUserRole($parts[0] ?? 'unknown')
        ];
    }
    
    private function getUserRole(string $username): string
    {
        // In production, fetch from database
        // For now, simple check
        $adminUsers = ['admin', 'administrator', 'root'];
        return in_array(strtolower($username), $adminUsers) ? 'admin' : 'user';
    }
    
    private function isAdmin(array $userInfo): bool
    {
        return isset($userInfo['role']) && $userInfo['role'] === 'admin';
    }
    
    private function isWebRequest(Request $request): bool
    {
        $accept = $request->getHeaderLine('Accept');
        return strpos($accept, 'text/html') !== false || 
               strpos($accept, 'application/json') === false;
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
    
    private function forbiddenResponse(): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'statusCode' => 403,
            'error' => [
                'type' => 'FORBIDDEN',
                'description' => 'Admin access required'
            ]
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }
}
