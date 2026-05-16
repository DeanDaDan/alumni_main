<?php
session_start();
require 'koneksi.php';

// Auth check - Hanya Superadmin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: login.php');
    exit;
}

// Search & filter
$search = trim($_GET['search'] ?? '');
$role   = trim($_GET['role']   ?? '');

$where  = [];
$params = [];

if ($search) {
    $where[]  = '(username LIKE ?)';
    $params[] = "%$search%";
}
if ($role) {
    $where[]  = 'role = ?';
    $params[] = $role;
}

$sql = "SELECT * FROM users";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY user_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$total = count($users);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User | STELLA Alum</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/dashboard.css?v=4">
</head>
<body>

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

    <nav class="navbar">
        <div class="nav-brand">
            <img src="logo.png" alt="" class="brand-logo">
            <span class="brand-text">STELLA Alum</span>
        </div>
        <div class="nav-links">
            <a href="dashboard_admin.php" class="nav-link">Data Alumni</a>
            <a href="tambah.php" class="nav-link">Tambah Alumni</a>
            <a href="users.php" class="nav-link active">Kelola User</a>
        </div>
    </nav>

    <div class="controls-wrapper">
        <div class="user-profile">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
            </div>
            <div class="user-info">
                <span class="user-role"><?= ucfirst($_SESSION['username']) ?> (<?= ucfirst($_SESSION['role']) ?>)</span>
                <a href="logout.php" class="btn-logout-small">Keluar / Logout</a>
            </div>
        </div>

        <form method="GET" class="filter-box">
            <input type="text" name="search" placeholder="Cari username..." value="<?= htmlspecialchars($search) ?>" class="filter-input">
            <select name="role" class="filter-select">
                <option value="">Semua Role</option>
                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="superadmin" <?= $role === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
            </select>
            <button type="submit" class="btn-filter">Filter</button>
        </form>
    </div>

    <main class="main-content">
        
        <div class="action-header">
            <div class="title-group">
                <h1 class="page-title">Kelola Akun</h1>
                <p class="page-subtitle">total <?= $total ?> pengguna terdaftar</p>
            </div>
            
            <div class="btn-group">
                <a href="tambah_user.php" class="btn-add">+ Tambah User</a>
                <?php if ($flash): ?>
                    <div class="alert-mini alert-<?= $flash['type'] ?>">
                        <?= htmlspecialchars($flash['msg']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="copyright-group">
                <marquee behavior="scroll" direction="right" class="copy-text">
                    Dhean Oktaviansyah - 11 RPL 1
                </marquee>
                <img src="stella.jpeg" class="img-header-alumni" alt="Foto Sekolah">
            </div>
        </div>

        <div class="table-card">
            <?php if (empty($users)): ?>
                <div style="padding: 20px; text-align: center;">Data pengguna tidak ditemukan.</div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>USERNAME</th>
                            <th>ROLE</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $u): ?>
                            <tr>
                                <td style="text-align: center; color: #777;"><?= $i + 1 ?></td>
                                <td style="text-align: center;">
                                    <span class="badge-angkatan"><?= $u['user_id'] ?></span>
                                </td>
                                <td style="font-weight: 600; color: #555;"><?= htmlspecialchars($u['username']) ?></td>
                                <td>
                                    <?php
                                    $roleColor = match($u['role']) {
                                        'superadmin' => '#d9534f',
                                        'admin' => '#5bc0de',
                                        default => '#777'
                                    };
                                    ?>
                                    <span style="color: white; background: <?= $roleColor ?>; padding: 2px 10px; border-radius: 10px; font-size: 0.8rem; font-weight: bold;">
                                        <?= strtoupper($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['username'] !== $_SESSION['username']): ?>
                                        <a href="edit_user.php?id=<?= $u['user_id'] ?>" class="btn-edit">Edit</a>
                                        <form method="POST" action="delete_user.php" style="display:inline;" onsubmit="return confirm('Hapus user ini?')">
                                            <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                            <button type="submit" class="btn-delete">Hapus</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 0.8rem; color: #aaa; font-style: italic;">— Akun Anda</span>
                                    <?php endif; ?>
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