<?php
$title = 'Detail Cerita - Admin Cerita Mahasiswa';
$currentPage = 'admin-stories';
ob_start();
?>
<main class="main-content" style="background:#f6f8fb; min-height:100vh;">
    <div class="container" style="max-width:900px; margin:auto; padding:2em 0;">
        <div class="page-header" style="margin-bottom:2.5em; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;">
            <h1 class="page-title" style="font-size:2.3em; font-weight:800; color:#2d2d6a; margin:0; letter-spacing:-1px;">Detail Cerita</h1>
            <a href="/admin/stories" class="btn btn-secondary" style="margin-top:0.5em; font-size:1em; padding:0.5em 1.2em;">&larr; Kembali</a>
        </div>
        <div class="admin-story-detail-card" style="background:#fff; border-radius:16px; box-shadow:0 4px 24px #0002; padding:2.5em 2.5em 2em 2.5em; max-width:700px; margin:auto;">
            <h2 style="font-size:2em; font-weight:700; color:#3b3b99; margin-bottom:0.5em; line-height:1.2; word-break:break-word;">
                <?= htmlspecialchars($story->getTitle()) ?>
            </h2>
            <div class="story-meta" style="font-size:1.08em; color:#555; margin-bottom:1.5em; display:grid; grid-template-columns:1fr 1fr; gap:0.7em 2em; align-items:center;">
                <div><i class="fas fa-user" style="color:#6c63ff;"></i> <b>Penulis:</b> <span style="color:#222;"> <?= htmlspecialchars($story->getUserName() ?? 'Anonim') ?></span></div>
                <div><i class="fas fa-tag" style="color:#fbbf24;"></i> <b>Kategori:</b> <span style="color:#222;"> <?= htmlspecialchars($story->getCategory()) ?></span></div>
                <div><i class="fas fa-calendar" style="color:#60a5fa;"></i> <b>Dibuat:</b> <span style="color:#222;"> <?= date('d M Y', strtotime($story->getCreatedAt())) ?></span></div>
                <div><i class="fas fa-info-circle" style="color:#34d399;"></i> <b>Status:</b> <span class="status-badge status-<?= htmlspecialchars($story->getStatus()) ?>" style="padding:0.25em 1em; border-radius:8px; font-weight:700; font-size:1em; margin-left:0.2em; letter-spacing:0.5px; background:#f3f4f6; color:#222; border:1px solid #e5e7eb; display:inline-block;"> <?= ucfirst($story->getStatus()) ?> </span></div>
            </div>
            <hr style="margin:1.5em 0 1.5em 0; border:0; border-top:1.5px solid #f0f0f0;">
            <div class="story-content" style="margin-top:1.5em; font-size:1.18em; line-height:1.8; color:#232323; background:#f8fafc; border-radius:10px; padding:1.5em 1.2em; box-shadow:0 1px 4px #0001; min-height:120px;">
                <?= nl2br(htmlspecialchars($story->getContent())) ?>
            </div>
        </div>
    </div>
</main>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php'; 