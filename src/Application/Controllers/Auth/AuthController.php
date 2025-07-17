<?php

declare(strict_types=1);

namespace App\Application\Controllers\Auth;

use App\Application\Controllers\BaseController;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Utils\MailHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class AuthController extends BaseController
{
    private UserRepository $userRepository;
    private PDO $pdo;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository, PDO $pdo)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
        $this->pdo = $pdo;
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


    /**
     * Handle Forgot Password
     */
    public function forgotPassword($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? null;

        if (!$email) {
            $response->getBody()->write(json_encode([
                'message' => 'Email harus diisi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            $response->getBody()->write(json_encode([
                'message' => 'Email tidak ditemukan.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->userRepository->saveResetToken($user->getId(), $token, $expires);

        $resetUrl = "http://localhost:8080/reset-password?token={$token}";

        $sendMail = \App\Utils\MailHelper::sendResetLink($user->getEmail(), $resetUrl);

        if (!$sendMail) {
            $response->getBody()->write(json_encode([
                'message' => 'Gagal mengirim email reset password.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode([
            'message' => 'Link reset password berhasil dikirim ke email!',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Handle Reset Password
     */
    public function resetPassword($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            $response->getBody()->write(json_encode([
                'message' => 'Token dan password wajib diisi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $userRow = $stmt->fetch();

        if (!$userRow) {
            $response->getBody()->write(json_encode([
                'message' => 'Token tidak valid.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        if (strtotime($userRow['reset_expires']) < time()) {
            $response->getBody()->write(json_encode([
                'message' => 'Token sudah kadaluarsa.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $this->pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $updateStmt->execute([$hashed, $userRow['id']]);

        $response->getBody()->write(json_encode([
            'message' => 'Password berhasil direset.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    protected function action(): Response
    {
        // This method won't be called directly
        return $this->response;
    }
}
