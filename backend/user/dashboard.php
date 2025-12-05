<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $conn->prepare("SELECT * FROM user WHERE id_user = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's reservations
$stmt = $conn->prepare("
    SELECT r.*, k.tipe_kamar, k.harga as harga_kamar, 
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    WHERE r.id_user = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();

// Get available rooms
$rooms = $conn->query("SELECT * FROM kamar WHERE jumlah_tersedia > 0 ORDER BY harga ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - User</title>
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
    <style>
        /* Tambahan Style Khusus Dashboard agar gambar pas */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .room-card {
            background: white;
            border-radius: 10px;
            overflow: hidden; /* Agar gambar tidak keluar radius */
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); /* Sedikit bayangan agar rapi */
            height: 100%; /* Pastikan tinggi kartu seragam */
        }
        .room-image-dashboard {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .room-content-padding {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .room-header h3 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }
        .room-available {
            font-size: 0.8rem;
            color: white;
            background-color: #4CAF50;
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 10px;
        }
        .room-description {
            flex-grow: 1;
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        /* PERBAIKAN UTAMA DI SINI */
        .room-footer {
            display: flex;
            justify-content: space-between;
            align-items: center; /* Menjaga harga dan tombol sejajar vertikal */
            margin-top: auto;
            gap: 10px; /* Jarak antara harga dan tombol */
        }

        .room-price {
            font-size: 16px; /* Ukuran font disesuaikan agar muat */
            font-weight: bold;
            color: #ff4757; /* Warna merah muda sesuai desain */
            white-space: nowrap; /* Mencegah teks turun ke baris baru */
            display: flex;
            flex-direction: column; /* Opsional: jika ingin /night di bawah harga, tapi nowrap di atas mencegahnya */
            line-height: 1.2;
        }
        
        /* Opsi Alternatif: Jika ingin satu baris lurus */
        .room-price {
            display: block; 
        }

        .room-price span {
            font-size: 12px;
            color: #999;
            font-weight: normal;
        }

        /* Pastikan tombol tidak mengecil */
        .room-footer .btn {
            white-space: nowrap;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>User Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="nav-icon">🏠</span>
                    Dashboard
                </a>
                <a href="reservations.php" class="nav-item">
                    <span class="nav-icon">📅</span>
                    My Reservations
                </a>
                <a href="rooms.php" class="nav-item">
                    <span class="nav-icon">🏨</span>
                    Browse Rooms
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="nav-icon">👤</span>
                    Profile
                </a>
                <a href="../logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Welcome, <?php echo htmlspecialchars($user['nama']); ?>!</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                    <?php if ($user['foto_profil']): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($user['foto_profil']); ?>" alt="Profile" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar-placeholder">
                            <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--brand-pink);">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $reservations->num_rows; ?></h3>
                        <p>Total Reservations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">✓</div>
                    <div class="stat-info">
                        <?php
                        $active = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'confirmed'")->fetch_assoc();
                        ?>
                        <h3><?php echo $active['count']; ?></h3>
                        <p>Active Bookings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ff9800;">⏳</div>
                    <div class="stat-info">
                        <?php
                        $pending = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'pending'")->fetch_assoc();
                        ?>
                        <h3><?php echo $pending['count']; ?></h3>
                        <p>Pending Bookings</p>
                    </div>
                </div>
            </div>

            <section class="content-section">
                <h2>Available Rooms</h2>
                <div class="rooms-grid">
                    <?php while($room = $rooms->fetch_assoc()): 
                        // Logika Penentuan Gambar
                        $tipe = strtolower($room['tipe_kamar']);
                        
                        // Default nama file saja
                        $gambar = '../../../frontend/assets/room_photo/standard_room.jpg';
                        
                        if (strpos($tipe, 'presidential') !== false) {
                            $gambar = '../../../frontend/assets/room_photo/presidential_suite.png';
                        } elseif (strpos($tipe, 'suite') !== false) {
                            $gambar = '../../../frontend/assets/room_photo/suite_room.jpg';
                        } elseif (strpos($tipe, 'deluxe') !== false) {
                            $gambar = '../../../frontend/assets/room_photo/deluxe_room.jpg';
                        }
                    ?>
                    <div class="room-card">
                        <img src="../assets/room_photo/<?php echo $gambar; ?>" alt="<?php echo htmlspecialchars($room['tipe_kamar']); ?>" class="room-image-dashboard">
                        
                        <div class="room-content-padding">
                            <div class="room-header">
                                <h3><?php echo htmlspecialchars($room['tipe_kamar']); ?></h3>
                                <span class="room-available"><?php echo $room['jumlah_tersedia']; ?> available</span>
                            </div>
                            <p class="room-description"><?php echo htmlspecialchars($room['deskripsi']); ?></p>
                            
                            <div class="room-footer">
                                <div class="room-price">
                                    <?php echo formatRupiah($room['harga']); ?><span>/night</span>
                                </div>
                                <a href="booking.php?room=<?php echo $room['id_kamar']; ?>" class="btn btn-primary" style="padding: 8px 16px;">Book Now</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <section class="content-section">
                <h2>Recent Reservations</h2>
                <?php if ($reservations->num_rows > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Duration</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $reservations->data_seek(0);
                            $count = 0;
                            while($res = $reservations->fetch_assoc()): 
                                if ($count >= 5) break;
                                $count++;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($res['tipe_kamar']); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_out'])); ?></td>
                                <td><?php echo $res['jumlah_hari']; ?> days</td>
                                <td><?php echo formatRupiah($res['total_harga']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $res['status']; ?>">
                                        <?php echo ucfirst($res['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="reservation_detail.php?id=<?php echo $res['id_reservation']; ?>" class="btn btn-sm">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center" style="margin-top: 20px;">
                    <a href="reservations.php" class="btn btn-outline">View All Reservations</a>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>You don't have any reservations yet.</p>
                    <a href="rooms.php" class="btn btn-primary">Browse Rooms</a>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>