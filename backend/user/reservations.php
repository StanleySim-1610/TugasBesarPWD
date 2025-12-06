<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's reservations
// Mengambil data reservasi beserta status pembayarannya
$reservations = $conn->query("
    SELECT r.*, k.tipe_kamar, k.harga,
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari,
           p.status as payment_status, p.metode as payment_method, p.paid_at
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    LEFT JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    WHERE r.id_user = $user_id
    ORDER BY r.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Saya - Lentera Nusantara</title>
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
        
        .main-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .page-title {
            font-size: 2.5rem;
            color: #ff6b7d;
            text-shadow: 2px 2px 4px rgba(255, 107, 125, 0.1);
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 125, 0.4);
        }
        
        .content-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
        }
        
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
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .data-table tr:hover {
            background: #fff9f0;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .btn-sm {
            padding: 8px 16px;
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
    <nav class="top-navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <img src="../../frontend/assets/logo.png?v=2" alt="Logo" class="navbar-logo">
                <span class="navbar-title">Lentera Nusantara Hotel</span>
            </div>
            <ul class="navbar-menu">
                <li class="navbar-item">
                    <a href="dashboard.php" class="navbar-link">
                        <i class="fas fa-home"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="reservations.php" class="navbar-link active">
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

    <div class="main-wrapper">
        <div class="page-header">
            <h1 class="page-title">📅 Reservasi Saya</h1>
            <a href="rooms.php" class="btn btn-primary">➕ Booking Baru</a>
        </div>

        <div class="content-section">
                <?php if ($reservations->num_rows > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Booking</th>
                                <th>Tipe Kamar</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Durasi</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th style="min-width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($res = $reservations->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $res['id_reservation']; ?></strong></td>
                                <td><?php echo htmlspecialchars($res['tipe_kamar']); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_out'])); ?></td>
                                <td><?php echo $res['jumlah_hari']; ?> malam</td>
                                <td><strong><?php echo formatRupiah($res['total_harga']); ?></strong></td>
                                <td>
                                    <?php if ($res['payment_status'] == 'paid'): ?>
                                        <span class="status-badge status-paid">Lunas</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Belum Bayar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $res['status']; ?>">
                                        <?php 
                                        $status_indo = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'cancelled' => 'Dibatalkan'];
                                        echo $status_indo[$res['status']] ?? ucfirst($res['status']);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $is_paid = ($res['payment_status'] == 'paid');
                                    // Cek apakah status masih aktif (bukan cancelled/completed)
                                    $is_active = in_array($res['status'], ['pending', 'confirmed']);
                                    ?>
                                    
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        
                                        <div style="display: flex; gap: 5px; width: 100%;">
                                            <a href="reservation_detail.php?id=<?php echo $res['id_reservation']; ?>" 
                                               class="btn btn-sm" 
                                               style="flex: 1; text-align: center; justify-content: center; background: #fdff94; color: #333;">
                                               Lihat
                                            </a>

                                            <?php if ($is_paid && $is_active): ?>
                                                <a href="edit_reservation.php?id=<?php echo $res['id_reservation']; ?>" 
                                                   class="btn btn-sm" 
                                                   style="background: #ff6b7d; color: white; flex: 1; text-align: center; justify-content: center;">
                                                   Edit
                                                </a>
                                            
                                            <?php elseif (!$is_paid && $res['status'] == 'pending'): ?>
                                                <a href="delete_reservation.php?id=<?php echo $res['id_reservation']; ?>" 
                                                   class="btn btn-sm" 
                                                   style="background: #dc3545; color: white; flex: 1; text-align: center; justify-content: center;"
                                                   onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?');">
                                                   Batal
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!$is_paid && $res['status'] == 'pending'): ?>
                                            <a href="payment.php?reservation=<?php echo $res['id_reservation']; ?>" 
                                               class="btn btn-sm" 
                                               style="background: #4CAF50; color: white; display: block; text-align: center; width: 100%; box-sizing: border-box;">
                                               Bayar Sekarang
                                            </a>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>Anda belum memiliki reservasi.</p>
                    <a href="rooms.php" class="btn btn-primary">Lihat Kamar Tersedia</a>
                </div>
                <?php endif; ?>
        </div>
    </div>
</body>
</html>