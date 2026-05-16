<?php
session_start();
require 'koneksi.php';

// Auth check: Hanya superadmin yang bisa menambah user
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: login.php');
    exit;
}

$errors = [];
$old    = ['username' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $role     = trim($_POST['role']     ?? 'user');

    // Validasi sederhana
    if ($username === '') {
        $errors['username'] = 'Username wajib diisi.';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username minimal 3 karakter.';
    } else {
        $chk = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $chk->execute([$username]);
        if ($chk->fetch()) {
            $errors['username'] = 'Username sudah digunakan.';
        }
    }

    if ($password === '') {
        $errors['password'] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    if ($confirm !== $password) {
        $errors['confirm'] = 'Konfirmasi password tidak cocok.';
    }

    $old = compact('username', 'role');

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $role]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => "Akun \"$username\" berhasil ditambahkan."
        ];
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
    <title>Tambah User | STELLA Alum</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>

        .navbar {
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            border-bottom: 2px solid #ddd;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        :root {
            --bg-body: #f0f2f5;
            --white: #ffffff;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --blue-accent: #5bc0de;
            --danger: #d9534f;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background-color: var(--bg-body); color: var(--text-dark); }

        /* Top Navigation */
        .top-nav { background-color: var(--white); padding: 0 40px; height: 70px; display: flex; align-items: center; border-bottom: 1px solid var(--border-color); }
        .nav-brand { font-size: 1.5rem; font-weight: 500; margin-left: 40px; color: #000; }
        .nav-links { display: flex; gap: 30px; }
        .nav-links a { text-decoration: none; color: #000; font-size: 1rem; }
        .nav-links a.active { color: #4ea8c7; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
    
        /* User Panel Top */
        .user-panel-wrapper { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .user-panel { background: var(--white); display: inline-flex; align-items: center; padding: 15px 25px; border-radius: 4px; border: 1px solid var(--border-color); }
        .user-avatar { width: 35px; height: 35px; background: #475569; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-left: 15px; }
        .user-info-text { font-weight: 600; font-size: 1rem; display: flex; gap: 8px; }
        .logout-text { color: var(--danger); text-decoration: none; }
        .system-logo { font-size: 2rem; color: #9ca3af; border-left: 2px solid #cbd5e1; padding-left: 20px; }

        /* Page Header */
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .page-title { font-size: 2.2rem; font-weight: 400; color: #333; }
        .btn-back { background-color: var(--blue-accent); color: white; text-decoration: none; padding: 10px 20px; font-weight: 600; border-radius: 2px; }

        /* Form Card */
        .form-card { background: var(--white); border: 1px solid var(--border-color); padding: 40px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .form-section { margin-bottom: 40px; }
        .section-title { font-size: 1.25rem; font-weight: 600; color: #333; margin-bottom: 25px; display: flex; align-items: center; }
        .section-title::before { content: ""; display: block; width: 4px; height: 24px; background-color: var(--blue-accent); margin-right: 12px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-label { font-weight: 700; font-size: 0.9rem; color: #333; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 2px; font-size: 1rem; }
        .form-error { color: var(--danger); font-size: 0.85rem; margin-top: 4px; }

        .btn-submit { background-color: #333; color: white; border: none; padding: 12px 30px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; }

        .footer-credit { text-align: center; margin-top: 40px; color: var(--text-muted); font-size: 0.9rem; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-brand">
            <span class="brand-text">STELLA Alum</span>
        </div>
        <div class="nav-links">
            <a href="dashboard_admin.php" class="nav-link">Data Alumni</a>
            <a href="tambah.php" class="nav-link active">Tambah Alumni</a>
            <a href="kelola_user.php" class="nav-link">Kelola User</a>
        </div>
    </nav>

    <div class="container">

        <div class="page-header">
            <div>
                <h1 class="page-title">Tambah User</h1>
                <p>Membuat kredensial akses baru</p>
            </div>
            <a href="users.php" class="btn-back">← Kembali</a>
        </div>

        <div class="form-card">
            <form method="POST" autocomplete="off">
                
                <div class="form-section">
                    <h2 class="section-title">Kredensial Login</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="username">Username *</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($old['username']) ?>" required>
                            <?php if (isset($errors['username'])): ?><span class="form-error"><?= $errors['username'] ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="role">Role *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="user" <?= $old['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="superadmin" <?= $old['role'] === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">Keamanan</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="password">Password *</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                            <?php if (isset($errors['password'])): ?><span class="form-error"><?= $errors['password'] ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="confirm">Konfirmasi Password *</label>
                            <input type="password" id="confirm" name="confirm" class="form-control" required>
                            <?php if (isset($errors['confirm'])): ?><span class="form-error"><?= $errors['confirm'] ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>

               <div class="form-group full-width" style="text-align: right;">
                    <button type="submit" class="btn-submit">Tambah Akun</button>
                </div>

            </form>
        </div>

        <footer class="footer-credit">
          <div class="footer-credit">
           Dhean Oktaviansyah
        </div>
        </footer>

    </div>

</body>
</html>