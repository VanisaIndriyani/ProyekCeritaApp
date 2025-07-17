<?php
$title = $title ?? 'Login - Cerita Mahasiswa';
$authTitle = $authTitle ?? 'Masuk ke Akun';
$authSubtitle = $authSubtitle ?? 'Selamat datang kembali! Silakan masuk untuk melanjutkan.';

ob_start();
?>
<form method="POST" action="/login" class="auth-form">
    <div class="form-group">
        <label for="username">Username</label>
        <div class="input-with-icon">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" required placeholder="Masukkan username" autocomplete="username">
        </div>
    </div>
    
    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required placeholder="Masukkan password" autocomplete="current-password">
        </div>
    </div>
    
    <div class="form-group">
        <div style="text-align: right; margin-bottom: 0.5rem;">
            <a href="/forgot-password" style="font-size: 0.9rem; color: #667eea; text-decoration: none;">
                Lupa password?
            </a>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin: 0.5rem 0;">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; margin: 0;">
                <input type="checkbox" id="remember" name="remember" style="margin: 0;">
                <span style="font-size: 0.9rem; color: #6B7280;">Ingat saya</span>
            </label>
            <!-- <a href="#" style="font-size: 0.9rem; color: #667eea; text-decoration: none;">Lupa password?</a> -->
        </div>
    </div>
    
    <button type="submit" class="btn-auth" id="loginBtn">
        <i class="fas fa-sign-in-alt"></i>
        Masuk
    </button>
</form>
<?php
$content = ob_get_clean();

// Use footerLinks from controller or default
$footerLinks = $footerLinks ?? '<p>Belum punya akun? <a href="/register" class="auth-link">Daftar di sini</a></p>';

// No additional scripts needed for PHP form
$additionalScripts = '';

include __DIR__ . '/../layouts/auth.php';
?>
