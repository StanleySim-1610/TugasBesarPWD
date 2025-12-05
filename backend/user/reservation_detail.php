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
    <title>Reservation Details - #<?php echo $reservation_id; ?></title>
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
    <style>
        .detail-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .detail-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 25px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
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
            color: #333;
            font-weight: 600;
        }
        .price-breakdown {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .price-row:last-child {
            border-bottom: none;
        }
        .price-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--brand-pink);
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 15px;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar <?php echo $is_admin ? 'admin-sidebar' : ''; ?>">
            <div class="sidebar-header">
                <img src="<?php echo $base_path; ?>/assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3><?php echo $is_admin ? 'Admin Panel' : 'User Panel'; ?></h3>
            </div>
            <nav class="sidebar-nav">
                <?php if ($is_admin): ?>
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
                <a href="users.php" class="nav-item">
                    <span class="nav-icon">👥</span>
                    Users Management
                </a>
                <a href="rooms_management.php" class="nav-item">
                    <span class="nav-icon">🏨</span>
                    Rooms Management
                </a>
                <a href="reservations_management.php" class="nav-item active">
                    <span class="nav-icon">📅</span>
                    Reservations
                </a>
                <a href="payments.php" class="nav-item">
                    <span class="nav-icon">💳</span>
                    Payments
                </a>
                <a href="fnb_orders.php" class="nav-item">
                    <span class="nav-icon">🍽️</span>
                    F&B Orders
                </a>
                <?php else: ?>
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    Dashboard
                </a>
                <a href="reservations.php" class="nav-item active">
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
                <?php endif; ?>
                <a href="<?php echo $base_path; ?>/logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="detail-container">
                <div class="top-bar">
                    <h1>Reservation Details</h1>
                    <div style="display:flex; gap: 12px; align-items:center;">
                        <a href="<?php echo $is_admin ? 'reservations_management.php' : 'reservations.php'; ?>" class="btn btn-outline">Back</a>
                        <?php if (!$is_admin && strtotime($reservation['check_in']) >= strtotime(date('Y-m-d')) && in_array($reservation['status'], ['pending','confirmed'])): ?>
                            <a href="edit_reservation.php?id=<?php echo $reservation_id; ?>" class="btn btn-sm" style="background: var(--brand-pink); color: white;">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-header">
                        <div>
                            <h2>Booking #<?php echo $reservation_id; ?></h2>
                            <p style="color: #666; margin-top: 5px;">
                                Booked on <?php echo date('d M Y, H:i', strtotime($reservation['created_at'])); ?>
                            </p>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo $reservation['status']; ?>" style="font-size: 14px; padding: 8px 20px;">
                                <?php echo ucfirst($reservation['status']); ?>
                            </span>
                        </div>
                    </div>

                    <h3>Guest Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Guest Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($reservation['nama']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo htmlspecialchars($reservation['email']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value"><?php echo htmlspecialchars($reservation['no_telp'] ?? '-'); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">ID Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($reservation['no_identitas'] ?? '-'); ?></div>
                        </div>
                    </div>

                    <h3 style="margin-top: 30px;">Room & Stay Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Room Type</div>
                            <div class="detail-value"><?php echo htmlspecialchars($reservation['tipe_kamar']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Check-in Date</div>
                            <div class="detail-value"><?php echo date('d M Y', strtotime($reservation['check_in'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Check-out Date</div>
                            <div class="detail-value"><?php echo date('d M Y', strtotime($reservation['check_out'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Number of Guests</div>
                            <div class="detail-value"><?php echo $reservation['jumlah_orang']; ?> person(s)</div>
                        </div>
                    </div>

                    <h3 style="margin-top: 30px;">Payment Information</h3>
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Room Rate (per night):</span>
                            <span><strong><?php echo formatRupiah($reservation['harga']); ?></strong></span>
                        </div>
                        <div class="price-row">
                            <span>Number of Nights:</span>
                            <span><strong><?php echo $reservation['jumlah_hari']; ?> nights</strong></span>
                        </div>
                        <div class="price-row price-total">
                            <span>Total Amount:</span>
                            <span><?php echo formatRupiah($reservation['total_harga']); ?></span>
                        </div>
                    </div>

                    <?php if ($reservation['payment_status']): ?>
                    <div class="alert alert-success">
                        <strong>Payment Status:</strong> <?php echo ucfirst($reservation['payment_status']); ?><br>
                        <strong>Payment Method:</strong> <?php echo htmlspecialchars($reservation['metode']); ?><br>
                        <strong>Paid At:</strong> <?php echo date('d M Y, H:i', strtotime($reservation['paid_at'])); ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-error">
                        <strong>Payment Status:</strong> Not Paid<br>
                        Please complete the payment to confirm your reservation.
                    </div>
                    <?php endif; ?>

                    <?php if (!$is_admin && $reservation['status'] == 'pending' && !$reservation['payment_status']): ?>
                    <div class="action-buttons">
                        <a href="payment.php?reservation=<?php echo $reservation_id; ?>" class="btn btn-primary" style="flex: 1;">
                            Complete Payment
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
