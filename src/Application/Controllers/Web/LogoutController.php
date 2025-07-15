<?php

declare(strict_types=1);

namespace App\Application\Controllers\Web;

use App\Application\Helpers\AuthHelper;
use Psr\Log\LoggerInterface;

class LogoutController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(): void
    {
        $this->logout();
    }

    public function logout(): void
    {
        try {
            // Check if user is logged in before logout (for logging)
            $isLoggedIn = AuthHelper::isAuthenticated();
            $user = null;
            
            if ($isLoggedIn) {
                $user = AuthHelper::getCurrentUser();
                $this->logger->info("User logout", [
                    'user_id' => $user['id'] ?? 'unknown',
                    'username' => $user['username'] ?? 'unknown'
                ]);
            }

            // Perform logout using AuthHelper
            AuthHelper::logout();

            // Set success message in new session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['logout_success'] = true;

        } catch (\Exception $e) {
            $this->logger->error("Logout failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set error message in session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['logout_error'] = true;
        }

        // Redirect to home page using header redirect
        header('Location: /');
        exit;
    }
}
