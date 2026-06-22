<?php
require 'config.php';
check_login();

// Ambil data stok barang
$stok_list = mysqli_query($conn, "SELECT b.*, k.nama_kategori 
                                   FROM barang b
                                   LEFT JOIN kategori k ON b.kategori_id = k.id
                                   ORDER BY b.nama_barang ASC");

// Hitung total stok dan nilai stok
$total_stok_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok) as total FROM barang"));
$total_stok = $total_stok_query['total'] ?? 0;

$nilai_stok_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stok * harga_beli) as total FROM barang"));
$nilai_stok = $nilai_stok_query['total'] ?? 0;

// Barang dengan stok rendah (di bawah 10)
$stok_rendah = mysqli_query($conn, "SELECT * FROM barang WHERE stok < 10 AND stok > 0 ORDER BY stok ASC");
$count_stok_rendah = mysqli_num_rows($stok_rendah);

// Barang dengan stok habis
$stok_habis = mysqli_query($conn, "SELECT * FROM barang WHERE stok = 0 ORDER BY nama_barang ASC");
$count_stok_habis = mysqli_num_rows($stok_habis);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok - Aplikasi Warung Turnip</title>
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
            <h2>Laporan Stok Barang</h2>

            <!-- Ringkasan Stok -->
            <div class="dashboard-grid">
                <div class="card" style="border-left-color: #27ae60;">
                    <h3>Total Stok</h3>
                    <div class="card-value"><?php echo $total_stok; ?> Unit</div>
                </div>

                <div class="card" style="border-left-color: #3498db;">
                    <h3>Nilai Stok</h3>
                    <div class="card-value">Rp <?php echo number_format($nilai_stok, 0, ',', '.'); ?></div>
                </div>

                <div class="card" style="border-left-color: #f39c12;">
                    <h3>Stok Rendah (&lt;10)</h3>
                    <div class="card-value"><?php echo $count_stok_rendah; ?> Item</div>
                </div>

                <div class="card" style="border-left-color: #e74c3c;">
                    <h3>Stok Habis</h3>
                    <div class="card-value"><?php echo $count_stok_habis; ?> Item</div>
                </div>
            </div>

            <hr style="margin: 30px 0;">

            <!-- Peringatan Stok Rendah -->
            <?php if ($count_stok_rendah > 0): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Perhatian:</strong> Ada <?php echo $count_stok_rendah; ?> item dengan stok rendah (kurang dari 10)
                </div>
            <?php endif; ?>

            <?php if ($count_stok_habis > 0): ?>
                <div class="alert alert-danger">
                    <strong>❌ Stok Habis:</strong> Ada <?php echo $count_stok_habis; ?> item yang stoknya habis
                </div>
            <?php endif; ?>

            <!-- Tab untuk berbagai laporan -->
            <div style="margin: 20px 0;">
                <button onclick="showTab('semua')" class="btn btn-info" style="margin-right: 5px;">Semua Barang</button>
                <button onclick="showTab('rendah')" class="btn btn-warning" style="margin-right: 5px;">Stok Rendah</button>
                <button onclick="showTab('habis')" class="btn btn-danger">Stok Habis</button>
            </div>

            <!-- Tab Semua Barang -->
            <div id="semua-tab">
                <h3>Semua Barang</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga Beli</th>
                            <th>Nilai Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        mysqli_data_seek($stok_list, 0);
                        while ($barang = mysqli_fetch_assoc($stok_list)): 
                            $nilai = $barang['stok'] * $barang['harga_beli'];
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $barang['kode_barang']; ?></td>
                                <td><?php echo $barang['nama_barang']; ?></td>
                                <td><?php echo $barang['nama_kategori']; ?></td>
                                <td>
                                    <strong style="<?php echo $barang['stok'] == 0 ? 'color: red;' : ($barang['stok'] < 10 ? 'color: orange;' : 'color: green;'); ?>">
                                        <?php echo $barang['stok']; ?>
                                    </strong>
                                </td>
                                <td>Rp <?php echo number_format($barang['harga_beli'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($nilai, 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tab Stok Rendah -->
            <div id="rendah-tab" style="display: none;">
                <h3>Barang dengan Stok Rendah (&lt;10)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga Beli</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($barang = mysqli_fetch_assoc($stok_rendah)): 
                        ?>
                            <tr style="background-color: #fff3cd;">
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $barang['kode_barang']; ?></td>
                                <td><?php echo $barang['nama_barang']; ?></td>
                                <td><?php echo $barang['nama_kategori']; ?></td>
                                <td><strong style="color: orange;"><?php echo $barang['stok']; ?></strong></td>
                                <td>Rp <?php echo number_format($barang['harga_beli'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tab Stok Habis -->
            <div id="habis-tab" style="display: none;">
                <h3>Barang dengan Stok Habis</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Harga Beli</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($barang = mysqli_fetch_assoc($stok_habis)): 
                        ?>
                            <tr style="background-color: #f8d7da;">
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $barang['kode_barang']; ?></td>
                                <td><?php echo $barang['nama_barang']; ?></td>
                                <td><?php echo $barang['nama_kategori']; ?></td>
                                <td>Rp <?php echo number_format($barang['harga_beli'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            document.getElementById('semua-tab').style.display = 'none';
            document.getElementById('rendah-tab').style.display = 'none';
            document.getElementById('habis-tab').style.display = 'none';
            
            document.getElementById(tab + '-tab').style.display = 'block';
        }
    </script>

    <footer>
        <p>&copy; 2024 Warung Turnip - All Rights Reserved</p>
    </footer>
</body>
</html>
