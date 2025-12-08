<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

$rooms = $conn->query("SELECT * FROM kamar WHERE jumlah_tersedia > 0 ORDER BY harga ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Kamar - Lentera Nusantara</title>
    <link rel="stylesheet" href="../../frontend/assets/css/lentera-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-pink: #ff6b7d;
            --primary-yellow: #fdff94;
            --gradient-start: #ff6b7d;
            --gradient-end: #fdff94;
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
        
        .top-navbar {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
            padding: 0;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
        }
        
        .navbar-logo {
            height: 50px;
        }
        
        .navbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 5px;
            list-style: none;
        }
        
        .navbar-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 18px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            font-weight: 500;
        }
        
        .navbar-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
        }
        
        .navbar-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
            color: white;
        }
        
        .navbar-icon {
            font-size: 1.2rem;
        }
        
        .main-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }
        
        .page-header {
            background: linear-gradient(180deg, white 0%, #fffbf5 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
            text-align: center;
        }
        
        .page-title {
            font-size: 2.5rem;
            color: #ff6b7d;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(255, 107, 125, 0.1);
        }
        
        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .rooms-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .showcase-card {
            background: linear-gradient(to bottom, white, #fffbf5);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
        }
        
        .showcase-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(255, 107, 125, 0.3);
        }
        
        .showcase-image {
            height: 220px;
            width: 100%;
            padding: 0;
            background: #eee;
            position: relative;
            overflow: hidden;
        }
        
        .showcase-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.1) 100%);
        }
        
        .showcase-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .showcase-card:hover .showcase-image img {
            transform: scale(1.1);
        }
        
        .showcase-content {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .showcase-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .showcase-title {
            font-size: 1.5rem;
            color: #ff6b7d;
            font-weight: 700;
        }
        
        .showcase-badge {
            background: linear-gradient(180deg, #4CAF50, #2e7d32);
            color: white;
            padding: 6px 14px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        
        .showcase-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
            flex-grow: 1;
        }
        
        .showcase-price {
            font-size: 1.8rem;
            color: #ff6b7d;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .showcase-price span {
            font-size: 0.9rem;
            color: #999;
            font-weight: 400;
        }
        
        .showcase-features {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .feature-tag {
            background: linear-gradient(180deg, rgba(255, 107, 125, 0.1), rgba(253, 255, 148, 0.1));
            color: #ff6b7d;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(255, 107, 125, 0.2);
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 125, 0.4);
        }
        
        @media (max-width: 768px) {
            .navbar-menu {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="top-navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <img src="../../frontend/assets/logo.png?v=2" alt="Logo" class="navbar-logo">
                <span class="navbar-title">Lentera Nusantara Hotel</span>
            </div>
            <ul class="navbar-menu">
                <li class="navbar-item">
                    <a href="dashboard.php" class="navbar-link">
                        <i class="fas fa-home"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="reservations.php" class="navbar-link">
                        <i class="fas fa-calendar-check"></i>
                        <span>Reservasi Saya</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="rooms.php" class="navbar-link active">
                        <i class="fas fa-bed"></i>
                        <span>Lihat Kamar</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="fnb_new_order.php" class="navbar-link">
                        <i class="fas fa-concierge-bell"></i>
                        <span>Dining</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="profile.php" class="navbar-link">
                        <i class="fas fa-user-circle"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="../logout.php" class="navbar-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="page-header">
            <h1 class="page-title">🛕 Kamar Tersedia</h1>
            <p class="page-subtitle">Pilih kamar terbaik untuk menginap Anda</p>
        </div>
                
                <div class="rooms-showcase">
                    <?php while($room = $rooms->fetch_assoc()): 
                        $tipe = strtolower($room['tipe_kamar']);
                        
                        $gambar = 'standard_room.jpg'; 
                        
                        if (strpos($tipe, 'presidential') !== false) {
                            $gambar = 'presidential_suite.png'; 
                        } elseif (strpos($tipe, 'suite') !== false) {
                            $gambar = 'suite_room.jpg';
                        } elseif (strpos($tipe, 'deluxe') !== false) {
                            $gambar = 'deluxe_room.jpg';
                        }
                    ?>
                    <div class="showcase-card">
                        <div class="showcase-image">
                            <img src="../../frontend/assets/room_photo/<?php echo $gambar; ?>" alt="<?php echo htmlspecialchars($room['tipe_kamar']); ?>">
                        </div>
                        <div class="showcase-content">
                            <div class="showcase-header">
                                <h3 class="showcase-title"><?php echo htmlspecialchars($room['tipe_kamar']); ?></h3>
                                <span class="showcase-badge"><?php echo $room['jumlah_tersedia']; ?> Tersedia</span>
                            </div>
                            
                            <p class="showcase-description">
                                <?php echo htmlspecialchars($room['deskripsi']); ?>
                            </p>
                            
                            <div class="showcase-features">
                                <span class="feature-tag">🛏️ King Bed</span>
                                <span class="feature-tag">📶 WiFi Gratis</span>
                                <span class="feature-tag">❄️ AC</span>
                                <span class="feature-tag">📺 TV</span>
                            </div>
                            
                            <div class="showcase-price">
                                <?php echo formatRupiah($room['harga']); ?> <span>/malam</span>
                            </div>
                            
                            <a href="booking.php?room=<?php echo $room['id_kamar']; ?>" class="btn btn-primary" style="width: 100%;">
                                Pesan Kamar Ini
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
        </div>
</body>
</html>