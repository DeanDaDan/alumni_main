<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$alumni = $stmt->fetch();

if ($alumni) {
    $del = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $del->execute([$id]);
    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Akun "' . htmlspecialchars($alumni['nama']) . '" berhasil dihapus.'
    ];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Pengguna tidak ditemukan.'];
}

header('Location: users.php');
exit;
