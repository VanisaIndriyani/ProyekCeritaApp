<?php
$title = $title ?? 'Daftar - Cerita Mahasiswa';
$authTitle = $authTitle ?? 'Buat Akun Baru';
$authSubtitle = $authSubtitle ?? 'Bergabunglah dengan komunitas Cerita Mahasiswa dan bagikan pengalamanmu!';

ob_start();
?>
<form method="POST" action="/register" class="auth-form">
    <div class="form-group">
        <label for="nama">Nama Lengkap</label>
        <div class="input-with-icon">
            <i class="fas fa-user-circle"></i>
            <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap" autocomplete="name">
        </div>
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <div class="input-with-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required placeholder="Masukkan email aktif" autocomplete="email">
        </div>
    </div>
    
    <div class="form-group">
        <label for="username">Username</label>
        <div class="input-with-icon">
            <i class="fas fa-at"></i>
            <input type="text" id="username" name="username" required placeholder="Pilih username unik" autocomplete="username">
        </div>
    </div>
    
    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required placeholder="Buat password yang kuat" autocomplete="new-password" minlength="6">
        </div>
        <small style="color: #6B7280; font-size: 0.8rem; margin-left: 0.25rem;">Minimal 6 karakter</small>
    </div>
    
    <div class="form-group">
        <label for="confirm-password">Konfirmasi Password</label>
        <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="confirm-password" name="confirm_password" required placeholder="Ulangi password" autocomplete="new-password">
        </div>
    </div>
    
    <div class="form-group">
        <label style="display: flex; align-items: flex-start; gap: 0.75rem; font-weight: normal; margin: 0; font-size: 0.9rem; line-height: 1.4;">
            <input type="checkbox" id="terms" name="terms" required style="margin-top: 0.1rem;">
            <span style="color: #6B7280;">
                Saya menyetujui <a href="#" style="color: #667eea; text-decoration: none;">Syarat & Ketentuan</a> 
                dan <a href="#" style="color: #667eea; text-decoration: none;">Kebijakan Privasi</a>
            </span>
        </label>
    </div>
    
    <button type="submit" class="btn-auth" id="registerBtn">
        <i class="fas fa-user-plus"></i>
        Daftar Akun
    </button>
</form>
<?php
$content = ob_get_clean();

// Use footerLinks from controller or default
$footerLinks = $footerLinks ?? '<p>Sudah punya akun? <a href="/login" class="auth-link">Masuk di sini</a></p>';

// No additional scripts needed for PHP form
$additionalScripts = '';

include __DIR__ . '/../layouts/auth.php';
?>
