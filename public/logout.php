<?php
// Simple logout script - Full PHP approach
require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\Helpers\AuthHelper;

try {
    // Check if user is logged in before logout
    $isLoggedIn = AuthHelper::isAuthenticated();
    
    if ($isLoggedIn) {
        $user = AuthHelper::getCurrentUser();
        error_log("User logout: " . ($user['username'] ?? 'unknown'));
    }
    
    // Perform logout (AuthHelper handles session start internally)
    AuthHelper::logout();
    
    // Don't start session again, AuthHelper already did it and set new session
    $_SESSION['logout_success'] = true;
    
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['logout_error'] = true;
}

// Redirect to home page
header('Location: /');
exit;
?>
