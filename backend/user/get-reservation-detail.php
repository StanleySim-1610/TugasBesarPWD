<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Reservation ID required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$reservation_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT r.*, k.tipe_kamar, k.harga as harga_kamar, 
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari,
           p.status as payment_status, p.metode as payment_method, p.paid_at
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    LEFT JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    WHERE r.id_reservation = ? AND r.id_user = ?
");

$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if ($res) {
    echo json_encode([
        'success' => true,
        'data' => $res
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Reservation not found'
    ]);
}
?>
