<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's reservations
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
    <title>My Reservations</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>User Panel</h3>
            </div>
            <nav class="sidebar-nav">
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
                <a href="../logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>My Reservations</h1>
                <a href="rooms.php" class="btn btn-primary">+ New Booking</a>
            </div>

            <section class="content-section">
                <?php if ($reservations->num_rows > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room Type</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Duration</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($res = $reservations->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $res['id_reservation']; ?></strong></td>
                                <td><?php echo htmlspecialchars($res['tipe_kamar']); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($res['check_out'])); ?></td>
                                <td><?php echo $res['jumlah_hari']; ?> nights</td>
                                <td><?php echo $res['jumlah_orang']; ?> person(s)</td>
                                <td><strong><?php echo formatRupiah($res['total_harga']); ?></strong></td>
                                <td>
                                    <?php if ($res['payment_status']): ?>
                                        <span class="status-badge status-<?php echo $res['payment_status']; ?>">
                                            <?php echo ucfirst($res['payment_status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $res['status']; ?>">
                                        <?php echo ucfirst($res['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="reservation_detail.php?id=<?php echo $res['id_reservation']; ?>" class="btn btn-sm">View</a>
                                    <?php
                                        // Allow rescheduling for future reservations (including same-day check-in) only
                                        $today = strtotime(date('Y-m-d'));
                                        $is_future_or_today = strtotime($res['check_in']) >= $today;
                                    ?>
                                    <?php if ($is_future_or_today && in_array($res['status'], ['pending','confirmed'])): ?>
                                        <a href="edit_reservation.php?id=<?php echo $res['id_reservation']; ?>" class="btn btn-sm" style="background: var(--brand-pink); color: white;">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($res['status'] == 'pending' && !$res['payment_status']): ?>
                                        <a href="payment.php?reservation=<?php echo $res['id_reservation']; ?>" class="btn btn-sm" style="background: #4CAF50;">Pay</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>You don't have any reservations yet.</p>
                    <a href="rooms.php" class="btn btn-primary">Browse Available Rooms</a>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
