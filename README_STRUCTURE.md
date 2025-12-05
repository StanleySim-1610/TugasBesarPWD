# 🏨 Hotel Management System - Struktur Terpisah

Project ini mengikuti ketentuan pemisahan **Backend (PHP)** dan **Frontend (HTML/CSS/JS)**.

---

## 📂 Struktur Folder

```
TugasBesarPWD/
│
├── backend/                 # ⚙️ Backend (PHP Murni)
│   ├── config/             # Konfigurasi database & functions
│   ├── admin/              # Admin management pages
│   ├── user/               # User pages
│   ├── api/                # API endpoints (optional)
│   ├── login.php           # Authentication
│   ├── register.php        # Registration
│   └── logout.php          # Logout
│
├── frontend/               # 🎨 Frontend (HTML/CSS/JS)
│   ├── assets/            # Static files
│   │   ├── css/          # Stylesheets
│   │   ├── bg_music/     # Audio files
│   │   └── room_photo/   # Images
│   ├── index.html         # Landing page
│   └── style.css          # Main stylesheet
│
├── index.php              # Entry point (redirect to frontend)
├── .htaccess              # Apache routing
└── STRUCTURE.md           # Dokumentasi lengkap
```

---

## 🚀 Cara Akses

### Development (XAMPP/WAMP - Port 8081)

1. **Landing Page:**
   ```
   http://localhost:8081
   atau
   http://localhost:8081/frontend/index.html
   ```

2. **Login:**
   ```
   http://localhost:8081/backend/login.php
   ```

3. **Register:**
   ```
   http://localhost:8081/backend/register.php
   ```

4. **Admin Dashboard:**
   ```
   http://localhost:8081/backend/admin/dashboard.php
   ```

5. **User Dashboard:**
   ```
   http://localhost:8081/backend/user/dashboard.php
   ```

---

## ✅ Ketentuan Yang Dipenuhi

### 1. Backend - PHP Murni ✅
- ✅ Tidak menggunakan framework
- ✅ Pure PHP untuk business logic
- ✅ Database connection & queries
- ✅ Session management
- ✅ Server-side validation
- ✅ Authentication & Authorization
- ✅ CRUD operations

**Lokasi:** Semua file `.php` di folder `backend/`

### 2. Frontend - HTML, CSS, JavaScript ✅
- ✅ HTML untuk struktur halaman
- ✅ CSS untuk styling & layout
- ✅ JavaScript untuk interaktivitas
- ✅ Client-side validation
- ✅ Responsive design
- ✅ Asset management terpisah

**Lokasi:** File `.html`, `.css`, `.js` dan assets di folder `frontend/`

---

## 🔗 Alur Komunikasi

```
User Browser
    ↓
Frontend (HTML/CSS/JS)
    ↓ [Form Submit / AJAX]
Backend (PHP)
    ↓ [Database Query]
MySQL Database
    ↓ [Response]
Backend (PHP)
    ↓ [HTML/JSON]
Frontend
    ↓
User Browser
```

---

## 📝 Path Reference

### Dari Frontend ke Backend:
```html
<!-- Link ke PHP backend -->
<a href="../backend/login.php">Login</a>
<form action="../backend/register.php">
```

### Dari Backend ke Frontend:
```php
// Redirect ke frontend
header('Location: ../frontend/index.html');

// Link asset frontend
<link rel="stylesheet" href="../frontend/assets/css/auth.css">
<img src="../frontend/assets/logo.png">
```

### Dari Backend ke Config:
```php
// File di backend/ (root backend)
require_once 'config/database.php';

// File di backend/admin/ atau backend/user/
require_once '../config/database.php';
```

---

## 🛠️ Setup Database

1. Import database SQL
2. Update `backend/config/database.php`:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_db";
```

---

## 👥 Demo Account

**Admin:**
- Email: `admin@gmail.com`
- Password: `admin123`

**User:** (Daftar sendiri via register)

---

## 📋 File Penting

- `STRUCTURE.md` - Dokumentasi detail struktur
- `README.md` - Dokumentasi umum project
- `INSTALL.md` - Panduan instalasi
- `API_DOCS.md` - Dokumentasi API (jika ada)
- `.htaccess` - Routing configuration

---

## 🔒 Security Features

- ✅ Password hashing (PHP `password_hash()`)
- ✅ SQL Injection protection (Prepared statements)
- ✅ XSS protection (Input sanitization)
- ✅ Session management
- ✅ Authentication middleware
- ✅ Role-based access control

---

## 📞 Support

Jika ada pertanyaan atau issue, silakan hubungi developer atau buka issue di repository.

---

**Developed with ❤️ using Pure PHP, HTML, CSS & JavaScript**
