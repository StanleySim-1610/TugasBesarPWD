<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

// Get all reservations
$reservations = $conn->query("
    SELECT r.*, u.nama, k.tipe_kamar
    FROM reservation r
    JOIN user u ON r.id_user = u.id_user
    JOIN kamar k ON r.id_kamar = k.id_kamar
    ORDER BY r.created_at DESC
");

$reservationsData = [];
while ($res = $reservations->fetch_assoc()) {
    $reservationsData[] = $res;
}

echo json_encode([
    'success' => true,
    'data' => $reservationsData
]);
?>
