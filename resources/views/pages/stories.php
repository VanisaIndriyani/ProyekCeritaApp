<?php
// Set page variables for layout
$title = 'Semua Cerita - Cerita Mahasiswa';
$currentPage = 'stories';
$pageType = 'public';
$additionalCSS = ['/stories-list.css'];

// Get data from controller (passed through render)
$stories = $stories ?? [];
$totalStories = $totalStories ?? 0;
$currentPageNum = $currentPageNum ?? 1;
$totalPages = $totalPages ?? 1;
$search = $search ?? '';
$category = $category ?? '';
$sort = $sort ?? 'newest';
$hasNextPage = $hasNextPage ?? false;
$hasPrevPage = $hasPrevPage ?? false;
$categories = $categories ?? [
    'akademik' => 'Akademik',
    'karir' => 'Karir', 
    'kehidupan' => 'Kehidupan',
    'teknologi' => 'Teknologi'
];

// Helper functions
function formatDate($dateString) {
    if (!$dateString) return "Baru saja";
    
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diffTime = $now->getTimestamp() - $date->getTimestamp();
    $diffDays = floor($diffTime / (60 * 60 * 24));
    
    if ($diffDays === 0) return "Hari ini";
    if ($diffDays === 1) return "Kemarin";
    if ($diffDays < 7) return $diffDays . " hari lalu";
    if ($diffDays < 30) return floor($diffDays / 7) . " minggu lalu";
    
    return $date->format('d F Y');
}

function getExcerpt($content, $maxLength = 120) {
    if (!$content) return "Tidak ada preview tersedia.";
    if (strlen($content) <= $maxLength) return $content;
    
    $truncated = substr($content, 0, $maxLength);
    $lastSpace = strrpos($truncated, " ");
    
    return ($lastSpace > 0 ? substr($truncated, 0, $lastSpace) : $truncated) . "...";
}

function getCategoryName($category, $categories) {
    return $categories[$category] ?? 'Akademik';
}

// Capture page content
ob_start();
?>

<!-- Page Header -->
<section class="page-header">
    <div class="header-container">
        <h1 class="page-title">Semua Cerita</h1>
        <p class="page-subtitle">Jelajahi berbagai pengalaman dan inspirasi dari mahasiswa Indonesia</p>
    </div>
</section>

<!-- Filter and Search Section -->
<section class="filter-search-section">
    <div class="filter-container">
        <!-- Search and Filter Form -->
        <form method="GET" action="/stories" class="filter-form">
            <!-- Search Bar -->
            <div class="search-wrapper">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Cari cerita berdasarkan judul atau konten..." 
                           class="search-input">
                    <button type="submit" class="search-button">Cari</button>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-wrapper">
                <div class="filter-group">
                    <label class="filter-label">Kategori:</label>
                    <select class="filter-select" name="category">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $key => $name): ?>
                            <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Urutkan:</label>
                    <select class="filter-select" name="sort">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Terlama</option>
                        <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Judul A-Z</option>
                    </select>
                </div>

                <button type="submit" class="filter-apply-btn">
                    <i class="fas fa-filter"></i>
                    Terapkan Filter
                </button>

                <?php if ($search || $category): ?>
                <a href="/stories" class="clear-filters-btn">
                    <i class="fas fa-times"></i>
                    Hapus Filter
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<!-- Active Filters Display -->
<?php if ($search || $category): ?>
<section class="active-filters">
    <div class="active-filters-container">
        <span class="active-filters-label">Filter aktif:</span>
        <div class="active-filters-list">
            <?php if ($search): ?>
                <span class="active-filter-tag">
                    Pencarian: "<?= htmlspecialchars($search) ?>"
                    <a href="<?= '/stories?' . http_build_query(array_filter(['category' => $category, 'sort' => $sort !== 'newest' ? $sort : null])) ?>">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
            <?php endif; ?>
            <?php if ($category): ?>
                <span class="active-filter-tag">
                    Kategori: <?= getCategoryName($category, $categories) ?>
                    <a href="<?= '/stories?' . http_build_query(array_filter(['search' => $search, 'sort' => $sort !== 'newest' ? $sort : null])) ?>">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Stories Results -->
<section class="stories-results">
    <div class="results-container">
        <!-- Results Header -->
        <div class="results-header">
            <div class="results-info">
                <span class="results-count">
                    <?php if ($totalStories === 0): ?>
                        Tidak ada cerita yang ditemukan
                    <?php else: ?>
                        Menampilkan <?= count($stories) ?> dari <?= $totalStories ?> cerita
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <?php if (empty($stories)): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
            <h3>Tidak Ada Cerita</h3>
            <p>
                <?php if ($search || $category): ?>
                    Tidak ada cerita yang sesuai dengan kriteria pencarian Anda. Coba ubah filter atau kata kunci pencarian.
                <?php else: ?>
                    Belum ada cerita yang tersedia. Jadilah yang pertama membagikan pengalaman Anda!
                <?php endif; ?>
            </p>
            <a href="/" class="cta-button">
                <i class="fas fa-home"></i>
                Kembali ke Beranda
            </a>
        </div>
        <?php else: ?>
        <!-- Stories Grid -->
        <div class="stories-grid">
            <?php foreach ($stories as $story): ?>
                <?php 
                // Debug: uncomment to see what category data we're getting
                // echo "<!-- DEBUG: Story ID: {$story['id']}, Category: '{$story['category']}' -->";
                
                $categoryClass = 'badge-' . strtolower($story['category'] ?? 'akademik');
                $categoryName = getCategoryName($story['category'] ?? 'akademik', $categories);
                $authorInitial = strtoupper(substr($story['userName'] ?? $story['author'] ?? 'U', 0, 1));
                $formattedDate = formatDate($story['createdAt'] ?? '');
                $excerpt = getExcerpt($story['content'] ?? '', 120);
                ?>
                <div class="story-card" style="cursor: pointer;" data-href="/story-detail?id=<?= $story['id'] ?>">
                    <div class="story-card-content">
                        <div class="story-card-header">
                            <div class="story-card-meta">
                                <span class="category-badge <?= $categoryClass ?>"><?= $categoryName ?></span>
                                <span class="read-time"><?= $story['readTime'] ?? '5 menit baca' ?></span>
                            </div>
                            <h3 class="story-card-title"><?= htmlspecialchars($story['title'] ?? 'Tanpa Judul') ?></h3>
                            <p class="story-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                        </div>
                        <div class="story-card-footer">
                            <div class="story-card-author">
                                <div class="author-avatar"><?= $authorInitial ?></div>
                                <span>Oleh <?= htmlspecialchars($story['userName'] ?? $story['author'] ?? 'Anonymous') ?></span>
                            </div>
                            <div class="read-time"><?= $formattedDate ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-section">
            <div class="pagination">
                <?php if ($hasPrevPage): ?>
                    <a href="<?= '/stories?' . http_build_query(array_filter(['search' => $search, 'category' => $category, 'sort' => $sort !== 'newest' ? $sort : null, 'page' => $currentPageNum - 1])) ?>" 
                       class="pagination-btn pagination-prev">
                        <i class="fas fa-chevron-left"></i>
                        Sebelumnya
                    </a>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php 
                $startPage = max(1, $currentPageNum - 2);
                $endPage = min($totalPages, $currentPageNum + 2);
                
                if ($startPage > 1): ?>
                    <a href="<?= '/stories?' . http_build_query(array_filter(['search' => $search, 'category' => $category, 'sort' => $sort !== 'newest' ? $sort : null, 'page' => 1])) ?>" 
                       class="pagination-btn">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i == $currentPageNum): ?>
                        <span class="pagination-btn pagination-current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= '/stories?' . http_build_query(array_filter(['search' => $search, 'category' => $category, 'sort' => $sort !== 'newest' ? $sort : null, 'page' => $i])) ?>" 
                           class="pagination-btn"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="<?= '/stories?' . http_build_query(array_filter(['search' => $search, 'category' => $category, 'sort' => $sort !== 'newest' ? $sort : null, 'page' => $totalPages])) ?>" 
                       class="pagination-btn"><?= $totalPages ?></a>
                <?php endif; ?>

                <?php if ($hasNextPage): ?>
                    <a href="<?= '/stories?' . http_build_query(array_filter(['search' => $search, 'category' => $category, 'sort' => $sort !== 'newest' ? $sort : null, 'page' => $currentPageNum + 1])) ?>" 
                       class="pagination-btn pagination-next">
                        Selanjutnya
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
// End content capture and render using layout
$content = ob_get_clean();

// Include the layout
include __DIR__ . '/../layouts/main.php';
?>

<script>
// Add click handlers for story cards
document.addEventListener('DOMContentLoaded', function() {
    // Handle story card clicks
    document.querySelectorAll('.story-card[data-href]').forEach(card => {
        card.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
        
        // Add hover effect
        card.style.cursor = 'pointer';
    });
});
</script>
