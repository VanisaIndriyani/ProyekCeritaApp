<?php
// Set page variables for layout
$title = isset($story) ? $story['title'] . ' - Cerita Mahasiswa' : 'Cerita Tidak Ditemukan - Cerita Mahasiswa';
$currentPage = 'story-detail';
$pageType = 'public';
$additionalCSS = ['/story-detail.css'];

// Helper functions
function formatDate($dateString) {
    if (empty($dateString)) return 'Baru saja';
    
    $date = new DateTime($dateString);
    return $date->format('d F Y');
}

function getCategoryName($category) {
    $categories = [
        'akademik' => 'Akademik',
        'karir' => 'Karir',
        'kehidupan' => 'Kehidupan',
        'teknologi' => 'Teknologi'
    ];
    
    return $categories[strtolower($category)] ?? 'Akademik';
}

function getExcerpt($content, $maxLength = 120) {
    if (empty($content)) return 'Tidak ada preview tersedia.';
    if (strlen($content) <= $maxLength) return $content;
    
    $truncated = substr($content, 0, $maxLength);
    $lastSpace = strrpos($truncated, ' ');
    
    return ($lastSpace > 0 ? substr($truncated, 0, $lastSpace) : $truncated) . '...';
}

// Capture page content
ob_start();
?>

<!-- Story Detail Container -->
<div class="story-detail-container">
    <?php if (!isset($story) || empty($story)): ?>
    <!-- Error State -->
    <div class="error-state">
        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem; color: #f56565;"></i>
        <h3>Cerita Tidak Ditemukan</h3>
        <p>Maaf, cerita yang Anda cari tidak dapat ditemukan atau mungkin telah dihapus.</p>
        <div class="error-actions">
            <a href="/" class="btn-primary">
                <i class="fas fa-home"></i>
                Kembali ke Beranda
            </a>
            <a href="/stories" class="btn-secondary">
                <i class="fas fa-list"></i>
                Lihat Semua Cerita
            </a>
        </div>
    </div>
    <?php else: ?>
    <!-- Story Content -->
    <div class="story-content-wrapper">
        <!-- Story Header -->
        <div class="story-header">
            <div class="story-breadcrumb">
                <a href="/" class="breadcrumb-link">Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <a href="/stories" class="breadcrumb-link">Cerita</a>
                <i class="fas fa-chevron-right"></i>
                <span class="breadcrumb-current"><?= htmlspecialchars($story['title']) ?></span>
            </div>

            <div class="story-meta">
                <?php 
                $categoryClass = 'badge-' . strtolower($story['category'] ?? 'akademik');
                $categoryName = getCategoryName($story['category'] ?? 'akademik');
                ?>
                <span class="category-badge <?= $categoryClass ?>"><?= $categoryName ?></span>
                <span class="read-time">
                    <i class="fas fa-clock"></i>
                    <?= $story['readTime'] ?? '5 menit baca' ?>
                </span>
            </div>

            <h1 class="story-title"><?= htmlspecialchars($story['title']) ?></h1>

            <div class="story-author-info">
                <?php $authorInitial = strtoupper(substr($story['userName'] ?? 'U', 0, 1)); ?>
                <div class="author-avatar"><?= $authorInitial ?></div>
                <div class="author-details">
                    <span class="author-name">Oleh <?= htmlspecialchars($story['userName'] ?? 'Anonymous') ?></span>
                    <span class="publish-date">Dipublikasikan <?= formatDate($story['createdAt'] ?? '') ?></span>
                </div>
            </div>

            <div class="story-actions">
                <button class="action-btn" onclick="copyStoryURL()">
                    <i class="fas fa-share-alt"></i>
                    Bagikan
                </button>
                <button class="action-btn" onclick="printStory()">
                    <i class="fas fa-print"></i>
                    Cetak
                </button>
            </div>
        </div>

        <!-- Story Content -->
        <div class="story-content">
            <div class="story-body">
                <?= nl2br(htmlspecialchars($story['content'])) ?>
            </div>
        </div>

        <!-- Story Footer -->
        <div class="story-footer">
            <div class="navigation-actions">
                <a href="/stories" class="nav-btn">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar Cerita
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Related Stories Section -->
<?php if (isset($relatedStories) && !empty($relatedStories)): ?>
<section class="related-stories">
    <div class="related-container">
        <div class="related-header">
            <h3 class="related-title">Cerita Terkait</h3>
            <p class="related-subtitle">Temukan cerita menarik lainnya yang serupa</p>
        </div>
        
        <div class="stories-grid stories-grid-3">
            <?php foreach ($relatedStories as $relatedStory): ?>
                <?php 
                $relatedCategoryClass = 'badge-' . strtolower($relatedStory['category'] ?? 'akademik');
                $relatedCategoryName = getCategoryName($relatedStory['category'] ?? 'akademik');
                $relatedAuthorInitial = strtoupper(substr($relatedStory['userName'] ?? 'U', 0, 1));
                $relatedFormattedDate = formatDate($relatedStory['createdAt'] ?? '');
                $relatedExcerpt = getExcerpt($relatedStory['content'] ?? '', 120);
                $relatedReadTime = $relatedStory['readTime'] ?? '5 menit baca';
                ?>
                <article class="story-card" onclick="viewRelatedStory(<?= $relatedStory['id'] ?>)">
                    <div class="story-card-content">
                        <div class="story-card-header">
                            <div class="story-card-meta">
                                <span class="category-badge <?= $relatedCategoryClass ?>"><?= htmlspecialchars($relatedCategoryName) ?></span>
                                <span class="read-time">
                                    <i class="fas fa-clock"></i>
                                    <?= htmlspecialchars($relatedReadTime) ?>
                                </span>
                            </div>
                            <h4 class="story-card-title"><?= htmlspecialchars($relatedStory['title']) ?></h4>
                            <p class="story-card-excerpt"><?= htmlspecialchars($relatedExcerpt) ?></p>
                        </div>
                        <div class="story-card-footer">
                            <div class="story-card-author">
                                <div class="author-avatar"><?= htmlspecialchars($relatedAuthorInitial) ?></div>
                                <span><?= htmlspecialchars($relatedStory['userName'] ?? 'Anonymous') ?></span>
                            </div>
                            <div class="story-date"><?= htmlspecialchars($relatedFormattedDate) ?></div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <div class="related-footer">
            <a href="/stories" class="btn btn-outline">
                <i class="fas fa-list"></i>
                Lihat Semua Cerita
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" style="display: none;" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- JavaScript for Story Detail Page -->
<script>
// Back to Top functionality
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
        backToTop.style.display = 'flex';
    } else {
        backToTop.style.display = 'none';
    }
});

// Navigate to related story
function viewRelatedStory(storyId) {
    if (!storyId) {
        console.error('Story ID is required');
        return;
    }
    window.location.href = `/story-detail?id=${storyId}`;
}

// Copy story URL to clipboard
function copyStoryURL() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // Show success message
        if (typeof showSuccess === 'function') {
            showSuccess('Link cerita berhasil disalin!');
        } else {
            alert('Link cerita berhasil disalin!');
        }
    }, function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = window.location.href;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        if (typeof showSuccess === 'function') {
            showSuccess('Link cerita berhasil disalin!');
        } else {
            alert('Link cerita berhasil disalin!');
        }
    });
}

// Print story
function printStory() {
    window.print();
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Story detail page loaded');
    
    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>

<?php
// End content capture and render using layout
$content = ob_get_clean();

// Include the layout
include __DIR__ . '/../layouts/main.php';
?>
