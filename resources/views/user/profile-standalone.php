<?php
// Get current user
use App\Application\Helpers\AuthHelper;
use App\Infrastructure\Persistence\Story\MySQLStoryRepository;

$user = AuthHelper::getCurrentUser();
if (!$user) {
    header('Location: /login');
    exit;
}

// Get database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
    $storyRepository = new MySQLStoryRepository($pdo);
    $userStories = $storyRepository->findByUserId($user['id']);
} catch (Exception $e) {
    $userStories = [];
    $errorMessage = 'Gagal memuat cerita: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Cerita - Cerita Mahasiswa</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
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
        }

        .header-actions {
            margin-top: 20px;
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

        .btn-outline {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f9fafb;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
            border: 1px solid #dc2626;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        .stats-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: bold;
            color: #2563eb;
        }

        .stat-label {
            display: block;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 4px;
        }

        .stories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .story-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .story-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .story-cover {
            position: relative;
            height: 160px;
            background: #f3f4f6;
        }

        .cover-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cover-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
            font-size: 2rem;
        }

        .status-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-published { background: #dcfce7; color: #166534; }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-pending { background: #dbeafe; color: #1e40af; }

        .story-content {
            padding: 20px;
        }

        .story-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .story-meta {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .story-category, .story-date {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .story-excerpt {
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 0;
        }

        .story-actions {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 1.5rem;
            color: #111827;
            margin-bottom: 12px;
        }

        .empty-description {
            color: #6b7280;
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .navbar {
            background: white;
            padding: 12px 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2563eb;
            text-decoration: none;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #4b5563;
        }

        .logout-btn {
            background: #f3f4f6;
            color: #374151;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .logout-btn:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="/" class="navbar-brand">📖 Cerita Mahasiswa</a>
            <div class="navbar-user">
                <span>👋 Halo, <?= htmlspecialchars($user['username']) ?></span>
                <a href="/logout" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <main class="story-manage-main">
        <div class="container">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Kelola Cerita</h1>
                <p class="page-subtitle">Kelola semua cerita yang Anda tulis</p>
                <div class="header-actions">
                    <a href="/user/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tulis Cerita Baru
                    </a>
                </div>
            </div>

            <!-- Stories Count -->
            <div class="stats-summary">
                <div class="stat-item">
                    <span class="stat-number"><?= count($userStories) ?></span>
                    <span class="stat-label">Total Cerita</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= count(array_filter($userStories, fn($s) => $s->getStatus() === 'published')) ?></span>
                    <span class="stat-label">Dipublikasi</span>
                </div>
            </div>

            <!-- Stories List -->
            <div class="stories-section">
                <?php if (empty($userStories)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="empty-title">Belum Ada Cerita</h3>
                        <p class="empty-description">Mulai tulis cerita pertama Anda dan bagikan pengalaman menarik!</p>
                        <a href="/user/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Tulis Cerita Pertama
                        </a>
                    </div>
                <?php else: ?>
                    <div class="stories-grid">
                        <?php foreach ($userStories as $story): ?>
                            <div class="story-card">
                                <!-- Story Cover -->
                                <div class="story-cover">
                                    <?php if ($story->getCoverImage()): ?>
                                        <img src="/uploads/<?= htmlspecialchars($story->getCoverImage()) ?>" alt="Cover" class="cover-image">
                                    <?php else: ?>
                                        <div class="cover-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="status-badge status-<?= htmlspecialchars($story->getStatus()) ?>">
                                        <?= ucfirst(htmlspecialchars($story->getStatus())) ?>
                                    </div>
                                </div>

                                <!-- Story Content -->
                                <div class="story-content">
                                    <h3 class="story-title">
                                        <?= htmlspecialchars($story->getTitle()) ?>
                                    </h3>
                                    
                                    <div class="story-meta">
                                        <?php if ($story->getCategory()): ?>
                                            <span class="story-category">
                                                <i class="fas fa-tag"></i>
                                                <?= htmlspecialchars($story->getCategory()) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="story-date">
                                            <i class="fas fa-calendar"></i>
                                            <?= date('d M Y', strtotime($story->getCreatedAt())) ?>
                                        </span>
                                    </div>

                                    <p class="story-excerpt">
                                        <?= htmlspecialchars(substr(strip_tags($story->getContent()), 0, 120)) ?>...
                                    </p>
                                </div>

                                <!-- Story Actions -->
                                <div class="story-actions">
                                    <a href="/user/edit/<?= $story->getId() ?>" class="btn btn-outline btn-small">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    
                                    <form method="POST" action="/user/delete/<?= $story->getId() ?>" style="display: inline;" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus cerita ini?')">
                                        <button type="submit" class="btn btn-danger btn-small">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                    
                                    <?php if ($story->getStatus() === 'published'): ?>
                                        <a href="/story/<?= $story->getId() ?>" class="btn btn-outline btn-small" target="_blank">
                                            <i class="fas fa-external-link-alt"></i>
                                            Lihat
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
