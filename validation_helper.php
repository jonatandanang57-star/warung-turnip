<?php
/**
 * Validation Helper Functions
 * Fungsi-fungsi validasi untuk form input
 */

// Fungsi validasi ketat untuk setiap field
function isValidDecimal($value) {
    return preg_match('/^\d+(\.\d{1,2})?$/', trim((string)$value));
}

function validateBarangForm($data) {
    $errors = [];
    $kode = trim($data['kode_barang'] ?? '');
    $nama = trim($data['nama_barang'] ?? '');
    $kategori = trim($data['kategori_id'] ?? '');
    $harga_beli = trim($data['harga_beli'] ?? '');
    $harga_jual = trim($data['harga_jual'] ?? '');
    $satuan = trim($data['satuan'] ?? '');

    if ($kode === '') {
        $errors['kode_barang'] = 'Kode barang tidak boleh kosong';
    } elseif (strlen($kode) < 3) {
        $errors['kode_barang'] = 'Kode barang minimal 3 karakter';
    } elseif (!preg_match('/^[A-Z0-9\-]+$/i', $kode)) {
        $errors['kode_barang'] = 'Kode barang hanya boleh huruf, angka, dan dash (-)';
    }

    if ($nama === '') {
        $errors['nama_barang'] = 'Nama barang tidak boleh kosong';
    } elseif (strlen($nama) < 3) {
        $errors['nama_barang'] = 'Nama barang minimal 3 karakter';
    } elseif (strlen($nama) > 150) {
        $errors['nama_barang'] = 'Nama barang maksimal 150 karakter';
    }

    if ($kategori === '') {
        $errors['kategori_id'] = 'Kategori harus dipilih';
    } elseif (!ctype_digit($kategori)) {
        $errors['kategori_id'] = 'Kategori tidak valid';
    }

    if ($harga_beli === '') {
        $errors['harga_beli'] = 'Harga beli tidak boleh kosong';
    } elseif (!is_numeric($harga_beli)) {
        $errors['harga_beli'] = 'Harga beli harus angka';
    } elseif (floatval($harga_beli) <= 0) {
        $errors['harga_beli'] = 'Harga beli harus lebih besar dari nol';
    } elseif (floatval($harga_beli) > 999999999) {
        $errors['harga_beli'] = 'Harga beli terlalu besar (maksimal 999.999.999)';
    } elseif (!isValidDecimal($harga_beli)) {
        $errors['harga_beli'] = 'Harga beli hanya boleh 2 desimal';
    }

    if ($harga_jual === '') {
        $errors['harga_jual'] = 'Harga jual tidak boleh kosong';
    } elseif (!is_numeric($harga_jual)) {
        $errors['harga_jual'] = 'Harga jual harus angka';
    } elseif (floatval($harga_jual) <= 0) {
        $errors['harga_jual'] = 'Harga jual harus lebih besar dari nol';
    } elseif (floatval($harga_jual) > 999999999) {
        $errors['harga_jual'] = 'Harga jual terlalu besar (maksimal 999.999.999)';
    } elseif (!isValidDecimal($harga_jual)) {
        $errors['harga_jual'] = 'Harga jual hanya boleh 2 desimal';
    } elseif (is_numeric($harga_beli) && floatval($harga_jual) < floatval($harga_beli)) {
        $errors['harga_jual'] = 'Harga jual tidak boleh lebih rendah dari harga beli';
    }

    if ($satuan === '') {
        $errors['satuan'] = 'Satuan tidak boleh kosong';
    } elseif (strlen($satuan) < 2) {
        $errors['satuan'] = 'Satuan minimal 2 karakter';
    } elseif (strlen($satuan) > 50) {
        $errors['satuan'] = 'Satuan maksimal 50 karakter';
    }

    return $errors;
}

// Validasi Barang Masuk
function validateBarangMasuk($data) {
    $errors = [];
    $barang_id = trim($data['barang_id'] ?? '');
    $jumlah = trim($data['jumlah'] ?? '');
    $harga_satuan = trim($data['harga_satuan'] ?? '');

    if ($barang_id === '') {
        $errors['barang_id'] = 'Barang harus dipilih';
    } elseif (!ctype_digit($barang_id)) {
        $errors['barang_id'] = 'Barang tidak valid';
    }

    if ($jumlah === '') {
        $errors['jumlah'] = 'Jumlah masuk tidak boleh kosong';
    } elseif (!ctype_digit($jumlah)) {
        $errors['jumlah'] = 'Jumlah masuk harus angka bulat positif';
    } elseif (intval($jumlah) <= 0) {
        $errors['jumlah'] = 'Jumlah masuk harus angka positif';
    } elseif (intval($jumlah) > 999999) {
        $errors['jumlah'] = 'Jumlah masuk terlalu besar (maksimal 999.999)';
    }

    if ($harga_satuan === '') {
        $errors['harga_satuan'] = 'Harga satuan tidak boleh kosong';
    } elseif (!is_numeric($harga_satuan)) {
        $errors['harga_satuan'] = 'Harga satuan harus angka';
    } elseif (floatval($harga_satuan) < 0) {
        $errors['harga_satuan'] = 'Harga satuan tidak boleh negatif';
    } elseif (floatval($harga_satuan) > 999999999) {
        $errors['harga_satuan'] = 'Harga satuan terlalu besar';
    } elseif (!isValidDecimal($harga_satuan)) {
        $errors['harga_satuan'] = 'Harga satuan hanya boleh 2 desimal';
    }

    return $errors;
}

// Validasi Penjualan
function validatePenjualan($data) {
    $errors = [];
    $barang_id = trim($data['barang_id'] ?? '');
    $jumlah = trim($data['jumlah'] ?? '');
    $harga_satuan = trim($data['harga_satuan'] ?? '');

    if ($barang_id === '') {
        $errors['barang_id'] = 'Barang harus dipilih';
    } elseif (!ctype_digit($barang_id)) {
        $errors['barang_id'] = 'Barang tidak valid';
    }

    if ($jumlah === '') {
        $errors['jumlah'] = 'Jumlah terjual tidak boleh kosong';
    } elseif (!ctype_digit($jumlah)) {
        $errors['jumlah'] = 'Jumlah terjual harus angka bulat positif';
    } elseif (intval($jumlah) <= 0) {
        $errors['jumlah'] = 'Jumlah terjual harus angka positif';
    } elseif (intval($jumlah) > 999999) {
        $errors['jumlah'] = 'Jumlah terjual terlalu besar (maksimal 999.999)';
    }

    if ($harga_satuan === '') {
        $errors['harga_satuan'] = 'Harga satuan tidak boleh kosong';
    } elseif (!is_numeric($harga_satuan)) {
        $errors['harga_satuan'] = 'Harga satuan harus angka';
    } elseif (floatval($harga_satuan) < 0) {
        $errors['harga_satuan'] = 'Harga satuan tidak boleh negatif';
    } elseif (floatval($harga_satuan) > 999999999) {
        $errors['harga_satuan'] = 'Harga satuan terlalu besar';
    } elseif (!isValidDecimal($harga_satuan)) {
        $errors['harga_satuan'] = 'Harga satuan hanya boleh 2 desimal';
    }

    return $errors;
}

// Fungsi helper untuk display error messages di form
function displayFieldError($fieldName, $allErrors = []) {
    if (isset($allErrors[$fieldName])) {
        return '<div class="error-msg" style="color: #991b1b; font-size: 0.82rem; margin-top: 6px; font-weight: 600; background: #fef2f2; padding: 8px 12px; border-radius: 8px; border-left: 3px solid #ef4444;">' . htmlspecialchars($allErrors[$fieldName]) . '</div>';
    }
    return '<div class="error-msg"></div>';
}
?>
