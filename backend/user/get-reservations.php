<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's reservations
$reservations = $conn->query("
    SELECT r.*, k.tipe_kamar, k.harga as harga_kamar, 
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari,
           p.status as payment_status, p.metode as payment_method, p.paid_at
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    LEFT JOIN payment_reservation p ON r.id_reservation = p.id_reservation
    WHERE r.id_user = $user_id
    ORDER BY r.created_at DESC
");

$reservationsData = [];
while ($res = $reservations->fetch_assoc()) {
    $reservationsData[] = $res;
}

// Apply limit if provided
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
if ($limit) {
    $reservationsData = array_slice($reservationsData, 0, $limit);
}

echo json_encode([
    'success' => true,
    'data' => $reservationsData
]);
?>
