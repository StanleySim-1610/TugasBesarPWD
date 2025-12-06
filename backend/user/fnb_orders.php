<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's F&B orders grouped by reservation
$fnb_orders = $conn->query("
    SELECT fo.*, r.id_reservation, r.check_in, r.check_out, k.tipe_kamar,
           (fo.qty * fo.harga) as subtotal
    FROM fnb_order fo
    JOIN reservation r ON fo.id_reservation = r.id_reservation
    JOIN kamar k ON r.id_kamar = k.id_kamar
    WHERE r.id_user = $user_id
    ORDER BY r.id_reservation DESC, fo.created_at DESC
");

// Group orders by reservation
$orders_by_reservation = [];
while ($order = $fnb_orders->fetch_assoc()) {
    $res_id = $order['id_reservation'];
    if (!isset($orders_by_reservation[$res_id])) {
        $orders_by_reservation[$res_id] = [
            'reservation' => [
                'id' => $res_id,
                'tipe_kamar' => $order['tipe_kamar'],
                'check_in' => $order['check_in'],
                'check_out' => $order['check_out']
            ],
            'orders' => [],
            'total' => 0,
            'all_pending' => true
        ];
    }
    $orders_by_reservation[$res_id]['orders'][] = $order;
    $orders_by_reservation[$res_id]['total'] += $order['subtotal'];
    if ($order['status'] != 'pending') {
        $orders_by_reservation[$res_id]['all_pending'] = false;
    }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id_fnb = intval($_GET['id']);
    
    // Verify order belongs to user
    $stmt = $conn->prepare("
        SELECT fo.id_fnb 
        FROM fnb_order fo
        JOIN reservation r ON fo.id_reservation = r.id_reservation
        WHERE fo.id_fnb = ? AND r.id_user = ? AND fo.status = 'pending'
    ");
    $stmt->bind_param("ii", $id_fnb, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $conn->query("DELETE FROM fnb_order WHERE id_fnb = $id_fnb");
        $_SESSION['success'] = 'Pesanan berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Pesanan tidak dapat dihapus!';
    }
    
    header('Location: fnb_orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan F&B - Lentera Nusantara Hotel</title>
    <link rel="stylesheet" href="../../frontend/assets/css/lentera-theme.css">
    <style>
        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: var(--cream);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .reservation-group {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(26, 58, 82, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
            border: 2px solid var(--light-gold);
        }
        
        .reservation-header {
            background: linear-gradient(135deg, var(--navy-blue) 0%, #2c5f8d 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .reservation-info h3 {
            font-size: 1.3rem;
            margin-bottom: 8px;
            color: var(--gold);
        }
        
        .reservation-meta {
            display: flex;
            gap: 20px;
            font-size: 0.95rem;
            opacity: 0.95;
        }
        
        .reservation-total {
            text-align: right;
        }
        
        .reservation-total-label {
            font-size: 0.9rem;
            opacity: 0.9;
            display: block;
            margin-bottom: 5px;
        }
        
        .reservation-total-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gold);
        }
        
        .orders-list {
            padding: 25px 30px;
        }
        
        .order-item {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-info h4 {
            color: var(--navy-blue);
            font-size: 1.15rem;
            margin-bottom: 8px;
        }
        
        .order-item-meta {
            display: flex;
            gap: 15px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-item-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .order-item-status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-top: 8px;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe5a1 100%);
            color: #856404;
        }
        
        .status-confirmed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .status-delivered {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }
        
        .status-cancelled {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }
        
        .order-item-price {
            text-align: right;
        }
        
        .order-item-price .price {
            color: var(--navy-blue);
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .order-item-price .qty {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .order-item-actions {
            margin-top: 10px;
        }
        
        .reservation-footer {
            padding: 20px 30px;
            background: var(--soft-blue);
            display: flex;
            justify-content: flex-end;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(26, 58, 82, 0.1);
        }
        
        .action-bar h2 {
            color: var(--navy-blue);
            font-size: 1.8rem;
            margin: 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(26, 58, 82, 0.1);
        }
        
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .empty-state h3 {
            color: var(--navy-blue);
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php 
    $current_page = 'fnb_orders.php';
    require_once '../includes/navbar.php'; 
    ?>
    
    <div class="container">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="action-bar">
            <h2>Daftar Pesanan F&B</h2>
            <a href="fnb_new_order.php" class="btn btn-primary">
                <span>➕</span>
                <span>Pesan Baru</span>
            </a>
        </div>
        
        <?php if (!empty($orders_by_reservation)): ?>
            <?php foreach ($orders_by_reservation as $res_id => $group): ?>
                <div class="reservation-group">
                    <div class="reservation-header">
                        <div class="reservation-info">
                            <h3>🏨 Booking #<?php echo $res_id; ?> - <?php echo htmlspecialchars($group['reservation']['tipe_kamar']); ?></h3>
                            <div class="reservation-meta">
                                <span>📅 Check-in: <?php echo date('d M Y', strtotime($group['reservation']['check_in'])); ?></span>
                                <span>📅 Check-out: <?php echo date('d M Y', strtotime($group['reservation']['check_out'])); ?></span>
                            </div>
                        </div>
                        <div class="reservation-total">
                            <span class="reservation-total-label">Total Pesanan</span>
                            <div class="reservation-total-amount">Rp <?php echo number_format($group['total'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                    
                    <div class="orders-list">
                        <?php foreach ($group['orders'] as $order): ?>
                            <div class="order-item">
                                <div class="order-item-info">
                                    <h4>🍽️ <?php echo htmlspecialchars($order['item']); ?></h4>
                                    <div class="order-item-meta">
                                        <span>📦 Jumlah: <?php echo $order['qty']; ?> porsi</span>
                                        <span>💵 @ Rp <?php echo number_format($order['harga'], 0, ',', '.'); ?></span>
                                        <span>🕐 <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-item-status status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $status_indo = [
                                            'pending' => '⏳ Menunggu Pembayaran',
                                            'confirmed' => '✓ Lunas',
                                            'delivered' => '✓ Dikirim',
                                            'cancelled' => '✗ Dibatalkan'
                                        ];
                                        echo $status_indo[$order['status']] ?? $order['status'];
                                        ?>
                                    </div>
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <div class="order-item-actions">
                                            <a href="?delete=1&id=<?php echo $order['id_fnb']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan <?php echo htmlspecialchars($order['item']); ?>?')">
                                                🗑️ Hapus
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="order-item-price">
                                    <div class="price">Rp <?php echo number_format($order['subtotal'], 0, ',', '.'); ?></div>
                                    <div class="qty"><?php echo $order['qty']; ?>x</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($group['all_pending']): ?>
                        <div class="reservation-footer">
                            <form method="POST" action="fnb_payment.php" style="display: inline;">
                                <?php foreach ($group['orders'] as $order): ?>
                                    <input type="hidden" name="order_ids[]" value="<?php echo $order['id_fnb']; ?>">
                                <?php endforeach; ?>
                                <button type="submit" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">
                                    💳 Bayar Semua Pesanan (Rp <?php echo number_format($group['total'], 0, ',', '.'); ?>)
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🍽️</div>
                <h3>Belum Ada Pesanan F&B</h3>
                <p>Anda belum membuat pesanan makanan atau minuman. Mulai pesan sekarang!</p>
                <a href="fnb_new_order.php" class="btn btn-primary">
                    <span>🍜</span>
                    <span>Lihat Menu</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
