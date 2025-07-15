<?php
session_start();
$title = 'Kelola Tim Kami - Admin Cerita Mahasiswa';
$currentPage = 'admin-about';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=cerita_app;charset=utf8mb4', 'root', '');
} catch (Exception $e) {
    die('Gagal koneksi database');
}

// Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_tim'])) {
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $foto = null;
    if ($nama && $jabatan) {
        $stmt = $pdo->prepare('INSERT INTO team (nama, jabatan, foto) VALUES (:nama, :jabatan, :foto)');
        $stmt->execute(['nama' => $nama, 'jabatan' => $jabatan, 'foto' => $foto]);
        $_SESSION['success'] = 'Anggota tim berhasil ditambahkan!';
        header('Location: /admin/about');
        exit;
    }
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tim'])) {
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $foto = $_POST['foto_lama'] ?? null;
    if ($nama && $jabatan) {
        $stmt = $pdo->prepare('UPDATE team SET nama=:nama, jabatan=:jabatan, foto=:foto WHERE id=:id');
        $stmt->execute(['nama' => $nama, 'jabatan' => $jabatan, 'foto' => $foto, 'id' => $id]);
        $_SESSION['success'] = 'Anggota tim berhasil diupdate!';
        header('Location: /admin/about');
        exit;
    }
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $pdo->prepare('DELETE FROM team WHERE id = :id')->execute(['id' => $id]);
    $_SESSION['success'] = 'Anggota tim berhasil dihapus!';
    header('Location: /admin/about');
    exit;
}

// Ambil Data
$stmt = $pdo->query('SELECT * FROM team ORDER BY id ASC');
$tim = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_start();
?>

<main class="main-content">
<div class="container">
    <h1>Kelola Tim Kami</h1>
    <p>Tambah, edit, dan hapus anggota tim admin Cerita Mahasiswa.</p>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <button class="btn btn-primary" onclick="showModal('tambah')">+ Tambah Anggota Tim</button>

    <!-- Modal Tambah -->
    <div id="modalTambahTim" class="modal-tim">
        <div class="modal-tim-content">
            <span class="modal-tim-close" onclick="closeModal('tambah')">&times;</span>
            <form method="post">
                <input type="text" name="nama" placeholder="Nama" required>
                <input type="text" name="jabatan" placeholder="Jabatan" required>
                <button type="submit" name="tambah_tim">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="modalEditTim" class="modal-tim">
        <div class="modal-tim-content">
            <span class="modal-tim-close" onclick="closeModal('edit')">&times;</span>
            <form method="post" id="formEditTim">
                <input type="hidden" name="id" id="edit_id">
                <input type="text" name="nama" id="edit_nama" required>
                <input type="text" name="jabatan" id="edit_jabatan" required>
                <input type="hidden" name="foto_lama" id="edit_foto_lama">
                <div id="edit_foto_preview" style="display:none;"></div>
                <button type="submit" name="edit_tim">Simpan</button>
            </form>
        </div>
    </div>

    <table>
        <thead>
            <tr><th>ID</th><th>Nama</th><th>Jabatan</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($tim as $anggota): ?>
                <tr>
                    <td><?= $anggota['id'] ?></td>
                    <td><?= htmlspecialchars($anggota['nama']) ?></td>
                    <td><?= htmlspecialchars($anggota['jabatan']) ?></td>
                    <td>
                        <button onclick="openEditModal(<?= $anggota['id'] ?>, '<?= addslashes($anggota['nama']) ?>', '<?= addslashes($anggota['jabatan']) ?>', '')">Edit</button>
                        <a href="?hapus=<?= $anggota['id'] ?>" onclick="return confirm('Hapus anggota ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</main>

<style>
body.admin-body { background: #f4f6fb; }

h1 {
  color: #2d3748;
  font-weight: 800;
  margin-bottom: 0.2rem;
  letter-spacing: -1px;
}

.modal-tim {
  display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(102,126,234,0.13); align-items: center; justify-content: center;
}
.modal-tim-content {
  background: #fff; margin: 5% auto; padding: 2.2rem 2rem 1.5rem 2rem; border-radius: 16px; max-width: 400px; box-shadow: 0 4px 24px 0 rgba(102,126,234,0.13); position: relative;
  display: flex; flex-direction: column; align-items: stretch;
}
.modal-tim-close {
  position: absolute; right: 1.2rem; top: 1.2rem; font-size: 2rem; color: #888; cursor: pointer; transition: color 0.2s;
}
.modal-tim-close:hover { color: #764ba2; }
.modal-tim-content h2 { font-size: 1.3rem; font-weight: 700; margin-bottom: 1.2rem; color: #667eea; text-align: center; }
.modal-tim-content input[type="text"], .modal-tim-content input[type="file"] {
  padding: 0.7rem 1rem; border: 1.5px solidrgb(114, 151, 211); border-radius: 8px; font-size: 1rem; margin-bottom: 1rem; background: #f8fafc;
}
.modal-tim-content button[type="submit"] {
  background: linear-gradient(90deg, #667eea 60%, #764ba2 100%); color: #fff; border: none; border-radius: 8px; padding: 0.7rem 1.2rem; font-weight: 600; font-size: 1rem; cursor: pointer; margin-top: 0.5rem; transition: background 0.2s;
}
.modal-tim-content button[type="submit"]:hover { background: #667eea; }

.alert-success {
  background: #e6fffa; color: #276749; border: 1.5px solid #38b2ac; border-radius: 8px; padding: 0.8rem 1.2rem; margin-bottom: 1.2rem; font-weight: 600;
}

/* Tabel tim */
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; box-shadow: 0 2px 12px 0 rgba(102,126,234,0.07); overflow: hidden; margin-top: 2rem; }
thead th { background: #f4f6fb; color: #667eea; font-weight: 700; padding: 0.8rem 1rem; border-bottom: 2px solid #e2e8f0; }
tbody td { padding: 0.7rem 1rem; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover { background: #f0f4ff; }

/* Foto bulat */
td img { border-radius: 50%; width: 42px; height: 42px; object-fit: cover; box-shadow: 0 2px 8px 0 rgba(102,126,234,0.10); }

/* Tombol aksi */
td button, td a { background: #f4f6fb; border: none; border-radius: 8px; padding: 0.5em 0.7em; color: #667eea; font-size: 1.1em; cursor: pointer; margin-right: 0.2em; transition: background 0.15s, color 0.15s; display: inline-flex; align-items: center; justify-content: center; }
td button:hover, td a:hover { background: #667eea; color: #fff; text-decoration: none; }
td button:active, td a:active { background: #764ba2; color: #fff; }
td button[onclick^="openEditModal"]::after { content: '\270E'; font-size: 1em; margin-left: 0.2em; }
td a[onclick]::after { content: '\1F5D1'; font-size: 1em; margin-left: 0.2em; }

@media (max-width: 700px) {
  .modal-tim-content { padding: 1rem; max-width: 95vw; }
  table, thead, tbody, th, td, tr { font-size: 0.97em; }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    window.showModal = function(type) {
        if(type === 'tambah') {
            document.getElementById('modalTambahTim').style.display = 'flex'; // Use flex for centering
            document.body.style.overflow = 'hidden';
        } else if(type === 'edit') {
            document.getElementById('modalEditTim').style.display = 'flex'; // Use flex for centering
            document.body.style.overflow = 'hidden';
        }
    }

    window.closeModal = function(type) {
        if(type === 'tambah') {
            document.getElementById('modalTambahTim').style.display = 'none';
        } else if(type === 'edit') {
            document.getElementById('modalEditTim').style.display = 'none';
        }
        document.body.style.overflow = '';
    }

    window.openEditModal = function(id, nama, jabatan, foto) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_jabatan').value = jabatan;
        document.getElementById('edit_foto_lama').value = foto;
        document.getElementById('edit_foto_preview').innerHTML = foto ? '<img src="'+foto+'" height="40">' : '';
        showModal('edit');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>
