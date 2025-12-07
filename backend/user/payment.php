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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --rose-pink: #ff6b7d;
            --soft-yellow: #fdff94;
            --deep-rose: #ff4f63;
            --white: #ffffff;
            --light-bg: #fffef9;
            --cream: #fff0f2;
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

        .main-content {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: #ff6b7d;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
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
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.15);
            margin-bottom: 25px;
            border: 2px solid #ffb3c1;
        }

        .payment-card h2 {
            color: var(--rose-pink);
            margin-bottom: 20px;
            font-size: 24px;
        }

        .reservation-summary {
            background: linear-gradient(135deg, #fff0f2 0%, #ffe4e7 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 2px solid #ffb3c1;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 107, 125, 0.1);
            color: #333;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--rose-pink);
            padding-top: 15px;
            border-top: 2px solid #ffb3c1;
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
            border-color: var(--rose-pink);
            background: linear-gradient(135deg, #fff0f2 0%, #ffe4e7 100%);
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
        }

        .payment-method label:hover {
            border-color: var(--rose-pink);
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
            color: var(--rose-pink);
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
            background: linear-gradient(135deg, var(--rose-pink) 0%, #ff8a94 50%, var(--soft-yellow) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 125, 0.4);
        }

        .btn-outline {
            background: white;
            color: var(--rose-pink);
            border: 2px solid var(--rose-pink);
        }

        .btn-outline:hover {
            background: var(--rose-pink);
            color: white;
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
                        <i class="fas fa-home navbar-icon"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="reservations.php" class="navbar-link active">
                        <i class="fas fa-calendar-check navbar-icon"></i>
                        <span>Reservasi Saya</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="rooms.php" class="navbar-link">
                        <i class="fas fa-bed navbar-icon"></i>
                        <span>Lihat Kamar</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="fnb_new_order.php" class="navbar-link">
                        <i class="fas fa-concierge-bell navbar-icon"></i>
                        <span>Dining</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="profile.php" class="navbar-link">
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