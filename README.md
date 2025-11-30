# Hotel Management System

Sistem Manajemen Hotel berbasis Web dengan PHP murni, HTML, CSS, dan JavaScript.

## 📋 Deskripsi Project

Aplikasi web untuk mengelola sistem hotel yang mencakup fitur booking kamar, manajemen user, sistem pembayaran, dan F&B orders. Project ini dibuat untuk memenuhi tugas besar Pemrograman Web Dasar dengan tema HOTEL.

## ✨ Fitur Utama

### Untuk User (Guest):
- ✅ **Registrasi & Login** - Sistem autentikasi dengan enkripsi password
- ✅ **Browse Rooms** - Melihat dan memilih kamar yang tersedia
- ✅ **Booking Kamar** - Melakukan reservasi dengan pilihan tanggal
- ✅ **Payment System** - Sistem pembayaran dengan berbagai metode
- ✅ **Profile Management** - Melihat dan update profil pengguna
- ✅ **Reservation History** - Melihat riwayat pemesanan

### Untuk Admin:
- ✅ **Dashboard** - Overview statistik hotel
- ✅ **User Management** - Melihat data semua pengguna
- ✅ **Room Management** - CRUD kamar hotel (Create, Read, Update, Delete)
- ✅ **Reservation Management** - Mengelola semua reservasi
- ✅ **Payment Management** - Monitoring pembayaran
- ✅ **F&B Orders** - Mengelola pesanan Food & Beverage

## 🛠️ Teknologi yang Digunakan

- **Backend:** PHP 7.4+ (Murni, tanpa framework)
- **Frontend:** HTML5, CSS3, JavaScript
- **Database:** MySQL/MariaDB
- **Server:** Apache (XAMPP/WAMP/LAMP)

## 📁 Struktur Project

```
Testing tubes/
├── admin/                      # Halaman admin
│   ├── dashboard.php
│   ├── users.php
│   ├── rooms_management.php
│   ├── reservations_management.php
│   ├── payments.php
│   ├── fnb_orders.php
│   └── reservation_detail.php
├── user/                       # Halaman user
│   ├── dashboard.php
│   ├── rooms.php
│   ├── booking.php
│   ├── payment.php
│   ├── reservations.php
│   ├── profile.php
│   └── reservation_detail.php
├── config/                     # Konfigurasi
│   ├── database.php
│   └── functions.php
├── database/                   # SQL files
│   └── hotel_management.sql
├── assets/                     # Static files
│   ├── css/
│   │   ├── auth.css
│   │   └── dashboard.css
│   ├── logo.png
│   └── video1.mp4
├── index.html                  # Landing page
├── style.css                   # Landing page styles
├── login.php                   # Login page
├── register.php                # Register page
├── logout.php                  # Logout handler
└── README.md                   # Dokumentasi
```

## 🚀 Cara Instalasi

### 1. Persiapan Environment

Pastikan Anda sudah menginstall:
- **XAMPP/WAMP/LAMP** (atau web server lain dengan PHP dan MySQL)
- **PHP 7.4** atau lebih baru
- **MySQL 5.7** atau lebih baru

### 2. Clone/Download Project

```bash
# Download project ke folder htdocs (untuk XAMPP)
# Misalnya: C:\xampp\htdocs\hotel-management
```

### 3. Setup Database

1. Buka **phpMyAdmin** (http://localhost/phpmyadmin)
2. Buat database baru dengan nama `hotel_management`
3. Import file SQL:
   - Klik database `hotel_management`
   - Pilih tab **Import**
   - Pilih file `database/hotel_management.sql`
   - Klik **Go**

### 4. Konfigurasi Database

Edit file `config/database.php` sesuai dengan konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Sesuaikan dengan username MySQL Anda
define('DB_PASS', '');               // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'hotel_management');
```

### 5. Jalankan Aplikasi

1. Start **Apache** dan **MySQL** di XAMPP Control Panel
2. Buka browser dan akses:
   ```
   http://localhost/Testing%20tubes/index.html
   ```
   
   **Catatan:** Gunakan `%20` untuk spasi di URL, atau langsung:
   ```
   http://localhost/Testing tubes/index.html
   ```

## 👤 Default Login Credentials

### Admin:
- **Email:** admin@gmail.com
- **Password:** admin123

### User:
- Silakan **Register** terlebih dahulu untuk membuat akun user baru

## 📊 Database Schema (ERD)

Database terdiri dari 7 tabel utama:

1. **user** - Data pengguna (guest dan admin)
2. **kamar** - Data kamar hotel
3. **reservation** - Data reservasi/booking
4. **fnb_order** - Pesanan Food & Beverage
5. **payment_fnb** - Pembayaran F&B
6. **payment_reservation** - Pembayaran reservasi

## 🎨 Fitur Keamanan

✅ **Password Encryption** - Menggunakan `password_hash()` PHP
✅ **Session Management** - Session untuk autentikasi user
✅ **SQL Injection Prevention** - Menggunakan Prepared Statements
✅ **XSS Prevention** - Input sanitization dengan `htmlspecialchars()`
✅ **Access Control** - Pemisahan akses admin dan user

## 📱 Responsive Design

Website ini **responsive** dan dapat diakses dengan baik di:
- 💻 Desktop
- 📱 Tablet
- 📱 Mobile

## 🔄 Flow Aplikasi

### User Flow:
1. User membuka landing page (index.html)
2. User register akun baru
3. User login dengan kredensial yang dibuat
4. User browse kamar yang tersedia
5. User booking kamar dengan memilih tanggal
6. User melakukan pembayaran
7. User dapat melihat riwayat reservasi
8. User dapat update profile

### Admin Flow:
1. Admin login dengan kredensial admin
2. Admin melihat dashboard dengan statistik
3. Admin dapat mengelola:
   - Data users
   - Data kamar (CRUD)
   - Reservasi semua user
   - Pembayaran
   - F&B orders

## 🌟 Fitur Tambahan

- ✅ **Video Background** pada landing page
- ✅ **Real-time Price Calculator** saat booking
- ✅ **Status Badge** untuk tracking reservasi
- ✅ **Email Validation** saat register
- ✅ **Date Validation** untuk booking
- ✅ **Payment Method Selection**
- ✅ **Statistics Dashboard** untuk admin

## 🐛 Troubleshooting

### Error: "Connection failed"
- Pastikan MySQL sudah running
- Cek konfigurasi di `config/database.php`
- Pastikan database `hotel_management` sudah dibuat

### Error: "Table doesn't exist"
- Import ulang file `database/hotel_management.sql`
- Pastikan semua tabel ter-create dengan benar

### Halaman redirect ke login terus
- Hapus cookies dan cache browser
- Cek apakah session PHP sudah enabled

### Admin tidak bisa login
- Pastikan data admin sudah ter-insert di database
- Cek tabel `user` apakah email `admin@gmail.com` ada

## 📝 Catatan Pengembangan

Project ini dibuat dengan:
- PHP Murni (tanpa framework)
- Tidak menggunakan library eksternal
- Database design sesuai ERD yang diberikan
- UI/UX mengikuti referensi Figma

## 👨‍💻 Developer

**Tugas Besar - Pemrograman Web Dasar**
- Tema: Hotel Management System
- Semester: 5
- Tahun: 2025

## 📞 Support

Jika ada pertanyaan atau kendala, silakan hubungi:
- Email: info@luxuryhotel.com
- Phone: +62 123 4567 890

---

**© 2025 Luxury Hotel. All rights reserved.**
