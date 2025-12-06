<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get reservation details
if (!isset($_GET['reservation']) || empty($_GET['reservation'])) {
    header('Location: dashboard.php');
    exit();
}

$reservation_id = intval($_GET['reservation']);
$stmt = $conn->prepare("
    SELECT r.*, k.tipe_kamar, k.harga as harga_kamar, u.nama, u.email
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    JOIN user u ON r.id_user = u.id_user
    WHERE r.id_reservation = ? AND r.id_user = ?
");
$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    header('Location: dashboard.php');
    exit();
}

// Check if payment already exists
$payment_check = $conn->query("SELECT * FROM payment_reservation WHERE id_reservation = $reservation_id");
if ($payment_check->num_rows > 0) {
    $existing_payment = $payment_check->fetch_assoc();
    if ($existing_payment['status'] == 'paid') {
        header('Location: reservation_detail.php?id=' . $reservation_id);
        exit();
    }
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metode = sanitize($_POST['metode']);
    
    if (empty($metode)) {
        $error = 'Pilih metode pembayaran!';
    } else {
        // Insert payment
        $total_bayar = $reservation['total_harga'];
        $status = 'paid';
        $paid_at = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO payment_reservation (id_reservation, total_bayar, metode, status, paid_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $reservation_id, $total_bayar, $metode, $status, $paid_at);
        
        if ($stmt->execute()) {
            // Update reservation status
            $conn->query("UPDATE reservation SET status = 'confirmed' WHERE id_reservation = $reservation_id");
            
            $success = 'Pembayaran berhasil! Reservasi Anda telah dikonfirmasi.';
        } else {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

$days = calculateDays($reservation['check_in'], $reservation['check_out']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Reservasi #<?php echo $reservation_id; ?></title>
    <style>
        :root {
            --chinese-red: #d32f2f;
            --chinese-gold: #f0b343;
            --chinese-dark: #8b0000;
            --white: #ffffff;
            --light-bg: #fff9f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Microsoft YaHei', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(211, 47, 47, 0.03) 10px, rgba(211, 47, 47, 0.03) 20px),
                repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(240, 179, 67, 0.03) 10px, rgba(240, 179, 67, 0.03) 20px);
            pointer-events: none;
            z-index: 0;
        }

        .topnavbar {
            background: linear-gradient(135deg, var(--chinese-red) 0%, var(--chinese-dark) 100%);
            padding: 0;
            box-shadow: 0 4px 20px rgba(211, 47, 47, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid var(--chinese-gold);
        }

        .topnavbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
        }

        .topnavbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
        }

        .topnavbar-brand h2 {
            color: var(--chinese-gold);
            font-size: 24px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .topnavbar-menu {
            display: flex;
            gap: 5px;
            list-style: none;
        }

        .topnavbar-menu a {
            color: white;
            text-decoration: none;
            padding: 20px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            border-bottom: 3px solid transparent;
        }

        .topnavbar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-bottom-color: var(--chinese-gold);
        }

        .topnavbar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            border-bottom-color: var(--chinese-gold);
        }

        .main-content {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: var(--chinese-red);
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .payment-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 2px solid #ef5350;
        }

        .payment-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.1);
            margin-bottom: 25px;
            border: 2px solid var(--chinese-gold);
        }

        .payment-card h2 {
            color: var(--chinese-red);
            margin-bottom: 20px;
            font-size: 24px;
        }

        .reservation-summary {
            background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 2px solid var(--chinese-gold);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(211, 47, 47, 0.1);
            color: #333;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--chinese-red);
            padding-top: 15px;
            border-top: 2px solid var(--chinese-gold);
            margin-top: 15px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method label {
            display: block;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .payment-method input[type="radio"]:checked + label {
            border-color: var(--chinese-gold);
            background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
            box-shadow: 0 4px 15px rgba(240, 179, 67, 0.3);
        }

        .payment-method label:hover {
            border-color: var(--chinese-gold);
        }

        .payment-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .success-message {
            text-align: center;
            padding: 40px;
        }

        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .success-message h2 {
            color: var(--chinese-red);
            margin-bottom: 15px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--chinese-red) 0%, var(--chinese-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.4);
        }

        .btn-outline {
            background: white;
            color: var(--chinese-red);
            border: 2px solid var(--chinese-red);
        }

        .btn-outline:hover {
            background: var(--chinese-red);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="topnavbar">
        <div class="topnavbar-content">
            <div class="topnavbar-brand">
                <h2>🏨 Hotel Management</h2>
            </div>
            <ul class="topnavbar-menu">
                <li><a href="dashboard.php"><span>🏠</span> Beranda</a></li>
                <li><a href="reservations.php" class="active"><span>📅</span> Reservasi Saya</a></li>
                <li><a href="rooms.php"><span>🏨</span> Lihat Kamar</a></li>
                <li><a href="fnb_new_order.php"><span>🍽️</span> Pesan F&B</a></li>
                <li><a href="fnb_orders.php"><span>📋</span> Pesanan F&B</a></li>
                <li><a href="profile.php"><span>👤</span> Profil</a></li>
                <li><a href="../logout.php"><span>🚪</span> Keluar</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1>Pembayaran Reservasi</h1>
        </div>

        <div class="payment-container">

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="payment-card">
                    <div class="success-message">
                        <div class="success-icon">✓</div>
                        <h2><?php echo $success; ?></h2>
                        <p>Booking ID: #<?php echo $reservation_id; ?></p>
                        <div style="margin-top: 30px;">
                            <a href="reservation_detail.php?id=<?php echo $reservation_id; ?>" class="btn btn-primary">Lihat Detail</a>
                            <a href="dashboard.php" class="btn btn-outline">Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="payment-card">
                    <h2>Ringkasan Reservasi</h2>
                    <div class="reservation-summary">
                        <div class="summary-row">
                            <span><strong>Tipe Kamar:</strong></span>
                            <span><?php echo htmlspecialchars($reservation['tipe_kamar']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span><strong>Nama Tamu:</strong></span>
                            <span><?php echo htmlspecialchars($reservation['nama']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span><strong>Check-in:</strong></span>
                            <span><?php echo date('d M Y', strtotime($reservation['check_in'])); ?></span>
                        </div>
                        <div class="summary-row">
                            <span><strong>Check-out:</strong></span>
                            <span><?php echo date('d M Y', strtotime($reservation['check_out'])); ?></span>
                        </div>
                        <div class="summary-row">
                            <span><strong>Jumlah Malam:</strong></span>
                            <span><?php echo $days; ?> malam</span>
                        </div>
                        <div class="summary-row">
                            <span><strong>Jumlah Tamu:</strong></span>
                            <span><?php echo $reservation['jumlah_orang']; ?> orang</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span><strong>Total Bayar:</strong></span>
                            <span><?php echo formatRupiah($reservation['total_harga']); ?></span>
                        </div>
                    </div>

                    <h2>Metode Pembayaran</h2>
                    <form method="POST" action="">
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="transfer" name="metode" value="Bank Transfer" required>
                                <label for="transfer">
                                    <div class="payment-icon">🏦</div>
                                    <div>Bank Transfer</div>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="credit" name="metode" value="Credit Card">
                                <label for="credit">
                                    <div class="payment-icon">💳</div>
                                    <div>Kartu Kredit</div>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="ewallet" name="metode" value="E-Wallet">
                                <label for="ewallet">
                                    <div class="payment-icon">📱</div>
                                    <div>E-Wallet</div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Konfirmasi Pembayaran
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>