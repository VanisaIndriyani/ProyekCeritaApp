<?php
$title = 'Kelola Cerita - Admin Cerita Mahasiswa';
$currentPage = 'admin-stories';

// Ambil filter dari GET
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Koneksi DB
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
} catch (Exception $e) {
    die('Gagal koneksi database');
}

// Approve story (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $approveId = (int)$_POST['approve_id'];
    $stmt = $pdo->prepare("UPDATE stories SET status = 'published' WHERE id = ?");
    $stmt->execute([$approveId]);
    header("Location: /admin/stories");
    exit;
}

// Query ke database
$stmt = $pdo->query("SELECT s.*, u.nama as userName FROM stories s LEFT JOIN users u ON s.userId = u.id ORDER BY s.created_at DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kelas Story sederhana
class Story {
    private $data;
    public function __construct($row) { $this->data = $row; }
    public function getId() { return $this->data['id'] ?? 0; }
    public function getTitle() { return $this->data['title'] ?? '(Tanpa Judul)'; }
    public function getUserName() { return $this->data['userName'] ?? '(Anonim)'; }
    public function getCategory() { return $this->data['category'] ?? '-'; }
    public function getStatus() { return $this->data['status'] ?? 'draft'; }
    public function getCreatedAt() { return $this->data['created_at'] ?? date('Y-m-d'); }
}

$stories = array_map(fn($row) => new Story($row), $data);

// Filter pencarian dan status
if (!empty($search)) {
    $stories = array_filter($stories, function($story) use ($search) {
        return stripos($story->getTitle(), $search) !== false ||
               stripos($story->getUserName(), $search) !== false ||
               stripos($story->getCategory(), $search) !== false;
    });
}
if (!empty($filterStatus)) {
    $stories = array_filter($stories, function($story) use ($filterStatus) {
        return $story->getStatus() === $filterStatus;
    });
}

// Tampilan
ob_start();
?>
<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Kelola Cerita</h1>
            <p class="page-subtitle">Lihat, cari, filter, dan approve cerita user untuk dipublish.</p>
        </div>
        <form class="admin-user-filter" method="get" action="">
            <input type="text" name="search" class="filter-input" placeholder="Cari judul, penulis, kategori..." value="<?= htmlspecialchars($search) ?>">
            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success" style="margin:1em 0 1.5em 0; padding:1em 1.5em; background:#e0fbe0; color:#166534; border-radius:8px; font-weight:600; border:1px solid #bbf7d0;">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="margin:1em 0 1.5em 0; padding:1em 1.5em; background:#fee2e2; color:#991b1b; border-radius:8px; font-weight:600; border:1px solid #fecaca;">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="admin-user-table-card">
            <table class="admin-user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stories)): ?>
                        <tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada cerita ditemukan.</td></tr>
                    <?php else: foreach ($stories as $story): ?>
                    <tr>
                        <td><?= htmlspecialchars($story->getId()) ?></td>
                        <td><?= htmlspecialchars($story->getTitle()) ?></td>
                        <td><?= htmlspecialchars($story->getUserName()) ?></td>
                        <td><?= htmlspecialchars($story->getCategory()) ?></td>
                        <td><span class="status-badge status-<?= htmlspecialchars($story->getStatus()) ?>"><?= ucfirst($story->getStatus()) ?></span></td>
                        <td><?= date('d M Y', strtotime($story->getCreatedAt())) ?></td>
                        <td style="display:flex;gap:0.3em;">
                            <?php if ($story->getStatus() === 'pending'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="approve_id" value="<?= htmlspecialchars($story->getId()) ?>">
                                <button type="submit" class="btn-table-action" title="Approve"><i class="fas fa-check"></i></button>
                            </form>
                            <?php endif; ?>
                            <a href="/admin/stories/show/<?= htmlspecialchars($story->getId()) ?>" class="btn-table-action" title="Lihat Detail (Admin)" target="_blank"><i class="fas fa-eye"></i></a>
                            <button class="btn-table-action" title="Hapus" onclick="return confirm('Yakin hapus cerita ini?')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>
