<?php
session_start();
require 'koneksi.php';

// 1. Proteksi akses Superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: users.php');
    exit;
}

// 2. Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Akun tidak ditemukan.'];
    header('Location: users.php');
    exit;
}

// 3. Proteksi: Tidak boleh edit diri sendiri di sini (harus lewat profil)
if ($user['username'] === $_SESSION['username']) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gunakan halaman profil untuk mengedit akun Anda.'];
    header('Location: users.php');
    exit;
}

$errors = [];
$data = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['username'] = trim($_POST['username'] ?? '');
    $data['role']     = trim($_POST['role']     ?? 'user');
    $password         = trim($_POST['password'] ?? '');
    $confirm          = trim($_POST['confirm']  ?? '');

    if (!$data['username']) {
        $errors[] = 'Username wajib diisi.';
    } else {
        $chk = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $chk->execute([$data['username'], $id]);
        if ($chk->fetch()) {
            $errors[] = 'Username sudah digunakan akun lain.';
        }
    }

    if ($password !== '') {
        if (strlen($password) < 4) $errors[] = 'Password minimal 4 karakter.';
        if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET username = ?, password = ?, role = ? WHERE user_id = ?";
            $params = [$data['username'], $hash, $data['role'], $id];
        } else {
            $sql = "UPDATE users SET username = ?, role = ? WHERE user_id = ?";
            $params = [$data['username'], $data['role'], $id];
        }
        $pdo->prepare($sql)->execute($params);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Akun berhasil diperbarui!'];
        header('Location: users.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | <?= htmlspecialchars($user['username']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #333;
            --accent: #5bc0de;
            --bg: #f4f7f6;
            --white: #ffffff;
            --border: #dee2e6;
            --error: #e74c3c;
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
            background: #fff5f5; color: var(--error); padding: 15px;
            border-left: 4px solid var(--error); margin-bottom: 25px;
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
            gap: 25px;
            margin-bottom: 30px;
        }
        .form-group { display: flex; flex-direction: column; gap: 8px; position: relative; }
        .full { grid-column: span 2; }
        
        label { font-weight: 600; font-size: 0.85rem; color: #444; }
        .form-control {
            padding: 12px; border: 1px solid var(--border);
            border-radius: 6px; font-size: 1rem; outline: none; width: 100%;
        }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91,192,222,0.1); }

        /* Password Wrapper for Toggle */
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .toggle-password {
            position: absolute; right: 12px; background: none; border: none;
            color: var(--accent); cursor: pointer; font-size: 0.75rem;
            font-weight: 700; text-transform: uppercase;
        }

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
                <h1 class="page-title">Edit User</h1>
                <p style="color: #777;">Mengelola akun: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
            </div>
            <a href="users.php" class="btn-back">← Kembali</a>
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

        <div class="form-card">
            <form method="POST" autocomplete="off">
                
                <div class="section-indicator">Informasi Login</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= htmlspecialchars($data['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Hak Akses (Role) *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="user" <?= $data['role'] === 'user' ? 'selected' : '' ?>>User (Lihat Data)</option>
                            <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : '' ?>>Admin (Edit Data)</option>
                            <option value="superadmin" <?= $data['role'] === 'superadmin' ? 'selected' : '' ?>>Superadmin (Kontrol Penuh)</option>
                        </select>
                    </div>
                </div>

                <div class="section-indicator" style="margin-top: 20px;">Keamanan</div>
                <p style="font-size: 0.8rem; color: #888; margin-bottom: 15px; margin-top: -15px;">
                    *Kosongkan kolom jika tidak ingin mengganti password.
                </p>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Min. 4 karakter">
                            <button type="button" class="toggle-password" onclick="togglePW('password', this)">Lihat</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm">Konfirmasi Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm" name="confirm" class="form-control" placeholder="Ulangi password">
                            <button type="button" class="toggle-password" onclick="togglePW('confirm', this)">Lihat</button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="users.php" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-submit">Simpan Perubahan</button>
                </div>

            </form>
        </div>

        <footer>
            Dhean Oktaviansyah
        </footer>
    </main>

    <script>
        function togglePW(id, btn) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                btn.textContent = "Tutup";
            } else {
                input.type = "password";
                btn.textContent = "Lihat";
            }
        }
    </script>
</body>
</html>