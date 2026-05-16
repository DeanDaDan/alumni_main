<?php
session_start();
require 'koneksi.php';

// 1. AUTH CHECK
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}

// 2. AMBIL ID DARI URL
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: dashboard_admin.php');
    exit;
}

// 3. AMBIL DATA ALUMNI LAMA
$stmt = $pdo->prepare("SELECT * FROM alumni WHERE id_alumni = ?");
$stmt->execute([$id]);
$alumni = $stmt->fetch();

if (!$alumni) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data alumni tidak ditemukan.'];
    header('Location: dashboard_admin.php');
    exit;
}

$errors = [];
$data   = $alumni; 

// 4. PROSES UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['nim', 'nama', 'angkatan', 'jurusan', 'email', 'alamat'];
    foreach ($fields as $f) {
        $data[$f] = trim($_POST[$f] ?? '');
    }

    if (!$data['nim'])      $errors[] = 'NIS wajib diisi.';
    if (!$data['nama'])     $errors[] = 'Nama wajib diisi.';
    if (!$data['angkatan']) $errors[] = 'Angkatan wajib diisi.';
    if (!$data['jurusan'])  $errors[] = 'Jurusan wajib diisi.';
    if (!$data['email'])    $errors[] = 'Email wajib diisi.';
    elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';

    if (!$errors) {
        $chk = $pdo->prepare("SELECT id_alumni FROM alumni WHERE nim = ? AND id_alumni != ?");
        $chk->execute([$data['nim'], $id]);
        if ($chk->fetch()) $errors[] = 'NIS/NIM sudah digunakan alumni lain.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE alumni 
            SET nim=?, nama=?, angkatan=?, jurusan=?, email=?, alamat=?
            WHERE id_alumni=?
        ");
        $stmt->execute([
            $data['nim'], 
            $data['nama'], 
            $data['angkatan'], 
            $data['jurusan'], 
            $data['email'], 
            $data['alamat'] ?: null, 
            $id
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data alumni berhasil diperbarui!'];
        header('Location: dashboard_admin.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alumni | <?= htmlspecialchars($alumni['nama']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #333;
            --accent: #5bc0de;
            --bg: #f4f7f6;
            --white: #ffffff;
            --border: #dee2e6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body { background-color: var(--bg); color: #333; }

        /* NAVBAR */
        .navbar {
            background: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .brand-text { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { text-decoration: none; color: #666; font-weight: 500; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { color: var(--accent); }

        /* MAIN */
        .main-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .action-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            margin-bottom: 30px; 
        }
        .page-title { font-size: 2rem; font-weight: 700; color: var(--primary); }
        .btn-back { 
            background: var(--accent); 
            color: white; 
            text-decoration: none; 
            padding: 8px 16px; 
            border-radius: 4px; 
            font-size: 0.9rem; 
            font-weight: 600;
        }

        /* ALERT */
        .alert-error {
            background: #fff5f5; color: #e74c3c; padding: 15px;
            border-left: 4px solid #e74c3c; margin-bottom: 25px;
            border-radius: 4px;
        }

        /* FORM CARD */
        .form-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 35px;
        }

        .section-indicator {
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 25px;
        }
        .section-indicator::before {
            content: ""; width: 4px; height: 20px; background: var(--accent);
            margin-right: 12px; display: inline-block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .full { grid-column: span 2; }
        
        label { font-weight: 600; font-size: 0.85rem; color: #444; }
        .form-control {
            padding: 12px; border: 1px solid var(--border);
            border-radius: 6px; font-size: 1rem; outline: none;
        }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91,192,222,0.1); }

        .form-actions {
            border-top: 1px solid #eee;
            padding-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-submit {
            background: var(--primary); color: white; border: none;
            padding: 12px 30px; font-weight: 600; border-radius: 6px;
            cursor: pointer; transition: 0.2s;
        }
        .btn-submit:hover { background: #000; }
        .btn-cancel { color: #888; text-decoration: none; font-size: 0.9rem; font-weight: 500; }
        .btn-cancel:hover { color: #e74c3c; }

        footer { margin-top: 50px; padding-bottom: 30px; text-align: center; color: #999; font-size: 0.85rem; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand-text">STELLA Alum</div>
    </nav>

    <main class="main-container">
        <div class="action-header">
            <div>
                <h1 class="page-title">Edit Data Alumni</h1>
                <p style="color: #777;">Mengupdate data untuk: <strong><?= htmlspecialchars($alumni['nama']) ?></strong></p>
            </div>
            <a href="dashboard_admin.php" class="btn-back">← Kembali</a>
        </div>

        <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul style="list-style: none;">
                <?php foreach ($errors as $e): ?>
                <li>⚠️ <?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" class="form-card">
            <div class="section-indicator">Informasi Akademik</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>NIS / NIM *</label>
                    <input type="text" name="nim" class="form-control" value="<?= htmlspecialchars($data['nim']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Angkatan *</label>
                    <input type="number" name="angkatan" class="form-control" value="<?= htmlspecialchars($data['angkatan']) ?>" required>
                </div>
                <div class="form-group full">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
                </div>
                <div class="form-group full">
                    <label>Jurusan / Program Studi *</label>
                    <select name="jurusan" class="form-control" required>
                        <option value="">-- Pilih Jurusan --</option>
                        <?php
                        $jurusans = ['Rekayasa Perangkat Lunak', 'Teknik Jaringan Akses Telekomunikasi', 'Teknik Komputer dan Jaringan', 'Animasi'];
                        foreach ($jurusans as $j):
                            $selected = ($data['jurusan'] === $j) ? 'selected' : '';
                        ?>
                        <option value="<?= $j ?>" <?= $selected ?>><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="section-indicator">Kontak & Domisili</div>
            <div class="form-grid">
                <div class="form-group full">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
                </div>
                <div class="form-group full">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">Simpan Data</button>
                <a href="dashboard_admin.php" class="btn-cancel">Batal</a>
            </div>
        </form>

        <footer>
            Dhean Oktaviansyah
        </footer>
    </main>

</body>
</html>