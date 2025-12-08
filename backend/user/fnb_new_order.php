<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get pre-selected reservation from URL parameter
$preselected_reservation = isset($_GET['reservation']) ? intval($_GET['reservation']) : 0;

// Get paid reservations for this user
$paid_reservations = $conn->query("
    SELECT r.*, k.tipe_kamar, p.status as payment_status
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    WHERE r.id_user = $user_id 
    AND p.status = 'paid'
    AND r.check_out >= CURDATE()
    ORDER BY r.check_in ASC
");

$error = '';
$success = '';

// Process F&B order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_order'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $orders = json_decode($_POST['orders'], true);
    
    // Validate reservation belongs to user and is paid
    $stmt = $conn->prepare("
        SELECT r.id_reservation 
        FROM reservation r
        JOIN payment_reservation p ON r.id_reservation = p.id_reservation
        WHERE r.id_reservation = ? 
        AND r.id_user = ? 
        AND p.status = 'paid'
    ");
    $stmt->bind_param("ii", $reservation_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['error'] = 'Reservasi tidak valid atau belum dibayar!';
    } elseif (empty($orders)) {
        $_SESSION['error'] = 'Pilih minimal 1 item untuk dipesan!';
    } else {
        // Insert orders
        $conn->begin_transaction();
        
        try {
            foreach ($orders as $order) {
                $item = $order['item'];
                $qty = intval($order['qty']);
                $harga = floatval($order['harga']);
                
                $stmt = $conn->prepare("INSERT INTO fnb_order (id_reservation, item, qty, harga, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->bind_param("isid", $reservation_id, $item, $qty, $harga);
                
                if (!$stmt->execute()) {
                    throw new Exception("Database error: " . $stmt->error);
                }
            }
            
            $conn->commit();
            $_SESSION['success'] = 'Pesanan F&B berhasil dibuat! Silakan lakukan pembayaran.';
            
            // Redirect to F&B orders list
            header("Location: fnb_orders.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Makanan & Minuman - Lentera Nusantara</title>
    <link rel="stylesheet" href="../../frontend/assets/css/lentera-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-pink: #ff6b7d;
            --primary-yellow: #fdff94;
            --gradient-mid: #ff8a94;
            --bg-light: #fff9f0;
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
        
        /* Top Navbar */
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
        
        .navbar-item {
            position: relative;
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
        
        /* Main Content */
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
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 5px solid #c62828;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 5px solid #2e7d32;
        }
        
        /* Reservation Selector */
        .reservation-selector {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 35px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
        }
        
        .section-title {
            color: #ff6b7d;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .reservation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .reservation-card {
            border: 3px solid #e0e0e0;
            padding: 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(to bottom, white, #fffbf5);
            position: relative;
        }
        
        .reservation-id-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff6b7d, #fdff94);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(255, 107, 125, 0.3);
        }
        
        .reservation-card:hover {
            border-color: #fdff94;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(253, 255, 148, 0.3);
        }
        
        .reservation-card.selected {
            border-color: #ff6b7d;
            background: #fff3f3;
            box-shadow: 0 8px 25px rgba(255, 107, 125, 0.3);
        }
        
        .reservation-card h4 {
            color: #ff6b7d;
            margin-bottom: 12px;
            font-size: 1.2rem;
        }
        
        .reservation-info {
            color: #666;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .badge-paid {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 10px;
        }
        
        /* Menu Section */
        .menu-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
            display: none;
        }
        
        .menu-section.active {
            display: block;
        }
        
        .menu-category {
            margin-bottom: 40px;
        }
        
        .category-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            
            margin-bottom: 25px;
        }
        
        .category-title {
            color: #ff6b7d;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .menu-item {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 0;
            transition: all 0.3s ease;
            background: linear-gradient(to bottom, #ffffff, #fef9f0);
            position: relative;
            overflow: hidden;
        }
        
        .menu-image {
            width: 100%;
            height: 180px;
            background-size: cover;
            background-position: center;
            border-radius: 12px 12px 0 0;
        }
        
        .menu-item > h4,
        .menu-item > .menu-description,
        .menu-item > .menu-price,
        .menu-item > .quantity-control {
            padding: 0 20px;
        }
        
        .menu-item h4 {
            color: #333;
            margin: 15px 0 10px;
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(255, 107, 125, 0.2);
            border-color: #fdff94;
        }
        
        .menu-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
            position: relative;
            z-index: 1;
            min-height: 40px;
        }
        }
        
        .menu-price {
            color: #ff6b7d;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .quantity-control button {
            width: 40px;
            height: 40px;
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
            background: white;
            color: #ff6b7d;
            border-radius: 8px;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        
        .quantity-control button:hover {
            background: #fdff94;
            color: white;
            transform: scale(1.1);
        }
        
        .quantity-control input {
            width: 70px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
            border-radius: 8px;
            padding: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #ff6b7d;
        }
        
        /* Cart Summary */
        .cart-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
            padding: 25px 30px;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.3);
            display: none;
            z-index: 999;
        }
        
        .cart-summary.active {
            display: block;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }
        
        .cart-summary-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .cart-info {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .cart-count {
            background: white;
            color: #ff6b7d;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .cart-total {
            font-size: 1.6rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .btn-submit-order {
            background: white;
            color: #ff6b7d;
            border: none;
            padding: 15px 45px;
            border-radius: 30px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-submit-order:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            background: #fdff94;
            color: white;
        }
        
        .no-reservations {
            text-align: center;
            padding: 60px 20px;
        }
        
        .no-reservations-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .no-reservations h3 {
            color: #666;
            margin-bottom: 15px;
        }
        
        .no-reservations p {
            color: #999;
            margin-bottom: 25px;
        }
        
        .btn-primary {
            display: inline-block;
            background: #ff6b7d;
            color: white;
            padding: 15px 35px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #ff8a94;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 125, 0.3);
        }
        
        @media (max-width: 768px) {
            .navbar-menu {
                display: none;
            }
            
            .cart-summary-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
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
                    <a href="rooms.php" class="navbar-link">
                        <i class="fas fa-bed"></i>
                        <span>Lihat Kamar</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="fnb_new_order.php" class="navbar-link active">
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

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="page-header">
            <h1 class="page-title">🍽️ Pemesanan Makanan & Minuman</h1>
            <p class="page-subtitle">Pesan makanan dan minuman untuk reservasi kamar Anda</p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Step 1: Select Reservation -->
        <div class="reservation-selector">
            <h2 class="section-title">
                <span>1️⃣</span>
                <span>Pilih Reservasi Kamar Anda</span>
            </h2>
            <?php if ($paid_reservations->num_rows > 0): ?>
                <div class="reservation-grid">
                    <?php while ($res = $paid_reservations->fetch_assoc()): ?>
                        <div class="reservation-card" data-reservation-id="<?php echo $res['id_reservation']; ?>">
                            <div class="reservation-id-badge">ID #<?php echo $res['id_reservation']; ?></div>
                            <h4><?php echo htmlspecialchars($res['tipe_kamar']); ?></h4>
                            <div class="reservation-info">
                                <span>📅</span>
                                <span>Check-in: <?php echo date('d/m/Y', strtotime($res['check_in'])); ?></span>
                            </div>
                            <div class="reservation-info">
                                <span>📅</span>
                                <span>Check-out: <?php echo date('d/m/Y', strtotime($res['check_out'])); ?></span>
                            </div>
                            <div class="reservation-info">
                                <span>👥</span>
                                <span><?php echo $res['jumlah_orang']; ?> Orang</span>
                            </div>
                            <span class="badge-paid">✓ Sudah Dibayar</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-reservations">
                    <div class="no-reservations-icon">🏨</div>
                    <h3>Belum Ada Reservasi</h3>
                    <p>Anda belum memiliki reservasi yang sudah dibayar. Silakan lakukan reservasi kamar terlebih dahulu untuk dapat memesan F&B.</p>
                    <a href="rooms.php" class="btn-primary">Lihat Kamar Tersedia</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Step 2: Select Menu -->
        <?php if ($paid_reservations->num_rows > 0): ?>
        <div class="menu-section" id="menuSection">
            <h2 class="section-title">
                <span>2️⃣</span>
                <span>Pilih Menu Makanan & Minuman</span>
            </h2>
            <div id="menuContainer"></div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Cart Summary -->
    <div class="cart-summary" id="cartSummary">
        <div class="cart-summary-content">
            <div class="cart-info">
                <span class="cart-count"><span id="cartItemCount">0</span> item</span>
            </div>
            <div class="cart-total">
                Total: Rp <span id="cartTotal">0</span>
            </div>
            <button class="btn-submit-order" onclick="submitOrder()">🛒 Buat Pesanan</button>
        </div>
    </div>

    <script>
        let selectedReservation = null;
        let menuData = [];
        let cart = {};
        
        // Pre-select reservation if provided in URL
        const urlParams = new URLSearchParams(window.location.search);
        const preselectedReservation = urlParams.get('reservation');
        
        // Load menu data
        fetch('../../backend/api/fnb_menu.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    menuData = data.data;
                    renderMenu();
                }
            })
            .catch(error => console.error('Error loading menu:', error));
        
        // Reservation selection
        document.querySelectorAll('.reservation-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.reservation-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                selectedReservation = this.dataset.reservationId;
                document.getElementById('menuSection').classList.add('active');
                document.getElementById('menuSection').scrollIntoView({ behavior: 'smooth' });
            });
            
            // Auto-select if matches preselected reservation
            if (preselectedReservation && card.dataset.reservationId === preselectedReservation) {
                setTimeout(() => {
                    card.click();
                }, 500);
            }
        });
        
        function renderMenu() {
            const categories = [...new Set(menuData.map(item => item.kategori))];
            const container = document.getElementById('menuContainer');
            container.innerHTML = '';
            
            const categoryIcons = {
                'Makanan': '🍜',
                'Minuman': '🥤',
                'Snack': '🍟'
            };
            
            categories.forEach(category => {
                const categorySection = document.createElement('div');
                categorySection.className = 'menu-category';
                
                const categoryHeader = document.createElement('div');
                categoryHeader.className = 'category-header';
                categoryHeader.innerHTML = `
                    <span style="font-size: 2rem;">${categoryIcons[category] || '🍽️'}</span>
                    <h3 class="category-title">${category}</h3>
                `;
                categorySection.appendChild(categoryHeader);
                
                const menuGrid = document.createElement('div');
                menuGrid.className = 'menu-grid';
                
                const items = menuData.filter(item => item.kategori === category);
                items.forEach(item => {
                    const menuItem = document.createElement('div');
                    menuItem.className = 'menu-item';
                    menuItem.innerHTML = `
                        <div class="menu-image" style="background-image: url('../../frontend/assets/fnb_menu/${item.foto}');"></div>
                        <h4>${item.nama}</h4>
                        <p class="menu-description">${item.deskripsi}</p>
                        <div class="menu-price">Rp ${item.harga.toLocaleString('id-ID')}</div>
                        <div class="quantity-control">
                            <button onclick="updateQuantity(${item.id}, -1)" type="button">−</button>
                            <input type="number" id="qty-${item.id}" value="0" min="0" readonly>
                            <button onclick="updateQuantity(${item.id}, 1)" type="button">+</button>
                        </div>
                    `;
                    menuGrid.appendChild(menuItem);
                });
                
                categorySection.appendChild(menuGrid);
                container.appendChild(categorySection);
            });
        }
        
        function updateQuantity(itemId, change) {
            const input = document.getElementById('qty-' + itemId);
            let qty = parseInt(input.value) + change;
            if (qty < 0) qty = 0;
            input.value = qty;
            
            const item = menuData.find(i => i.id === itemId);
            if (qty > 0) {
                cart[itemId] = {
                    item: item.nama,
                    qty: qty,
                    harga: item.harga
                };
            } else {
                delete cart[itemId];
            }
            
            updateCart();
        }
        
        function updateCart() {
            const itemCount = Object.keys(cart).length;
            const total = Object.values(cart).reduce((sum, item) => sum + (item.qty * item.harga), 0);
            
            document.getElementById('cartItemCount').textContent = itemCount;
            document.getElementById('cartTotal').textContent = total.toLocaleString('id-ID');
            
            if (itemCount > 0) {
                document.getElementById('cartSummary').classList.add('active');
            } else {
                document.getElementById('cartSummary').classList.remove('active');
            }
        }
        
        function submitOrder() {
            if (!selectedReservation) {
                alert('❌ Silakan pilih reservasi terlebih dahulu!');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            
            if (Object.keys(cart).length === 0) {
                alert('❌ Silakan pilih minimal 1 item untuk dipesan!');
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="reservation_id" value="${selectedReservation}">
                <input type="hidden" name="orders" value='${JSON.stringify(Object.values(cart))}'>
                <input type="hidden" name="submit_order" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
