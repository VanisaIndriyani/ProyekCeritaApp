<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Cerita Mahasiswa' ?></title>
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="/components.css">
    <link rel="stylesheet" href="/flash.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Additional Styles -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        /* Minimal inline styles only for layout structure that's not in components.css */

        /* Header Styles */
        .main-header {
            background: var(--white);
            box-shadow: var(--shadow);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-nav {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-img {
            width: 42px;
            height: 42px;
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .main-nav {
            display: flex;
            gap: 1.5rem;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: var(--transition);
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }

        /* Auth Section */
        .auth-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .auth-section .login-btn {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .auth-section .login-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* User Profile Dropdown */
        .user-profile {
            position: relative;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--white);
            border: 2px solid var(--primary-color);
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .profile-btn:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        .profile-btn:hover .profile-avatar {
            background: var(--white);
            color: var(--primary-color);
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            display: none;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }

        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 0.5rem 0;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            width: 100%;
        }

        /* Footer */
        .modern-footer {
            background: var(--text-dark);
            color: var(--white);
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .footer-section p {
            color: #a0aec0;
            line-height: 1.6;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #a0aec0;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--accent-color);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid #4a5568;
            padding-top: 1rem;
            text-align: center;
            color: #a0aec0;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                padding: 0 1rem;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .nav-section {
                gap: 1rem;
                flex-wrap: wrap;
            }

            .main-nav {
                gap: 1rem;
                order: 3;
                width: 100%;
                justify-content: center;
                margin-top: 0.5rem;
            }

            .nav-link {
                padding: 0.4rem 0.8rem;
                font-size: 0.875rem;
            }

            .auth-section {
                order: 2;
            }

            .auth-section .login-btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.875rem;
            }

            .profile-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.875rem;
            }

            .user-name {
                display: none;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .main-nav {
                gap: 0.5rem;
            }

            .nav-link {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }

            .logo-title {
                font-size: 1.2rem;
            }
        }

        @media print {
            footer, .footer, .site-footer, .bottom-nav, .sidebar, .category-popular, .help-section, .nav, .navbar, .menu, .user-menu, .main-footer, .page-footer, .kategori-populer, .bantuan, .copyright {
                display: none !important;
            }
        }
    </style>
    
    <!-- Page Specific Styles -->
    <?php if (isset($pageStyles)): ?>
        <style><?= $pageStyles ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../components/header.php'; ?>

    <!-- Flash Messages -->
    <?php 
    // Handle session flash messages
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $sessionFlashMessage = null;
    if (isset($_SESSION['flash_message'])) {
        $sessionFlashMessage = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']); // Clear after reading
    }
    
    // Handle logout messages
    if (isset($_SESSION['logout_success'])) {
        $sessionFlashMessage = [
            'type' => 'success',
            'message' => 'Logout berhasil! Sampai jumpa.'
        ];
        unset($_SESSION['logout_success']);
    } elseif (isset($_SESSION['logout_error'])) {
        $sessionFlashMessage = [
            'type' => 'error',
            'message' => 'Terjadi kesalahan saat logout, tapi Anda telah di-logout.'
        ];
        unset($_SESSION['logout_error']);
    }
    ?>
    
    <?php if ($sessionFlashMessage): ?>
        <div class="flash-messages">
            <div class="flash-message flash-<?= $sessionFlashMessage['type'] ?>" data-auto-dismiss="true">
                <span class="flash-icon"><?= $sessionFlashMessage['type'] === 'success' ? '✓' : '✗' ?></span>
                <span class="flash-text"><?= htmlspecialchars($sessionFlashMessage['message']) ?></span>
                <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($flashMessages) && !empty($flashMessages)): ?>
        <div class="flash-messages">
            <?php foreach ($flashMessages as $message): ?>
                <div class="flash-message flash-<?= $message['type'] ?>" data-auto-dismiss="true">
                    <span class="flash-icon"><?= $message['type'] === 'success' ? '✓' : '✗' ?></span>
                    <span class="flash-text"><?= htmlspecialchars($message['message']) ?></span>
                    <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../components/footer.php'; ?>

    <!-- Base Scripts -->
    <script src="/flash.js"></script>
    <script src="/auth.js"></script>
    
    <!-- Additional Scripts -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Page Specific Scripts -->
    <?php if (isset($pageScripts)): ?>
        <script><?= $pageScripts ?></script>
    <?php endif; ?>
</body>
</html>
