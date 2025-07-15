<?php
$title = 'Dashboard Admin - Cerita Mahasiswa';
$currentPage = 'admin';

// Ambil data statistik dari database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
    // Total user
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $totalUsers = $stmt->fetchColumn();
    // Total cerita
    $stmt = $pdo->query('SELECT COUNT(*) FROM stories');
    $totalStories = $stmt->fetchColumn();
    // Total pending
    $stmt = $pdo->query("SELECT COUNT(*) FROM stories WHERE status = 'pending'");
    $totalPending = $stmt->fetchColumn();
} catch (Exception $e) {
    $totalUsers = $totalStories = $totalPending = 0;
}

ob_start();
?>
<main class="main-content">
    <div class="container">
        <div class="admin-dashboard-hero">
            <div class="hero-icon-bg">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h1 class="page-title">Dashboard Admin</h1>
                <p class="page-subtitle">Selamat datang, <b>Admin</b>! Kelola platform Cerita Mahasiswa dengan mudah dan efisien.</p>
            </div>
        </div>
        <div class="dashboard-stats-grid">
            <div class="dashboard-stat-card">
                <div class="stat-icon stat-users"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">Total User</div>
                </div>
            </div>
            <div class="dashboard-stat-card">
                <div class="stat-icon stat-stories"><i class="fas fa-book"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalStories ?></div>
                    <div class="stat-label">Total Cerita</div>
                </div>
            </div>
            <div class="dashboard-stat-card">
                <div class="stat-icon stat-pending"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-value"><?= $totalPending ?></div>
                    <div class="stat-label">Menunggu Review</div>
                </div>
            </div>
        </div>
        <div class="content-card" style="margin-top:2.5rem;">
            <h2>Selamat Bertugas!</h2>
            <p>Gunakan menu di samping untuk mengelola user, cerita, dan konten lain di platform Cerita Mahasiswa.<br>Jika butuh bantuan, silakan hubungi tim pengembang.</p>
        </div>
    </div>
</main>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?> 