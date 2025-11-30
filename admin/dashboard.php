<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM user WHERE email != 'admin@gmail.com'")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM kamar")->fetch_assoc()['count'];
$total_reservations = $conn->query("SELECT COUNT(*) as count FROM reservation")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_bayar) as total FROM payment_reservation WHERE status = 'paid'")->fetch_assoc()['total'] ?? 0;

// Get recent reservations
$recent_reservations = $conn->query("
    SELECT r.*, u.nama, u.email, k.tipe_kamar
    FROM reservation r
    JOIN user u ON r.id_user = u.id_user
    JOIN kamar k ON r.id_kamar = k.id_kamar
    ORDER BY r.created_at DESC
    LIMIT 10
");

// Get room statistics
$room_stats = $conn->query("
    SELECT k.tipe_kamar, k.jumlah_tersedia, 
           COUNT(r.id_reservation) as booking_count,
           SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
    FROM kamar k
    LEFT JOIN reservation r ON k.id_kamar = r.id_kamar
    GROUP BY k.id_kamar
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Management</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Administrator</span>
                    <div class="user-avatar-placeholder admin-avatar">A</div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--brand-pink);">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">🏨</div>
                    <div class="stat-info">
                        <h3><?php echo $total_rooms; ?></h3>
                        <p>Total Rooms</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ff9800;">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $total_reservations; ?></h3>
                        <p>Total Reservations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f44336;">💰</div>
                    <div class="stat-info">
                        <h3><?php echo formatRupiah($total_revenue); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Room Statistics -->
            <section class="content-section">
                <h2>Room Statistics</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Available</th>
                                <th>Total Bookings</th>
                                <th>Confirmed</th>
                                <th>Occupancy Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($room = $room_stats->fetch_assoc()): 
                                $occupancy_rate = $room['booking_count'] > 0 ? 
                                    round(($room['confirmed_count'] / $room['booking_count']) * 100) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($room['tipe_kamar']); ?></strong></td>
                                <td><?php echo $room['jumlah_tersedia']; ?> rooms</td>
                                <td><?php echo $room['booking_count']; ?></td>
                                <td><?php echo $room['confirmed_count']; ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $occupancy_rate; ?>%"></div>
                                        <span><?php echo $occupancy_rate; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent Reservations -->
            <section class="content-section">
                <div class="section-header">
                    <h2>Recent Reservations</h2>
                    <a href="reservations_management.php" class="btn btn-primary">View All</a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest Name</th>
                                <th>Email</th>
                                <th>Room Type</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($res = $recent_reservations->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $res['id_reservation']; ?></td>
                                <td><?php echo htmlspecialchars($res['nama']); ?></td>
                                <td><?php echo htmlspecialchars($res['email']); ?></td>
                                <td><?php echo htmlspecialchars($res['tipe_kamar']); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_out'])); ?></td>
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
            </section>
        </main>
    </div>
</body>
</html>
