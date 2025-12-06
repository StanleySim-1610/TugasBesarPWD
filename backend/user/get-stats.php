<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get stats
$total = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id")->fetch_assoc();
$active = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'confirmed'")->fetch_assoc();
$pending = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_user = $user_id AND status = 'pending'")->fetch_assoc();

echo json_encode([
    'success' => true,
    'data' => [
        'total_reservations' => $total['count'],
        'active_bookings' => $active['count'],
        'pending_bookings' => $pending['count']
    ]
]);
?>
