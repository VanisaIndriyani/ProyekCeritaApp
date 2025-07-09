// Notifikasi sederhana
function showNotif(msg, error) {
    let notif = document.getElementById('notif');
    if (!notif) {
        document.body.insertAdjacentHTML('beforeend', '<div id="notif" style="display:none;position:fixed;top:2rem;right:2rem;z-index:2000;padding:1em 2em;border-radius:12px;font-weight:bold;font-size:1.1em;"></div>');
        notif = document.getElementById('notif');
    }
    notif.style.display = 'block';
    notif.style.background = error ? '#ff4f8c' : '#4f8cff';
    notif.style.color = '#fff';
    notif.textContent = msg;
    setTimeout(() => notif.style.display = 'none', 2200);
}

const form = document.getElementById('loginForm');
form.onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());
    try {
        const res = await fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const resJson = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = resJson?.error?.description || 'Login gagal';
            throw new Error(msg);
        }
        // Ambil user dari resJson.user atau resJson.data.user
        const user = resJson.user || (resJson.data && resJson.data.user);
        if (!user) {
            showNotif('Login gagal: User tidak ditemukan', true);
            return;
        }
        // Simpan role ke localStorage
        localStorage.setItem('role', user.role);
        showNotif('Login berhasil! Redirect...');
        setTimeout(() => {
            if (user.role === 'admin') {
                window.location.href = 'admin.html';
            } else {
                window.location.href = 'index.html';
            }
        }, 1200);
    } catch (err) {
        showNotif(err.message || 'Login gagal', true);
    }
}; 