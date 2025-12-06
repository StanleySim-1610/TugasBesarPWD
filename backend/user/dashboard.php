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
    <title>Beranda - Lentera Nusantara</title>
    <link rel="stylesheet" href="../../frontend/assets/css/lentera-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-pink: #ff6b7d;
            --primary-yellow: #fdff94;
            --gradient-start: #ff6b7d;
            --gradient-end: #fdff94;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
            background: white;
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
            padding: 0;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
        }
        
        .navbar-logo {
            height: 50px;
            filter: brightness(0) invert(1);
        }
        
        .navbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 5px;
            list-style: none;
        }
        
        .navbar-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 18px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            font-weight: 500;
        }
        
        .navbar-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
        }
        
        .navbar-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
            color: white;
        }
        
        .navbar-icon {
            font-size: 1.2rem;
        }
        
        /* Main Content */
        .main-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }
        
        .welcome-section {
            background: linear-gradient(180deg, white 0%, #fffbf5 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
        }
        
        .welcome-title {
            font-size: 2rem;
            color: #ff6b7d;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(255, 107, 125, 0.2);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .stat-info h3 {
            font-size: 2rem;
            color: #ff6b7d;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #666;
            font-size: 0.95rem;
        }
        
        /* Section */
        .content-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
        }
        
        .content-section h2 {
            color: #ff6b7d;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            
        }
        
        /* Rooms Grid */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .room-card {
            background: linear-gradient(to bottom, white, #fffbf5);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 107, 125, 0.2);
            outline: 2px solid;
            outline-offset: 0;
            outline-color: transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #ff6b7d, #fdff94) border-box;
            border: 2px solid transparent;
            border-color: #fdff94;
        }
        
        .room-image-dashboard {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .room-content-padding {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .room-header h3 {
            color: #ff6b7d;
            margin-bottom: 8px;
            font-size: 1.2rem;
        }
        
        .room-available {
            background: #4CAF50;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 12px;
        }
        
        .room-description {
            flex-grow: 1;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .room-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            gap: 15px;
        }
        
        .room-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #ff6b7d;
        }
        
        .room-price span {
            font-size: 0.85rem;
            color: #999;
            font-weight: normal;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(180deg, #ff4f58 0%, #ff6b7d 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 125, 0.3);
        }
        
        /* Table */
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .data-table tr:hover {
            background: #fff9f0;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .text-center {
            text-align: center;
        }
        
        .btn-outline {
            background: white;
            color: #ff6b7d;
            box-shadow: 0 2px 8px rgba(255, 107, 125, 0.15);
        }
        
        .btn-outline:hover {
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #ff6b7d, #fdff94) border-box;
            border: 2px solid transparent;
            color: #ff6b7d;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.25);
        }
        
        .btn-sm {
            padding: 6px 15px;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .navbar-menu {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <img src="../../frontend/assets/logo.png?v=2" alt="Logo" class="navbar-logo">
                <span class="navbar-title">Lentera Nusantara Hotel</span>
            </div>
            <ul class="navbar-menu">
                <li class="navbar-item">
                    <a href="dashboard.php" class="navbar-link active">
                        <i class="fas fa-home"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="reservations.php" class="navbar-link">
                        <i class="fas fa-calendar-check"></i>
                        <span>Reservasi Saya</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="rooms.php" class="navbar-link">
                        <i class="fas fa-bed"></i>
                        <span>Lihat Kamar</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="fnb_new_order.php" class="navbar-link">
                        <i class="fas fa-concierge-bell"></i>
                        <span>Dining</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="profile.php" class="navbar-link">
                        <i class="fas fa-user-circle"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="../logout.php" class="navbar-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="welcome-section">
            <h1 class="welcome-title">🏨 Selamat Datang, <?php echo htmlspecialchars($user['nama']); ?>!</h1>
            <p class="welcome-subtitle">📧 <?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(180deg, #ff6b7d, #ff8a94);">📅</div>
                <div class="stat-info">
                    <h3><?php echo $reservations->num_rows; ?></h3>
                    <p>Total Reservasi</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(180deg, #4CAF50, #2e7d32);">✓</div>
                <div class="stat-info">
                    <?php
                    $active = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'confirmed'")->fetch_assoc();
                    ?>
                    <h3><?php echo $active['count']; ?></h3>
                    <p>Booking Aktif</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(180deg, #ff9800, #f57c00);">⏳</div>
                <div class="stat-info">
                    <?php
                    $pending = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'pending'")->fetch_assoc();
                    ?>
                    <h3><?php echo $pending['count']; ?></h3>
                    <p>Menunggu Pembayaran</p>
                </div>
            </div>
        </div>

            <h2>🏨 Kamar Tersedia</h2>
                <div class="rooms-grid">
                    <?php while($room = $rooms->fetch_assoc()): 
                        // Logika Penentuan Gambar
                        $tipe = strtolower($room['tipe_kamar']);
                        
                        // Default nama file saja
                        $gambar = 'standard_room.jpg';
                        
                        if (strpos($tipe, 'presidential') !== false) {
                            $gambar = 'presidential_suite.png';
                        } elseif (strpos($tipe, 'suite') !== false) {
                            $gambar = 'suite_room.jpg';
                        } elseif (strpos($tipe, 'deluxe') !== false) {
                            $gambar = 'deluxe_room.jpg';
                        }
                    ?>
                    <div class="room-card">
                        <img src="../../frontend/assets/room_photo/<?php echo $gambar; ?>" alt="<?php echo htmlspecialchars($room['tipe_kamar']); ?>" class="room-image-dashboard">
                        
                        <div class="room-content-padding">
                            <div class="room-header">
                                <h3><?php echo htmlspecialchars($room['tipe_kamar']); ?></h3>
                                <span class="room-available"><?php echo $room['jumlah_tersedia']; ?> available</span>
                            </div>
                            <p class="room-description"><?php echo htmlspecialchars($room['deskripsi']); ?></p>
                            
                            <div class="room-footer">
                                <div class="room-price">
                                    <?php echo formatRupiah($room['harga']); ?><span>/malam</span>
                                </div>
                                <a href="booking.php?room=<?php echo $room['id_kamar']; ?>" class="btn btn-primary">Pesan Sekarang</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
        </section>

        <section class="content-section">
            <h2>📅 Reservasi Terbaru</h2>
            <?php if ($reservations->num_rows > 0): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tipe Kamar</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Durasi</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
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
                            <td><?php echo $res['jumlah_hari']; ?> hari</td>
                            <td><?php echo formatRupiah($res['total_harga']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $res['status']; ?>">
                                    <?php 
                                    $status_indo = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'cancelled' => 'Dibatalkan'];
                                    echo $status_indo[$res['status']] ?? ucfirst($res['status']);
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="reservation_detail.php?id=<?php echo $res['id_reservation']; ?>" class="btn btn-sm btn-primary">Lihat</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center" style="margin-top: 20px;">
                <a href="reservations.php" class="btn btn-outline">Lihat Semua Reservasi</a>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>Anda belum memiliki reservasi.</p>
                <a href="rooms.php" class="btn btn-primary">Lihat Kamar</a>
            </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>