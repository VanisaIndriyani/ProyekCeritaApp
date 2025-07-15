<?php
// Get current user and stories from database
use App\Application\Helpers\AuthHelper;
use App\Infrastructure\Persistence\Story\MySQLStoryRepository;

$user = AuthHelper::getCurrentUser();
if (!$user) {
    header('Location: /login');
    exit;
}

// Get database connection and stories
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
    $storyRepository = new MySQLStoryRepository($pdo);
    $userStories = $storyRepository->findByUserId($user['id']);
} catch (Exception $e) {
    $userStories = [];
    $errorMessage = 'Gagal memuat cerita: ' . $e->getMessage();
}

$title = 'Kelola Cerita - Cerita Mahasiswa';
$additionalCSS = ['/story-manage.css'];
$additionalJS = []; // Remove JavaScript dependency
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

        <!-- Filter & Search -->
        <div class="toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari cerita..." autocomplete="off">
            </div>
            <div class="filter-group">
                <select id="statusFilter" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Menunggu Review</option>
                    <option value="published">Dipublikasi</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <select id="categoryFilter" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <option value="akademik">Akademik</option>
                    <option value="karir">Karir</option>
                    <option value="kehidupan">Kehidupan</option>
                    <option value="teknologi">Teknologi</option>
                    <option value="organisasi">Organisasi</option>
                    <option value="magang">Magang</option>
                    <option value="kompetisi">Kompetisi</option>
                    <option value="wisuda">Wisuda</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
        </div>

        <!-- Stories Table -->
        <div class="content-card">
            <div class="stories-table-container">
                <table class="stories-table" id="storiesTable">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="storiesTableBody">
                        <?php if (empty($userStories)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    <div class="empty-icon" style="font-size: 3rem; color: #d1d5db; margin-bottom: 16px;">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <h3 style="margin-bottom: 8px;">Belum ada cerita</h3>
                                    <p style="color: #6b7280; margin-bottom: 20px;">Mulai menulis cerita pertama Anda dan bagikan pengalaman kepada sesama mahasiswa</p>
                                    <a href="/user/create" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Tulis Cerita Pertama
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($userStories as $story): ?>
                                <tr data-id="<?= $story->getId() ?>">
                                    <td>
                                        <div class="story-title-cell">
                                            <strong><?= htmlspecialchars($story->getTitle()) ?></strong>
                                            <p class="story-excerpt">
                                                <?= htmlspecialchars(substr(strip_tags($story->getContent()), 0, 100)) ?>...
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($story->getCategory()): ?>
                                            <span class="category-badge">
                                                <?= htmlspecialchars($story->getCategory()) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= htmlspecialchars($story->getStatus()) ?>">
                                            <?= ucfirst(htmlspecialchars($story->getStatus())) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-text">
                                            <?= date('d M Y', strtotime($story->getCreatedAt())) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-text">
                                            <?= $story->getUpdatedAt() ? date('d M Y', strtotime($story->getUpdatedAt())) : '-' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/user/edit/<?= $story->getId() ?>" class="btn btn-small btn-outline" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="/user/delete/<?= $story->getId() ?>" style="display: inline;" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus cerita ini?')">
                                                <button type="submit" class="btn btn-small btn-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php if ($story->getStatus() === 'published'): ?>
                                                <a href="/story/<?= $story->getId() ?>" class="btn btn-small btn-link" title="Lihat" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" id="paginationContainer" style="display: none;">
            <div class="pagination" id="pagination">
                <!-- Pagination will be generated here -->
            </div>
        </div>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModalOverlay" style="display: none;">
    <div class="modal delete-modal">
        <div class="modal-header">
            <h3 class="modal-title">Konfirmasi Hapus</h3>
            <button class="modal-close" id="closeDeleteModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="delete-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Apakah Anda yakin ingin menghapus cerita "<span id="deleteStoryTitle"></span>"?</p>
                <p class="warning-text">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelDelete">Batal</button>
            <button class="btn btn-danger" id="confirmDelete">
                <i class="fas fa-trash-alt"></i>
                Hapus Cerita
            </button>
        </div>
    </div>
</div>

<?php
// End content capture and render using layout
$content = ob_get_clean();

// Include the layout
include __DIR__ . '/../layouts/main.php';
?>
