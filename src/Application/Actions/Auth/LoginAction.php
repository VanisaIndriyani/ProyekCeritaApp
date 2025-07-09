<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

class LoginAction extends Action
{
    private UserRepository $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
    }

    protected function action(): Response
    {
        $data = $this->getFormData();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            return $this->respondWithError('Username dan password wajib diisi', 400);
        }
        $user = $this->userRepository->verifyLogin($username, $password);
        if (!$user) {
            return $this->respondWithError('Username atau password salah', 401);
        }
        // Dummy token (bisa diganti JWT nanti)
        $token = base64_encode($username . '|' . time());
        $this->logger->info("User login: $username");
        return $this->respondWithData([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'role' => method_exists($user, 'getRole') ? $user->getRole() : (property_exists($user, 'role') ? $user->role : 'user'),
            ]
        ]);
    }

    private function respondWithError($msg, $code = 400): Response
    {
        return $this->respond(['statusCode' => $code, 'error' => ['description' => $msg]], $code);
    }
} 