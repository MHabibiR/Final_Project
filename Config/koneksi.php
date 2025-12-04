<?php
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../Class/EnvLoader.php';

try {
    EnvLoader::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Ambil variable dari .env
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>