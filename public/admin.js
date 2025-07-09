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

// Proteksi admin (dummy: cek localStorage role)
const role = localStorage.getItem('role');
if (role !== 'admin') {
    window.location.href = 'login.html';
}

// Logout
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.onclick = function() {
        localStorage.clear();
        window.location.href = 'login.html';
    };
}

// Fetch & render stories
async function fetchStories() {
    const res = await fetch('/admin/stories');
    const data = await res.json();
    renderStories(data.data || []);
}
function renderStories(stories) {
    const tbody = document.querySelector('#storiesTable tbody');
    tbody.innerHTML = '';
    stories.forEach(story => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${story.id}</td>
            <td>${story.title}</td>
            <td>${story.userId}</td>
            <td>${story.status || 'pending'}</td>
            <td>
                ${story.status !== 'published' ? `<button onclick="publishStory(${story.id})">Publish</button>` : ''}
                <button onclick="deleteStory(${story.id})">Hapus</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}
window.publishStory = async function(id) {
    if (!confirm('Publish cerita ini?')) return;
    const res = await fetch(`/admin/stories/${id}/publish`, {method:'POST'});
    if (res.ok) {
        showNotif('Cerita dipublish!');
        fetchStories();
    } else {
        showNotif('Gagal publish cerita', true);
    }
}
window.deleteStory = async function(id) {
    if (!confirm('Hapus cerita ini?')) return;
    const res = await fetch(`/admin/stories/${id}`, {method:'DELETE'});
    if (res.ok) {
        showNotif('Cerita dihapus!');
        fetchStories();
    } else {
        showNotif('Gagal hapus cerita', true);
    }
}

// Fetch & render users
async function fetchUsers() {
    const res = await fetch('/admin/users');
    const data = await res.json();
    renderUsers(data.data || []);
}
function renderUsers(users) {
    const tbody = document.querySelector('#usersTable tbody');
    tbody.innerHTML = '';
    users.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.firstName || ''}</td>
            <td>${user.lastName || ''}</td>
            <td>${user.role || 'user'}</td>
            <td><button onclick="deleteUser(${user.id})">Hapus</button></td>
        `;
        tbody.appendChild(tr);
    });
}
window.deleteUser = async function(id) {
    if (!confirm('Hapus user ini?')) return;
    const res = await fetch(`/admin/users/${id}`, {method:'DELETE'});
    if (res.ok) {
        showNotif('User dihapus!');
        fetchUsers();
    } else {
        showNotif('Gagal hapus user', true);
    }
}

// Initial load
fetchStories();
fetchUsers(); 