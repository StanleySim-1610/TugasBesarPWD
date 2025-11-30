# 📦 Panduan Instalasi Hotel Management System

Ikuti langkah-langkah berikut untuk menginstall dan menjalankan aplikasi Hotel Management System.

## 🎯 Requirements

Sebelum memulai, pastikan komputer Anda sudah terinstall:

- [x] XAMPP / WAMP / LAMP (Recommended: XAMPP 8.0+)
- [x] PHP 7.4 atau lebih baru
- [x] MySQL 5.7 atau lebih baru
- [x] Web Browser modern (Chrome, Firefox, Edge, Safari)
- [x] Text Editor (VS Code, Sublime, Notepad++, dll) - opsional

## 📥 Step 1: Download Project

### Opsi A: Clone dari Git (jika menggunakan Git)
```bash
cd C:\xampp\htdocs
git clone [repository-url] hotel-management
```

### Opsi B: Manual Download
1. Download project sebagai ZIP
2. Extract ke folder `C:\xampp\htdocs\hotel-management`

## ⚙️ Step 2: Install XAMPP

Jika belum memiliki XAMPP:

1. Download XAMPP dari https://www.apachefriends.org/
2. Install XAMPP (pilih Apache dan MySQL)
3. Jalankan XAMPP Control Panel
4. Start **Apache** dan **MySQL**

**Catatan untuk Windows:**
- Install di `C:\xampp\` (path default)
- Jika port 80 sudah digunakan (Skype, IIS), ubah port Apache

## 🗄️ Step 3: Setup Database

### 3.1. Buka phpMyAdmin

1. Start Apache dan MySQL di XAMPP Control Panel
2. Buka browser, akses: http://localhost/phpmyadmin
3. Login (username: `root`, password: kosongkan)

### 3.2. Create Database

1. Klik tab **"Database"**
2. Di kolom "Create database", ketik: `hotel_management`
3. Pilih Collation: `utf8_general_ci`
4. Klik **"Create"**

### 3.3. Import SQL File

1. Klik database `hotel_management` yang baru dibuat
2. Klik tab **"Import"**
3. Klik **"Choose File"**
4. Pilih file: `database/hotel_management.sql`
5. Scroll ke bawah, klik **"Go"**
6. Tunggu hingga muncul pesan sukses

**Verifikasi:**
- Setelah import, Anda harus melihat 7 tabel:
  - user
  - kamar
  - reservation
  - fnb_order
  - payment_fnb
  - payment_reservation

## 🔧 Step 4: Konfigurasi Database

Edit file `config/database.php`:

```php
<?php
// Sesuaikan dengan konfigurasi MySQL Anda
define('DB_HOST', 'localhost');     // Biasanya localhost
define('DB_USER', 'root');          // Username MySQL (default: root)
define('DB_PASS', '');              // Password MySQL (default: kosong)
define('DB_NAME', 'hotel_management'); // Nama database
?>
```

**Untuk XAMPP Default:**
- Host: `localhost`
- User: `root`
- Pass: `` (kosong)
- Database: `hotel_management`

**Untuk MAMP (Mac):**
- Host: `localhost`
- User: `root`
- Pass: `root`
- Port: `8889` (tambahkan `:8889` setelah localhost jika perlu)

## 🚀 Step 5: Jalankan Aplikasi

### 5.1. Start Services

Di XAMPP Control Panel, pastikan **Apache** dan **MySQL** berjalan (hijau).

### 5.2. Akses Aplikasi

Buka browser dan akses:

```
http://localhost/Testing%20tubes/index.html
```

**Catatan:** Karena nama folder mengandung spasi, gunakan `%20` untuk mengganti spasi di URL.

Alternatif jika link di atas tidak bekerja:
```
http://localhost/Testing tubes/index.html
```

## 👤 Step 6: Login Pertama Kali

### Login sebagai Admin:

1. Klik tombol **"Login"** di homepage
2. Masukkan kredensial:
   - **Email:** admin@gmail.com
   - **Password:** admin123
3. Klik **"Login"**
4. Anda akan diarahkan ke Admin Dashboard

### Register sebagai User:

1. Klik tombol **"Register"** di homepage
2. Isi form registrasi:
   - Nama Lengkap
   - Email (gunakan email yang valid)
   - No. Telepon
   - No. Identitas (KTP/SIM)
   - Password (minimal 6 karakter)
   - Konfirmasi Password
3. Klik **"Daftar"**
4. Login dengan email dan password yang baru dibuat
5. Anda akan diarahkan ke User Dashboard

## ✅ Step 7: Testing

### Test Basic Features:

1. **Homepage** ✓
   - Video background berjalan
   - Navbar responsive
   - Tombol Login/Register berfungsi

2. **Register** ✓
   - Form validasi bekerja
   - Email duplicate detection
   - Password encryption

3. **Login** ✓
   - Admin redirect ke admin dashboard
   - User redirect ke user dashboard
   - Error handling untuk kredensial salah

4. **User Dashboard** ✓
   - Menampilkan kamar available
   - Statistics muncul
   - Recent reservations tampil

5. **Admin Dashboard** ✓
   - Statistics ditampilkan
   - Tabel data muncul
   - Navigasi antar menu lancar

## 🐛 Troubleshooting

### Problem: "Connection failed"

**Solusi:**
1. Pastikan MySQL running di XAMPP
2. Cek credentials di `config/database.php`
3. Pastikan database `hotel_management` sudah dibuat
4. Restart Apache dan MySQL

### Problem: "Table 'hotel_management.user' doesn't exist"

**Solusi:**
1. Import ulang file SQL
2. Buka phpMyAdmin
3. Drop database `hotel_management`
4. Buat ulang database
5. Import file SQL lagi

### Problem: "Blank page" atau "White screen"

**Solusi:**
1. Enable error reporting di PHP:
   ```php
   // Tambahkan di awal file PHP
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Cek PHP error log di `C:\xampp\php\logs\php_error_log`
3. Pastikan semua file PHP tidak ada syntax error

### Problem: "Cannot modify header information"

**Solusi:**
1. Pastikan tidak ada spasi/karakter sebelum `<?php`
2. Pastikan tidak ada `echo` sebelum `header()`
3. Cek encoding file (gunakan UTF-8 without BOM)

### Problem: Admin tidak bisa login

**Solusi:**
1. Cek tabel `user` di database
2. Pastikan ada record dengan email `admin@gmail.com`
3. Jika tidak ada, jalankan query ini di phpMyAdmin:
   ```sql
   INSERT INTO user (nama, email, password) VALUES 
   ('Administrator', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
   ```

### Problem: Session tidak tersimpan

**Solusi:**
1. Cek apakah `session_start()` dipanggil
2. Pastikan folder session writable
3. Restart Apache
4. Hapus cookies browser

### Problem: Styling tidak muncul (CSS tidak load)

**Solusi:**
1. Cek path CSS di HTML/PHP
2. Pastikan folder `assets/css/` ada
3. Cek permissions folder
4. Hard refresh browser (Ctrl + F5)

## 🔐 Keamanan

Setelah instalasi, perlu dilakukan:

1. **Ubah Password Admin:**
   - Login sebagai admin
   - Ubah password default `admin123`

2. **Protect Config Files:**
   - File `.htaccess` sudah melindungi file config
   - Pastikan `.htaccess` aktif

3. **Production Mode:**
   - Disable error display di `config/database.php`
   - Set `display_errors = Off` di php.ini

## 📱 Test Multi-Device

Coba akses dari:
- Desktop browser
- Mobile browser
- Tablet

Pastikan responsive design berfungsi dengan baik.

## 🎉 Selesai!

Aplikasi Hotel Management System sudah siap digunakan!

**Next Steps:**
1. Explore semua fitur
2. Test booking flow
3. Test admin CRUD operations
4. Customize sesuai kebutuhan

## 📞 Need Help?

Jika masih ada masalah:
1. Cek file `README.md` untuk dokumentasi lengkap
2. Cek error log di `C:\xampp\php\logs\`
3. Google error message yang muncul
4. Tanya ke dosen/asisten praktikum

---

**Good luck! 🚀**
