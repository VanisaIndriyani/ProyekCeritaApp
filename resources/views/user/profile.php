<?php
// Get current user and stories from database
use App\Application\Helpers\AuthHelper;
use App\Infrastructure\Persistence\Story\MySQLStoryRepository;

$user = AuthHelper::getCurrentUser();
if (!$user) {
    header('Location: /login');
    exit;
}

$title = 'Profile Setting - Cerita Mahasiswa';
$currentPage = 'profile';
ob_start();
?>
<main class="main-content" style="background:#f6f8fb; min-height:100vh;">
    <div class="container" style="max-width:500px; margin:auto; padding:2em 0;">
        <div class="profile-header" style="margin-bottom:2em; text-align:center;">
            <h1 style="font-size:2em; font-weight:700; color:#3b3b99; margin-bottom:0.2em;">Pengaturan Profil</h1>
            <p style="color:#666;">Edit data profil akun Anda di bawah ini.</p>
        </div>
        <form method="post" action="/user/profile" class="profile-form" style="background:#fff; border-radius:14px; box-shadow:0 2px 12px #0001; padding:2em 2em 1.5em 2em;">
            <div class="form-group" style="margin-bottom:1.2em;">
                <label for="nama" style="font-weight:600;">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" required style="width:100%; padding:0.7em; border-radius:8px; border:1.5px solid #e5e7eb; margin-top:0.3em;">
            </div>
            <div class="form-group" style="margin-bottom:1.2em;">
                <label for="email" style="font-weight:600;">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required style="width:100%; padding:0.7em; border-radius:8px; border:1.5px solid #e5e7eb; margin-top:0.3em;">
            </div>
            <div class="form-group" style="margin-bottom:1.2em;">
                <label for="password" style="font-weight:600;">Password Baru <span style="color:#888; font-weight:400;">(opsional)</span></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Isi jika ingin ganti password" style="width:100%; padding:0.7em; border-radius:8px; border:1.5px solid #e5e7eb; margin-top:0.3em;">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:0.9em; font-size:1.1em; font-weight:700;">Simpan Perubahan</button>
        </form>
    </div>
</main>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
