<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get available rooms
$rooms = $conn->query("SELECT * FROM kamar WHERE jumlah_tersedia > 0 ORDER BY harga ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Rooms</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .rooms-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        .showcase-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .showcase-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .showcase-image {
            height: 220px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }
        .showcase-content {
            padding: 25px;
        }
        .showcase-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .showcase-title {
            font-size: 22px;
            color: #333;
            font-weight: 700;
        }
        .showcase-badge {
            background: #4CAF50;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
        }
        .showcase-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .showcase-price {
            font-size: 28px;
            color: var(--brand-pink);
            font-weight: 700;
            margin-bottom: 20px;
        }
        .showcase-price span {
            font-size: 14px;
            color: #999;
            font-weight: 400;
        }
        .showcase-features {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .feature-tag {
            background: rgba(255,122,137,0.06);
            color: var(--brand-pink);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
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
                <a href="reservations.php" class="nav-item">
                    <span class="nav-icon">📅</span>
                    My Reservations
                </a>
                <a href="rooms.php" class="nav-item active">
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
                <h1>Browse Available Rooms</h1>
            </div>

            <section class="content-section">
                <h2>Our Premium Rooms</h2>
                <p style="color: #666; margin-bottom: 30px;">Choose the perfect room for your stay</p>
                
                <div class="rooms-showcase">
                    <?php while($room = $rooms->fetch_assoc()): ?>
                    <div class="showcase-card">
                        <div class="showcase-image">
                            🏨
                        </div>
                        <div class="showcase-content">
                            <div class="showcase-header">
                                <h3 class="showcase-title"><?php echo htmlspecialchars($room['tipe_kamar']); ?></h3>
                                <span class="showcase-badge"><?php echo $room['jumlah_tersedia']; ?> Available</span>
                            </div>
                            
                            <p class="showcase-description">
                                <?php echo htmlspecialchars($room['deskripsi']); ?>
                            </p>
                            
                            <div class="showcase-features">
                                <span class="feature-tag">🛏️ King Bed</span>
                                <span class="feature-tag">📶 Free WiFi</span>
                                <span class="feature-tag">❄️ AC</span>
                                <span class="feature-tag">📺 TV</span>
                            </div>
                            
                            <div class="showcase-price">
                                <?php echo formatRupiah($room['harga']); ?> <span>/night</span>
                            </div>
                            
                            <a href="booking.php?room=<?php echo $room['id_kamar']; ?>" class="btn btn-primary" style="width: 100%;">
                                Book This Room
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
