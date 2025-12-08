<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
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
        background: linear-gradient(180deg, #ff6b7d 0%, #fdff94 100%);
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
        filter: brightness(0) invert(1);
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
        border-bottom-color: #fdff94;
    }
    
    .navbar-link.active {
        background: rgba(255, 255, 255, 0.2);
        border-bottom-color: #fdff94;
        color: white;
    }
    
    .navbar-icon {
        font-size: 1.2rem;
    }
    
    .chinese-pattern {
        background-image: 
            repeating-linear-gradient(45deg, transparent, transparent 20px, rgba(253, 255, 148, 0.05) 20px, rgba(253, 255, 148, 0.05) 40px),
            repeating-linear-gradient(-45deg, transparent, transparent 20px, rgba(255, 107, 125, 0.05) 20px, rgba(255, 107, 125, 0.05) 40px);
    }
    
    .main-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 30px;
    }
    
    @media (max-width: 768px) {
        .navbar-menu {
            display: none;
        }
        
        .navbar-title {
            font-size: 1.2rem;
        }
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<nav class="top-navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <img src="../../frontend/assets/logo.png?v=2" alt="Logo" class="navbar-logo">
            <span class="navbar-title">LENTERA NUSANTARA</span>
        </div>
        <ul class="navbar-menu">
            <li class="navbar-item">
                <a href="dashboard.php" class="navbar-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-home navbar-icon"></i>
                    <span>Beranda</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="reservations.php" class="navbar-link <?php echo ($current_page == 'reservations') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check navbar-icon"></i>
                    <span>Reservasi Saya</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="rooms.php" class="navbar-link <?php echo ($current_page == 'rooms') ? 'active' : ''; ?>">
                    <i class="fas fa-bed navbar-icon"></i>
                    <span>Lihat Kamar</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="fnb_new_order.php" class="navbar-link <?php echo ($current_page == 'fnb_new_order') ? 'active' : ''; ?>">
                    <i class="fas fa-concierge-bell navbar-icon"></i>
                    <span>Dining</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="profile.php" class="navbar-link <?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle navbar-icon"></i>
                    <span>Profil</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="../logout.php" class="navbar-link">
                    <i class="fas fa-sign-out-alt navbar-icon"></i>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
