<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: reservations.php');
    exit();
}

$stmt = $conn->prepare("
    SELECT r.*, p.status as payment_status 
    FROM reservation r 
    LEFT JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    WHERE r.id_reservation = ? AND r.id_user = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    echo "<script>alert('Reservasi tidak ditemukan.'); window.location='reservations.php';</script>";
    exit();
}

if ($reservation['payment_status'] == 'paid') {
    echo "<script>alert('Reservasi yang sudah dibayar tidak dapat dihapus. Silakan hubungi admin.'); window.location='reservations.php';</script>";
    exit();
}

if ($reservation['status'] == 'cancelled') {
    header('Location: reservations.php');
    exit();
}

$conn->begin_transaction();

try {
    $cancelStmt = $conn->prepare("UPDATE reservation SET status = 'cancelled' WHERE id_reservation = ?");
    $cancelStmt->bind_param("i", $id);
    $cancelStmt->execute();

    $roomId = $reservation['id_kamar'];
    $stockStmt = $conn->prepare("UPDATE kamar SET jumlah_tersedia = jumlah_tersedia + 1 WHERE id_kamar = ?");
    $stockStmt->bind_param("i", $roomId);
    $stockStmt->execute();

    $conn->commit();

    echo "<script>alert('Reservasi berhasil dihapus/dibatalkan.'); window.location='reservations.php';</script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Terjadi kesalahan sistem saat menghapus reservasi.'); window.location='reservations.php';</script>";
}
?>