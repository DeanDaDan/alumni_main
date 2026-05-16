<?php
session_start();
require 'koneksi.php';

// Kalau sudah login, langsung lempar ke dashboard masing-masing
if (isset($_SESSION['user_id'])) {
    $target = (in_array($_SESSION['role'], ['admin', 'superadmin'])) ? 'dashboard_admin.php' : 'dashboard_user.php';
    header("Location: $target");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $valid_login = false;
            if (password_verify($password, $user['password'])) {
                $valid_login = true;
            } elseif ($user['password'] === $password) {
                $valid_login = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $updateStmt->execute([$newHash, $user['user_id']]);
            }

            if ($valid_login) {
                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                if (in_array($user['role'], ['admin', 'superadmin'])) {
                    header('Location: dashboard_admin.php');
                } else {
                    header('Location: dashboard_user.php');
                }
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Harap isi username dan password!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Alumni Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #333333;
            --accent: #5bc0de;
            --bg-gray: #f4f7f6;
            --white: #ffffff;
            --error: #e74c3c;
            --text-muted: #777;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-gray);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .auth-card {
            background: var(--white);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        /* Header dengan Logo */
        .auth-header {
            display: flex;
            align-items: center;
            gap: 15px; /* Jarak antara logo dan teks */
            margin-bottom: 30px;
        }

        .auth-logo {
            width: 60px; /* Ukuran logo */
            height: auto;
            object-fit: contain;
        }

        .auth-title {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
            color: #222;
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* Indikator Garis Biru */
        .section-indicator {
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
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
            margin-bottom: 20px;
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
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(91, 192, 222, 0.1);
        }

        .btn-login {
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

        .btn-login:hover {
            background-color: #000;
        }

        .alert-error {
            background: #fff5f5;
            color: var(--error);
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid var(--error);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .auth-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .help-text {
            display: block;
            margin-top: 15px;
            font-size: 0.8rem;
            color: #bbb;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="telkom.jpeg" alt="Logo Telkom" class="auth-logo">
                <div>
                    <h1 class="auth-title">STELLA Alum</h1>
                    <p class="auth-subtitle">Manajemen Data Alumni</p>
                </div>
            </div>

            <div class="section-indicator">Login Akun</div>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Masukkan username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-login">Masuk Sekarang</button>
            </form>

            <div class="auth-footer">
                Belum punya akun? <a href="register.php">Daftar Sekarang</a>
                <code class="help-text">Butuh bantuan? <a href="#" style="color: #999;">Hubungi Admin</a></code>
            </div>
        </div>
    </div>
</body>
</html>