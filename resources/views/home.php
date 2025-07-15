<?php
use App\Application\Helpers\AuthHelper;

// Set page variables for layout
$title = 'Cerita Mahasiswa - Beranda';
$currentPage = 'home';
$pageType = 'public';
$additionalCSS = ['/home-enhanced.css'];

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAuthenticated = AuthHelper::isAuthenticated();
$currentUser = AuthHelper::getCurrentUser();

// Get data from controller
$stories = $stories ?? [];
$hasError = $hasError ?? false;
$errorMessage = $errorMessage ?? '';

// If no data from controller, try alternative method
if (empty($stories) && !$hasError) {
    try {
        // Fallback: Try to get data via API call
        $apiUrl = 'http://localhost:8000/api/stories?limit=6';
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true,
                'user_agent' => 'CeritaMahasiswa/1.0'
            ]
        ]);
        
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response !== false) {
            $result = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && $result) {
                if (isset($result['data']) && is_array($result['data'])) {
                    $stories = array_slice($result['data'], 0, 6);
                }
            }
        }
    } catch (Exception $e) {
        // Silently fail, will show empty state
    }
}

// Helper functions
function formatDate($dateString) {
    if (!$dateString) return 'Tanggal tidak tersedia';
    
    try {
        $date = new DateTime($dateString);
        return $date->format('d F Y');
    } catch (Exception $e) {
        return 'Tanggal tidak valid';
    }
}

function getCategoryName($category) {
    $categories = [
        'akademik' => 'Akademik',
        'karir' => 'Karir',
        'kehidupan' => 'Kehidupan',
        'teknologi' => 'Teknologi',
        'organisasi' => 'Organisasi',
        'kompetisi' => 'Kompetisi',
        'wisuda' => 'Wisuda',
        'lainnya' => 'Lainnya'
    ];
    
    return $categories[strtolower($category)] ?? 'Akademik';
}

function calculateReadTime($content) {
    if (!$content) return '1 menit baca';
    
    $wordCount = str_word_count(strip_tags($content));
    $readingSpeed = 200; // words per minute
    $minutes = max(1, ceil($wordCount / $readingSpeed));
    
    return $minutes . ' menit baca';
}

function truncateContent($content, $maxLength = 150) {
    if (!$content) return 'Tidak ada konten tersedia';
    
    $content = strip_tags($content);
    if (strlen($content) <= $maxLength) return $content;
    
    $truncated = substr($content, 0, $maxLength);
    $lastSpace = strrpos($truncated, ' ');
    
    return ($lastSpace > 0 ? substr($truncated, 0, $lastSpace) : $truncated) . '...';
}

// Capture page content
ob_start();
?>

<!-- Hero Section with Jumbotron -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                Bagikan <span class="highlight">Cerita</span> Perjalanan Kampus Anda
            </h1>
            <p class="hero-description">
                Platform untuk berbagi pengalaman, pencapaian, dan inspirasi dari kehidupan mahasiswa. 
                Mari saling menginspirasi melalui cerita-cerita luar biasa.
            </p>
            
            <!-- Search Bar -->
            <div class="search-container">
                <form class="search-form" action="/stories" method="GET">
                    <div class="search-box">
                        <input type="text" 
                               id="searchInput" 
                               name="search"
                               placeholder="Cari cerita yang menginspirasi..." 
                               class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Hero Actions -->
            <div class="hero-actions">
                <button class="btn btn-primary" onclick="window.location.href='/stories'">
                    <i class="fas fa-book-open"></i>
                    Jelajahi Cerita
                </button>
                <?php if ($isAuthenticated): ?>
                    <button class="btn btn-secondary" onclick="window.location.href='/user'">
                        <i class="fas fa-plus"></i>
                        Mulai Buat Cerita
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary" onclick="handleCreateStoryGuest()">
                        <i class="fas fa-plus"></i>
                        Mulai Buat Cerita
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Latest Stories Section -->
<section class="stories-section">
    <div class="section-header">
        <h2 class="section-title">Cerita Terbaru</h2>
        <p class="section-subtitle">
            Temukan cerita-cerita inspiratif terbaru dari mahasiswa di seluruh Indonesia
        </p>
    </div>
    
    <?php if ($hasError): ?>
    <!-- Error State -->
    <div class="error-state">
        <div class="empty-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Terjadi Kesalahan</h3>
        <p><?= htmlspecialchars($errorMessage) ?></p>
        <button class="btn btn-primary" onclick="window.location.reload()">
            <i class="fas fa-refresh"></i>
            Muat Ulang
        </button>
    </div>
    <?php elseif (empty($stories)): ?>
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-book-open"></i>
        </div>
        <h3>Belum Ada Cerita</h3>
        <p>Jadilah yang pertama berbagi cerita inspiratif Anda!</p>
        <?php if ($isAuthenticated): ?>
            <button class="btn btn-primary" onclick="window.location.href='/user'">
                <i class="fas fa-plus"></i>
                Buat Cerita Pertama
            </button>
        <?php else: ?>
            <button class="btn btn-primary" onclick="handleCreateStoryGuest()">
                <i class="fas fa-plus"></i>
                Buat Cerita Pertama
            </button>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Stories Grid -->
    <div class="stories-grid">
        <?php foreach ($stories as $story): ?>
            <?php
            $authorName = $story['author_name'] ?? $story['author']['nama'] ?? $story['author']['username'] ?? 'Anonymous';
            $authorInitial = $authorName ? strtoupper($authorName[0]) : 'U';
            $categoryName = getCategoryName($story['kategori'] ?? 'akademik');
            $categoryClass = 'badge-' . strtolower($story['kategori'] ?? 'akademik');
            $truncatedContent = truncateContent($story['konten'] ?? '');
            $readTime = calculateReadTime($story['konten'] ?? '');
            $formattedDate = formatDate($story['created_at'] ?? '');
            ?>
            <article class="story-card" onclick="viewStory(<?= $story['id'] ?>)">
                <div class="story-card-content">
                    <div class="story-card-header">
                        <div class="story-card-meta">
                            <span class="category-badge <?= $categoryClass ?>"><?= htmlspecialchars($categoryName) ?></span>
                            <span class="read-time"><?= htmlspecialchars($readTime) ?></span>
                        </div>
                        <h3 class="story-card-title"><?= htmlspecialchars($story['judul'] ?? 'Judul tidak tersedia') ?></h3>
                        <p class="story-card-excerpt"><?= htmlspecialchars($truncatedContent) ?></p>
                    </div>
                    <div class="story-card-footer">
                        <div class="story-card-author">
                            <div class="author-avatar"><?= htmlspecialchars($authorInitial) ?></div>
                            <span><?= htmlspecialchars($authorName) ?></span>
                        </div>
                        <div class="read-time"><?= htmlspecialchars($formattedDate) ?></div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- View All Button -->
    <div class="section-footer">
        <button class="btn btn-outline" onclick="window.location.href='/stories'">
            <i class="fas fa-arrow-right"></i>
            Lihat Semua Cerita
        </button>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Mengapa Cerita Mahasiswa?</h2>
            <p class="section-subtitle">
                Platform yang dirancang khusus untuk menghubungkan mahasiswa melalui cerita
            </p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Komunitas Mahasiswa</h3>
                <p>Terhubung dengan mahasiswa dari berbagai universitas dan jurusan di seluruh Indonesia.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3>Inspirasi & Motivasi</h3>
                <p>Dapatkan inspirasi dari pengalaman dan pencapaian mahasiswa lain untuk memotivasi perjalanan Anda.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-share-alt"></i>
                </div>
                <h3>Berbagi Pengalaman</h3>
                <p>Bagikan cerita perjalanan akademik, organisasi, magang, dan pencapaian Anda kepada sesama mahasiswa.</p>
            </div>
        </div>
    </div>
</section>

<script>
// Simplified Home Page JavaScript (No Auto-filtering)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Home page loaded with Full PHP rendering');
    
    // Only handle navigation functions - no auto filtering
    setupNavigationHandlers();
});

function setupNavigationHandlers() {
    // Search form submission is handled by native form action
    console.log('Navigation handlers setup complete');
}

// Global functions for navigation
function viewStory(storyId) {
    if (!storyId) {
        console.error('Story ID is required');
        return;
    }
    window.location.href = `/story-detail?id=${storyId}`;
}

function handleCreateStoryGuest() {
    // Show login prompt for guests
    if (typeof showInfo === 'function') {
        showInfo('Silakan login terlebih dahulu untuk membuat cerita', 'Login Required');
    } else if (typeof showError === 'function') {
        showError('Silakan login terlebih dahulu untuk membuat cerita', 'Authentication Required');
    } else {
        alert('Silakan login terlebih dahulu untuk membuat cerita');
    }
    
    setTimeout(() => {
        window.location.href = '/login';
    }, 1500);
}

// Navigation helper functions
function navigateToStories() {
    window.location.href = '/stories';
}

function navigateToUserDashboard() {
    window.location.href = '/user';
}

function navigateToLogin() {
    window.location.href = '/login';
}

function refreshPage() {
    window.location.reload();
}
</script>

<?php
$content = ob_get_clean();

// Include the main layout
include __DIR__ . '/layouts/main.php';
?>
