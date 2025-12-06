<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

// Get available rooms
$rooms = $conn->query("SELECT * FROM kamar WHERE jumlah_tersedia > 0 ORDER BY harga ASC");
$roomsData = [];

while ($room = $rooms->fetch_assoc()) {
    $roomsData[] = $room;
}

echo json_encode([
    'success' => true,
    'data' => $roomsData
]);
?>
