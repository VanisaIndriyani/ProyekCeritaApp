<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Cerita Mahasiswa' ?></title>
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="/auth.css">
    <link rel="stylesheet" href="/flash.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?= $additionalHead ?? '' ?>
</head>
<body class="auth-body">
    <!-- Flash Messages -->
    <?php 
    if (isset($flashMessages) && !empty($flashMessages)) {
        echo \App\Infrastructure\Utils\FlashMessage::renderMessages($flashMessages);
    }
    ?>
    
    <div class="auth-background">
        <div class="auth-container">
            <div class="auth-card">
                <!-- Header Section -->
                <div class="auth-header">
                    <div class="auth-logo">
                        <img src="https://img.icons8.com/color/48/000000/graduation-cap--v2.png" alt="Logo" class="logo-img">
                        <span class="logo-title">Cerita Mahasiswa</span>
                    </div>
                    <h2 class="auth-title"><?= $authTitle ?? 'Authentication' ?></h2>
                    <p class="auth-subtitle"><?= $authSubtitle ?? 'Silakan masuk untuk melanjutkan' ?></p>
                </div>
                
                <!-- Content Section -->
                <div class="auth-content">
                    <?= $content ?? '' ?>
                </div>
                
                <!-- Footer Links -->
                <div class="auth-footer">
                    <?= $footerLinks ?? '' ?>
                </div>
            </div>
            
            <!-- Back to Home -->
            <div class="back-to-home">
                <a href="/" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    
    <!-- Add auth utilities and guards -->
    <script src="/flash.js"></script>
    <script src="/auth.js"></script>
    <script src="/guards.js"></script>
    <script src="/login.js"></script>
    <?= $additionalScripts ?? '' ?>
    <meta name="page-type" content="guest">
</body>
</html>
