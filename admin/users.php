<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get all users except admin
$users = $conn->query("SELECT * FROM user WHERE email != 'admin@gmail.com' ORDER BY created_at DESC");

// Get user statistics
$stmt = $conn->prepare("
    SELECT u.id_user, u.nama, u.email,
           COUNT(r.id_reservation) as total_reservations,
           SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
           SUM(r.total_harga) as total_spent
    FROM user u
    LEFT JOIN reservation r ON u.id_user = r.id_user
    WHERE u.email != 'admin@gmail.com'
    GROUP BY u.id_user
    ORDER BY total_spent DESC
");
$stmt->execute();
$user_stats = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin</title>
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
                <a href="users.php" class="nav-item active">
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

        <main class="main-content">
            <div class="top-bar">
                <h1>Users Management</h1>
            </div>

            <section class="content-section">
                <h2>Registered Users</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>ID Number</th>
                                <th>Total Bookings</th>
                                <th>Confirmed</th>
                                <th>Total Spent</th>
                                <th>Joined Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $user_stats->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id_user']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['nama']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                    $u = $conn->query("SELECT * FROM user WHERE id_user = {$user['id_user']}")->fetch_assoc();
                                    echo htmlspecialchars($u['no_telp'] ?? '-'); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($u['no_identitas'] ?? '-'); ?></td>
                                <td><?php echo $user['total_reservations']; ?></td>
                                <td><?php echo $user['confirmed_bookings']; ?></td>
                                <td><?php echo formatRupiah($user['total_spent'] ?? 0); ?></td>
                                <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
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
