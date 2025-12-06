<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get room details
if (!isset($_GET['room']) || empty($_GET['room'])) {
    header('Location: dashboard.php');
    exit();
}

$room_id = intval($_GET['room']);
$stmt = $conn->prepare("SELECT * FROM kamar WHERE id_kamar = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    header('Location: dashboard.php');
    exit();
}

// Process booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in = sanitize($_POST['check_in']);
    $check_out = sanitize($_POST['check_out']);
    $jumlah_orang = intval($_POST['jumlah_orang']);
    
    // Validation
    if (empty($check_in) || empty($check_out) || $jumlah_orang < 1) {
        $error = 'Semua field wajib diisi!';
    } elseif (strtotime($check_in) < strtotime(date('Y-m-d'))) {
        $error = 'Tanggal check-in tidak boleh kurang dari hari ini!';
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $error = 'Tanggal check-out harus lebih dari check-in!';
    } else {
        // Calculate total price
        $days = calculateDays($check_in, $check_out);
        $total_harga = $room['harga'] * $days;
        
        // Insert reservation
        $status = 'pending';
        $stmt = $conn->prepare("INSERT INTO reservation (id_user, id_kamar, check_in, check_out, jumlah_orang, status, total_harga) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iississ", $user_id, $room_id, $check_in, $check_out, $jumlah_orang, $status, $total_harga);
        
        if ($stmt->execute()) {
            $reservation_id = $conn->insert_id;
            
            // Update room availability
            $conn->query("UPDATE kamar SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id_kamar = $room_id");
            
            // Redirect to payment
            header("Location: payment.php?reservation=$reservation_id");
            exit();
        } else {
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
    <title>Booking Kamar - <?php echo htmlspecialchars($room['tipe_kamar']); ?></title>
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

        .booking-container {
            max-width: 900px;
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

        .room-details {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.1);
            border: 2px solid var(--chinese-gold);
        }

        .room-details h2 {
            color: var(--chinese-red);
            margin-bottom: 15px;
            font-size: 28px;
        }

        .room-details p {
            color: #666;
            line-height: 1.6;
        }

        .room-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            padding: 15px;
            background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
            border-radius: 10px;
            border: 1px solid var(--chinese-gold);
        }

        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 18px;
            color: var(--chinese-red);
            font-weight: 600;
        }

        .booking-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.1);
            border: 2px solid var(--chinese-gold);
        }

        .booking-form h2 {
            color: var(--chinese-red);
            margin-bottom: 25px;
            font-size: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--chinese-red);
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--chinese-gold);
            box-shadow: 0 0 0 3px rgba(240, 179, 67, 0.1);
        }

        .price-summary {
            background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border: 2px solid var(--chinese-gold);
        }

        .price-summary h3 {
            color: var(--chinese-red);
            margin-bottom: 15px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #333;
        }

        .price-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--chinese-red);
            border-top: 2px solid var(--chinese-gold);
            padding-top: 15px;
            margin-top: 15px;
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
                <li><a href="reservations.php"><span>📅</span> Reservasi Saya</a></li>
                <li><a href="rooms.php" class="active"><span>🏨</span> Lihat Kamar</a></li>
                <li><a href="fnb_new_order.php"><span>🍽️</span> Pesan F&B</a></li>
                <li><a href="fnb_orders.php"><span>📋</span> Pesanan F&B</a></li>
                <li><a href="profile.php"><span>👤</span> Profil</a></li>
                <li><a href="../logout.php"><span>🚪</span> Keluar</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1>Booking Kamar</h1>
        </div>

        <div class="booking-container">

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="room-details">
                <h2><?php echo htmlspecialchars($room['tipe_kamar']); ?></h2>
                <p><?php echo htmlspecialchars($room['deskripsi']); ?></p>
                
                <div class="room-info-grid">
                    <div class="info-item">
                        <div class="info-label">Harga per Malam</div>
                        <div class="info-value"><?php echo formatRupiah($room['harga']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kamar Tersedia</div>
                        <div class="info-value"><?php echo $room['jumlah_tersedia']; ?> kamar</div>
                    </div>
                </div>
            </div>

            <div class="booking-form">
                <h2>Detail Booking</h2>
                <form method="POST" action="" id="bookingForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="check_in">Tanggal Check-in</label>
                            <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="check_out">Tanggal Check-out</label>
                            <input type="date" id="check_out" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah_orang">Jumlah Tamu</label>
                        <input type="number" id="jumlah_orang" name="jumlah_orang" min="1" max="10" value="1" required>
                    </div>

                    <div class="price-summary">
                        <h3>Ringkasan Harga</h3>
                        <div class="price-row">
                            <span>Harga per malam:</span>
                            <span id="pricePerNight"><?php echo formatRupiah($room['harga']); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Jumlah malam:</span>
                            <span id="numberOfNights">0</span>
                        </div>
                        <div class="price-row price-total">
                            <span>Total:</span>
                            <span id="totalPrice"><?php echo formatRupiah(0); ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        Lanjut ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const pricePerNight = <?php echo $room['harga']; ?>;
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        function calculatePrice() {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            
            if (checkIn && checkOut && checkOut > checkIn) {
                const diffTime = Math.abs(checkOut - checkIn);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const total = pricePerNight * diffDays;
                
                document.getElementById('numberOfNights').textContent = diffDays;
                document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
            }
        }
        
        checkInInput.addEventListener('change', calculatePrice);
        checkOutInput.addEventListener('change', calculatePrice);
        
        // Update min checkout date when checkin changes
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            checkInDate.setDate(checkInDate.getDate() + 1);
            checkOutInput.min = checkInDate.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
