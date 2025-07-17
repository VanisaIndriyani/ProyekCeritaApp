<?php
$title = $title ?? 'Lupa Password - Cerita Mahasiswa';
$authTitle = $authTitle ?? 'Lupa Password';
$authSubtitle = $authSubtitle ?? 'Masukkan email akunmu untuk mereset password.';

ob_start();
?>
<form id="forgotForm" class="auth-form">
    <div class="form-group">
        <label for="email">Email</label>
        <div class="input-with-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required placeholder="Masukkan email terdaftar">
        </div>
    </div>

    <button type="submit" class="btn-auth">
        <i class="fas fa-paper-plane"></i>
        Kirim Link Reset
    </button>

    <p id="responseMsg" style="margin-top: 1rem; font-size: 0.9rem;"></p>
    
    <p style="margin-top: 1rem; font-size: 0.9rem;">
        <a href="/login" class="auth-link">Kembali ke Login</a>
    </p>
</form>

<script>
document.getElementById('forgotForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const responseMsg = document.getElementById('responseMsg');

    try {
        const res = await fetch('/api/auth/forgot-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });

        const data = await res.json();
        if (res.ok) {
            responseMsg.style.color = 'green';
            responseMsg.textContent = `Link reset berhasil dibuat!`;
        } else {
            responseMsg.style.color = 'red';
            responseMsg.textContent = data.message || 'Gagal mengirim link reset.';
        }
    } catch (err) {
        responseMsg.style.color = 'red';
        responseMsg.textContent = 'Terjadi kesalahan saat mengirim.';
    }
});
</script>
<?php
$content = ob_get_clean();
$footerLinks = '<p>Sudah ingat password? <a href="/login" class="auth-link">Masuk di sini</a></p>';
$additionalScripts = '';
include __DIR__ . '/../layouts/auth.php';
?>