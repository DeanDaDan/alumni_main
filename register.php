<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    $target = ($_SESSION['role'] === 'user') ? 'dashboard_user.php' : 'dashboard_admin.php';
    header("Location: $target");
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$username) $errors[] = "Username tidak boleh kosong.";
    if (strlen($password) < 4) $errors[] = "Password minimal 4 karakter.";
    if ($password !== $confirm) $errors[] = "Konfirmasi password tidak cocok.";

    if (!$errors) {
       $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "Username sudah terdaftar.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            try {
                $stmt->execute([$username, $hashedPassword]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Gagal mendaftar: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun – Alumni Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #333333;
            --accent: #5bc0de;
            --bg-gray: #f4f7f6;
            --white: #ffffff;
            --error: #e74c3c;
            --success: #2ecc71;
            --text-muted: #777;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-gray);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
        }

        .auth-card {
            background: var(--white);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .auth-header {
            margin-bottom: 25px;
        }

        .auth-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: #222;
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.4;
        }

        /* Garis Biru Indikator */
        .section-indicator {
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 25px 0;
        }

        .section-indicator::before {
            content: "";
            width: 4px;
            height: 20px;
            background-color: var(--accent);
            margin-right: 12px;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(91, 192, 222, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-register:hover {
            background-color: #000;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .alert-error {
            background: #fff5f5;
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-success {
            background: #f0fff4;
            color: #276749;
            border-left: 4px solid var(--success);
        }

        .auth-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">STELLA Alum</h1>
            <p class="auth-subtitle">Bergabunglah dengan jaringan alumni kami.</p>
        </div>

        <div class="section-indicator">Daftar Akun Baru</div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>Berhasil!</strong> Akun Anda telah dibuat. <br>
                Sekarang Anda bisa <a href="login.php" style="color: #2ecc71; font-weight: bold; text-decoration: underline;">Login di sini</a>.
            </div>
        <?php else: ?>
            
            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <ul style="margin-left: 15px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Buat username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Minimal 4 karakter" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Ulangi password" required>
                </div>

                <button type="submit" class="btn-register">Daftar Sekarang</button>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>