# Struktur Project - Hotel Management System

Project ini telah dipisahkan menjadi 2 folder utama sesuai ketentuan:

## 📁 BACKEND (PHP Murni)
Folder `backend/` berisi semua logika pemrosesan server-side menggunakan PHP murni tanpa framework.

### Struktur Backend:
```
backend/
├── config/
│   ├── database.php          # Konfigurasi koneksi database
│   └── functions.php         # Fungsi-fungsi helper PHP
├── admin/
│   ├── dashboard.php         # Dashboard admin
│   ├── fnb_orders.php        # Manajemen pesanan F&B
│   ├── payments.php          # Manajemen pembayaran
│   ├── reservation_detail.php
│   ├── reservations_management.php
│   ├── rooms_management.php  # Manajemen kamar
│   └── users.php             # Manajemen user
├── user/
│   ├── dashboard.php         # Dashboard user
│   ├── booking.php           # Proses booking
│   ├── delete_reservation.php
│   ├── edit_reservation.php
│   ├── payment.php           # Proses pembayaran
│   ├── profile.php           # Profile user
│   ├── reservation_detail.php
│   ├── reservations.php      # Daftar reservasi
│   └── rooms.php             # Daftar kamar
├── login.php                 # Proses login
├── register.php              # Proses registrasi
├── logout.php                # Proses logout
├── test_connection.php       # Test koneksi database
└── test_password.php         # Test hashing password
```

### Fungsi Backend:
- ✅ Koneksi dan query database
- ✅ Validasi input server-side
- ✅ Authentication & Authorization
- ✅ Business logic (booking, payment, dll)
- ✅ Session management
- ✅ CRUD operations

---

## 📁 FRONTEND (HTML, CSS, JavaScript)
Folder `frontend/` berisi semua tampilan dan interaksi user interface.

### Struktur Frontend:
```
frontend/
├── assets/
│   ├── css/
│   │   ├── auth.css          # Styling halaman login/register
│   │   └── dashboard.css     # Styling dashboard
│   ├── bg_music/             # Background music
│   └── room_photo/           # Foto-foto kamar
├── index.html                # Halaman utama/landing page
└── style.css                 # Global styling
```

### Fungsi Frontend:
- ✅ User Interface (HTML)
- ✅ Styling & Layout (CSS)
- ✅ Client-side validation (JavaScript)
- ✅ Interactive features (JavaScript)
- ✅ Responsive design

---

## 🔗 Komunikasi Frontend-Backend

Frontend berkomunikasi dengan backend melalui:
1. **Form submission** - HTML form mengirim data ke PHP
2. **AJAX calls** - JavaScript fetch/XMLHttpRequest ke PHP endpoints
3. **Session** - PHP session untuk maintain user state

---

## 🚀 Cara Menjalankan

1. **Setup Apache & MySQL** (XAMPP/WAMP)
2. **Konfigurasi VirtualHost** untuk port 8081
3. **Set DocumentRoot** ke folder project utama
4. **Import Database** (jika ada)
5. **Update konfigurasi** di `backend/config/database.php`
6. **Akses**:
   - Landing page: `http://localhost:8081/frontend/index.html`
   - Login: `http://localhost:8081/backend/login.php`
   - Register: `http://localhost:8081/backend/register.php`

---

## 📝 Catatan Penting

### Path References
Setelah pemisahan folder, pastikan update path di file-file berikut:

1. **File PHP** - Update `require_once` path:
   ```php
   // Dari:
   require_once 'config/database.php';
   
   // Menjadi:
   require_once '../config/database.php';  // jika dari folder admin/user
   require_once 'config/database.php';     // jika dari folder backend
   ```

2. **File HTML/CSS** - Update link asset:
   ```html
   <!-- Dari: -->
   <link rel="stylesheet" href="assets/css/auth.css">
   
   <!-- Menjadi: -->
   <link rel="stylesheet" href="../frontend/assets/css/auth.css">
   ```

3. **Form Action** - Update action path:
   ```html
   <!-- Dari: -->
   <form action="login.php">
   
   <!-- Menjadi: -->
   <form action="../backend/login.php">
   ```

---

## ✅ Ketentuan Terpenuhi

1. ✅ **Backend berbasis PHP murni** (tanpa framework)
   - Semua file `.php` di folder `backend/`
   - Pure PHP untuk logic, database, validation

2. ✅ **Frontend berbasis HTML, CSS, JavaScript**
   - File `.html`, `.css` di folder `frontend/`
   - JavaScript untuk client-side interaction
   - Asset (images, css) terpisah di `frontend/assets/`

---

## 📦 File Dokumentasi Lainnya

- `README.md` - Dokumentasi utama project
- `API_DOCS.md` - Dokumentasi API (jika ada)
- `INSTALL.md` - Panduan instalasi
- `QUICKSTART.md` - Panduan cepat memulai
- `CHANGELOG.md` - Catatan perubahan versi
