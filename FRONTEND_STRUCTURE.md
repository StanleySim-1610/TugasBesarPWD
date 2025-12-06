# Frontend HTML, CSS, JavaScript - Hotel Management System

## Struktur Direktori Frontend

```
frontend/
├── index.html                 # Halaman Home
├── css/
│   ├── dashboard.css          # CSS untuk semua halaman
│   └── auth.css              # CSS untuk halaman login/register
├── js/
│   ├── api.js                # Helper functions dan API communication
│   ├── dashboard.js          # User dashboard logic
│   ├── rooms.js              # Browse rooms logic
│   ├── booking.js            # Room booking logic
│   ├── reservations.js       # View reservations logic
│   ├── profile.js            # User profile logic
│   ├── reservation-detail.js # Reservation detail logic
│   ├── admin-dashboard.js    # Admin dashboard logic
│   ├── admin-rooms.js        # Admin rooms management
│   ├── admin-reservations.js # Admin reservations management
│   ├── admin-users.js        # Admin users management
│   ├── admin-payments.js     # Admin payments management
│   └── admin-fnb.js          # Admin F&B orders management
├── pages/
│   ├── login.html            # Login page
│   ├── register.html         # Registration page
│   ├── dashboard.html        # User dashboard
│   ├── rooms.html            # Browse rooms
│   ├── booking.html          # Room booking form
│   ├── reservations.html     # User reservations list
│   ├── reservation-detail.html # Reservation detail
│   └── profile.html          # User profile
├── admin/
│   ├── dashboard.html        # Admin dashboard
│   ├── rooms-management.html # Rooms management
│   ├── reservations-management.html # Reservations management
│   ├── users.html            # Users management
│   ├── payments.html         # Payments management
│   └── fnb-orders.html       # F&B orders management
└── assets/
    ├── logo.png
    ├── room_photo/           # Room images
    └── bg_music/             # Background music
```

## Fitur Utama

### User Pages
1. **Login & Register** - Autentikasi user dengan PHP backend
2. **Dashboard** - Menampilkan statistik, room tersedia, dan recent reservations
3. **Browse Rooms** - Melihat semua room yang tersedia dengan detail lengkap
4. **Booking** - Form untuk booking room dengan kalkulasi harga real-time
5. **My Reservations** - Daftar reservasi user dengan opsi view detail dan delete
6. **Profile** - Manage data profil dan ubah password
7. **Reservation Detail** - Detail booking lengkap dengan status pembayaran

### Admin Pages
1. **Dashboard** - Statistik system (total rooms, reservations, users, revenue)
2. **Rooms Management** - CRUD operations untuk room
3. **Reservations Management** - View dan manage semua reservations
4. **Users Management** - View semua users terdaftar
5. **Payments Management** - Tracking pembayaran reservations
6. **F&B Orders Management** - Manage F&B orders

## Arsitektur Komunikasi

### Frontend (HTML/CSS/JavaScript)
- HTML5 untuk struktur
- CSS3 untuk styling (responsive design)
- Vanilla JavaScript untuk logic (tanpa framework)
- Fetch API untuk HTTP requests

### Backend API Endpoints
```
User Endpoints:
- /backend/user/get-profile.php
- /backend/user/get-stats.php
- /backend/user/get-available-rooms.php
- /backend/user/get-room.php
- /backend/user/get-reservations.php
- /backend/user/get-reservation-detail.php
- /backend/user/create-reservation.php
- /backend/user/delete-reservation.php
- /backend/user/update-profile.php

Admin Endpoints:
- /backend/admin/get-dashboard-stats.php
- /backend/admin/get-admin-info.php
- /backend/admin/get-rooms.php
- /backend/admin/get-room.php
- /backend/admin/get-users.php
- /backend/admin/get-reservations.php
- /backend/admin/get-all-reservations.php
- /backend/admin/get-payments.php
- /backend/admin/get-fnb-orders.php
```

## Fitur Teknis

### Responsive Design
- Mobile-first approach
- Grid dan Flexbox untuk layout
- Media queries untuk breakpoints (768px, 480px, 1024px)
- Touch-friendly interface

### Security
- Session-based authentication via PHP
- Sanitized inputs
- CORS headers untuk API
- Protected endpoints dengan requireLogin() dan requireAdmin()

### Data Validation
- Client-side validation di JavaScript
- Date validation (check-in/check-out)
- Price calculation dengan real-time update
- Password confirmation

### UI/UX Features
- Status badges dengan color coding
- Loading states untuk API calls
- Alert notifications (success/error)
- Smooth animations dan transitions
- Empty states handling
- Sidebar navigation dengan active states

## Styling Variables
```css
--brand-pink: #ff7a89
--brand-gold: #f0b343
--accent: linear-gradient(135deg, var(--brand-gold) 0%, var(--brand-pink) 100%)
--status-pending: #fff3cd
--status-confirmed: #d4edda
--status-cancelled: #f8d7da
--status-completed: #d1ecf1
```

## Cara Menggunakan

1. **Setup Database**
   - Import hotel_management.sql ke MySQL

2. **Update API Base URL**
   - Buka frontend/js/api.js
   - Update `API_BASE_URL` sesuai dengan server Anda

3. **Akses Frontend**
   - Home: http://localhost/TugasBesarPWD/frontend/index.html
   - Login: http://localhost/TugasBesarPWD/frontend/pages/login.html
   - Admin: http://localhost/TugasBesarPWD/frontend/admin/dashboard.html (after login as admin)

## Testing Accounts

```
Admin:
Email: admin@gmail.com
Password: (see database)

User:
Email: juandarren09@gmail.com
Password: (see database)
```

## Kualitas Kode

- ✅ HTML5 semantic structure
- ✅ CSS3 modern styling
- ✅ Vanilla JavaScript (no frameworks)
- ✅ RESTful API endpoints
- ✅ Responsive design
- ✅ Error handling
- ✅ Input validation
- ✅ Consistent naming conventions
- ✅ Modular code structure
- ✅ Separated concerns (HTML/CSS/JS)

## Notes

- Tampilan UI tetap sama dengan versi PHP sebelumnya
- Semua logika bisnis dihandle oleh JavaScript
- Backend hanya menangani data dan proses
- Dapat diperluas dengan framework (React, Vue) di masa depan
