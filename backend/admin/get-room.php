<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Room ID required']);
    exit;
}

$room_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM kamar WHERE id_kamar = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if ($room) {
    echo json_encode([
        'success' => true,
        'data' => $room
    ]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Room not found']);
}
?>
