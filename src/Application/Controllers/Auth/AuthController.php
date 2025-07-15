<?php

declare(strict_types=1);

namespace App\Application\Controllers\Auth;

use App\Application\Controllers\BaseController;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class AuthController extends BaseController
{
    private UserRepository $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
    }

    /**
     * Handle user registration
     */
    public function register(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

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
        
        $this->logInfo("User registered: $username");
        
        return $this->respondWithData([
            'message' => 'Register berhasil', 
            'user' => $saved
        ], 201);
    }

    /**
     * Handle user login
     */
    public function login(): Response
    {
        $this->request = func_get_args()[0] ?? $this->request;
        $this->response = func_get_args()[1] ?? $this->response;
        $this->args = func_get_args()[2] ?? $this->args;

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

        // Generate token (bisa diganti JWT nanti)
        $token = base64_encode($username . '|' . time());
        
        $this->logInfo("User login: $username");
        
        return $this->respondWithData([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'role' => method_exists($user, 'getRole') ? $user->getRole() : 
                         (property_exists($user, 'role') ? $user->role : 'user'),
            ]
        ]);
    }

    protected function action(): Response
    {
        // This method won't be called directly
        return $this->response;
    }
}
