<?php
$title = $title ?? 'Reset Password - Cerita Mahasiswa';
$authTitle = $authTitle ?? 'Reset Password';
$authSubtitle = $authSubtitle ?? 'Masukkan password baru untuk akunmu.';

ob_start();
?>
<form id="resetForm" class="auth-form" method="POST" action="/api/auth/reset-password" onsubmit="return false;">
    <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($resetToken ?? '') ?>">

    <div class="form-group">
        <label for="password">Password Baru</label>
        <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required placeholder="Masukkan password baru">
        </div>
    </div>

    <div class="form-group">
        <label for="confirm">Konfirmasi Password</label>
        <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" id="confirm" name="confirm" required placeholder="Ulangi password baru">
        </div>
    </div>

    <button type="submit" class="btn-auth">
        <i class="fas fa-sync-alt"></i> Simpan Password Baru
    </button>

    <p id="responseMsg" style="margin-top: 1rem; font-size: 0.9rem;"></p>
</form>

<script>
console.log("Reset password page loaded");
const params = new URLSearchParams(window.location.search);
const token = params.get('token');
console.log("TOKEN =", token);

document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');
    if (!token) {
        document.body.innerHTML = '<p style="color:red;">Token tidak valid.</p>';
        return;
    }

    document.getElementById('token').value = token;

    document.getElementById('resetForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm').value;
        const responseMsg = document.getElementById('responseMsg');

        if (password !== confirm) {
            responseMsg.style.color = 'red';
            responseMsg.textContent = 'Password tidak cocok!';
            return;
        }

        try {
            const res = await fetch('/api/auth/reset-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token, password })
            });

            const data = await res.json();
            if (res.ok) {
                responseMsg.style.color = 'green';
                responseMsg.textContent = data.message || 'Password berhasil direset!';
            } else {
                responseMsg.style.color = 'red';
                responseMsg.textContent = data.message || 'Gagal mereset password.';
            }
        } catch (err) {
            responseMsg.style.color = 'red';
            responseMsg.textContent = 'Terjadi kesalahan server.';
        }
    });
});
</script>
<?php
$content = ob_get_clean();
$footerLinks = '<p>Sudah ingat password? <a href="/login" class="auth-link">Masuk di sini</a></p>';
$additionalScripts = '';
include __DIR__ . '/../layouts/auth.php';
?>
