<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $check_in = sanitize($_POST['check_in']);
    $check_out = sanitize($_POST['check_out']);
    $jumlah_orang = intval($_POST['jumlah_orang']);
    
    if (empty($check_in) || empty($check_out) || $jumlah_orang < 1) {
        $error = 'Semua field wajib diisi!';
    } elseif (strtotime($check_in) < strtotime(date('Y-m-d'))) {
        $error = 'Tanggal check-in tidak boleh kurang dari hari ini!';
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $error = 'Tanggal check-out harus lebih dari check-in!';
    } else {
        $days = calculateDays($check_in, $check_out);
        $total_harga = $room['harga'] * $days;
        
        $status = 'pending';
        $stmt = $conn->prepare("INSERT INTO reservation (id_user, id_kamar, check_in, check_out, jumlah_orang, status, total_harga) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iississ", $user_id, $room_id, $check_in, $check_out, $jumlah_orang, $status, $total_harga);
        
        if ($stmt->execute()) {
            $reservation_id = $conn->insert_id;
            
            $conn->query("UPDATE kamar SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id_kamar = $room_id");
            
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-pink: #ff6b7d;
            --primary-yellow: #fdff94;
            --white: #ffffff;
            --light-bg: #fffbf5;
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
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: #ff6b7d;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
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
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.15);
        }

        .room-details h2 {
            color: #ff6b7d;
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
            box-shadow: 0 1px 5px rgba(255, 107, 125, 0.1);
        }

        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 18px;
            color: #ff6b7d;
            font-weight: 600;
        }

        .booking-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.15);
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
        }

        .booking-form h2 {
            color: #ff6b7d;
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
            color: #ff6b7d;
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
            border-color: #fdff94;
            box-shadow: 0 0 0 3px rgba(253, 255, 148, 0.1);
        }

        .price-summary {
            background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(255, 107, 125, 0.15);
        }

        .price-summary h3 {
            color: #ff6b7d;
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
            color: #ff6b7d;
            border-top: 2px solid #fdff94;
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
            background: linear-gradient(135deg, #ff6b7d 0%, #ff8a94 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 125, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 125, 0.4);
        }
    </style>
</head>
<body>
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
                    <a href="reservations.php" class="navbar-link">
                        <i class="fas fa-calendar-check navbar-icon"></i>
                        <span>Reservasi Saya</span>
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="rooms.php" class="navbar-link active">
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
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            checkInDate.setDate(checkInDate.getDate() + 1);
            checkOutInput.min = checkInDate.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
