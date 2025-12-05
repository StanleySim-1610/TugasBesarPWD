<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get all F&B orders
$orders = $conn->query("
    SELECT f.*, r.id_reservation, r.id_user, r.check_in, r.check_out,
           u.nama, u.email, k.tipe_kamar,
           pf.status as payment_status, pf.metode as payment_method
    FROM fnb_order f
    JOIN reservation r ON f.id_reservation = r.id_reservation
    JOIN user u ON r.id_user = u.id_user
    JOIN kamar k ON r.id_kamar = k.id_kamar
    LEFT JOIN payment_fnb pf ON f.id_fnb = pf.id_fnb
    ORDER BY f.created_at DESC
");

$total_orders = $conn->query("SELECT COUNT(*) as count FROM fnb_order")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM fnb_order WHERE status = 'pending'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) as count FROM fnb_order WHERE status = 'completed'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F&B Orders - Admin</title>
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
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
                <a href="payments.php" class="nav-item">
                    <span class="nav-icon">💳</span>
                    Payments
                </a>
                <a href="fnb_orders.php" class="nav-item active">
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
                <h1>Food & Beverage Orders</h1>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--brand-pink);">🍽️</div>
                    <div class="stat-info">
                        <h3><?php echo $total_orders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ff9800;">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo $pending_orders; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">✓</div>
                    <div class="stat-info">
                        <h3><?php echo $completed_orders; ?></h3>
                        <p>Completed Orders</p>
                    </div>
                </div>
            </div>

            <section class="content-section">
                <h2>All F&B Orders</h2>
                <?php if ($orders->num_rows > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Booking ID</th>
                                <th>Guest Info</th>
                                <th>Room</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Order Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id_fnb']; ?></strong></td>
                                <td>#<?php echo $order['id_reservation']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['nama']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($order['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($order['tipe_kamar']); ?></td>
                                <td><?php echo htmlspecialchars($order['item']); ?></td>
                                <td><?php echo $order['qty']; ?></td>
                                <td><?php echo formatRupiah($order['harga']); ?></td>
                                <td><strong><?php echo formatRupiah($order['harga'] * $order['qty']); ?></strong></td>
                                <td>
                                    <?php if ($order['payment_status']): ?>
                                        <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Not Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>No F&B orders yet.</p>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
