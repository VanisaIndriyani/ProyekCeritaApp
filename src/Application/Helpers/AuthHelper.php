<?php

declare(strict_types=1);

namespace App\Application\Helpers;

class AuthHelper
{
    /**
     * Ensure session is started
     */
    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        self::ensureSession();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser(): ?array
    {
        self::ensureSession();
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'nama' => $_SESSION['nama'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? 'user'
        ];
    }

    /**
     * Check if current user is admin
     */
    public static function isAdmin(): bool
    {
        $user = self::getCurrentUser();
        return $user && ($user['role'] === 'admin');
    }

    /**
     * Get user's initial for avatar
     */
    public static function getUserInitial(): string
    {
        $user = self::getCurrentUser();
        if (!$user) {
            return 'G'; // Guest
        }

        $name = $user['nama'] ?? $user['username'] ?? 'U';
        return strtoupper(substr($name, 0, 1));
    }

    /**
     * Get user's display name
     */
    public static function getUserDisplayName(): string
    {
        $user = self::getCurrentUser();
        if (!$user) {
            return 'Guest';
        }

        return $user['nama'] ?? $user['username'] ?? 'User';
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        // Only start session if none is active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Store user info for logging (before clearing session)
        $userId = $_SESSION['user_id'] ?? 'unknown';
        $username = $_SESSION['username'] ?? 'unknown';
        
        // Clear all session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, 
                $params["path"], $params["domain"], 
                $params["secure"], $params["httponly"]
            );
        }
        
        // Clear auth-related cookies (used by AuthWebController)
        setcookie('authToken', '', time() - 3600, '/', '', false, true);
        setcookie('userRole', '', time() - 3600, '/', '', false, false);
        setcookie('userId', '', time() - 3600, '/', '', false, false);
        setcookie('userName', '', time() - 3600, '/', '', false, false);
        
        // Destroy session completely
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Start a new clean session to ensure no leftover data
        session_start();
        session_regenerate_id(true);
        
        // Log successful logout 
        error_log("User logout successful - ID: $userId, Username: $username");
    }

    /**
     * Require authentication (redirect if not authenticated)
     */
    public static function requireAuth(string $redirectTo = '/login'): bool
    {
        if (!self::isAuthenticated()) {
            header("Location: $redirectTo");
            exit;
        }
        return true;
    }

    /**
     * Require admin role (redirect if not admin)
     */
    public static function requireAdmin(string $redirectTo = '/'): bool
    {
        if (!self::isAuthenticated()) {
            header("Location: /login");
            exit;
        }
        
        if (!self::isAdmin()) {
            header("Location: $redirectTo");
            exit;
        }
        
        return true;
    }

    /**
     * Require guest (redirect if authenticated)
     */
    public static function requireGuest(string $redirectTo = null): bool
    {
        if (self::isAuthenticated()) {
            $user = self::getCurrentUser();
            if ($user && $user['role'] === 'admin') {
                header("Location: /admin");
            } else {
                header("Location: " . ($redirectTo ?? '/'));
            }
            exit;
        }
        return true;
    }
}
