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
    <title>Book Room - <?php echo htmlspecialchars($room['tipe_kamar']); ?></title>
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
    <style>
        .booking-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .room-details {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .room-details h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .room-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            color: #333;
            font-weight: 600;
        }
        .booking-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .price-total {
            font-size: 24px;
            font-weight: 700;
            color: var(--brand-pink);
            border-top: 2px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
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
                    <span class="nav-icon">🏠</span>
                    Dashboard
                </a>
                <a href="reservations.php" class="nav-item">
                    <span class="nav-icon">📅</span>
                    My Reservations
                </a>
                <a href="rooms.php" class="nav-item active">
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
            <div class="booking-container">
                <div class="top-bar">
                    <h1>Book Room</h1>
                    <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="room-details">
                    <h2><?php echo htmlspecialchars($room['tipe_kamar']); ?></h2>
                    <p><?php echo htmlspecialchars($room['deskripsi']); ?></p>
                    
                    <div class="room-info-grid">
                        <div class="info-item">
                            <div class="info-label">Price per Night</div>
                            <div class="info-value"><?php echo formatRupiah($room['harga']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Available Rooms</div>
                            <div class="info-value"><?php echo $room['jumlah_tersedia']; ?> rooms</div>
                        </div>
                    </div>
                </div>

                <div class="booking-form">
                    <h2>Booking Details</h2>
                    <form method="POST" action="" id="bookingForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="check_in">Check-in Date</label>
                                <input type="date" id="check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="check_out">Check-out Date</label>
                                <input type="date" id="check_out" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="jumlah_orang">Number of Guests</label>
                            <input type="number" id="jumlah_orang" name="jumlah_orang" min="1" max="10" value="1" required>
                        </div>

                        <div class="price-summary">
                            <h3>Price Summary</h3>
                            <div class="price-row">
                                <span>Price per night:</span>
                                <span id="pricePerNight"><?php echo formatRupiah($room['harga']); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Number of nights:</span>
                                <span id="numberOfNights">0</span>
                            </div>
                            <div class="price-row price-total">
                                <span>Total:</span>
                                <span id="totalPrice"><?php echo formatRupiah(0); ?></span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                            Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

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
