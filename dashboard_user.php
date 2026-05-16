<?php
session_start();
require 'koneksi.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect admin/superadmin ke dashboard admin
if (in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: dashboard_admin.php');
    exit;
}

// Search & filter
$search   = trim($_GET['search'] ?? '');
$jurusan  = trim($_GET['jurusan'] ?? '');
$angkatan = trim($_GET['angkatan'] ?? '');

$where  = [];
$params = [];

if ($search) {
    $where[]  = '(nama LIKE ? OR nim LIKE ?)';
    $like = "%$search%";
    $params = array_merge($params, [$like, $like]);
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

// Ambil list untuk filter
$jurusans   = $pdo->query("SELECT DISTINCT jurusan FROM alumni ORDER BY jurusan")->fetchAll(PDO::FETCH_COLUMN);
$angkatans = $pdo->query("SELECT DISTINCT angkatan FROM alumni ORDER BY angkatan DESC")->fetchAll(PDO::FETCH_COLUMN);

// Fungsi Helper untuk menyingkat nama jurusan di tampilan
function singkatJurusan($nama) {
    return match ($nama) {
        'Rekayasa Perangkat Lunak'              => 'RPL',
        'Teknik Komputer dan Jaringan'          => 'TKJ',
        'Teknik Jaringan Akses Telekomunikasi'  => 'TJAT',
        default => $nama
    };
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Alumni</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/dashboard.css?v=<?= time(); ?>">
</head>

<body>

    <style>
        .action-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 20px !important;
            width: 100% !important;
            height: 95px !important;
            position: relative !important;
        }

        /* Sisi Kiri: Judul & Subtitle */
        .title-group {
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
            color: #555 !important;
            font-weight: normal !important;
            display: block !important;
        }

        /* Sisi Kanan: Gambar Sekolah Lancip (Tanpa Tumpul) */
        .img-header-alumni {
            position: static !important;
            transform: none !important;
            height: 95px !important;
            width: 350px !important; 
            object-fit: cover !important;
            border-radius: 0px !important; /* Membuat sudut kotak persegi biasa */
            box-shadow: 0 2px 6px rgba(0,0,0,0.1) !important;
            flex-shrink: 0 !important;
            display: block !important;
        }
    </style>

    <nav class="navbar">
        <div class="nav-brand">
            <span class="brand-text">STELLA Alum</span>
        </div>
        <div class="nav-links">
            <a href="dashboard_user.php" class="nav-link">Direktori Alumni</a>
        </div>
    </nav>

    <div class="controls-wrapper">
        <div class="user-profile">
            <div class="user-avatar"><?= strtoupper($_SESSION['username'][0]) ?></div>
            <div class="user-info">
                <span class="user-role"><?= htmlspecialchars($_SESSION['username']) ?> (<?= ucfirst($_SESSION['role']) ?>)</span>
                <a href="logout.php" class="btn-logout-small">Keluar / Logout</a>
            </div>
        </div>

        <form method="GET" class="filter-box">
            <input type="text" name="search" placeholder="Cari nama atau NIS..."
                value="<?= htmlspecialchars($search) ?>" class="filter-input">
            
            <select name="jurusan" class="filter-select">
                <option value="">Semua Jurusan</option>
                <?php foreach ($jurusans as $j): ?>
                    <option value="<?= htmlspecialchars($j) ?>" <?= $jurusan === $j ? 'selected' : '' ?>>
                        <?= htmlspecialchars(singkatJurusan($j)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="angkatan" class="filter-select">
                <option value="">Semua Angkatan</option>
                <?php foreach ($angkatans as $a): ?>
                    <option value="<?= $a ?>" <?= $angkatan == $a ? 'selected' : '' ?>><?= $a ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-filter">Filter</button>
            <?php if ($search || $jurusan || $angkatan): ?>
                <a href="dashboard_user.php" class="btn-edit" style="display: flex; align-items: center; text-decoration: none;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <main class="main-content">
        <div class="action-header">
            <div class="title-group">
                <h1 class="page-title">Direktori Alumni</h1>
                <p class="page-subtitle">Temukan alumni STELLA di sini</p>
            </div>

            <div class="marquee-space">
                <marquee behavior="scroll" direction="right" class="copy-text">
                    Dhean Oktaviansyah - 11 RPL 1
                </marquee>
            </div>

            <img src="stella.jpeg" class="img-header-alumni" alt="Foto Sekolah">
        </div>

        <div class="cards-grid">
            <?php if (empty($alumni)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📁</span>
                    <h3>Tidak ada data ditemukan</h3>
                    <p>Coba ubah kata kunci pencarian atau filter Anda.</p>
                </div>
            <?php else: ?>
                <?php foreach ($alumni as $a): ?>
                    <div class="alumni-card">
                        <div class="card-avatar"><?= strtoupper(mb_substr($a['nama'], 0, 1)) ?></div>
                        <div class="card-body">
                            <h3 class="card-name"><?= htmlspecialchars($a['nama']) ?></h3>
                            <p class="card-nim"><?= htmlspecialchars($a['nim']) ?></p>
                            
                            <div class="card-tags">
                                <span class="badge-jurusan">Angkatan <?= $a['angkatan'] ?></span>
                                <span class="badge-jurusan"><?= htmlspecialchars(singkatJurusan($a['jurusan'])) ?></span>
                            </div>

                            <div class="card-info">
                                <p>📧 <?= htmlspecialchars($a['email']) ?></p>
                                <?php if (!empty($a['alamat'])): ?>
                                    <p>📍 <?= htmlspecialchars($a['alamat']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <br><br>
    </main>

</body>
</html>