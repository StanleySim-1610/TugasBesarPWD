<?php
/**
 * Lentera Nusantara Hotel - Topnavbar Component
 * Include this file in all user pages
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --navy-blue: #1a3a52;
            --gold: #d4af37;
            --light-gold: #f4e4c1;
            --cream: #faf7f2;
            --soft-blue: #e8f1f5;
            --dark-navy: #0f2537;
            --text-primary: #333;
            --text-secondary: #666;
            --success: #2e7d32;
            --warning: #f57c00;
            --error: #c62828;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: var(--cream);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .topnavbar {
            background: linear-gradient(180deg, #ff6b7d 0%, #fdff94 100%);
            box-shadow: 0 4px 20px rgba(255, 107, 125, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid #ff8a94;
        }

        .topnavbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
        }

        .topnavbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 0;
        }

        .topnavbar-logo {
            height: 60px;
            width: auto;
        }

        .topnavbar-brand h2 {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 0.5px;
        }

        .topnavbar-menu {
            display: flex;
            gap: 5px;
            list-style: none;
        }

        .topnavbar-menu a {
            color: white;
            text-decoration: none;
            padding: 25px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 1.05rem;
            border-bottom: 3px solid transparent;
        }

        .topnavbar-menu a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
        }

        .topnavbar-menu a.active {
            background: rgba(255, 255, 255, 0.3);
            border-bottom: 3px solid;
            border-image: linear-gradient(90deg, #ff6b7d, #fdff94) 1;
            color: white;
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: var(--navy-blue);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(26, 58, 82, 0.15);
            border: 1px solid var(--light-gold);
            margin-bottom: 25px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--gold);
            color: var(--dark-navy);
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-primary:hover {
            background: #c29d2e;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
        }

        .btn-outline {
            background: white;
            color: var(--navy-blue);
            border: 2px solid var(--navy-blue);
        }

        .btn-outline:hover {
            background: var(--navy-blue);
            color: white;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #e8f5e9;
            color: var(--success);
            border: 2px solid var(--success);
        }

        .alert-error {
            background: #ffebee;
            color: var(--error);
            border: 2px solid var(--error);
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
        }

        .status-pending {
            background: #fff3e0;
            color: var(--warning);
        }

        .status-confirmed,
        .status-paid {
            background: #e8f5e9;
            color: var(--success);
        }

        .status-cancelled {
            background: #ffebee;
            color: var(--error);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--navy-blue);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
    </style>
</head>
<body>
    <nav class="topnavbar">
        <div class="topnavbar-content">
            <div class="topnavbar-brand">
                <img src="../../frontend/assets/logo.png" alt="Lentera Nusantara" class="topnavbar-logo">
                <h2>Lentera Nusantara Hotel</h2>
            </div>
            <ul class="topnavbar-menu">
                <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><span>🏠</span> Beranda</a></li>
                <li><a href="reservations.php" class="<?php echo ($current_page == 'reservations.php') ? 'active' : ''; ?>"><span>📅</span> Reservasi Saya</a></li>
                <li><a href="rooms.php" class="<?php echo ($current_page == 'rooms.php') ? 'active' : ''; ?>"><span>🏨</span> Lihat Kamar</a></li>
                <li><a href="fnb_new_order.php" class="<?php echo ($current_page == 'fnb_new_order.php') ? 'active' : ''; ?>"><span>🍽️</span> Pesan F&B</a></li>
                <li><a href="fnb_orders.php" class="<?php echo ($current_page == 'fnb_orders.php') ? 'active' : ''; ?>"><span>📋</span> Pesanan F&B</a></li>
                <li><a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><span>👤</span> Profil</a></li>
                <li><a href="../logout.php"><span>🚪</span> Keluar</a></li>
            </ul>
        </div>
    </nav>
