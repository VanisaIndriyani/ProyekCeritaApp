<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin - Cerita Mahasiswa' ?></title>
    <link rel="stylesheet" href="/style.css">
    <link rel="stylesheet" href="/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
.admin-layout {
    display: flex;
    min-height: 100vh;
}
.admin-sidebar {
    width: 240px;
    background: linear-gradient(160deg, #4850e4 0%, #6a82fb 100%);
    border-right: 1.5px solid #4850e4;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
    box-shadow: 2px 0 8px #0001;
}
.admin-sidebar .sidebar-header, .admin-sidebar .sidebar-link {
    color: #fff !important;
}
.admin-sidebar .sidebar-link.active {
    background: rgba(255,255,255,0.13);
    color: #fff !important;
}
.admin-sidebar .sidebar-link:hover {
    background: rgba(255,255,255,0.18);
    color: #fff !important;
}
.admin-main-content {
    flex: 1;
    padding: 2.5em 2em 2em 2em;
    margin-left: 240px;
    min-width: 0;
}
@media (max-width: 900px) {
    .admin-sidebar { width: 70px; }
    .admin-main-content { margin-left: 70px; }
    .sidebar-header .logo-title { display: none; }
    .sidebar-link { font-size: 1.1em; }
}
</style>
</head>
<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="https://img.icons8.com/color/48/000000/graduation-cap--v2.png" alt="Logo" class="logo-img">
                <span class="logo-title">Admin Panel</span>
            </div>
            <nav class="sidebar-nav">
                <a href="/admin" class="sidebar-link<?= ($currentPage ?? '') === 'admin' ? ' active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="/admin/users" class="sidebar-link<?= ($currentPage ?? '') === 'admin-users' ? ' active' : '' ?>">
                    <i class="fas fa-users"></i> Kelola User
                </a>
                <a href="/admin/stories" class="sidebar-link<?= ($currentPage ?? '') === 'admin-stories' ? ' active' : '' ?>">
                    <i class="fas fa-book"></i> Kelola Cerita
                </a>
                <a href="/admin/about" class="sidebar-link">
                    <i class="fas fa-info-circle"></i> Tentang
                </a>
                <a href="/logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>
        <main class="admin-main-content">
            <?php if (isset($flashMessages) && !empty($flashMessages)) {
                echo \App\Infrastructure\Utils\FlashMessage::renderMessages($flashMessages);
            } ?>
            <?= $content ?? '' ?>
        </main>
    </div>
    <script src="/flash.js"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($pageScripts)): ?>
        <script><?= $pageScripts ?></script>
    <?php endif; ?>
</body>
</html> 