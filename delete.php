<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard_admin.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    header('Location: dashboard_admin.php');
    exit;
}

$stmt = $pdo->prepare("SELECT nama FROM alumni WHERE id_alumni = ?");
$stmt->execute([$id]);
$alumni = $stmt->fetch();

if ($alumni) {
    $del = $pdo->prepare("DELETE FROM alumni WHERE id_alumni = ?");
    $del->execute([$id]);
    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Data alumni "' . htmlspecialchars($alumni['nama']) . '" berhasil dihapus.'
    ];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data alumni tidak ditemukan.'];
}

header('Location: dashboard_admin.php');
exit;
?>
