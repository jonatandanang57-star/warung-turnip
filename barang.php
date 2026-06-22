<?php
require 'config.php';
require 'validation_helper.php';
check_login();

$validation_errors = [];

// Proses tambah barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_barang'])) {
    $kode_barang = trim(mysqli_real_escape_string($conn, $_POST['kode_barang']));
    $nama_barang = trim(mysqli_real_escape_string($conn, $_POST['nama_barang']));
    $kategori_id = trim(mysqli_real_escape_string($conn, $_POST['kategori_id']));
    $harga_beli = trim(mysqli_real_escape_string($conn, $_POST['harga_beli']));
    $harga_jual = trim(mysqli_real_escape_string($conn, $_POST['harga_jual']));
    $satuan = trim(mysqli_real_escape_string($conn, $_POST['satuan']));
    $deskripsi = trim(mysqli_real_escape_string($conn, $_POST['deskripsi']));

    // Validasi input
    $validation_errors = validateBarangForm([
        'kode_barang' => $kode_barang,
        'nama_barang' => $nama_barang,
        'kategori_id' => $kategori_id,
        'harga_beli' => $harga_beli,
        'harga_jual' => $harga_jual,
        'satuan' => $satuan,
    ]);

    if (empty($validation_errors['kategori_id'])) {
        $kategori_exists = mysqli_query($conn, "SELECT id FROM kategori WHERE id = '$kategori_id'");
        if (!$kategori_exists || mysqli_num_rows($kategori_exists) === 0) {
            $validation_errors['kategori_id'] = 'Kategori tidak ditemukan';
        }
    }

    if (!empty($validation_errors)) {
        $error = implode(', ', $validation_errors);
    } else {
        // Cek kode barang sudah ada atau tidak
        $cek_kode = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM barang WHERE kode_barang = '$kode_barang'");
        $cek_result = mysqli_fetch_assoc($cek_kode);
        
        if ($cek_result['cnt'] > 0) {
            $error = "Kode barang " . htmlspecialchars($kode_barang) . " sudah terdaftar!";
        } else {
            $query = "INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga_beli, harga_jual, satuan, deskripsi) 
                      VALUES ('$kode_barang', '$nama_barang', '$kategori_id', '$harga_beli', '$harga_jual', '$satuan', '$deskripsi')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Barang berhasil ditambahkan!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Proses edit barang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_barang'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama_barang = trim(mysqli_real_escape_string($conn, $_POST['nama_barang']));
    $kategori_id = trim(mysqli_real_escape_string($conn, $_POST['kategori_id']));
    $harga_beli = trim(mysqli_real_escape_string($conn, $_POST['harga_beli']));
    $harga_jual = trim(mysqli_real_escape_string($conn, $_POST['harga_jual']));
    $satuan = trim(mysqli_real_escape_string($conn, $_POST['satuan']));
    $deskripsi = trim(mysqli_real_escape_string($conn, $_POST['deskripsi']));

    // Validasi input
    $validation_errors = validateBarangForm([
        'kode_barang' => $edit_data['kode_barang'],
        'nama_barang' => $nama_barang,
        'kategori_id' => $kategori_id,
        'harga_beli' => $harga_beli,
        'harga_jual' => $harga_jual,
        'satuan' => $satuan,
    ]);

    if (empty($validation_errors['kategori_id'])) {
        $kategori_exists = mysqli_query($conn, "SELECT id FROM kategori WHERE id = '$kategori_id'");
        if (!$kategori_exists || mysqli_num_rows($kategori_exists) === 0) {
            $validation_errors['kategori_id'] = 'Kategori tidak ditemukan';
        }
    }

    if (!empty($validation_errors)) {
        $error = implode(', ', $validation_errors);
    } else {
        $query = "UPDATE barang SET nama_barang='$nama_barang', kategori_id='$kategori_id', 
                  harga_beli='$harga_beli', harga_jual='$harga_jual', satuan='$satuan', deskripsi='$deskripsi' 
                  WHERE id='$id'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Barang berhasil diupdate!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Proses hapus barang
// Proses hapus paksa (hapus transaksi terkait lalu hapus barang)
if (isset($_GET['hapus_force'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus_force']);
    mysqli_begin_transaction($conn);
    $delMasuk = mysqli_query($conn, "DELETE FROM barang_masuk WHERE barang_id='$id'");
    $delJual = mysqli_query($conn, "DELETE FROM penjualan WHERE barang_id='$id'");

    if ($delMasuk === false || $delJual === false) {
        mysqli_rollback($conn);
        $error = "Gagal menghapus transaksi terkait: " . mysqli_error($conn);
    } else {
        $delBarang = mysqli_query($conn, "DELETE FROM barang WHERE id='$id'");
        if ($delBarang) {
            mysqli_commit($conn);
            $success = "Barang dan transaksi terkait berhasil dihapus (paksa).";
        } else {
            mysqli_rollback($conn);
            $error = "Gagal menghapus barang: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    // Cek apakah barang terkait dengan transaksi di barang_masuk atau penjualan
    $checkMasuk = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM barang_masuk WHERE barang_id='$id'");
    $rowMasuk = mysqli_fetch_assoc($checkMasuk);
    $countMasuk = isset($rowMasuk['cnt']) ? (int)$rowMasuk['cnt'] : 0;

    $checkJual = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM penjualan WHERE barang_id='$id'");
    $rowJual = mysqli_fetch_assoc($checkJual);
    $countJual = isset($rowJual['cnt']) ? (int)$rowJual['cnt'] : 0;

    if ($countMasuk > 0 || $countJual > 0) {
        $error = "Tidak dapat menghapus barang karena ada data transaksi terkait (barang_masuk/penjualan). Hapus atau pindahkan transaksi terlebih dahulu.";
    } else {
        $query = "DELETE FROM barang WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            $success = "Barang berhasil dihapus!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Ambil data barang
$barang_list = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM barang b 
                                     LEFT JOIN kategori k ON b.kategori_id = k.id ORDER BY b.id DESC");

// Ambil kategori
$kategori_list = mysqli_query($conn, "SELECT * FROM kategori");

// Ambil data barang untuk edit (jika ada parameter edit)
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = mysqli_real_escape_string($conn, $_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM barang WHERE id='$id'");
    $edit_data = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Barang - Aplikasi Warung Turnip</title>
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
            <h2><?php echo $edit_mode ? 'Edit Barang' : 'Kelola Barang'; ?></h2>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Form Tambah/Edit Barang -->
            <form method="POST">
                <div class="form-group">
                    <label for="kode_barang">Kode Barang:</label>
                    <input type="text" id="kode_barang" name="kode_barang" 
                           value="<?php echo htmlspecialchars($_POST['kode_barang'] ?? $edit_data['kode_barang'] ?? ''); ?>" 
                           required <?php echo $edit_mode ? 'readonly' : ''; ?>>
                    <div class="error-msg" id="error_kode_barang"><?php echo $validation_errors['kode_barang'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="nama_barang">Nama Barang:</label>
                    <input type="text" id="nama_barang" name="nama_barang" 
                           value="<?php echo htmlspecialchars($_POST['nama_barang'] ?? $edit_data['nama_barang'] ?? ''); ?>" required>
                    <div class="error-msg" id="error_nama_barang"><?php echo $validation_errors['nama_barang'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="kategori_id">Kategori:</label>
                    <select id="kategori_id" name="kategori_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php 
                        mysqli_data_seek($kategori_list, 0);
                        while ($kat = mysqli_fetch_assoc($kategori_list)): 
                            $selectedKategori = isset($_POST['kategori_id']) ? $_POST['kategori_id'] : ($edit_data['kategori_id'] ?? '');
                        ?>
                            <option value="<?php echo $kat['id']; ?>" 
                                    <?php echo $selectedKategori == $kat['id'] ? 'selected' : ''; ?>>
                                <?php echo $kat['nama_kategori']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="error-msg" id="error_kategori_id"><?php echo $validation_errors['kategori_id'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="harga_beli">Harga Beli (Rp):</label>
                    <input type="number" id="harga_beli" name="harga_beli" 
                           value="<?php echo htmlspecialchars($_POST['harga_beli'] ?? $edit_data['harga_beli'] ?? ''); ?>" 
                           min="0" step="0.01" required>
                    <div class="error-msg" id="error_harga_beli"><?php echo $validation_errors['harga_beli'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="harga_jual">Harga Jual (Rp):</label>
                    <input type="number" id="harga_jual" name="harga_jual" 
                           value="<?php echo htmlspecialchars($_POST['harga_jual'] ?? $edit_data['harga_jual'] ?? ''); ?>" 
                           min="0" step="0.01" required>
                    <div class="error-msg" id="error_harga_jual"><?php echo $validation_errors['harga_jual'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="satuan">Satuan:</label>
                    <input type="text" id="satuan" name="satuan" 
                           value="<?php echo htmlspecialchars($_POST['satuan'] ?? $edit_data['satuan'] ?? ''); ?>" 
                           placeholder="Pcs, Box, Kg, dll">
                    <div class="error-msg" id="error_satuan"><?php echo $validation_errors['satuan'] ?? ''; ?></div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($_POST['deskripsi'] ?? $edit_data['deskripsi'] ?? ''); ?></textarea>
                </div>

                <button type="submit" name="<?php echo $edit_mode ? 'edit_barang' : 'tambah_barang'; ?>" 
                        class="btn btn-primary">
                    <?php echo $edit_mode ? 'Update Barang' : 'Tambah Barang'; ?>
                </button>
                
                <?php if ($edit_mode): ?>
                    <a href="barang.php" class="btn btn-info">Batal</a>
                <?php endif; ?>
            </form>

            <hr style="margin: 30px 0;">

            <!-- Daftar Barang -->
            <h3>Daftar Barang</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($barang = mysqli_fetch_assoc($barang_list)): 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $barang['kode_barang']; ?></td>
                            <td><?php echo $barang['nama_barang']; ?></td>
                            <td><?php echo $barang['nama_kategori']; ?></td>
                            <td>Rp <?php echo number_format($barang['harga_beli'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($barang['harga_jual'], 0, ',', '.'); ?></td>
                            <td><?php echo $barang['satuan']; ?></td>
                            <td><strong><?php echo $barang['stok']; ?></strong></td>
                            <td>
                                <a href="barang.php?edit=<?php echo $barang['id']; ?>" class="btn btn-warning">Edit</a>
                                <a href="barang.php?hapus=<?php echo $barang['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                                <a href="barang.php?hapus_force=<?php echo $barang['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus barang dan semua transaksi terkait? Tindakan ini tidak dapat dibatalkan.');" style="margin-top:6px; display:inline-block;">Hapus Paksa</a>
                            </td>
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
        // Validasi form barang secara real-time
        const form = document.querySelector('form');
        const kodBarang = document.getElementById('kode_barang');
        const namaBarang = document.getElementById('nama_barang');
        const kategoriId = document.getElementById('kategori_id');
        const hargaBeli = document.getElementById('harga_beli');
        const hargaJual = document.getElementById('harga_jual');
        const satuan = document.getElementById('satuan');

        // Validasi Kode Barang
        function validateKodeBarang() {
            if (!kodBarang || kodBarang.readOnly) return true;
            const value = kodBarang.value.trim();
            const parent = kodBarang.parentElement;
            const errorDiv = document.getElementById('error_kode_barang');
            
            if (!value) {
                errorDiv.textContent = 'Kode barang tidak boleh kosong';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (value.length < 3) {
                errorDiv.textContent = 'Kode barang minimal 3 karakter';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (!/^[A-Z0-9-]+$/.test(value)) {
                errorDiv.textContent = 'Kode barang hanya boleh A-Z, 0-9, dan strip (-)';
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

        // Validasi Nama Barang
        function validateNamaBarang() {
            const value = namaBarang.value.trim();
            const parent = namaBarang.parentElement;
            const errorDiv = document.getElementById('error_nama_barang');
            
            if (!value) {
                errorDiv.textContent = 'Nama barang tidak boleh kosong';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (value.length < 3) {
                errorDiv.textContent = 'Nama barang minimal 3 karakter';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (value.length > 150) {
                errorDiv.textContent = 'Nama barang maksimal 150 karakter';
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

        // Validasi Kategori
        function validateKategori() {
            const value = kategoriId.value;
            const parent = kategoriId.parentElement;
            const errorDiv = document.getElementById('error_kategori_id');
            
            if (!value) {
                errorDiv.textContent = 'Kategori harus dipilih';
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

        // Validasi Harga Beli
        function validateHargaBeli() {
            const value = hargaBeli.value;
            const parent = hargaBeli.parentElement;
            const errorDiv = document.getElementById('error_harga_beli');
            
            if (!value) {
                errorDiv.textContent = 'Harga beli tidak boleh kosong';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (isNaN(value) || parseFloat(value) < 0) {
                errorDiv.textContent = 'Harga beli harus angka';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (parseFloat(value) > 999999999) {
                errorDiv.textContent = 'Harga beli tidak boleh lebih dari Rp 999.999.999';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (!/^\d+(\.\d{1,2})?$/.test(value)) {
                errorDiv.textContent = 'Harga beli maksimal 2 desimal';
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

        // Validasi Harga Jual
        function validateHargaJual() {
            const value = hargaJual.value;
            const parent = hargaJual.parentElement;
            const errorDiv = document.getElementById('error_harga_jual');
            
            if (!value) {
                errorDiv.textContent = 'Harga jual tidak boleh kosong';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (isNaN(value) || parseFloat(value) < 0) {
                errorDiv.textContent = 'Harga jual harus angka';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (parseFloat(value) > 999999999) {
                errorDiv.textContent = 'Harga jual tidak boleh lebih dari Rp 999.999.999';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (!/^\d+(\.\d{1,2})?$/.test(value)) {
                errorDiv.textContent = 'Harga jual maksimal 2 desimal';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (hargaBeli.value && parseFloat(value) < parseFloat(hargaBeli.value)) {
                errorDiv.textContent = 'Harga jual tidak boleh lebih rendah dari harga beli';
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

        // Validasi Satuan
        function validateSatuan() {
            const value = satuan.value.trim();
            const parent = satuan.parentElement;
            const errorDiv = document.getElementById('error_satuan');
            
            if (!value) {
                errorDiv.textContent = 'Satuan tidak boleh kosong';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (value.length < 2) {
                errorDiv.textContent = 'Satuan minimal 2 karakter';
                parent.classList.add('has-error');
                parent.classList.remove('has-success');
                return false;
            } else if (value.length > 50) {
                errorDiv.textContent = 'Satuan maksimal 50 karakter';
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
        if (kodBarang && !kodBarang.readOnly) {
            kodBarang.addEventListener('input', validateKodeBarang);
            kodBarang.addEventListener('blur', validateKodeBarang);
        }
        namaBarang.addEventListener('input', validateNamaBarang);
        namaBarang.addEventListener('blur', validateNamaBarang);
        kategoriId.addEventListener('change', validateKategori);
        kategoriId.addEventListener('blur', validateKategori);
        hargaBeli.addEventListener('input', validateHargaBeli);
        hargaBeli.addEventListener('blur', validateHargaBeli);
        hargaJual.addEventListener('input', validateHargaJual);
        hargaJual.addEventListener('blur', validateHargaJual);
        satuan.addEventListener('input', validateSatuan);
        satuan.addEventListener('blur', validateSatuan);

        // Validasi saat submit form
        form.addEventListener('submit', function(e) {
            let errors = [];

            if (!validateKodeBarang() && (kodBarang && !kodBarang.readOnly)) errors.push('Kode barang tidak valid');
            if (!validateNamaBarang()) errors.push('Nama barang tidak valid');
            if (!validateKategori()) errors.push('Kategori tidak valid');
            if (!validateHargaBeli()) errors.push('Harga beli tidak valid');
            if (!validateHargaJual()) errors.push('Harga jual tidak valid');
            if (!validateSatuan()) errors.push('Satuan tidak valid');

            if (errors.length > 0) {
                e.preventDefault();
                alert('⚠️ Kesalahan Validasi:\n\n' + errors.join('\n'));
            }
        });

    </script>
</body>
</html>
