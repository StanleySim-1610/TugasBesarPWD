<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

// Fetch reservation dengan status pembayaran
$stmt = $conn->prepare("
    SELECT r.*, k.tipe_kamar, k.harga, k.jumlah_tersedia, p.status as payment_status,
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari
    FROM reservation r 
    JOIN kamar k ON r.id_kamar = k.id_kamar 
    LEFT JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    WHERE r.id_reservation = ? AND r.id_user = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    header('Location: reservations.php');
    exit();
}

// ATURAN 1: HARUS SUDAH BAYAR UNTUK BISA EDIT (RESCHEDULE)
if ($reservation['payment_status'] !== 'paid') {
    echo "<script>alert('Anda harus melakukan pembayaran terlebih dahulu untuk melakukan reschedule.'); window.location='reservations.php';</script>";
    exit();
}

// Simpan info asli
$original_days = intval($reservation['jumlah_hari']);
$original_check_in = $reservation['check_in'];

$error = '';
$success = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_check_in = sanitize($_POST['check_in']);
    
    // Kalkulasi check-out otomatis berdasarkan durasi asli
    $expected_check_out = date('Y-m-d', strtotime($new_check_in . ' + ' . $original_days . ' days'));

    // Validasi Dasar
    if (empty($new_check_in)) {
        $error = 'Tanggal check-in wajib diisi.';
    } elseif (strtotime($new_check_in) < strtotime(date('Y-m-d'))) {
        $error = 'Tanggal check-in tidak boleh kurang dari hari ini.';
    }

    // ATURAN 2: TANGGAL BARU HARUS SETELAH TANGGAL ASLI
    // Jika tanggal baru <= tanggal asli, tampilkan error
    if (empty($error) && strtotime($new_check_in) <= strtotime($original_check_in)) {
        $error = 'Sesuai ketentuan, Reschedule hanya diperbolehkan untuk memundurkan tanggal (setelah tanggal asli: ' . date('d M Y', strtotime($original_check_in)) . ').';
    }

    if (empty($error)) {
        // Cek ketersediaan kamar pada tanggal baru
        $room_id = $reservation['id_kamar'];
        // Kita hitung ketersediaan (mengabaikan booking kita sendiri saat ini)
        $allowed_rooms = intval($reservation['jumlah_tersedia']) + 1; 

        $checkStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reservation WHERE id_kamar = ? AND status IN ('pending','confirmed') AND id_reservation != ? AND NOT (check_out <= ? OR check_in >= ?)");
        $checkStmt->bind_param("iiss", $room_id, $id, $new_check_in, $expected_check_out);
        $checkStmt->execute();
        $overlapCount = $checkStmt->get_result()->fetch_assoc()['cnt'];

        if ($overlapCount >= $allowed_rooms) {
            $error = 'Maaf, kamar tidak tersedia pada tanggal baru yang dipilih.';
        }
    }

    if (empty($error)) {
        // Update Reservation (Durasi & Total Harga tetap sama karena hanya geser tanggal)
        $updateStmt = $conn->prepare("UPDATE reservation SET check_in = ?, check_out = ? WHERE id_reservation = ? AND id_user = ?");
        $updateStmt->bind_param("ssii", $new_check_in, $expected_check_out, $id, $user_id);
        
        if ($updateStmt->execute()) {
            $success = 'Reservasi berhasil di-reschedule.';
            // Refresh data
            $reservation['check_in'] = $new_check_in;
            $reservation['check_out'] = $expected_check_out;
        } else {
            $error = 'Terjadi kesalahan saat memperbarui reservasi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Reservasi</title>
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

        .edit-container {
            max-width: 600px;
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

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 2px solid #4caf50;
        }

        .edit-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.1);
            border: 2px solid var(--chinese-gold);
        }

        .info-box {
            background: linear-gradient(135deg, #fff9f0 0%, #ffe4e1 100%);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--chinese-dark);
            border: 2px solid var(--chinese-gold);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--chinese-red);
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

        .form-group input:disabled {
            background: #f5f5f5;
            color: #666;
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
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.4);
        }

        .btn-outline {
            background: white;
            color: var(--chinese-red);
            border: 2px solid var(--chinese-red);
            margin-bottom: 20px;
            display: inline-block;
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
            <h1>Reschedule Booking #<?php echo $id; ?></h1>
        </div>

        <div class="edit-container">
            <a href="reservations.php" class="btn btn-outline">← Kembali ke Daftar Reservasi</a>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="edit-card">
                <div class="info-box">
                    <strong>Aturan Reschedule:</strong><br>
                    Anda hanya dapat mengubah tanggal Check-in ke tanggal setelah <b><?php echo date('d M Y', strtotime($original_check_in)); ?></b>.<br>
                    Durasi menginap akan tetap <b><?php echo $original_days; ?> malam</b>.
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Tipe Kamar</label>
                        <input type="text" value="<?php echo htmlspecialchars($reservation['tipe_kamar']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="check_in">Tanggal Check In Baru</label>
                        <input type="date" id="check_in" name="check_in" 
                               required 
                               min="<?php echo date('Y-m-d', strtotime($original_check_in . ' +1 day')); ?>" 
                               value="<?php echo htmlspecialchars($reservation['check_in']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Estimasi Check Out (Otomatis)</label>
                        <input type="text" id="check_out_display" disabled value="<?php echo date('d M Y', strtotime($reservation['check_out'])); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan Tanggal</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const checkInInput = document.getElementById('check_in');
        const checkOutDisplay = document.getElementById('check_out_display');
        const originalDays = <?php echo $original_days; ?>;

        checkInInput.addEventListener('change', function() {
            if(this.value) {
                const date = new Date(this.value);
                date.setDate(date.getDate() + originalDays);
                
                const options = { day: 'numeric', month: 'short', year: 'numeric' };
                checkOutDisplay.value = date.toLocaleDateString('id-ID', options);
            }
        });
    </script>
</body>
</html>