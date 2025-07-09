<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Action;

class RegisterAction extends Action
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
        $nama = $data['nama'] ?? '';
        $email = $data['email'] ?? '';

        if (!$username || !$password) {
            return $this->respondWithError('Username dan password wajib diisi', 400);
        }
        if ($this->userRepository->findByUsername($username)) {
            return $this->respondWithError('Username sudah terdaftar', 400);
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(null, $username, $nama, $email);
        $saved = $this->userRepository->save($user, $passwordHash);
        $this->logger->info("User registered: $username");
        return $this->respondWithData(['message' => 'Register berhasil', 'user' => $saved], 201);
    }

    private function respondWithError($msg, $code = 400): Response
    {
        return $this->respond(['statusCode' => $code, 'error' => ['description' => $msg]], $code);
    }
} 