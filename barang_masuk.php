<?php
require 'config.php';
require 'validation_helper.php';
check_login();

$validation_errors = [];
$old_keterangan = '';

// Proses input barang masuk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['input_barang_masuk'])) {
    $barang_id = trim(mysqli_real_escape_string($conn, $_POST['barang_id']));
    $jumlah = trim(mysqli_real_escape_string($conn, $_POST['jumlah']));
    $harga_satuan = trim(mysqli_real_escape_string($conn, $_POST['harga_satuan']));
    $keterangan = trim(mysqli_real_escape_string($conn, $_POST['keterangan']));
    $admin_id = $_SESSION['admin_id'];
    $old_keterangan = $keterangan;
    
    // Validasi input
    $validation_errors = validateBarangMasuk([
        'barang_id' => $barang_id,
        'jumlah' => $jumlah,
        'harga_satuan' => $harga_satuan,
    ]);

    if (empty($validation_errors['barang_id'])) {
        $barang_exists = mysqli_query($conn, "SELECT id FROM barang WHERE id = '$barang_id'");
        if (!$barang_exists || mysqli_num_rows($barang_exists) === 0) {
            $validation_errors['barang_id'] = 'Barang tidak ditemukan';
        }
    }

    if (!empty($validation_errors)) {
        $error = implode(', ', $validation_errors);
    } else {
        $total_harga = floatval($jumlah) * floatval($harga_satuan);

        // Insert ke tabel barang_masuk
        $query = "INSERT INTO barang_masuk (barang_id, jumlah, harga_satuan, total_harga, keterangan, admin_id) 
                  VALUES ('$barang_id', '$jumlah', '$harga_satuan', '$total_harga', '$keterangan', '$admin_id')";
        
        if (mysqli_query($conn, $query)) {
            // Update stok barang
            $update_stok = "UPDATE barang SET stok = stok + $jumlah WHERE id = '$barang_id'";
            mysqli_query($conn, $update_stok);
            
            $success = "Barang masuk berhasil dicatat!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Ambil data barang
$barang_list = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang ASC");

// Ambil data barang masuk
$barang_masuk_list = mysqli_query($conn, "SELECT bm.*, b.kode_barang, b.nama_barang, a.nama_lengkap 
                                           FROM barang_masuk bm
                                           LEFT JOIN barang b ON bm.barang_id = b.id
                                           LEFT JOIN admin a ON bm.admin_id = a.id
                                           ORDER BY bm.tanggal_masuk DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Masuk - Aplikasi Warung Turnip</title>
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
            <h2>Input Barang Masuk</h2>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Form Input Barang Masuk -->
            <form method="POST">
                <div class="form-group">
                    <label for="barang_id">Nama Barang:</label>
                    <select id="barang_id" name="barang_id" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php 
                        while ($barang = mysqli_fetch_assoc($barang_list)): 
                        ?>
                            <option value="<?php echo $barang['id']; ?>" <?php echo (isset($_POST['barang_id']) && $_POST['barang_id'] == $barang['id']) ? 'selected' : ''; ?>>
                                <?php echo $barang['kode_barang'] . ' - ' . $barang['nama_barang']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="error-msg" id="error_barang_id"><?php echo $validation_errors['barang_id'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah Masuk:</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" required value="<?php echo htmlspecialchars($_POST['jumlah'] ?? ''); ?>">
                    <div class="error-msg" id="error_jumlah"><?php echo $validation_errors['jumlah'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="harga_satuan">Harga Satuan (Rp):</label>
                    <input type="number" id="harga_satuan" name="harga_satuan" min="0" step="0.01" required value="<?php echo htmlspecialchars($_POST['harga_satuan'] ?? ''); ?>">
                    <div class="error-msg" id="error_harga_satuan"><?php echo $validation_errors['harga_satuan'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan:</label>
                    <textarea id="keterangan" name="keterangan" placeholder="Misal: Dari supplier ABC, PO No. 123"><?php echo htmlspecialchars($old_keterangan); ?></textarea>
                </div>

                <button type="submit" name="input_barang_masuk" class="btn btn-success">Simpan Barang Masuk</button>
            </form>

            <hr style="margin: 30px 0;">

            <!-- Riwayat Barang Masuk -->
            <h3>Riwayat Barang Masuk</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                        <th>Keterangan</th>
                        <th>Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($barang_masuk = mysqli_fetch_assoc($barang_masuk_list)): 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($barang_masuk['tanggal_masuk'])); ?></td>
                            <td><?php echo $barang_masuk['kode_barang']; ?></td>
                            <td><?php echo $barang_masuk['nama_barang']; ?></td>
                            <td><?php echo $barang_masuk['jumlah']; ?></td>
                            <td>Rp <?php echo number_format($barang_masuk['harga_satuan'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($barang_masuk['total_harga'], 0, ',', '.'); ?></td>
                            <td><?php echo $barang_masuk['keterangan']; ?></td>
                            <td><?php echo $barang_masuk['nama_lengkap']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Warung Turnip - All Rights Reserved</p>
    </footer>

    <script>
        const form = document.querySelector('form');
        const barangId = document.getElementById('barang_id');
        const jumlah = document.getElementById('jumlah');
        const hargaSatuan = document.getElementById('harga_satuan');

        // Validasi Barang
        function validateBarang() {
            const value = barangId.value;
            const parent = barangId.parentElement;
            const errorDiv = document.getElementById('error_barang_id');
            
            if (!value || isNaN(parseInt(value))) {
                errorDiv.textContent = 'Barang harus dipilih';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else {
                errorDiv.textContent = '';
                parent.classList.remove('has-error');
                parent.classList.add('has-success');
                return true;
            }
        }

        // Validasi Jumlah
        function validateJumlah() {
            const value = jumlah.value;
            const parent = jumlah.parentElement;
            const errorDiv = document.getElementById('error_jumlah');
            
            if (!value) {
                errorDiv.textContent = 'Jumlah masuk tidak boleh kosong';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (!/^[0-9]+$/.test(value) || parseInt(value) <= 0) {
                errorDiv.textContent = 'Jumlah masuk harus angka bulat positif';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (parseInt(value) > 999999) {
                errorDiv.textContent = 'Jumlah masuk maksimal 999.999';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else {
                errorDiv.textContent = '';
                parent.classList.remove('has-error');
                parent.classList.add('has-success');
                return true;
            }
        }

        // Validasi Harga Satuan
        function validateHargaSatuan() {
            const value = hargaSatuan.value;
            const parent = hargaSatuan.parentElement;
            const errorDiv = document.getElementById('error_harga_satuan');
            
            if (!value) {
                errorDiv.textContent = 'Harga satuan tidak boleh kosong';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (isNaN(value) || parseFloat(value) < 0) {
                errorDiv.textContent = 'Harga satuan harus angka';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (!/^\d+(\.\d{1,2})?$/.test(value)) {
                errorDiv.textContent = 'Harga satuan hanya boleh 2 desimal';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (parseFloat(value) > 999999999) {
                errorDiv.textContent = 'Harga satuan tidak boleh lebih dari Rp 999.999.999';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else {
                errorDiv.textContent = '';
                parent.classList.remove('has-error');
                parent.classList.add('has-success');
                return true;
            }
        }

        // Real-time validation listeners
        barangId.addEventListener('change', validateBarang);
        barangId.addEventListener('blur', validateBarang);
        jumlah.addEventListener('input', validateJumlah);
        jumlah.addEventListener('blur', validateJumlah);
        hargaSatuan.addEventListener('input', validateHargaSatuan);
        hargaSatuan.addEventListener('blur', validateHargaSatuan);

        form.addEventListener('submit', function(e) {
            let errors = [];

            if (!validateBarang()) errors.push('Barang tidak valid');
            if (!validateJumlah()) errors.push('Jumlah masuk tidak valid');
            if (!validateHargaSatuan()) errors.push('Harga satuan tidak valid');

            if (errors.length > 0) {
                e.preventDefault();
                alert('⚠️ Kesalahan Validasi:\n\n' + errors.join('\n'));
            }
        });

        // Hitung total harga otomatis
        function hitungTotal() {
            if (jumlah.value && hargaSatuan.value) {
                const total = parseInt(jumlah.value) * parseFloat(hargaSatuan.value);
                console.log('Total: Rp ' + total.toLocaleString('id-ID'));
            }
        }

        jumlah.addEventListener('change', hitungTotal);
        hargaSatuan.addEventListener('change', hitungTotal);
    </script>
</body>
</html>
