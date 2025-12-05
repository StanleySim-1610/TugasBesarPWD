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
    <title>Payment - Reservation #<?php echo $reservation_id; ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .payment-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        .reservation-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .summary-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--brand-pink);
            padding-top: 15px;
            border-top: 2px solid #333;
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
        }
        .payment-method input[type="radio"]:checked + label {
            border-color: var(--brand-pink);
            background: rgba(255,122,137,0.06);
        }
        .payment-method label:hover {
            border-color: var(--brand-pink);
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>User Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    Dashboard
                </a>
                <a href="reservations.php" class="nav-item active">
                    <span class="nav-icon">📅</span>
                    My Reservations
                </a>
                <a href="rooms.php" class="nav-item">
                    <span class="nav-icon">🏨</span>
                    Browse Rooms
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="nav-icon">👤</span>
                    Profile
                </a>
                <a href="../logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="payment-container">
                <div class="top-bar">
                    <h1>Payment</h1>
                </div>

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
                                <a href="reservation_detail.php?id=<?php echo $reservation_id; ?>" class="btn btn-primary">View Details</a>
                                <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="payment-card">
                        <h2>Reservation Summary</h2>
                        <div class="reservation-summary">
                            <div class="summary-row">
                                <span><strong>Room Type:</strong></span>
                                <span><?php echo htmlspecialchars($reservation['tipe_kamar']); ?></span>
                            </div>
                            <div class="summary-row">
                                <span><strong>Guest Name:</strong></span>
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
                                <span><strong>Number of Nights:</strong></span>
                                <span><?php echo $days; ?> nights</span>
                            </div>
                            <div class="summary-row">
                                <span><strong>Number of Guests:</strong></span>
                                <span><?php echo $reservation['jumlah_orang']; ?> person(s)</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span><strong>Total Amount:</strong></span>
                                <span><?php echo formatRupiah($reservation['total_harga']); ?></span>
                            </div>
                        </div>

                        <h2>Payment Method</h2>
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
                                        <div>Credit Card</div>
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
                                Confirm Payment
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>