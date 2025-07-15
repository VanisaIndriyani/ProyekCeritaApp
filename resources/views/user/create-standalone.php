<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulis Cerita Baru - Cerita Mahasiswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .breadcrumb {
            margin-bottom: 20px;
        }

        .breadcrumb-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .breadcrumb-item:hover {
            opacity: 0.8;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .story-form {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-row {
            margin-bottom: 24px;
        }

        .form-group {
            width: 100%;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-label.required::after {
            content: " *";
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .content-editor {
            min-height: 300px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.6;
        }

        .form-help {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 6px;
        }

        .file-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            transition: border-color 0.2s;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #2563eb;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .file-upload-content {
            color: #6b7280;
        }

        .file-upload-content i {
            font-size: 2rem;
            margin-bottom: 12px;
            color: #9ca3af;
        }

        .file-upload-content p {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .file-requirements {
            font-size: 0.875rem;
            color: #9ca3af;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        @media (max-width: 768px) {
            .story-form {
                padding: 24px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <main class="story-form-main">
        <div class="container">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="/user" class="breadcrumb-item">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Kelola Cerita
                </a>
            </nav>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Tulis Cerita Baru</h1>
                <p class="page-subtitle">Bagikan pengalaman dan inspirasi Anda kepada sesama mahasiswa</p>
            </div>

            <!-- Story Form -->
            <div class="content-card">
                <form method="POST" action="/user/create" class="story-form" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3 class="section-title">Informasi Dasar</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title" class="form-label required">Judul Cerita</label>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       class="form-control" 
                                       placeholder="Masukkan judul cerita yang menarik" 
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                       required>
                                <div class="form-help">Judul yang baik akan menarik pembaca untuk membaca cerita Anda</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="category" class="form-label required">Kategori</label>
                                <select id="category" name="category" class="form-control" required>
                                    <option value="">Pilih kategori cerita</option>
                                    <option value="Akademik" <?= ($_POST['category'] ?? '') === 'Akademik' ? 'selected' : '' ?>>Akademik</option>
                                    <option value="Karir" <?= ($_POST['category'] ?? '') === 'Karir' ? 'selected' : '' ?>>Karir</option>
                                    <option value="Kehidupan" <?= ($_POST['category'] ?? '') === 'Kehidupan' ? 'selected' : '' ?>>Kehidupan</option>
                                    <option value="Technology" <?= ($_POST['category'] ?? '') === 'Technology' ? 'selected' : '' ?>>Teknologi</option>
                                    <option value="Organisasi" <?= ($_POST['category'] ?? '') === 'Organisasi' ? 'selected' : '' ?>>Organisasi</option>
                                    <option value="Magang" <?= ($_POST['category'] ?? '') === 'Magang' ? 'selected' : '' ?>>Magang</option>
                                    <option value="Kompetisi" <?= ($_POST['category'] ?? '') === 'Kompetisi' ? 'selected' : '' ?>>Kompetisi</option>
                                    <option value="Wisuda" <?= ($_POST['category'] ?? '') === 'Wisuda' ? 'selected' : '' ?>>Wisuda</option>
                                </select>
                                <div class="form-help">Kategori membantu pembaca menemukan cerita sesuai minat mereka</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="coverImage" class="form-label">Cover Image</label>
                                <div class="file-upload-area">
                                    <input type="file" 
                                           id="coverImage" 
                                           name="coverImage" 
                                           class="file-input" 
                                           accept="image/*">
                                    <div class="file-upload-content">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Klik untuk upload atau drag & drop gambar</p>
                                        <span class="file-requirements">Format: JPG, PNG, GIF (Max: 5MB)</span>
                                    </div>
                                </div>
                                <div class="form-help">Cover image akan membuat cerita Anda lebih menarik</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Isi Cerita</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="content" class="form-label required">Konten Cerita</label>
                                <textarea id="content" 
                                          name="content" 
                                          class="form-control content-editor" 
                                          placeholder="Tulis cerita Anda di sini... Ceritakan pengalaman, pembelajaran, dan inspirasi yang ingin Anda bagikan." 
                                          rows="15" 
                                          required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                                <div class="form-help">
                                    Tulis dengan detail dan jelas. Bagikan tips, pembelajaran, dan insight yang bermanfaat untuk pembaca.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="/user" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Batal
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Publikasikan Cerita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
