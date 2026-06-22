<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'toko_management');

// Koneksi Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Session
session_start();

// Fungsi untuk mengecek login
function is_logged_in() {
    return isset($_SESSION['admin_id']);
}

// Fungsi untuk redirect jika belum login
function check_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}
?>
