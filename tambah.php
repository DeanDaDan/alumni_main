<?php
session_start();
require 'koneksi.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pastikan hanya admin/superadmin yang bisa akses halaman ini
if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: dashboard_user.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alumni | STELLA Alum</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* RESET & BASE */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'DM Sans', sans-serif; }
        body { background-color: #efefef; color: #333; line-height: 1.6; }

        /* NAVBAR */
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
        .nav-brand { display: flex; align-items: center; gap: 10px; }
        .brand-text { font-size: 1.8rem; font-weight: normal; color: #000; letter-spacing: -1px; }
        .nav-links { display: flex; gap: 25px; margin-right: auto; margin-left: 50px; }
        .nav-link { text-decoration: none; color: #000; font-size: 1.1rem; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { color: #5bc0de; }

        /* CONTROLS (PROFIL & INFO) */
        .controls-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .user-profile {
            background: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #aaa;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.15);
        }
        .user-avatar {
            background: #4a5c75;
            color: white;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .user-info { display: flex; flex-direction: column; }
        .user-role { font-weight: bold; font-size: 1rem; color: #333; }
        .btn-logout-small { color: #d9534f; text-decoration: none; font-size: 0.85rem; font-weight: bold; margin-top: 2px; }

        .copyright-group { border-left: 2px solid #aaa; padding-left: 20px; display: flex; align-items: center; }
        .copy-text { font-size: 1.8rem; color: #888; font-weight: normal; }

        /* MAIN CONTENT */
        .main-container { max-width: 1200px; margin: 0 auto; padding: 0 20px 50px; }
        
        .action-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .page-title { font-size: 2.2rem; font-weight: normal; letter-spacing: -1px; color: #000; }
        .page-subtitle { font-size: 0.9rem; color: #666; margin-top: -5px; }
        .btn-back { background: #5bc0de; color: white; text-decoration: none; padding: 6px 15px; font-weight: bold; font-size: 0.9rem; }

        /* FORM CARD */
        .form-card {
            background: #fff;
            border: 1px solid #aaa;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .form-section-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0 25px;
        }
        .form-section-title::before {
            content: "";
            width: 5px;
            height: 25px;
            background: #5bc0de;
            display: block;
        }
        .form-section-title h2 { font-size: 1.2rem; font-weight: bold; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: span 2; }
        
        label { font-weight: bold; font-size: 0.9rem; }
        .form-control {
            padding: 12px;
            border: 1px solid #aaa;
            font-size: 1rem;
            outline: none;
            width: 100%;
        }
        .form-control:focus { border-color: #5bc0de; }

        .form-actions {
            border-top: 1px solid #eee;
            padding-top: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
         .btn-submit { background-color: #333; color: white; border: none; padding: 12px 30px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; }

        .footer-credit { text-align: center; margin-top: 40px; color: var(--text-muted); font-size: 0.9rem; }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-brand">
            <span class="brand-text">STELLA Alum</span>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="main-container">
        <div class="action-header">
            <div class="title-group">
                <h1 class="page-title">Tambah Alumni</h1>
                <p class="page-subtitle">Isi data alumni baru di bawah ini</p>
            </div>
            <a href="dashboard_admin.php" class="btn-back">← Kembali</a>
        </div>

        <form action="proses_tambah.php" method="POST" class="form-card">
            
            <!-- SEKSI AKADEMIK -->
            <div class="form-section-title">
                <h2>Informasi Akademik</h2>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>NIS *</label>
                    <input type="text" name="nim" class="form-control" placeholder="Contoh: 2019001" required>
                </div>
                <div class="form-group">
                    <label>Angkatan *</label>
                    <input type="number" name="angkatan" class="form-control" placeholder="Contoh: 11" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="form-group">
                    <label>Jurusan / Program Studi *</label>
                    <select name="jurusan" class="form-control" required>
                        <option value="">-- Pilih Jurusan --</option>
                        <option value="Rekayasa Perangkat Lunak">RPL</option>
                        <option value="Teknik Komputer dan Jaringan">TKJ</option>
                        <option value="Teknik Jaringan Akses Telekomunikasi">TJAT</option>
                        <option value="Animasi">Animasi</option>
                    </select>
                </div>
            </div>

            <!-- SEKSI KONTAK -->
            <div class="form-section-title">
                <h2>Informasi Kontak & Domisili</h2>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required>
                </div>
                <div class="form-group full">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap saat ini"></textarea>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="form-group full-width" style="text-align: right;">
                    <button type="submit" class="btn-submit">Tambah Akun</button>
                </div>

        <footer>
            <div class="footer-credit">
           Dhean Oktaviansyah
            </div>

        </footer>
    </main>

</body>
</html>