<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $new_status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE reservation SET status = ? WHERE id_reservation = ?");
    $stmt->bind_param("si", $new_status, $reservation_id);
    
    if ($stmt->execute()) {
        $success = 'Reservation status updated successfully!';
    } else {
        $error = 'Failed to update status!';
    }
}

// Get all reservations
$reservations = $conn->query("
    SELECT r.*, u.nama, u.email, u.no_telp, k.tipe_kamar, k.harga,
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari,
           p.status as payment_status, p.metode as payment_method
    FROM reservation r
    JOIN user u ON r.id_user = u.id_user
    JOIN kamar k ON r.id_kamar = k.id_kamar
    LEFT JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    ORDER BY r.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations Management - Admin</title>
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
    <style>
        .status-update-form {
            display: inline-flex;
            gap: 5px;
            align-items: center;
        }
        .status-update-form select {
            padding: 5px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 12px;
        }
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
    </style>
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
                <a href="../logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Reservations Management</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <section class="content-section">
                <h2>All Reservations</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest Info</th>
                                <th>Room Type</th>
                                <th>Check In/Out</th>
                                <th>Duration</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($res = $reservations->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $res['id_reservation']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($res['nama']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($res['email']); ?></small><br>
                                    <small><?php echo htmlspecialchars($res['no_telp'] ?? '-'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($res['tipe_kamar']); ?></td>
                                <td>
                                    <strong>In:</strong> <?php echo date('d M Y', strtotime($res['check_in'])); ?><br>
                                    <strong>Out:</strong> <?php echo date('d M Y', strtotime($res['check_out'])); ?>
                                </td>
                                <td><?php echo $res['jumlah_hari']; ?> nights</td>
                                <td><?php echo $res['jumlah_orang']; ?> person(s)</td>
                                <td><strong><?php echo formatRupiah($res['total_harga']); ?></strong></td>
                                <td>
                                    <?php if ($res['payment_status']): ?>
                                        <span class="status-badge status-<?php echo $res['payment_status']; ?>">
                                            <?php echo ucfirst($res['payment_status']); ?>
                                        </span><br>
                                        <small><?php echo htmlspecialchars($res['payment_method']); ?></small>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Not Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="status-update-form">
                                        <input type="hidden" name="reservation_id" value="<?php echo $res['id_reservation']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $res['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $res['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="cancelled" <?php echo $res['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="completed" <?php echo $res['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <a href="reservation_detail.php?id=<?php echo $res['id_reservation']; ?>" class="btn btn-sm">View Details</a>
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
