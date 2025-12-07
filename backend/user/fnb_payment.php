<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get multiple order IDs from POST or single ID from GET
$order_ids = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_ids'])) {
    $order_ids = array_map('intval', $_POST['order_ids']);
} elseif (isset($_GET['id'])) {
    $order_ids = [intval($_GET['id'])];
}

if (empty($order_ids)) {
    $_SESSION['error'] = 'Tidak ada pesanan yang dipilih!';
    header('Location: fnb_orders.php');
    exit();
}

// Get all selected orders grouped by reservation
$ids_string = implode(',', $order_ids);
$query = "
    SELECT fo.*, r.id_reservation, r.check_in, r.check_out, k.tipe_kamar,
           (fo.qty * fo.harga) as subtotal
    FROM fnb_order fo
    JOIN reservation r ON fo.id_reservation = r.id_reservation
    JOIN kamar k ON r.id_kamar = k.id_kamar
    WHERE fo.id_fnb IN ($ids_string) AND r.id_user = $user_id AND fo.status = 'pending'
    ORDER BY r.id_reservation, fo.created_at
";
$result = $conn->query($query);

$orders_by_reservation = [];
$total_bayar = 0;

while ($row = $result->fetch_assoc()) {
    $res_id = $row['id_reservation'];
    if (!isset($orders_by_reservation[$res_id])) {
        $orders_by_reservation[$res_id] = [
            'reservation' => [
                'id' => $res_id,
                'tipe_kamar' => $row['tipe_kamar'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out']
            ],
            'orders' => [],
            'total' => 0
        ];
    }
    $orders_by_reservation[$res_id]['orders'][] = $row;
    $orders_by_reservation[$res_id]['total'] += $row['subtotal'];
    $total_bayar += $row['subtotal'];
}

if (empty($orders_by_reservation)) {
    $_SESSION['error'] = 'Pesanan tidak valid atau sudah diproses!';
    header('Location: fnb_orders.php');
    exit();
}

// Process payment when metode is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['metode'])) {
    $metode = sanitize($_POST['metode']);
    
    if (empty($metode)) {
        $error = 'Pilih metode pembayaran!';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert payment records for each order
            $stmt = $conn->prepare("INSERT INTO payment_fnb (id_fnb, total_bayar, metode, status, paid_at) VALUES (?, ?, ?, 'paid', NOW())");
            
            foreach ($orders_by_reservation as $group) {
                foreach ($group['orders'] as $order) {
                    $stmt->bind_param("ids", $order['id_fnb'], $order['subtotal'], $metode);
                    $stmt->execute();
                    
                    // Update order status
                    $conn->query("UPDATE fnb_order SET status = 'confirmed' WHERE id_fnb = {$order['id_fnb']}");
                }
            }
            
            $conn->commit();
            
            $total_items = count($order_ids);
            $_SESSION['success'] = 'Pembayaran berhasil! ' . $total_items . ' pesanan Anda sedang diproses.';
            header('Location: fnb_orders.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran F&B - Lentera Nusantara Hotel</title>
    <link rel="stylesheet" href="../../frontend/assets/css/lentera-theme.css">
    <style>
        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: var(--cream);
            min-height: 100vh;
            padding: 20px;
        }
        
        .main-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .page-title {
            font-size: 2.2rem;
            color: var(--rose-pink);
            text-align: center;
            margin-bottom: 35px;
        }
        
        .payment-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(255, 107, 125, 0.15);
            border: 2px solid #ffb3c1;
        }
        
        .reservation-section {
            background: #fff0f2;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid var(--rose-pink);
        }
        
        .reservation-header {
            font-size: 1.2rem;
            color: var(--rose-pink);
            margin-bottom: 12px;
            font-weight: 700;
        }
        
        .reservation-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .order-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .order-item-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-item-price {
            text-align: right;
        }
        
        .order-item-price .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--rose-pink);
        }
        
        .order-item-price .qty {
            color: #666;
            font-size: 0.85rem;
        }
        
        .subtotal-row {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: linear-gradient(135deg, #fff0f2 0%, #ffe4e7 100%);
            border-radius: 8px;
            margin-top: 10px;
            font-weight: 700;
            color: var(--rose-pink);
        }
        
        .payment-summary {
            background: linear-gradient(135deg, var(--rose-pink) 0%, #ff8a94 100%);
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            color: white;
        }
        
        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--soft-yellow);
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            margin-top: 15px;
        }
        
        .summary-total-label {
            font-size: 1.2rem;
        }
        
        .summary-total-amount {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--soft-yellow);
        }
        
        .payment-methods {
            margin-top: 30px;
        }
        
        .payment-methods-title {
            font-size: 1.3rem;
            color: var(--rose-pink);
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .payment-method-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .payment-method-option {
            position: relative;
        }
        
        .payment-method-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .payment-method-label {
            display: block;
            padding: 20px;
            background: #fff0f2;
            border: 2px solid transparent;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            color: #333;
        }
        
        .payment-method-option input[type="radio"]:checked + .payment-method-label {
            background: var(--soft-yellow);
            border-color: var(--rose-pink);
            color: var(--rose-pink);
            transform: scale(1.05);
        }
        
        .payment-method-label:hover {
            border-color: var(--rose-pink);
        }
        
        .payment-icon {
            font-size: 2rem;
            display: block;
            margin-bottom: 8px;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-back {
            padding: 15px 30px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-submit {
            padding: 15px 40px;
            background: linear-gradient(135deg, var(--rose-pink) 0%, #ff8a94 50%, var(--soft-yellow) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.4);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.6);
        }
    </style>
</head>
<body>
    <?php 
    $current_page = 'fnb_orders.php';
    require_once '../includes/navbar.php'; 
    ?>
    
    <div class="main-wrapper">
        <h1 class="page-title">💳 Pembayaran F&B</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="payment-container">
            <h2 style="color: var(--rose-pink); margin-bottom: 25px; font-size: 1.5rem;">📋 Rincian Pesanan</h2>
            
            <?php foreach ($orders_by_reservation as $res_id => $group): ?>
                <div class="reservation-section">
                    <div class="reservation-header">
                        🏨 Booking #<?php echo $res_id; ?> - <?php echo htmlspecialchars($group['reservation']['tipe_kamar']); ?>
                    </div>
                    <div class="reservation-meta">
                        <span>📅 Check-in: <?php echo date('d M Y', strtotime($group['reservation']['check_in'])); ?></span>
                        <span>📅 Check-out: <?php echo date('d M Y', strtotime($group['reservation']['check_out'])); ?></span>
                    </div>
                    
                    <?php foreach ($group['orders'] as $order): ?>
                        <div class="order-item">
                            <div>
                                <div class="order-item-name">🍽️ <?php echo htmlspecialchars($order['item']); ?></div>
                                <div class="order-item-details">
                                    <?php echo $order['qty']; ?> porsi × Rp <?php echo number_format($order['harga'], 0, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="order-item-price">
                                <div class="price">Rp <?php echo number_format($order['subtotal'], 0, ',', '.'); ?></div>
                                <div class="qty"><?php echo $order['qty']; ?>x</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="subtotal-row">
                        <span>Subtotal Reservasi #<?php echo $res_id; ?></span>
                        <span>Rp <?php echo number_format($group['total'], 0, ',', '.'); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="payment-summary">
                <div class="summary-title">💰 Total Pembayaran</div>
                <div style="color: rgba(255, 255, 255, 0.9);">
                    <p style="margin-bottom: 8px;">Jumlah Pesanan: <?php echo count($order_ids); ?> item</p>
                    <p>Dari <?php echo count($orders_by_reservation); ?> reservasi</p>
                </div>
                <div class="summary-total">
                    <span class="summary-total-label">Total yang Harus Dibayar:</span>
                    <span class="summary-total-amount">Rp <?php echo number_format($total_bayar, 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <form method="POST" action="">
                <?php foreach ($order_ids as $id): ?>
                    <input type="hidden" name="order_ids[]" value="<?php echo $id; ?>">
                <?php endforeach; ?>
                
                <div class="payment-methods">
                    <h3 class="payment-methods-title">Pilih Metode Pembayaran</h3>
                    <div class="payment-method-grid">
                        <div class="payment-method-option">
                            <input type="radio" id="transfer" name="metode" value="Transfer Bank" required>
                            <label for="transfer" class="payment-method-label">
                                <span class="payment-icon">🏦</span>
                                Transfer Bank
                            </label>
                        </div>
                        <div class="payment-method-option">
                            <input type="radio" id="kartu" name="metode" value="Kartu Kredit">
                            <label for="kartu" class="payment-method-label">
                                <span class="payment-icon">💳</span>
                                Kartu Kredit
                            </label>
                        </div>
                        <div class="payment-method-option">
                            <input type="radio" id="ewallet" name="metode" value="E-Wallet">
                            <label for="ewallet" class="payment-method-label">
                                <span class="payment-icon">📱</span>
                                E-Wallet
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <a href="fnb_orders.php" class="btn-back">← Kembali</a>
                    <button type="submit" class="btn-submit">✓ Bayar Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
