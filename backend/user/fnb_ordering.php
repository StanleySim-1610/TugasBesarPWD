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

// Get user's active reservations
$stmt = $conn->prepare("
    SELECT r.id_reservation, r.check_in, r.check_out, k.tipe_kamar
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    WHERE r.id_user = ? AND r.status = 'confirmed'
    ORDER BY r.check_in DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();

// Get F&B menu items
$menu_items = $conn->query("
    SELECT * FROM fnb_menu 
    WHERE ketersediaan = 1 
    ORDER BY kategori, nama_item ASC
");

// Get user's F&B orders
$user_orders = $conn->query("
    SELECT fo.*, r.check_in, r.check_out, k.tipe_kamar
    FROM fnb_order fo
    JOIN reservation r ON fo.id_reservation = r.id_reservation
    JOIN kamar k ON r.id_kamar = k.id_kamar
    WHERE r.id_user = $user_id
    ORDER BY fo.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F&B Ordering - User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
    <style>
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab-btn {
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 600;
            color: #999;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .tab-btn.active {
            color: var(--brand-pink);
            border-bottom-color: var(--brand-pink);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .category-section {
            margin-bottom: 35px;
        }
        .category-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--brand-pink);
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 18px;
        }
        .menu-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            cursor: pointer;
        }
        .menu-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
            transform: translateY(-3px);
            border-color: var(--brand-pink);
        }
        .menu-name {
            font-size: 1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        .menu-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            flex: 1;
            line-height: 1.4;
        }
        .menu-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--brand-pink);
            margin-bottom: 12px;
        }
        .menu-btn {
            background: var(--brand-pink);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .menu-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 12px rgba(255,127,153,0.3);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        .close-modal:hover {
            color: #333;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--brand-pink);
            box-shadow: 0 0 0 3px rgba(255,127,153,0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 70px;
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .qty-btn:hover {
            border-color: var(--brand-pink);
            color: var(--brand-pink);
        }
        .qty-input {
            width: 50px;
            text-align: center;
        }
        .price-summary {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .summary-row.total {
            border-top: 2px solid #e0e0e0;
            padding-top: 10px;
            font-weight: 700;
            color: var(--brand-pink);
            font-size: 15px;
        }
        .form-actions {
            display: flex;
            gap: 12px;
        }
        .form-actions button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        .btn-order {
            background: var(--brand-pink);
            color: white;
        }
        .btn-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255,127,153,0.3);
        }
        .btn-close {
            background: #e0e0e0;
            color: #333;
        }
        .btn-close:hover {
            background: #d0d0d0;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .orders-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid #e0e0e0;
        }
        .orders-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        .orders-table tr:hover {
            background: #f9f9f9;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        .empty-state p {
            margin: 0 0 20px 0;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../frontend/assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>User Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home nav-icon"></i>
                    Beranda
                </a>
                <a href="reservations.php" class="nav-item">
                    <i class="fas fa-calendar-check nav-icon"></i>
                    Reservasi Saya
                </a>
                <a href="rooms.php" class="nav-item">
                    <i class="fas fa-bed nav-icon"></i>
                    Lihat Kamar
                </a>
                <a href="fnb_ordering.php" class="nav-item active">
                    <i class="fas fa-concierge-bell nav-icon"></i>
                    Dining
                </a>
                <a href="profile.php" class="nav-item">
                    <i class="fas fa-user-circle nav-icon"></i>
                    Profil
                </a>
                <a href="../logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    Keluar
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>F&B Ordering</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">✓ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">✗ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('menu')">📋 Menu</button>
                <button class="tab-btn" onclick="switchTab('orders')">📦 My Orders</button>
            </div>

            <!-- Menu Tab -->
            <div class="tab-content active" id="menu">
                <section class="content-section">
                    <?php if ($reservations->num_rows > 0): ?>
                        <?php 
                        $categories = [];
                        $menu_items->data_seek(0);
                        while ($item = $menu_items->fetch_assoc()) {
                            $cat = $item['kategori'] ?: 'Other';
                            if (!isset($categories[$cat])) {
                                $categories[$cat] = [];
                            }
                            $categories[$cat][] = $item;
                        }
                        ?>
                        
                        <?php foreach ($categories as $category => $items): ?>
                            <div class="category-section">
                                <div class="category-title"><?php echo htmlspecialchars($category); ?></div>
                                <div class="menu-grid">
                                    <?php foreach ($items as $item): ?>
                                        <div class="menu-card" onclick="openOrderModal(<?php echo $item['id_fnb_menu']; ?>, '<?php echo htmlspecialchars($item['nama_item']); ?>', <?php echo $item['harga']; ?>)">
                                            <div class="menu-name"><?php echo htmlspecialchars($item['nama_item']); ?></div>
                                            <div class="menu-desc"><?php echo htmlspecialchars($item['deskripsi']); ?></div>
                                            <div class="menu-price"><?php echo formatRupiah($item['harga']); ?></div>
                                            <button type="button" class="menu-btn">Pesan</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Anda harus memiliki reservasi yang aktif untuk memesan F&B.</p>
                            <a href="reservations.php" class="btn btn-primary">Lihat Reservasi Saya</a>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Orders Tab -->
            <div class="tab-content" id="orders">
                <section class="content-section">
                    <?php if ($user_orders->num_rows > 0): ?>
                        <div class="table-container">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $user_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['item']); ?></td>
                                            <td><?php echo $order['qty']; ?></td>
                                            <td><?php echo formatRupiah($order['harga']); ?></td>
                                            <td><strong><?php echo formatRupiah($order['harga'] * $order['qty']); ?></strong></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Anda belum membuat pesanan F&B.</p>
                            <button type="button" class="btn btn-primary" onclick="switchTab('menu')">Lihat Menu</button>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <!-- Order Modal -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="itemTitle"></h2>
                <button type="button" class="close-modal" onclick="closeOrderModal()">&times;</button>
            </div>

            <form method="POST" action="submit_fnb_order.php">
                <input type="hidden" id="id_fnb_menu" name="id_fnb_menu" value="">
                <input type="hidden" id="item_price" name="item_price" value="">

                <div class="form-group">
                    <label for="id_reservation">Pilih Reservasi *</label>
                    <select id="id_reservation" name="id_reservation" required>
                        <option value="">-- Pilih Reservasi --</option>
                        <?php 
                        $reservations->data_seek(0);
                        while ($res = $reservations->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $res['id_reservation']; ?>">
                                <?php echo htmlspecialchars($res['tipe_kamar']); ?> 
                                (<?php echo date('d M', strtotime($res['check_in'])); ?> - <?php echo date('d M', strtotime($res['check_out'])); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jumlah</label>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="decreaseQty()">−</button>
                        <input type="number" id="qty" name="qty" class="qty-input" value="1" min="1" max="100">
                        <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                    </div>
                </div>

                <div class="price-summary">
                    <div class="summary-row">
                        <span>Harga per item:</span>
                        <span id="priceDisplay">Rp 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Jumlah:</span>
                        <span id="qtyDisplay">1</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="totalDisplay">Rp 0</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan (Opsional)</label>
                    <textarea id="catatan" name="catatan" placeholder="Contoh: tanpa bawang, extra saus..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-order">Pesan Sekarang</button>
                    <button type="button" class="btn-close" onclick="closeOrderModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            document.getElementById(tab).classList.add('active');
            event.target.classList.add('active');
        }

        function openOrderModal(id, name, price) {
            document.getElementById('itemTitle').textContent = name;
            document.getElementById('id_fnb_menu').value = id;
            document.getElementById('item_price').value = price;
            document.getElementById('qty').value = 1;
            updateDisplay(price);
            document.getElementById('orderModal').classList.add('show');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('show');
        }

        function increaseQty() {
            const qty = document.getElementById('qty');
            qty.value = parseInt(qty.value) + 1;
            updateDisplay(document.getElementById('item_price').value);
        }

        function decreaseQty() {
            const qty = document.getElementById('qty');
            if (parseInt(qty.value) > 1) {
                qty.value = parseInt(qty.value) - 1;
            }
            updateDisplay(document.getElementById('item_price').value);
        }

        function updateDisplay(price) {
            const qty = parseInt(document.getElementById('qty').value);
            const total = price * qty;
            
            document.getElementById('priceDisplay').textContent = 'Rp ' + price.toLocaleString('id-ID');
            document.getElementById('qtyDisplay').textContent = qty;
            document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        document.getElementById('qty').addEventListener('change', function() {
            updateDisplay(document.getElementById('item_price').value);
        });

        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    </script>
</body>
</html>
