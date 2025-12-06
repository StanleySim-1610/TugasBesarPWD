<?php
/**
 * Lentera Nusantara Hotel - Topnavbar Component
 * InterContinental-Inspired Luxury Design
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
    <style>
        /* Additional page-specific styles can be added here */
        body {
            margin: 0;
            padding: 0;
        }
        
        .main-content {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
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
                    <span>🏠</span> Home
                </a>
                <a href="rooms.php" class="<?php echo ($current_page == 'rooms.php') ? 'active' : ''; ?>">
                    <span>🏨</span> Rooms
                </a>
                <a href="reservations.php" class="<?php echo ($current_page == 'reservations.php') ? 'active' : ''; ?>">
                    <span>📋</span> Reservations
                </a>
                <a href="fnb_new_order.php" class="<?php echo (strpos($current_page, 'fnb') !== false) ? 'active' : ''; ?>">
                    <span>🍽️</span> Dining
                </a>
                <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                    <span>👤</span> Profile
                </a>
                <a href="../logout.php">
                    <span>🚪</span> Logout
                </a>
            </div>
        </div>
    </nav>
