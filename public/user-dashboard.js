// User Dashboard JavaScript
class UserDashboard {
    constructor() {
        this.currentStoryId = null;
        this.stories = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDashboardData();
    }

    setupEventListeners() {
        // Create story buttons
        const createStoryBtn = document.getElementById('createStoryBtn');
        const createFirstStoryBtn = document.getElementById('createFirstStoryBtn');
        
        if (createStoryBtn) {
            createStoryBtn.addEventListener('click', () => this.openStoryModal());
        }
        if (createFirstStoryBtn) {
            createFirstStoryBtn.addEventListener('click', () => this.openStoryModal());
        }

        // Modal controls
        const closeModal = document.getElementById('closeModal');
        const closeDeleteModal = document.getElementById('closeDeleteModal');
        
        if (closeModal) {
            closeModal.addEventListener('click', () => this.closeStoryModal());
        }
        if (closeDeleteModal) {
            closeDeleteModal.addEventListener('click', () => this.closeDeleteModal());
        }

        // Form submission
        const storyForm = document.getElementById('storyForm');
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        
        if (storyForm) {
            storyForm.addEventListener('submit', (e) => this.handleStorySubmit(e));
        }
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', () => this.saveDraft());
        }

        // Delete confirmation
        const confirmDelete = document.getElementById('confirmDelete');
        const cancelDelete = document.getElementById('cancelDelete');
        
        if (confirmDelete) {
            confirmDelete.addEventListener('click', () => this.deleteStory());
        }
        if (cancelDelete) {
            cancelDelete.addEventListener('click', () => this.closeDeleteModal());
        }

        // Filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => this.filterStories(e.target.value));
        }

        // Image upload
        const coverImage = document.getElementById('coverImage');
        const removeImage = document.getElementById('removeImage');
        
        if (coverImage) {
            coverImage.addEventListener('change', (e) => this.handleImageUpload(e));
        }
        if (removeImage) {
            removeImage.addEventListener('click', () => this.removeImage());
        }

        // Close modal on backdrop click
        const storyModal = document.getElementById('storyModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (storyModal) {
            storyModal.addEventListener('click', (e) => {
                if (e.target === e.currentTarget) this.closeStoryModal();
            });
        }
        if (deleteModal) {
            deleteModal.addEventListener('click', (e) => {
                if (e.target === e.currentTarget) this.closeDeleteModal();
            });
        }
    }

    async loadDashboardData() {
        try {
            await this.loadUserStats();
            await this.loadUserStories();
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showError('Gagal memuat data dashboard');
        }
    }

    async loadUserStats() {
        try {
            const response = await fetch('/api/user/stats', {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`
                }
            });

            if (response.ok) {
                const result = await response.json();
                const stats = result.data || result;
                this.updateStatsDisplay(stats);
            } else {
                // Use default stats if API fails
                this.updateStatsDisplay({
                    totalStories: 0,
                    totalViews: 0,
                    totalLikes: 0,
                    totalComments: 0
                });
            }
        } catch (error) {
            console.error('Error loading stats:', error);
            // Use default stats if API fails
            this.updateStatsDisplay({
                totalStories: 0,
                totalViews: 0,
                totalLikes: 0,
                totalComments: 0
            });
        }
    }

    updateStatsDisplay(stats) {
        document.getElementById('totalStories').textContent = stats.totalStories || 0;
        document.getElementById('totalViews').textContent = stats.totalViews || 0;
        document.getElementById('totalLikes').textContent = stats.totalLikes || 0;
        document.getElementById('totalComments').textContent = stats.totalComments || 0;
    }

    async loadUserStories() {
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const storiesGrid = document.getElementById('storiesGrid');

        loadingState.style.display = 'block';
        emptyState.style.display = 'none';
        storiesGrid.innerHTML = '';

        try {
            const response = await fetch('/api/user/stories', {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.stories = result.data || result;
                this.renderStories(this.stories);
            } else {
                console.error('Failed to load stories:', response.status);
                this.stories = [];
                this.showError('Gagal memuat cerita');
            }
        } catch (error) {
            console.error('Error loading stories:', error);
            this.stories = [];
            this.showError('Gagal memuat cerita');
        } finally {
            loadingState.style.display = 'none';
            if (this.stories.length === 0) {
                emptyState.style.display = 'block';
            }
        }
    }

    renderStories(stories) {
        const storiesGrid = document.getElementById('storiesGrid');
        const emptyState = document.getElementById('emptyState');

        if (stories.length === 0) {
            storiesGrid.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        storiesGrid.innerHTML = stories.map(story => this.createStoryCard(story)).join('');
    }

    createStoryCard(story) {
        const statusClass = `status-${story.status}`;
        const statusText = {
            'draft': 'Draft',
            'published': 'Dipublikasi',
            'rejected': 'Ditolak'
        }[story.status] || story.status;

        return `
            <div class="story-card">
                <div class="story-header">
                    <div>
                        <h3 class="story-title">${this.escapeHtml(story.title)}</h3>
                        <span class="story-category">${this.escapeHtml(story.category)}</span>
                    </div>
                </div>
                
                <div class="story-excerpt">${this.escapeHtml(story.excerpt || story.content.substring(0, 150) + '...')}</div>
                
                <div class="story-meta">
                    <div class="story-date">
                        <i class="fas fa-calendar"></i>
                        ${this.formatDate(story.created_at)}
                    </div>
                    <div class="story-stats">
                        <div class="story-stat">
                            <i class="fas fa-eye"></i>
                            ${story.views || 0}
                        </div>
                        <div class="story-stat">
                            <i class="fas fa-heart"></i>
                            ${story.likes || 0}
                        </div>
                    </div>
                </div>
                
                <div class="story-status ${statusClass}">
                    ${statusText}
                </div>
                
                <div class="story-actions">
                    <button class="btn-icon btn-edit" onclick="userDashboard.editStory(${story.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${story.status === 'published' ? `
                        <button class="btn-icon btn-view" onclick="window.open('/story/${story.id}', '_blank')" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </button>
                    ` : ''}
                    <button class="btn-icon btn-delete" onclick="userDashboard.confirmDeleteStory(${story.id})" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    filterStories(status) {
        if (!status) {
            this.renderStories(this.stories);
            return;
        }

        const filteredStories = this.stories.filter(story => story.status === status);
        this.renderStories(filteredStories);
    }

    openStoryModal(story = null) {
        const modal = document.getElementById('storyModal');
        const form = document.getElementById('storyForm');
        const modalTitle = document.getElementById('modalTitle');

        if (story) {
            this.currentStoryId = story.id;
            modalTitle.textContent = 'Edit Cerita';
            this.populateForm(story);
        } else {
            this.currentStoryId = null;
            modalTitle.textContent = 'Buat Cerita Baru';
            form.reset();
            this.removeImage();
        }

        modal.classList.add('active');
    }

    closeStoryModal() {
        const modal = document.getElementById('storyModal');
        modal.classList.remove('active');
        this.currentStoryId = null;
    }

    populateForm(story) {
        document.getElementById('storyId').value = story.id;
        document.getElementById('title').value = story.title;
        document.getElementById('category').value = story.category;
        document.getElementById('content').value = story.content;

        if (story.coverImage) {
            this.showImagePreview(story.coverImage);
        }
    }

    async handleStorySubmit(e) {
        e.preventDefault();
        await this.saveStory(false);
    }

    async saveDraft() {
        await this.saveStory(true);
    }

    async saveStory(isDraft = false) {
        const form = document.getElementById('storyForm');
        const formData = new FormData(form);
        
        // Set status based on database enum values
        formData.append('status', isDraft ? 'draft' : 'published');

        try {
            const url = this.currentStoryId 
                ? `/api/stories/${this.currentStoryId}`
                : '/api/stories';
            
            const method = this.currentStoryId ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`
                },
                body: formData
            });

            if (response.ok) {
                this.showSuccess(isDraft ? 'Draft berhasil disimpan' : 'Cerita berhasil dipublikasikan');
                this.closeStoryModal();
                await this.loadUserStories();
                await this.loadUserStats();
            } else {
                const error = await response.json();
                this.showError(error.message || 'Gagal menyimpan cerita');
            }
        } catch (error) {
            console.error('Error saving story:', error);
            this.showError('Gagal menyimpan cerita');
        }
    }

    editStory(storyId) {
        const story = this.stories.find(s => s.id === storyId);
        if (story) {
            this.openStoryModal(story);
        }
    }

    confirmDeleteStory(storyId) {
        this.currentStoryId = storyId;
        document.getElementById('deleteModal').classList.add('active');
    }

    closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        this.currentStoryId = null;
    }

    async deleteStory() {
        if (!this.currentStoryId) return;

        try {
            const response = await fetch(`/api/stories/${this.currentStoryId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`
                }
            });

            if (response.ok) {
                this.showSuccess('Cerita berhasil dihapus');
                this.closeDeleteModal();
                await this.loadUserStories();
                await this.loadUserStats();
            } else {
                this.showError('Gagal menghapus cerita');
            }
        } catch (error) {
            console.error('Error deleting story:', error);
            this.showError('Gagal menghapus cerita');
        }
    }

    handleImageUpload(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                this.showError('Ukuran file maksimal 5MB');
                e.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.showImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    }

    showImagePreview(src) {
        const preview = document.getElementById('imagePreview');
        const img = document.getElementById('previewImg');
        
        img.src = src;
        preview.style.display = 'block';
    }

    removeImage() {
        const coverImageInput = document.getElementById('coverImage');
        const imagePreview = document.getElementById('imagePreview');
        
        if (coverImageInput) {
            coverImageInput.value = '';
        }
        if (imagePreview) {
            imagePreview.style.display = 'none';
        }
    }

    // Utility methods
    getToken() {
        return localStorage.getItem('authToken') || '';
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    showSuccess(message) {
        // Use flash message system if available
        if (typeof showSuccess === 'function') {
            showSuccess(message);
        } else if (window.showFlashMessage) {
            window.showFlashMessage(message, 'success');
        } else {
            // Fallback to a simple notification
            this.showNotification(message, 'success');
        }
    }

    showError(message) {
        // Use flash message system if available
        if (typeof showError === 'function') {
            showError(message);
        } else if (window.showFlashMessage) {
            window.showFlashMessage(message, 'error');
        } else {
            // Fallback to a simple notification
            this.showNotification(message, 'error');
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.userDashboard = new UserDashboard();
});
