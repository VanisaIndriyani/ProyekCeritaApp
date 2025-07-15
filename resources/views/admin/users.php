<?php
$title = 'Kelola User - Admin Cerita Mahasiswa';
$currentPage = 'admin-users';

// Ambil data filter
$search = $_GET['search'] ?? '';
$filterRole = $_GET['role'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Query ke database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];
    if ($search) {
        $sql .= " AND (nama LIKE :search OR username LIKE :search OR email LIKE :search)";
        $params['search'] = "%$search%";
    }
    if ($filterRole) {
        $sql .= " AND role = :role";
        $params['role'] = $filterRole;
    }
    if ($filterStatus) {
        $sql .= " AND status = :status";
        $params['status'] = $filterStatus;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}

ob_start();
?>
<main class="main-content" style="background:#f6f8fb; min-height:100vh;">
    <div class="container" style="max-width:1100px; margin:auto; padding:2em 0;">
        <div class="page-header" style="margin-bottom:2.5em;">
            <h1 class="page-title" style="font-size:2.2em; font-weight:800; color:#2d2d6a; margin-bottom:0.2em; letter-spacing:-1px;">Kelola User</h1>
            <p class="page-subtitle" style="color:#666; font-size:1.1em; margin-bottom:0.5em;">Lihat, cari, dan kelola seluruh user platform Cerita Mahasiswa.</p>
        </div>
        <form class="admin-user-filter" method="get" action="" style="display:flex; gap:1em; margin-bottom:1.5em; flex-wrap:wrap;">
            <input type="text" name="search" class="filter-input" placeholder="Cari nama, username, email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex:2; min-width:200px;">
            <select name="role" class="filter-select" style="flex:1; min-width:120px;">
                <option value="">Semua Role</option>
                <option value="admin" <?= ($_GET['role'] ?? '')==='admin'?'selected':'' ?>>Admin</option>
                <option value="user" <?= ($_GET['role'] ?? '')==='user'?'selected':'' ?>>User</option>
            </select>
            <select name="status" class="filter-select" style="flex:1; min-width:120px;">
                <option value="">Semua Status</option>
                <option value="active" <?= ($_GET['status'] ?? '')==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= ($_GET['status'] ?? '')==='inactive'?'selected':'' ?>>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding:0.7em 2em; font-size:1em;"> <i class="fas fa-search"></i> Cari</button>
        </form>
        <div class="admin-user-table-card" style="background:#fff; border-radius:14px; box-shadow:0 2px 12px #0001; padding:1.5em 1.5em 1em 1.5em;">
            <table class="admin-user-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f3f4f6;">
                        <th style="padding:0.7em 0.5em;">ID</th>
                        <th style="padding:0.7em 0.5em;">Nama</th>
                        <th style="padding:0.7em 0.5em;">Username</th>
                        <th style="padding:0.7em 0.5em;">Email</th>
                        <th style="padding:0.7em 0.5em;">Role</th>
                        <th style="padding:0.7em 0.5em;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" style="text-align:center;color:#888;">Tidak ada user ditemukan.</td></tr>
                    <?php else: foreach ($users as $user): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['nama'] ?? '-') ?></td>
                        <td>@<?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                        <td>
                            <span style="padding:0.3em 1em; border-radius:8px; font-weight:600; font-size:0.98em; background:<?= $user['role']==='admin'?'#a78bfa':'#f3f4f6' ?>; color:<?= $user['role']==='admin'?'#fff':'#222' ?>;">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span style="padding:0.2em 0.8em; border-radius:8px; font-weight:600; font-size:0.95em; background:#e0f2fe; color:#0369a1;">
                                <?= (isset($user['status']) && $user['status']) ? 'Active' : 'Inactive' ?>
                            </span>
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