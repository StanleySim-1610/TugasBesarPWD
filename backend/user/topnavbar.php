<?php
// Topnavbar Component - Include this in all user pages
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<style>
    :root {
        --chinese-red: #d32f2f;
        --chinese-gold: #f0b343;
        --chinese-dark: #8b0000;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
        background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
        min-height: 100vh;
    }
    
    /* Top Navbar */
    .top-navbar {
        background: linear-gradient(135deg, var(--chinese-red) 0%, var(--chinese-dark) 100%);
        color: white;
        padding: 0;
        box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
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
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-bottom-color: var(--chinese-gold);
    }
    
    .navbar-link.active {
        background: rgba(255, 255, 255, 0.15);
        border-bottom-color: var(--chinese-gold);
        color: white;
    }
    
    .navbar-icon {
        font-size: 1.2rem;
    }
    
    /* Chinese Pattern */
    .chinese-pattern {
        background-image: 
            repeating-linear-gradient(45deg, transparent, transparent 20px, rgba(240, 179, 67, 0.03) 20px, rgba(240, 179, 67, 0.03) 40px),
            repeating-linear-gradient(-45deg, transparent, transparent 20px, rgba(211, 47, 47, 0.03) 20px, rgba(211, 47, 47, 0.03) 40px);
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

<nav class="top-navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <img src="../../frontend/assets/logo.png?v=2" alt="Logo" class="navbar-logo">
            <span class="navbar-title">Hotel Management</span>
        </div>
        <ul class="navbar-menu">
            <li class="navbar-item">
                <a href="dashboard.php" class="navbar-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <span class="navbar-icon">🏠</span>
                    <span>Beranda</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="reservations.php" class="navbar-link <?php echo ($current_page == 'reservations') ? 'active' : ''; ?>">
                    <span class="navbar-icon">📅</span>
                    <span>Reservasi Saya</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="rooms.php" class="navbar-link <?php echo ($current_page == 'rooms') ? 'active' : ''; ?>">
                    <span class="navbar-icon">🏨</span>
                    <span>Lihat Kamar</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="fnb_new_order.php" class="navbar-link <?php echo ($current_page == 'fnb_new_order') ? 'active' : ''; ?>">
                    <span class="navbar-icon">🍽️</span>
                    <span>Pesan F&B</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="fnb_orders.php" class="navbar-link <?php echo ($current_page == 'fnb_orders') ? 'active' : ''; ?>">
                    <span class="navbar-icon">📋</span>
                    <span>Pesanan F&B</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="profile.php" class="navbar-link <?php echo ($current_page == 'profile') ? 'active' : ''; ?>">
                    <span class="navbar-icon">👤</span>
                    <span>Profil</span>
                </a>
            </li>
            <li class="navbar-item">
                <a href="../logout.php" class="navbar-link">
                    <span class="navbar-icon">🚪</span>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
