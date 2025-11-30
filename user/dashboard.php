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
    <title>Dashboard - User</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>User Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="nav-icon">🏠</span>
                    Dashboard
                </a>
                <a href="reservations.php" class="nav-item">
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

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Welcome, <?php echo htmlspecialchars($user['nama']); ?>!</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                    <?php if ($user['foto_profil']): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($user['foto_profil']); ?>" alt="Profile" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar-placeholder">
                            <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--brand-pink);">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $reservations->num_rows; ?></h3>
                        <p>Total Reservations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">✓</div>
                    <div class="stat-info">
                        <?php
                        $active = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'confirmed'")->fetch_assoc();
                        ?>
                        <h3><?php echo $active['count']; ?></h3>
                        <p>Active Bookings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ff9800;">⏳</div>
                    <div class="stat-info">
                        <?php
                        $pending = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'pending'")->fetch_assoc();
                        ?>
                        <h3><?php echo $pending['count']; ?></h3>
                        <p>Pending Bookings</p>
                    </div>
                </div>
            </div>

            <!-- Available Rooms -->
            <section class="content-section">
                <h2>Available Rooms</h2>
                <div class="rooms-grid">
                    <?php while($room = $rooms->fetch_assoc()): ?>
                    <div class="room-card">
                        <div class="room-header">
                            <h3><?php echo htmlspecialchars($room['tipe_kamar']); ?></h3>
                            <span class="room-available"><?php echo $room['jumlah_tersedia']; ?> available</span>
                        </div>
                        <p class="room-description"><?php echo htmlspecialchars($room['deskripsi']); ?></p>
                        <div class="room-footer">
                            <div class="room-price"><?php echo formatRupiah($room['harga']); ?><span>/night</span></div>
                            <a href="booking.php?room=<?php echo $room['id_kamar']; ?>" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <!-- Recent Reservations -->
            <section class="content-section">
                <h2>Recent Reservations</h2>
                <?php if ($reservations->num_rows > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Duration</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Action</th>
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
                                <td><?php echo $res['jumlah_hari']; ?> days</td>
                                <td><?php echo formatRupiah($res['total_harga']); ?></td>
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
                <div class="text-center" style="margin-top: 20px;">
                    <a href="reservations.php" class="btn btn-outline">View All Reservations</a>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>You don't have any reservations yet.</p>
                    <a href="rooms.php" class="btn btn-primary">Browse Rooms</a>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
