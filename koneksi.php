<?php
$host     = 'localhost';
$dbname   = 'db_alumni';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
        <h2>Koneksi Database Gagal</h2>
        <p>' . $e->getMessage() . '</p>
    </div>');
}
?>
