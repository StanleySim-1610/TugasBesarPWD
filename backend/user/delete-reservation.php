<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Reservation ID required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$reservation_id = intval($_GET['id']);

// Check if reservation exists and belongs to user
$stmt = $conn->prepare("SELECT id_kamar FROM reservation WHERE id_reservation = ? AND id_user = ? AND status = 'pending'");
$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Reservation not found or cannot be deleted']);
    exit;
}

// Delete reservation and update room availability
$stmt = $conn->prepare("DELETE FROM reservation WHERE id_reservation = ?");
$stmt->bind_param("i", $reservation_id);

if ($stmt->execute()) {
    // Update room availability
    $conn->query("UPDATE kamar SET jumlah_tersedia = jumlah_tersedia + 1 WHERE id_kamar = {$res['id_kamar']}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil dihapus'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus reservasi']);
}
?>
