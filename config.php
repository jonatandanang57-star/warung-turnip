<?php
// Ambil konfigurasi dari Railway jika tersedia,
// jika tidak ada maka gunakan konfigurasi XAMPP.

$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'toko_management';
$db_port = getenv('MYSQLPORT') ?: '3306';

// Koneksi Database
$conn = new mysqli(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    (int)$db_port
);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8");

session_start();

function is_logged_in()
{
    return isset($_SESSION['admin_id']);
}

function check_login()
{
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}
?>