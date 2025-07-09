const API_URL = '/stories';

// Modal logic
const modal = document.getElementById('modal');
const openModalBtn = document.getElementById('openModalBtn');
const closeModalBtn = document.getElementById('closeModalBtn');
const storyForm = document.getElementById('storyForm');

openModalBtn.onclick = () => modal.style.display = 'flex';
closeModalBtn.onclick = () => modal.style.display = 'none';
window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

// Fetch and render stories
async function fetchStories() {
    const res = await fetch(API_URL);
    const data = await res.json();
    renderStories(data.data || []);
}

function renderStories(stories) {
    const list = document.getElementById('stories-list');
    list.innerHTML = '';
    if (!stories.length) {
        list.innerHTML = '<p>Tidak ada cerita.</p>';
        return;
    }
    stories.forEach(story => {
        const card = document.createElement('div');
        card.className = 'story-card';
        card.innerHTML = `
            ${story.coverImage ? `<img src="${story.coverImage}" class="story-cover" alt="cover">` : ''}
            <div class="story-title">${story.title}</div>
            <div class="story-meta">Kategori: ${story.category} | Oleh User ${story.userId} | ${story.createdAt}</div>
            <div class="story-content">${story.content.substring(0, 120)}${story.content.length > 120 ? '...' : ''}</div>
        `;
        list.appendChild(card);
    });
}

// Handle form submit
storyForm.onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(storyForm);
    const data = Object.fromEntries(formData.entries());
    try {
        const res = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const resJson = await res.json().catch(() => ({}));
        if (!res.ok) {
            // Ambil pesan error dari backend jika ada
            const msg = resJson?.error?.description || 'Gagal menambah cerita';
            throw new Error(msg);
        }
        showNotif('Cerita berhasil ditambah!');
        storyForm.reset();
        modal.style.display = 'none';
        fetchStories();
    } catch (err) {
        showNotif(err.message || 'Gagal menambah cerita', true);
    }
};

// Notifikasi sederhana
document.body.insertAdjacentHTML('beforeend', '<div id="notif" style="display:none;position:fixed;top:2rem;right:2rem;z-index:2000;padding:1em 2em;border-radius:12px;font-weight:bold;font-size:1.1em;"></div>');
function showNotif(msg, error) {
    const notif = document.getElementById('notif');
    notif.style.display = 'block';
    notif.style.background = error ? '#ff4f8c' : '#4f8cff';
    notif.style.color = '#fff';
    notif.textContent = msg;
    setTimeout(() => notif.style.display = 'none', 2200);
}

// Initial load
fetchStories();

const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.onclick = function() {
        localStorage.removeItem('username');
        localStorage.removeItem('token');
        window.location.href = 'login.html';
    };
} 