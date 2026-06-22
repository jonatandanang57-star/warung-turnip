<?php
require 'config.php';
check_login();

// Ambil data untuk dashboard
$total_barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang"))['total'];
$total_stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) as total FROM barang"))['total'];
$total_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan"))['total'];
$total_pemasukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM penjualan"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Toko Warung Turnip</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>🏪 APLIKASI PENGELOLAAN WARUNG TURNIP</h1>
    </header>

    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="barang.php">Barang</a></li>
            <li><a href="barang_masuk.php">Barang Masuk</a></li>
            <li><a href="penjualan.php">Penjualan</a></li>
            <li><a href="stok.php">Laporan Stok</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="content">
            <h2>Dashboard</h2>
            <p class="intro-text">Selamat datang, <strong><?php echo $_SESSION['admin_nama']; ?></strong>. Ini ringkasan bisnis warung Anda hari ini.</p>
            <hr class="section-divider">

            <div class="dashboard-grid">
                <div class="card" style="border-left-color: #3498db;">
                    <h3>Total Barang</h3>
                    <div class="card-value"><?php echo $total_barang; ?></div>
                </div>

                <div class="card" style="border-left-color: #27ae60;">
                    <h3>Total Stok</h3>
                    <div class="card-value"><?php echo $total_stok ?? 0; ?></div>
                </div>

                <div class="card" style="border-left-color: #f39c12;">
                    <h3>Total Penjualan</h3>
                    <div class="card-value"><?php echo $total_penjualan; ?></div>
                </div>

                <div class="card" style="border-left-color: #e74c3c;">
                    <h3>Total Pemasukan</h3>
                    <div class="card-value">Rp <?php echo number_format($total_pemasukan ?? 0, 0, ',', '.'); ?></div>
                </div>
            </div>

            <hr style="margin: 30px 0;">

            <h3>Akses Menu Utama:</h3>
            <ul style="margin-top: 15px; line-height: 2;">
                <li><a href="barang.php" class="btn btn-info">Kelola Barang</a> - Tambah, edit, hapus barang</li>
                <li><a href="barang_masuk.php" class="btn btn-success">Input Barang Masuk</a> - Catat barang yang masuk</li>
                <li><a href="penjualan.php" class="btn btn-primary">Catat Penjualan</a> - Pencatatan penjualan barang</li>
                <li><a href="stok.php" class="btn btn-warning">Laporan Stok</a> - Lihat laporan stok barang</li>
            </ul>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Warung Turnip - All Rights Reserved</p>
    </footer>
</body>
</html>
