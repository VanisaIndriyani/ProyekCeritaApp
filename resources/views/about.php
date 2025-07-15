<?php
$title = 'Tentang - Cerita Mahasiswa';
$additionalCSS = ['/about.css'];
$currentPage = 'about';

// Capture page content
ob_start();
?>

<main class="about-main">
    <!-- Hero Section -->
    <section class="hero-alt">
        <div class="hero-ornament">
            <svg width="340" height="80" viewBox="0 0 340 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="170" cy="40" rx="160" ry="28" fill="#667eea" fill-opacity="0.10"/>
                <ellipse cx="170" cy="40" rx="100" ry="18" fill="#667eea" fill-opacity="0.13"/>
                <circle cx="80" cy="40" r="15" fill="#667eea" fill-opacity="0.18"/>
                <circle cx="260" cy="40" r="12" fill="#764ba2" fill-opacity="0.16"/>
            </svg>
        </div>
        <h1 class="hero-title">
            <span class="highlight">Tentang</span> Cerita Mahasiswa
        </h1>
        <p class="hero-description">
            Platform berbagi cerita, pengalaman, dan inspirasi dari mahasiswa untuk mahasiswa.<br>
            Temukan kisah menarik, bagikan pengalamanmu, dan tumbuh bersama komunitas kampus!
        </p>
    </section>

    <!-- Main Content -->
    <section class="about-content">
        <!-- Vision & Mission -->
        <div class="vision-mission-section">
            <div class="visi-misi-row">
                <div class="visi-card">
                    <div class="visi-icon">🎯</div>
                    <h3>Visi</h3>
                    <p>Menjadi wadah utama bagi mahasiswa Indonesia untuk saling berbagi pengalaman, inspirasi, dan tips seputar dunia kampus.</p>
                </div>
                <div class="misi-card">
                    <div class="misi-icon">🚀</div>
                    <h3>Misi</h3>
                    <p>Menginspirasi, mengedukasi, dan mempererat solidaritas antar mahasiswa melalui kekuatan cerita.</p>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="container">
                <h2 class="section-title">Mengapa Cerita Mahasiswa?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Komunitas Mahasiswa</h3>
                        <p>Terhubung dengan ribuan mahasiswa dari berbagai universitas di Indonesia</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Cerita Inspiratif</h3>
                        <p>Baca dan bagikan cerita-cerita inspiratif seputar kehidupan kampus</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h3>Tips & Trik</h3>
                        <p>Dapatkan tips berguna untuk sukses di dunia perkuliahan</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3>Saling Mendukung</h3>
                        <p>Berbagi pengalaman dan saling memberikan dukungan positif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="team-section">
            <div class="container">
                <h2 class="section-title">Tim Kami</h2>
                <div class="team-grid">
                    <?php
                    // Ambil data tim dari database
                    try {
                        $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
                        $stmt = $pdo->query('SELECT * FROM team ORDER BY id ASC');
                        $tim = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        $tim = [];
                    }
                    if (empty($tim)) {
                        echo '<p style="color:#888;text-align:center;width:100%;">Belum ada data tim.</p>';
                    } else {
                        foreach ($tim as $anggota) {
                            $foto = $anggota['foto'] ?? '';
                            if (!$foto) {
                                // Avatar default berdasarkan gender/jabatan (random)
                                $foto = 'https://randomuser.me/api/portraits/lego/' . ($anggota['id'] % 10) . '.jpg';
                            }
                            echo '<div class="team-member">';
                            echo '<img src="' . htmlspecialchars($foto) . '" alt="' . htmlspecialchars($anggota['nama']) . '" class="team-avatar">';
                            echo '<h4>' . htmlspecialchars($anggota['nama']) . '</h4>';
                            echo '<p>' . htmlspecialchars($anggota['jabatan']) . '</p>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact-section">
            <div class="container">
                <div class="contact-box">
                    <h2>Hubungi Kami</h2>
                    <p class="contact-text">
                        Punya pertanyaan, saran, atau ingin berkolaborasi? Jangan ragu untuk menghubungi kami!
                    </p>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>Email: <a href="mailto:info@ceritamahasiswa.id">info@ceritamahasiswa.id</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>Telepon: <a href="tel:+6281234567890">+62 812-3456-7890</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jakarta, Indonesia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// End content capture and render using layout
$content = ob_get_clean();

// Include the layout
include __DIR__ . '/layouts/main.php';
?>
