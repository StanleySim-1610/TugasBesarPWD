<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$reservation_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Get reservation details
$stmt = $conn->prepare("
    SELECT r.*, k.tipe_kamar, k.harga, k.deskripsi,
           u.nama, u.email, u.no_telp, u.no_identitas,
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari,
           p.id_payment_res, p.total_bayar, p.metode, p.status as payment_status, p.paid_at
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    JOIN user u ON r.id_user = u.id_user
    LEFT JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    WHERE r.id_reservation = ?
");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    header('Location: ' . ($is_admin ? 'dashboard.php' : '../user/dashboard.php'));
    exit();
}

// Check if user has permission
if (!$is_admin && $reservation['id_user'] != $user_id) {
    header('Location: ../user/dashboard.php');
    exit();
}

$base_path = $is_admin ? '..' : '..';
$dashboard_link = $is_admin ? 'dashboard.php' : 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Reservasi - #<?php echo $reservation_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --rose-pink: #ff6b7d;
            --soft-yellow: #fdff94;
            --deep-rose: #ff4f63;
            --white: #ffffff;
            --light-bg: #fffef9;
            --cream: #fff0f2;
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

        .topnavbar {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
            padding: 0;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .topnavbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
        }

        .topnavbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
        }

        .topnavbar-brand h2 {
            color: var(--soft-yellow);
            font-size: 24px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .topnavbar-menu {
            display: flex;
            gap: 5px;
            list-style: none;
        }

        .topnavbar-menu a {
            color: white;
            text-decoration: none;
            padding: 20px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            border-bottom: 3px solid transparent;
        }

        .topnavbar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
        }

        .topnavbar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
        }

        .main-content {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: #ff6b7d;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .detail-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .detail-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.15);
            margin-bottom: 25px;
            border: 2px solid #ffb3c1;
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            padding-bottom: 20px;
            border-bottom: 2px solid #ffb3c1;
            margin-bottom: 25px;
        }

        .detail-header h2 {
            color: var(--rose-pink);
            font-size: 28px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .detail-item {
            padding: 15px;
            background: linear-gradient(135deg, #fff0f2 0%, #ffe4e7 100%);
            border-radius: 10px;
            border: 1px solid #ffb3c1;
        }

        .detail-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .detail-value {
            font-size: 16px;
            color: var(--rose-pink);
            font-weight: 600;
        }

        .price-breakdown {
            background: linear-gradient(135deg, #fff0f2 0%, #ffe4e7 100%);
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
            border: 2px solid #ffb3c1;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 107, 125, 0.1);
            color: #333;
        }

        .price-row:last-child {
            border-bottom: none;
        }

        .price-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--rose-pink);
            border-top: 2px solid #ffb3c1;
            padding-top: 15px;
            margin-top: 15px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--rose-pink) 0%, #ff8a94 50%, var(--soft-yellow) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 125, 0.4);
        }

        .btn-outline {
            background: white;
            color: var(--rose-pink);
            border: 2px solid var(--rose-pink);
        }

        .btn-outline:hover {
            background: var(--rose-pink);
            color: white;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
        }

        .status-pending {
            background: #fff4e5;
            color: #ff9800;
            border: 2px solid #ff9800;
        }

        .status-confirmed {
            background: #e8f5e9;
            color: #4caf50;
            border: 2px solid #4caf50;
        }

        .status-cancelled {
            background: #ffebee;
            color: #f44336;
            border: 2px solid #f44336;
        }

        .status-paid {
            background: #e8f5e9;
            color: #4caf50;
            border: 2px solid #4caf50;
        }

        .status-unpaid {
            background: #ffebee;
            color: #f44336;
            border: 2px solid #f44336;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="topnavbar">
        <div class="topnavbar-content">
            <div class="topnavbar-brand">
                <h2>LENTERA NUSANTARA</h2>
            </div>
            <ul class="topnavbar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="reservations.php" class="active"><i class="fas fa-calendar-check"></i> Reservasi Saya</a></li>
                <li><a href="rooms.php"><i class="fas fa-bed"></i> Lihat Kamar</a></li>
                <li><a href="fnb_new_order.php"><i class="fas fa-concierge-bell"></i> Dining</a></li>
                <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profil</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1>Detail Reservasi</h1>
        </div>

        <div class="detail-container">

            <div class="detail-card">
                <div class="detail-header">
                    <div>
                        <h2>Booking #<?php echo $reservation_id; ?></h2>
                        <p style="color: #666; margin-top: 5px;">
                            Dibuat pada <?php echo date('d M Y, H:i', strtotime($reservation['created_at'])); ?>
                        </p>
                    </div>
                    <div>
                        <span class="status-badge status-<?php echo $reservation['status']; ?>" style="font-size: 14px; padding: 8px 20px;">
                            <?php echo ucfirst($reservation['status']); ?>
                        </span>
                    </div>
                </div>

                <h3 style="color: var(--rose-pink);">Informasi Tamu</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Nama Tamu</div>
                        <div class="detail-value"><?php echo htmlspecialchars($reservation['nama']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($reservation['email']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Telepon</div>
                        <div class="detail-value"><?php echo htmlspecialchars($reservation['no_telp'] ?? '-'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">No. Identitas</div>
                        <div class="detail-value"><?php echo htmlspecialchars($reservation['no_identitas'] ?? '-'); ?></div>
                    </div>
                </div>

                <h3 style="margin-top: 30px; color: var(--rose-pink);">Informasi Kamar & Menginap</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Tipe Kamar</div>
                        <div class="detail-value"><?php echo htmlspecialchars($reservation['tipe_kamar']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Check-in</div>
                        <div class="detail-value"><?php echo date('d M Y', strtotime($reservation['check_in'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Check-out</div>
                        <div class="detail-value"><?php echo date('d M Y', strtotime($reservation['check_out'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Jumlah Tamu</div>
                        <div class="detail-value"><?php echo $reservation['jumlah_orang']; ?> orang</div>
                    </div>
                </div>

                <h3 style="margin-top: 30px; color: var(--rose-pink);">Informasi Pembayaran</h3>
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Harga Kamar (per malam):</span>
                        <span><strong><?php echo formatRupiah($reservation['harga']); ?></strong></span>
                    </div>
                    <div class="price-row">
                        <span>Jumlah Malam:</span>
                        <span><strong><?php echo $reservation['jumlah_hari']; ?> malam</strong></span>
                    </div>
                    <div class="price-row price-total">
                        <span>Total Bayar:</span>
                        <span><?php echo formatRupiah($reservation['total_harga']); ?></span>
                    </div>
                </div>

                <?php if ($reservation['payment_status']): ?>
                <div style="background: #e8f5e9; padding: 20px; border-radius: 10px; border: 2px solid #4caf50; color: #2e7d32;">
                    <strong>Status Pembayaran:</strong> <?php echo ucfirst($reservation['payment_status']); ?><br>
                    <strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($reservation['metode']); ?><br>
                    <strong>Dibayar Pada:</strong> <?php echo date('d M Y, H:i', strtotime($reservation['paid_at'])); ?>
                </div>
                <?php else: ?>
                <div style="background: #ffebee; padding: 20px; border-radius: 10px; border: 2px solid #f44336; color: #c62828;">
                    <strong>Status Pembayaran:</strong> Belum Dibayar<br>
                    Silakan selesaikan pembayaran untuk mengkonfirmasi reservasi Anda.
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="reservations.php" class="btn btn-outline">
                        Kembali ke Daftar
                    </a>
                    <?php if (!$is_admin && strtotime($reservation['check_in']) >= strtotime(date('Y-m-d')) && in_array($reservation['status'], ['pending','confirmed'])): ?>
                        <a href="edit_reservation.php?id=<?php echo $reservation_id; ?>" class="btn btn-primary">
                            Edit Reservasi
                        </a>
                    <?php endif; ?>
                    <?php if (!$is_admin && $reservation['status'] == 'pending' && !$reservation['payment_status']): ?>
                        <a href="payment.php?reservation=<?php echo $reservation_id; ?>" class="btn btn-primary">
                            Selesaikan Pembayaran
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
