<?php

declare(strict_types=1);

namespace App\Application\Controllers\Auth;

use App\Application\Controllers\BaseController;
use App\Domain\User\UserRepository;
use App\Infrastructure\Utils\FlashMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class AuthWebController extends BaseController
{
    private UserRepository $userRepository;
    private FlashMessage $flash;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
        $this->flash = new FlashMessage();
    }

    /**
     * Show login form
     */
    public function showLogin(Request $request, Response $response): Response
    {
        $this->request = $request;
        $this->response = $response;
        
        // Get flash messages
        $flashMessages = $this->flash->getMessages();
        
        return $this->respondWithView('auth/login.php', [
            'title' => 'Login - Cerita Mahasiswa',
            'authTitle' => 'Masuk ke Akun',
            'authSubtitle' => 'Silakan masuk untuk melanjutkan',
            'flashMessages' => $flashMessages,
            'footerLinks' => $this->getLoginFooterLinks()
        ]);
    }

    /**
     * Show register form
     */
    public function showRegister(Request $request, Response $response): Response
    {
        $this->request = $request;
        $this->response = $response;
        
        // Get flash messages
        $flashMessages = $this->flash->getMessages();
        
        return $this->respondWithView('auth/register.php', [
            'title' => 'Daftar - Cerita Mahasiswa',
            'authTitle' => 'Buat Akun Baru',
            'authSubtitle' => 'Bergabung dengan komunitas cerita mahasiswa',
            'flashMessages' => $flashMessages,
            'footerLinks' => $this->getRegisterFooterLinks()
        ]);
    }

    /**
     * Process login form
     */
    public function processLogin(Request $request, Response $response): Response
    {
        $this->request = $request;
        $this->response = $response;
        
        $data = $request->getParsedBody();
        
        // Validate input
        $validation = $this->validateLoginData($data);
        if (!$validation['valid']) {
            $this->flash->error($validation['message']);
            return $this->redirect('/login');
        }
        
        try {
            // Attempt login
            $user = $this->userRepository->findByUsername($data['username']);
            
            if (!$user || !password_verify($data['password'], $user->getPassword())) {
                $this->flash->error('Username atau password salah');
                return $this->redirect('/login');
            }
            
            // Set session/cookies
            $this->setAuthSession($user);
            
            $this->flash->success('Login berhasil! Selamat datang ' . $user->getNama());
            
            // Redirect based on role
            if ($user->getRole() === 'admin') {
                return $this->redirect('/admin');
            } else {
                return $this->redirect('/');
            }
            
        } catch (\Exception $e) {
            $this->flash->error('Terjadi kesalahan saat login. Silakan coba lagi.');
            return $this->redirect('/login');
        }
    }

    /**
     * Process register form
     */
    public function processRegister(Request $request, Response $response): Response
    {
        $this->request = $request;
        $this->response = $response;
        
        $data = $request->getParsedBody();
        
        // Validate input
        $validation = $this->validateRegisterData($data);
        if (!$validation['valid']) {
            $this->flash->error($validation['message']);
            return $this->redirect('/register');
        }
        
        try {
            // Check if username/email already exists
            if ($this->userRepository->findByUsername($data['username'])) {
                $this->flash->error('Username sudah digunakan');
                return $this->redirect('/register');
            }
            
            if ($this->userRepository->findByEmail($data['email'])) {
                $this->flash->error('Email sudah terdaftar');
                return $this->redirect('/register');
            }
            
            // Create user
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $userId = $this->userRepository->create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => $hashedPassword,
                'role' => 'user'
            ]);
            
            $this->flash->success('Registrasi berhasil! Silakan login dengan akun Anda.');
            return $this->redirect('/login');
            
        } catch (\Exception $e) {
            $this->flash->error('Terjadi kesalahan saat registrasi. Silakan coba lagi.');
            return $this->redirect('/register');
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request, Response $response): Response
    {
        $this->request = $request;
        $this->response = $response;
        
        // Clear session and cookies
        $this->clearAuthSession();
        
        $this->flash->success('Logout berhasil. Sampai jumpa!');
        return $this->redirect('/');
    }

    private function validateLoginData(array $data): array
    {
        if (empty($data['username']) || strlen(trim($data['username'])) < 3) {
            return ['valid' => false, 'message' => 'Username minimal 3 karakter'];
        }
        
        if (empty($data['password']) || strlen($data['password']) < 6) {
            return ['valid' => false, 'message' => 'Password minimal 6 karakter'];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    private function validateRegisterData(array $data): array
    {
        if (empty($data['nama']) || strlen(trim($data['nama'])) < 2) {
            return ['valid' => false, 'message' => 'Nama minimal 2 karakter'];
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Format email tidak valid'];
        }
        
        if (empty($data['username']) || strlen(trim($data['username'])) < 3) {
            return ['valid' => false, 'message' => 'Username minimal 3 karakter'];
        }
        
        if (empty($data['password']) || strlen($data['password']) < 6) {
            return ['valid' => false, 'message' => 'Password minimal 6 karakter'];
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            return ['valid' => false, 'message' => 'Konfirmasi password tidak sesuai'];
        }
        
        if (empty($data['terms'])) {
            return ['valid' => false, 'message' => 'Anda harus menyetujui syarat dan ketentuan'];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    private function setAuthSession($user): void
    {
        // Create simple token (in production, use JWT)
        $token = base64_encode($user->getUsername() . '|' . time() . '|' . $user->getRole());
        
        // Set cookies
        setcookie('authToken', $token, time() + (7 * 24 * 60 * 60), '/', '', false, true); // 7 days, httpOnly
        setcookie('userRole', $user->getRole(), time() + (7 * 24 * 60 * 60), '/', '', false, false);
        setcookie('userId', (string)$user->getId(), time() + (7 * 24 * 60 * 60), '/', '', false, false);
        setcookie('userName', $user->getNama(), time() + (7 * 24 * 60 * 60), '/', '', false, false);
        
        // Also set session for immediate use
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['role'] = $user->getRole();
        $_SESSION['nama'] = $user->getNama();
    }

    private function clearAuthSession(): void
    {
        // Clear cookies
        setcookie('authToken', '', time() - 3600, '/', '', false, true);
        setcookie('userRole', '', time() - 3600, '/', '', false, false);
        setcookie('userId', '', time() - 3600, '/', '', false, false);
        setcookie('userName', '', time() - 3600, '/', '', false, false);
        
        // Clear session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
    }

    private function getLoginFooterLinks(): string
    {
        return '<p>Belum punya akun? <a href="/register" class="auth-link">Daftar di sini</a></p>';
    }

    private function getRegisterFooterLinks(): string
    {
        return '<p>Sudah punya akun? <a href="/login" class="auth-link">Masuk di sini</a></p>';
    }

    protected function action(): Response
    {
        // This method won't be called directly
        return $this->response;
    }
}
