<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get all payments
$payments = $conn->query("
    SELECT pr.*, r.id_reservation, r.check_in, r.check_out, r.status as reservation_status,
           u.nama, u.email, k.tipe_kamar
    FROM payment_reservation pr
    JOIN reservation r ON pr.id_reservation = r.id_reservation
    JOIN user u ON r.id_user = u.id_user
    JOIN kamar k ON r.id_kamar = k.id_kamar
    ORDER BY pr.created_at DESC
");

// Calculate totals
$total_revenue = $conn->query("SELECT SUM(total_bayar) as total FROM payment_reservation WHERE status = 'paid'")->fetch_assoc()['total'] ?? 0;
$pending_payments = $conn->query("SELECT COUNT(*) as count FROM payment_reservation WHERE status = 'pending'")->fetch_assoc()['count'];
$completed_payments = $conn->query("SELECT COUNT(*) as count FROM payment_reservation WHERE status = 'paid'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
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
                <a href="reservations_management.php" class="nav-item">
                    <span class="nav-icon">📅</span>
                    Reservations
                </a>
                <a href="payments.php" class="nav-item active">
                    <span class="nav-icon">💳</span>
                    Payments
                </a>
                <a href="fnb_orders.php" class="nav-item">
                    <span class="nav-icon">🍽️</span>
                    F&B Orders
                </a>
                <a href="../logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Payment Management</h1>
            </div>

            <!-- Payment Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">💰</div>
                    <div class="stat-info">
                        <h3><?php echo formatRupiah($total_revenue); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">✓</div>
                    <div class="stat-info">
                        <h3><?php echo $completed_payments; ?></h3>
                        <p>Completed Payments</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ff9800;">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo $pending_payments; ?></h3>
                        <p>Pending Payments</p>
                    </div>
                </div>
            </div>

            <section class="content-section">
                <h2>All Payments</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking ID</th>
                                <th>Guest Name</th>
                                <th>Room Type</th>
                                <th>Check In/Out</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Paid At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($payment = $payments->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $payment['id_payment_res']; ?></strong></td>
                                <td>#<?php echo $payment['id_reservation']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['nama']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($payment['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($payment['tipe_kamar']); ?></td>
                                <td>
                                    <?php echo date('d M', strtotime($payment['check_in'])); ?> - 
                                    <?php echo date('d M Y', strtotime($payment['check_out'])); ?>
                                </td>
                                <td><strong><?php echo formatRupiah($payment['total_bayar']); ?></strong></td>
                                <td><?php echo htmlspecialchars($payment['metode']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    echo $payment['paid_at'] ? date('d M Y H:i', strtotime($payment['paid_at'])) : '-';
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
