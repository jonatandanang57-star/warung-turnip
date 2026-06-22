# 📖 Dokumentasi Aplikasi Warung Turnip

## 🎯 Daftar Isi
1. [Cara Setup](#cara-setup)
2. [Fitur Aplikasi](#fitur-aplikasi)
3. [Struktur Database](#struktur-database)
4. [Panduan Penggunaan](#panduan-penggunaan)
5. [Akun Demo](#akun-demo)

---

## 🛠 Cara Setup

### 1. Persiapan
- Pastikan **XAMPP** sudah terinstall
- Buat folder di: `C:\xampp\htdocs\PROJECT SQL`
- Copy semua file PHP ke folder tersebut

### 2. Setup Database

**Langkah-langkah:**

1. Buka **phpMyAdmin**: http://localhost/phpmyadmin
2. Klik **"New"** di sebelah kiri untuk membuat database baru
3. Beri nama database: `toko_management`
4. Klik **"Create"**
5. Pilih tab **"Import"**
6. Klik **"Choose File"** dan pilih file **database.sql**
7. Klik **"Go"** untuk mengimport struktur database

### 3. Jalankan Aplikasi

- Buka browser dan akses: **http://localhost/PROJECT SQL/**
- Anda akan diarahkan ke halaman login

---

## ✨ Fitur Aplikasi

### 1. **Login Admin**
   - Username: `admin`
   - Password: `admin123`
   - Hanya admin/pemilik toko yang bisa mengakses

### 2. **Kelola Barang**
   - ➕ Tambah barang baru dengan kode, nama, kategori, harga
   - ✏️ Edit informasi barang
   - 🗑️ Hapus barang
   - 📊 Lihat daftar semua barang dan stok

### 3. **Input Barang Masuk**
   - 📥 Catat barang yang masuk dari supplier
   - 💰 Input harga pembelian per item
   - 📝 Tambahkan keterangan (nomor PO, supplier, dll)
   - ✅ Stok otomatis bertambah

### 4. **Catat Penjualan**
   - 🛍️ Catat setiap transaksi penjualan
   - 👤 Nama pembeli (opsional)
   - 📦 Stok otomatis berkurang
   - 💵 Harga jual otomatis terisi

### 5. **Laporan Stok**
   - 📊 Lihat total stok semua barang
   - 💎 Nilai stok (harga beli × jumlah stok)
   - ⚠️ Peringatan stok rendah (< 10 unit)
   - ❌ Laporan barang yang stoknya habis
   - 📑 Filter per kategori

---

## 🗄️ Struktur Database

### Tabel utama:

1. **admin** - Data login admin/pemilik
   - id, username, password, nama_lengkap, email

2. **kategori** - Kategori barang
   - id, nama_kategori

3. **barang** - Master data barang
   - id, kode_barang, nama_barang, kategori_id, harga_beli, harga_jual, stok, satuan

4. **barang_masuk** - Riwayat barang masuk
   - id, barang_id, jumlah, harga_satuan, total_harga, keterangan, admin_id, tanggal_masuk

5. **penjualan** - Riwayat penjualan
   - id, barang_id, jumlah, harga_satuan, total_harga, nama_pembeli, admin_id, tanggal_penjualan

---

## 📖 Panduan Penggunaan

### **Menu Dashboard (Halaman Utama)**
- Menampilkan ringkasan: Total Barang, Total Stok, Total Penjualan, Total Pemasukan
- Akses cepat ke semua menu utama

### **Menu Barang**
1. Isi form dengan data barang baru
2. Klik **"Tambah Barang"**
3. Untuk edit, klik tombol **"Edit"** di daftar barang
4. Untuk hapus, klik **"Hapus"** (akan minta konfirmasi)

### **Menu Barang Masuk**
1. Pilih barang dari dropdown
2. Masukkan jumlah yang masuk
3. Masukkan harga satuan pembelian
4. (Opsional) Tambahkan keterangan
5. Klik **"Simpan Barang Masuk"**
6. ✅ Stok akan otomatis bertambah

### **Menu Penjualan**
1. Pilih barang yang dijual
   - Stok dan harga jual otomatis terisi
2. Masukkan jumlah terjual
3. (Opsional) Masukkan nama pembeli
4. Klik **"Catat Penjualan"**
5. ✅ Stok akan otomatis berkurang

### **Menu Laporan Stok**
- **Tab Semua Barang** - Lihat semua barang dan stoknya
- **Tab Stok Rendah** - Barang dengan stok < 10 unit (perlu restock)
- **Tab Stok Habis** - Barang yang sudah tidak ada stok

---

## 🔑 Akun Demo

**Username:** `admin`  
**Password:** `admin123`

*Catatan: Password ini sudah ter-hash. Anda bisa mengganti dengan password sendiri setelah login pertama kali.*

---

## 🚀 Mulai Gunakan

1. Akses aplikasi: **http://localhost/PROJECT SQL/**
2. Login dengan username & password
3. Mulai kelola barang toko Anda!

---

## 💡 Tips

- Selalu gunakan **Kode Barang** yang unik untuk memudahkan identifikasi
- Masukkan **Harga Beli** dengan akurat untuk laporan nilai stok
- Periksa laporan stok secara berkala untuk mengetahui barang mana yang perlu restock
- Catat **Keterangan** pada barang masuk untuk dokumentasi yang rapi

---

**Semoga aplikasi ini membantu dalam mengelola barang toko Anda! 🎉**
