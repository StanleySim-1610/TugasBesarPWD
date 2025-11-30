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
    <title>Reschedule Reservation</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .edit-container { max-width: 600px; margin: 0 auto; }
        .edit-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input { padding: 10px; border: 2px solid #ddd; border-radius: 6px; width: 100%; }
        .info-box { background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #0d47a1; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h3>User Panel</h3></div>
            <nav class="sidebar-nav">
                <a href="reservations.php" class="nav-item active">📅 Back to List</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Reschedule Booking #<?php echo $id; ?></h1>
            </div>

            <section class="content-section">
                <div class="edit-container">
                    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

                    <div class="edit-card">
                        <div class="info-box">
                            <strong>Aturan Reschedule:</strong><br>
                            Anda hanya dapat mengubah tanggal Check-in ke tanggal setelah <b><?php echo date('d M Y', strtotime($original_check_in)); ?></b>.<br>
                            Durasi menginap akan tetap <b><?php echo $original_days; ?> malam</b>.
                        </div>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label>Tipe Kamar</label>
                                <input type="text" value="<?php echo htmlspecialchars($reservation['tipe_kamar']); ?>" disabled style="background: #f9f9f9;">
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

                            <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Perubahan Tanggal</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const checkInInput = document.getElementById('check_in');
        const checkOutDisplay = document.getElementById('check_out_display');
        const originalDays = <?php echo $original_days; ?>;

        checkInInput.addEventListener('change', function() {
            if(this.value) {
                const date = new Date(this.value);
                date.setDate(date.getDate() + originalDays);
                
                const options = { day: 'numeric', month: 'short', year: 'numeric' };
                checkOutDisplay.value = date.toLocaleDateString('en-GB', options);
            }
        });
    </script>
</body>
</html>