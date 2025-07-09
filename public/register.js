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

const form = document.getElementById('registerForm');
form.onsubmit = async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());
    try {
        const res = await fetch('/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const resJson = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = resJson?.error?.description || 'Gagal register';
            throw new Error(msg);
        }
        showNotif('Register berhasil! Redirect ke login...');
        setTimeout(() => window.location.href = 'login.html', 1500);
    } catch (err) {
        showNotif(err.message || 'Gagal register', true);
    }
}; 