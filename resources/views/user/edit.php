<?php
$title = 'Edit Cerita - Cerita Mahasiswa';
$currentPage = 'user';

// Success/Error messages
$successMessage = $_SESSION['success'] ?? null;
$errorMessage = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Capture content using output buffering
ob_start();
?>

<main class="main-content">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/user" class="breadcrumb-item">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Kelola Cerita
            </a>
        </nav>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Edit Cerita</h1>
            <p class="page-subtitle">Perbarui cerita Anda dan bagikan versi terbaru</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($story) && $story->getStatus() === 'rejected'): ?>
            <div class="admin-reject-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <b>Cerita ditolak admin.</b><br>
                    <span>Komentar admin: <span class="admin-reject-comment">"<?= htmlspecialchars($story->getAdminComment()) ?>"</span></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Story Form -->
        <div class="content-card">
            <form method="POST" action="/user/edit/<?= $story->getId() ?>" class="story-form" enctype="multipart/form-data">
                
                <div class="form-section">
                    <h3 class="section-title">Informasi Dasar</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title" class="form-label required">Judul Cerita</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($story->getTitle()) ?>"
                                   placeholder="Masukkan judul cerita yang menarik" 
                                   required>
                            <div class="form-help">Judul yang baik akan menarik pembaca untuk membaca cerita Anda</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category" class="form-label required">Kategori</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Pilih kategori cerita</option>
                                <option value="akademik" <?= $story->getCategory() === 'akademik' ? 'selected' : '' ?>>Akademik</option>
                                <option value="karir" <?= $story->getCategory() === 'karir' ? 'selected' : '' ?>>Karir & Magang</option>
                                <option value="kehidupan" <?= $story->getCategory() === 'kehidupan' ? 'selected' : '' ?>>Kehidupan Kampus</option>
                                <option value="teknologi" <?= $story->getCategory() === 'teknologi' ? 'selected' : '' ?>>Teknologi</option>
                                <option value="organisasi" <?= $story->getCategory() === 'organisasi' ? 'selected' : '' ?>>Organisasi</option>
                                <option value="kompetisi" <?= $story->getCategory() === 'kompetisi' ? 'selected' : '' ?>>Kompetisi</option>
                                <option value="wisuda" <?= $story->getCategory() === 'wisuda' ? 'selected' : '' ?>>Wisuda</option>
                                <option value="lainnya" <?= $story->getCategory() === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="content" class="form-label required">Isi Cerita</label>
                            <textarea id="content" 
                                      name="content" 
                                      class="form-control content-editor" 
                                      rows="15" 
                                      placeholder="Tuliskan cerita Anda di sini. Ceritakan pengalaman, pelajaran yang didapat, atau momen berkesan lainnya..."
                                      required><?= htmlspecialchars($story->getContent()) ?></textarea>
                            <div class="form-help">
                                Bagikan pengalaman Anda dengan detail. Cerita yang personal dan autentik akan lebih berkesan.
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="coverImage" class="form-label">Gambar Cover</label>
                            <?php if ($story->getCoverImage()): ?>
                                <div class="current-image">
                                    <img src="/uploads/<?= htmlspecialchars($story->getCoverImage()) ?>" 
                                         alt="Current cover" 
                                         style="max-width: 200px; max-height: 150px; border-radius: 8px; margin-bottom: 10px;">
                                    <p class="text-muted">Gambar saat ini</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" 
                                   id="coverImage" 
                                   name="coverImage" 
                                   class="form-control" 
                                   accept="image/*">
                            <div class="form-help">
                                Upload gambar baru untuk mengganti gambar cover (opsional). Format yang didukung: JPG, PNG, GIF. Maksimal 5MB.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" 
                            class="btn btn-secondary" 
                            onclick="window.location.href='/user'">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Cerita
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.breadcrumb {
    margin-bottom: 20px;
}

.breadcrumb-item {
    color: #666;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.breadcrumb-item:hover {
    color: #007bff;
}

.page-header {
    text-align: center;
    margin-bottom: 30px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #666;
    font-size: 1.1rem;
}

.content-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.story-form {
    max-width: 800px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.form-row {
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-label.required::after {
    content: ' *';
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    background-color: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.content-editor {
    min-height: 300px;
    resize: vertical;
    font-family: inherit;
    line-height: 1.6;
}

.form-help {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding-top: 30px;
    border-top: 1px solid #e9ecef;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    transform: translateY(-1px);
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.current-image {
    margin-bottom: 10px;
}

.text-muted {
    color: #6c757d;
    font-size: 14px;
}

.admin-reject-alert {
    display: flex;
    align-items: flex-start;
    gap: 1em;
    background: #fff1f2;
    color: #b91c1c;
    border: 1.5px solid #fecaca;
    border-radius: 10px;
    padding: 18px 22px;
    margin-bottom: 1.5em;
    font-size: 1.08em;
    box-shadow: 0 2px 8px #0001;
}
.admin-reject-alert i {
    font-size: 1.7em;
    margin-top: 2px;
}
.admin-reject-comment {
    color: #991b1b;
    font-weight: 600;
    font-size: 1.08em;
}
@media (max-width: 600px) {
    .admin-reject-alert { flex-direction: column; gap: 0.5em; padding: 12px 10px; font-size: 1em; }
    .admin-reject-alert i { font-size: 1.3em; }
}

@media (max-width: 768px) {
    .content-card {
        padding: 20px;
        margin: 15px;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        text-align: center;
        justify-content: center;
    }
}
</style>

<?php
// Store content in variable and include layout
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
