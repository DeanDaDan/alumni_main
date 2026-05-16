<?php
session_start();
require 'koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}

// Logika Search & Filter
$search   = trim($_GET['search'] ?? '');
$jurusan  = trim($_GET['jurusan'] ?? '');
$angkatan = trim($_GET['angkatan'] ?? '');

$where  = [];
$params = [];

if ($search) {
    $where[]  = '(nama LIKE ? OR nim LIKE ? OR email LIKE ?)';
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}
if ($jurusan) {
    $where[]  = 'jurusan = ?';
    $params[] = $jurusan;
}
if ($angkatan) {
    $where[]  = 'angkatan = ?';
    $params[] = $angkatan;
}

$sql = "SELECT * FROM alumni";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY angkatan DESC, nama ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alumni = $stmt->fetchAll();

$jurusans  = $pdo->query("SELECT DISTINCT jurusan FROM alumni ORDER BY jurusan")->fetchAll(PDO::FETCH_COLUMN);
$angkatans = $pdo->query("SELECT DISTINCT angkatan FROM alumni ORDER BY angkatan DESC")->fetchAll(PDO::FETCH_COLUMN);

$total = count($alumni);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function singkatJurusan($nama) {
    return match ($nama) {
        'Rekayasa Perangkat Lunak' => 'RPL',
        'Teknik Komputer dan Jaringan' => 'TKJ',
        'Teknik Jaringan Akses Telekomunikasi' => 'TJAT',
        default => $nama
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | STELLA Alum</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/dashboard.css?v=<?= time(); ?>">

    <style>
        .action-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 20px !important;
            width: 100% !important;
            height: 90px !important;
            position: relative !important;
        }

        /* Sisi Kiri: Judul & Subtitle */
        .title-group {
            flex-shrink: 0 !important;
        }

        /* Tombol Tambah Alumni */
        .btn-group {
            flex-shrink: 0 !important;
        }

        /* Sisi Tengah: Wadah Marquee flex-grow penuh ngisi space kosong */
        .marquee-space {
            flex-grow: 1 !important;
            min-width: 100px !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 15px !important;
            overflow: hidden !important;
        }

        .action-header marquee.copy-text {
            width: 100% !important;
            margin: 0 !important;
            font-size: 1.5rem !important;
            color: #888 !important;
            font-weight: normal !important;
        }

        /* Sisi Kanan: Gambar Sekolah Full */
        .img-header-alumni {
            position: static !important;
            transform: none !important;
            height: 85px !important;
            width: 350px !important; /* Luas area gambar */
            object-fit: cover !important;
            border-radius: 0px !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
            flex-shrink: 0 !important;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-brand">
            <img src="logo.png" alt="" class="brand-logo">
            <span class="brand-text">STELLA Alum</span>
        </div>
        <div class="nav-links">
            <a href="dashboard_admin.php" class="nav-link active">Data Alumni</a>
            <a href="tambah.php" class="nav-link">Tambah Alumni</a>
            
            <?php if ($_SESSION['role'] === 'superadmin'): ?>
                <a href="users.php" class="nav-link">Kelola User</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="controls-wrapper">
        <div class="user-profile">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['role'], 0, 1)) ?>
            </div>
            <div class="user-info">
                <span class="user-role"><?= ucfirst($_SESSION['role']) ?> Panel</span>
                <a href="logout.php" class="btn-logout-small">Keluar / Logout</a>
            </div>
        </div>

        <form method="GET" class="filter-box">
            <input type="text" name="search" placeholder="Cari nama, NIS, email..." value="<?= htmlspecialchars($search) ?>" class="filter-input">
            <select name="jurusan" class="filter-select">
                <option value="">Semua Jurusan</option>
                <?php foreach ($jurusans as $j): ?>
                    <option value="<?= htmlspecialchars($j) ?>" <?= $jurusan === $j ? 'selected' : '' ?>><?= htmlspecialchars($j) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="angkatan" class="filter-select">
                <option value="">Semua Angkatan</option>
                <?php foreach ($angkatans as $a): ?>
                    <option value="<?= $a ?>" <?= $angkatan == $a ? 'selected' : '' ?>><?= $a ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-filter">Filter</button>
        </form>
    </div>

    <main class="main-content">
        <div class="action-header">
            <div class="title-group">
                <h1 class="page-title">Data Alumni</h1>
                <p class="page-subtitle">total <?= $total ?> alumni terdaftar</p>
            </div>
            
            <div class="btn-group">
                <a href="tambah.php" class="btn-add">+ Tambah Alumni</a>
                <?php if ($flash): ?>
                    <div class="alert-mini alert-<?= $flash['type'] ?>">
                        <?= htmlspecialchars($flash['msg']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="marquee-space">
                <marquee behavior="scroll" direction="right" class="copy-text">
                    Dhean Oktaviansyah - 11 RPL 1
                </marquee>
            </div>

            <img src="stella.jpeg" class="img-header-alumni" alt="Foto Sekolah">
        </div>

        <div class="table-card">
            <?php if (empty($alumni)): ?>
                <div style="padding: 20px; text-align: center;">Belum ada data.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NIS</th>
                            <th>NAMA</th>
                            <th>ANGKATAN</th>
                            <th>JURUSAN</th>
                            <th>EMAIL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumni as $i => $a): ?>
                            <tr>
                                <td style="text-align: center; color: #777;"><?= $i + 1 ?></td>
                                <td style="color: #888;"><?= htmlspecialchars($a['nim']) ?></td>
                                <td style="font-weight: 600; color: #555;"><?= htmlspecialchars($a['nama']) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge-angkatan"><?= $a['angkatan'] ?></span>
                                </td>
                                <td><?= htmlspecialchars(singkatJurusan($a['jurusan'])) ?></td>
                                <td style="color: #888;"><?= htmlspecialchars($a['email']) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $a['id_alumni'] ?>" class="btn-edit">Edit</a>
                                    <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Yakin hapus?')">
                                        <input type="hidden" name="id" value="<?= $a['id_alumni'] ?>">
                                        <button type="submit" class="btn-delete">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>