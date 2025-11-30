<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

// Fetch reservation
$stmt = $conn->prepare("SELECT r.*, k.tipe_kamar, k.harga, k.jumlah_tersedia FROM reservation r JOIN kamar k ON r.id_kamar = k.id_kamar WHERE r.id_reservation = ? AND r.id_user = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    header('Location: reservations.php');
    exit();
}

// keep original booking info to apply rules
$original_days = intval($reservation['jumlah_hari']);
$original_check_in = $reservation['check_in'];

// Prevent editing past reservations or cancelled ones
// Allow editing on the check-in day as well (so users can adjust on the day)
if (!in_array($reservation['status'], ['pending','confirmed']) || strtotime($reservation['check_in']) < strtotime(date('Y-m-d'))) {
    header('Location: reservations.php');
    exit();
}

$error = '';
$success = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_check_in = sanitize($_POST['check_in']);
    // client sends a check_out signed by JS, but we will derive expected_check_out server-side
    $posted_check_out = sanitize($_POST['check_out'] ?? '');

    // Basic validation
    if (empty($new_check_in)) {
        $error = 'Tanggal check-in wajib diisi.';
    } elseif (strtotime($new_check_in) < strtotime(date('Y-m-d'))) {
        $error = 'Tanggal check-in tidak boleh kurang dari hari ini.';
    }

    // now check rules: new check_in cannot be earlier than original check_in
    if (empty($error) && strtotime($new_check_in) < strtotime($original_check_in)) {
        $error = 'Tanggal check-in tidak boleh dimundurkan dari tanggal asli reservasi.';
    }

    if (empty($error)) {
        // Derive expected check_out based on original duration (jumlah_hari)
        $expected_check_out = date('Y-m-d', strtotime($new_check_in . ' + ' . $original_days . ' days'));
        // Double-check posted check_out (if sent) matches expected
        if (!empty($posted_check_out) && $posted_check_out !== $expected_check_out) {
            $error = 'Check-out harus tetap sesuai durasi asli reservasi (' . $original_days . ' malam).';
        }
        // Check availability for the room during the new dates (excluding current reservation)
        $room_id = $reservation['id_kamar'];
        $allowed_rooms = intval($reservation['jumlah_tersedia']) + 1; // Include this reservation

        $checkStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reservation WHERE id_kamar = ? AND status IN ('pending','confirmed') AND id_reservation != ? AND NOT (check_out <= ? OR check_in >= ?)");
        $checkStmt->bind_param("iiss", $room_id, $id, $new_check_in, $expected_check_out);
        $checkStmt->execute();
        $overlapCount = $checkStmt->get_result()->fetch_assoc()['cnt'];

        if ($overlapCount >= $allowed_rooms) {
            $error = 'Maaf, kamar tidak tersedia pada tanggal yang dipilih.';
        }
    }

    if (empty($error)) {
        // Keep original duration
        $days = $original_days;
        $new_total = floatval($reservation['harga']) * $days;

        $updateStmt = $conn->prepare("UPDATE reservation SET check_in = ?, check_out = ?, jumlah_hari = ?, total_harga = ? WHERE id_reservation = ? AND id_user = ?");
        $updateStmt->bind_param("ssiddi", $new_check_in, $expected_check_out, $days, $new_total, $id, $user_id);
        if ($updateStmt->execute()) {
            // Update payment record if exists (adjust total)
            $payStmt = $conn->prepare("SELECT id_payment_res, status FROM payment_reservation WHERE id_reservation = ?");
            $payStmt->bind_param("i", $id);
            $payStmt->execute();
            $payment = $payStmt->get_result()->fetch_assoc();
            if ($payment) {
                $updatePay = $conn->prepare("UPDATE payment_reservation SET total_bayar = ? WHERE id_payment_res = ?");
                $updatePay->bind_param("di", $new_total, $payment['id_payment_res']);
                $updatePay->execute();
            }

            $success = 'Reservasi berhasil diubah.';
            // Refresh reservation data to reflect changes
            $stmt = $conn->prepare("SELECT r.*, k.tipe_kamar, k.harga, k.jumlah_tersedia FROM reservation r JOIN kamar k ON r.id_kamar = k.id_kamar WHERE r.id_reservation = ? AND r.id_user = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $reservation = $stmt->get_result()->fetch_assoc();
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
    <title>Edit Reservation - #<?php echo $reservation['id_reservation']; ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .edit-container { max-width: 800px; margin: 0 auto; }
        .edit-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input[type="date"] { padding: 10px; border: 2px solid #ddd; border-radius: 6px; width: 100%; }
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
                <a href="dashboard.php" class="nav-item">🏠 Dashboard</a>
                <a href="reservations.php" class="nav-item active">📅 My Reservations</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Edit Reservation</h1>
                <a href="reservations.php" class="btn btn-outline">Back</a>
            </div>

            <section class="content-section">
                <div class="edit-container">
                    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

                    <div class="edit-card">
                        <h3>Room: <?php echo htmlspecialchars($reservation['tipe_kamar']); ?></h3>
                        <p>Price per night: <strong><?php echo formatRupiah($reservation['harga']); ?></strong></p>
                        <form method="POST" action="">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="check_in">Check In</label>
                                    <input type="date" id="check_in" name="check_in" required min="<?php echo htmlspecialchars($reservation['check_in']); ?>" value="<?php echo htmlspecialchars($reservation['check_in']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="check_out">Check Out</label>
                                    <input type="date" id="check_out" disabled value="<?php echo htmlspecialchars($reservation['check_out']); ?>">
                                    <input type="hidden" id="hidden_check_out" name="check_out" value="<?php echo htmlspecialchars($reservation['check_out']); ?>">
                                </div>
                            </div>
                            <div style="margin-top: 20px; display:flex; gap:12px; align-items:center;">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="reservation_detail.php?id=<?php echo $reservation['id_reservation']; ?>" class="btn btn-secondary">View Details</a>
                            </div>
                            <div style="margin-top:12px;">
                                <small>Estimated nights: <span id="calc_days"><?php echo $reservation['jumlah_hari']; ?></span></small>
                                &nbsp;•&nbsp;
                                <small>New total: <strong id="calc_total"><?php echo formatRupiah($reservation['total_harga']); ?></strong></small>
                            </div>
                        </form>
                            <script>
                                const checkIn = document.getElementById('check_in');
                                const checkOut = document.getElementById('check_out');
                                const hiddenCheckOut = document.getElementById('hidden_check_out');
                                const calcDaysSpan = document.getElementById('calc_days');
                                const calcTotalSpan = document.getElementById('calc_total');
                                const pricePerNight = <?php echo floatval($reservation['harga']); ?>;
                                const originalDays = <?php echo intval($original_days); ?>;

                                function formatDateYYYYMMDD(d) {
                                    const y = d.getFullYear();
                                    const m = String(d.getMonth() + 1).padStart(2, '0');
                                    const day = String(d.getDate()).padStart(2, '0');
                                    return `${y}-${m}-${day}`;
                                }

                                function updateCalc() {
                                    const inDate = new Date(checkIn.value);
                                    if (isNaN(inDate)) return;
                                    // Calculate expected check_out using originalDays
                                    const outDate = new Date(inDate);
                                    outDate.setDate(outDate.getDate() + originalDays);
                                    const outStr = formatDateYYYYMMDD(outDate);
                                    checkOut.value = outStr;
                                    hiddenCheckOut.value = outStr;

                                    calcDaysSpan.textContent = originalDays + ' nights';
                                    calcTotalSpan.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(originalDays * pricePerNight);
                                }

                                // Initialize the fields on load
                                updateCalc();
                                checkIn.addEventListener('change', updateCalc);
                            </script>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
