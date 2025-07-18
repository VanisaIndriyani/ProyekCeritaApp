<?php
// Get current user
use App\Application\Helpers\AuthHelper;
use App\Infrastructure\Persistence\Story\MySQLStoryRepository;

$user = AuthHelper::getCurrentUser();
if (!$user) {
    header('Location: /login');
    exit;
}

// Get database connection (similar to other files)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
    $storyRepository = new MySQLStoryRepository($pdo);
    $userStories = $storyRepository->findByUserId($user['id']);
} catch (Exception $e) {
    $userStories = [];
    $errorMessage = 'Gagal memuat cerita: ' . $e->getMessage();
}

$title = 'Kelola Cerita - Cerita Mahasiswa';
$currentPage = 'user';

// Capture page content
ob_start();
?>

<main class="story-manage-main">
    <div class="container">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1 class="page-title">Kelola Cerita</h1>
                <p class="page-subtitle">Kelola semua cerita yang Anda tulis</p>
            </div>
            <div class="header-actions">
                <a href="/user/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tulis Cerita Baru
                </a>
            </div>
        </div>

        <!-- Stories Count -->
        <div class="stats-cards">
            <div class="stat-card stat-total">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div>
                    <div class="stat-number"><?= count($userStories) ?></div>
                    <div class="stat-label">Total Cerita</div>
                </div>
            </div>
            <div class="stat-card stat-published">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-number"><?= count(array_filter($userStories, fn($s) => $s->getStatus() === 'published')) ?></div>
                    <div class="stat-label">Dipublikasi</div>
                </div>
            </div>
            <div class="stat-card stat-pending">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-number"><?= count(array_filter($userStories, fn($s) => $s->getStatus() === 'pending')) ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="stat-card stat-rejected">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-number"><?= count(array_filter($userStories, fn($s) => $s->getStatus() === 'rejected')) ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>
        </div>

        <!-- Stories List -->
        <div class="stories-section">
            <?php if (empty($userStories)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="empty-title">Belum Ada Cerita</h3>
                    <p class="empty-description">Mulai tulis cerita pertama Anda dan bagikan pengalaman menarik!</p>
                    <a href="/user/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tulis Cerita Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="stories-grid">
                    <?php foreach ($userStories as $story): ?>
                        <div class="story-card">
                            <!-- Story Cover -->
                            <div class="story-cover">
                                <?php if ($story->getCoverImage()): ?>
                                    <img src="/uploads/<?= htmlspecialchars($story->getCoverImage()) ?>" alt="Cover" class="cover-image">
                                <?php else: ?>
                                    <div class="cover-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="status-badge status-<?= htmlspecialchars($story->getStatus()) ?>">
                                    <?= ucfirst(htmlspecialchars($story->getStatus())) ?>
                                </div>
                            </div>

                            <!-- Story Content -->
                            <div class="story-content">
                                <h3 class="story-title">
                                    <?= htmlspecialchars($story->getTitle()) ?>
                                </h3>
                                
                                <div class="story-meta">
                                    <?php if ($story->getCategory()): ?>
                                        <span class="story-category">
                                            <i class="fas fa-tag"></i>
                                            <?= htmlspecialchars($story->getCategory()) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="story-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('d M Y', strtotime($story->getCreatedAt())) ?>
                                    </span>
                                </div>

                                <p class="story-excerpt">
                                    <?= htmlspecialchars(substr(strip_tags($story->getContent()), 0, 120)) ?>...
                                </p>
                                <?php if ($story->getStatus() === 'rejected' && $story->getAdminComment()): ?>
                                    <div class="admin-comment-rejected" style="background:#fff1f2;color:#b91c1c;border-radius:8px;padding:10px 14px;margin-top:10px;font-size:0.97em;">
                                        <b>Komentar Admin:</b> <?= htmlspecialchars($story->getAdminComment()) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Story Actions -->
                            <div class="story-actions">
                                <a href="/user/edit/<?= $story->getId() ?>" class="btn btn-outline btn-small">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                
                                <form method="POST" action="/user/delete/<?= $story->getId() ?>" style="display: inline;" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus cerita ini?')">
                                    <button type="submit" class="btn btn-danger btn-small">
                                        <i class="fas fa-trash"></i>
                                        Hapus
                                    </button>
                                </form>
                                
                                <?php if ($story->getStatus() === 'published'): ?>
                                    <a href="/story/<?= $story->getId() ?>" class="btn btn-link btn-small" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                        Lihat
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
/* Tambahan style untuk full PHP version */
.alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 6px;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.stats-summary { display:none; }
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1.5em;
    margin-bottom: 2.5em;
    margin-top: 1.5em;
}
.stat-card {
    display: flex;
    align-items: center;
    gap: 1em;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px #0001;
    padding: 1.2em 1.5em;
    transition: box-shadow 0.2s, transform 0.2s;
    cursor: pointer;
}
.stat-card:hover {
    box-shadow: 0 6px 24px #0002;
    transform: translateY(-2px) scale(1.03);
}
.stat-icon {
    font-size: 2.2em;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
}
.stat-total .stat-icon { background: #e0e7ff; color: #3730a3; }
.stat-published .stat-icon { background: #d1fae5; color: #065f46; }
.stat-pending .stat-icon { background: #dbeafe; color: #1e40af; }
.stat-rejected .stat-icon { background: #fee2e2; color: #991b1b; }
.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #2563eb;
    line-height: 1;
}
.stat-label {
    font-size: 0.97rem;
    color: #6b7280;
    margin-top: 2px;
}
@media (max-width: 600px) {
    .stats-cards { grid-template-columns: 1fr 1fr; }
    .stat-card { padding: 1em; }
    .stat-icon { font-size: 1.5em; width: 36px; height: 36px; }
    .stat-number { font-size: 1.3em; }
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

.story-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.story-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.story-cover {
    position: relative;
    height: 160px;
    background: #f3f4f6;
}

.cover-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cover-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #9ca3af;
    font-size: 2rem;
}

.status-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-published { background: #dcfce7; color: #166534; }
.status-draft { background: #fef3c7; color: #92400e; }
.status-pending { background: #dbeafe; color: #1e40af; }
.status-rejected { background: #fecaca; color: #991b1b; }
.admin-comment-rejected { background: #fff1f2; color: #b91c1c; border-radius: 8px; padding: 10px 14px; margin-top: 10px; font-size: 0.97em; }

.story-content {
    padding: 20px;
}

.story-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 12px;
    line-height: 1.4;
}

.story-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 12px;
    font-size: 0.875rem;
    color: #6b7280;
}

.story-category, .story-date {
    display: flex;
    align-items: center;
    gap: 4px;
}

.story-excerpt {
    color: #4b5563;
    line-height: 1.5;
    margin-bottom: 0;
}

.story-actions {
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.875rem;
}

.btn-outline {
    background: transparent;
    border: 1px solid #d1d5db;
    color: #374151;
}

.btn-outline:hover {
    background: #f9fafb;
}

.btn-danger {
    background: #dc2626;
    color: white;
    border: 1px solid #dc2626;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-link {
    background: transparent;
    color: #2563eb;
    border: none;
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-title {
    font-size: 1.5rem;
    color: #111827;
    margin-bottom: 12px;
}

.empty-description {
    color: #6b7280;
    margin-bottom: 24px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
</style>

<?php
$content = ob_get_clean();

// Include the layout with absolute path
$layoutPath = __DIR__ . '/../layouts/main.php';
if (file_exists($layoutPath)) {
    include $layoutPath;
} else {
    echo $content; // Fallback: just show content without layout
}
?>
