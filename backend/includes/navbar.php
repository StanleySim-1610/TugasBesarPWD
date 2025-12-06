<?php
/**
 * Lentera Nusantara Hotel - Topnavbar Component
 * Modern Luxury Theme - Rose Pink & Soft Yellow
 */

if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../frontend/assets/css/lentera-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        
        .main-content {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Professional Icons Styling */
        .topnavbar-menu a i {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <nav class="topnavbar">
        <div class="topnavbar-content">
            <div class="topnavbar-brand">
                <img src="../../frontend/assets/logo.png" alt="Lentera Nusantara Hotel" class="topnavbar-logo">
                <h2>LENTERA NUSANTARA</h2>
            </div>
            <div class="topnavbar-menu">
                <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="reservations.php" class="<?php echo ($current_page == 'reservations.php' || $current_page == 'fnb_orders.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Reservasi Saya
                </a>
                <a href="rooms.php" class="<?php echo ($current_page == 'rooms.php') ? 'active' : ''; ?>">
                    <i class="fas fa-bed"></i> Lihat Kamar
                </a>
                <a href="fnb_new_order.php" class="<?php echo ($current_page == 'fnb_new_order.php') ? 'active' : ''; ?>">
                    <i class="fas fa-concierge-bell"></i> Dining
                </a>
                <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i> Profil
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </div>
    </nav>
