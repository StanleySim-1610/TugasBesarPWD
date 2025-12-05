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

// Get JSON body
$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'];
$room_id = intval($data['id_kamar'] ?? 0);
$check_in = sanitize($data['check_in'] ?? '');
$check_out = sanitize($data['check_out'] ?? '');
$jumlah_orang = intval($data['jumlah_orang'] ?? 1);
$total_harga = floatval($data['total_harga'] ?? 0);

// Validation
if (!$room_id || !$check_in || !$check_out) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (strtotime($check_in) < strtotime(date('Y-m-d'))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal check-in tidak valid']);
    exit;
}

if (strtotime($check_out) <= strtotime($check_in)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal check-out harus lebih dari check-in']);
    exit;
}

// Insert reservation
$status = 'pending';
$stmt = $conn->prepare("INSERT INTO reservation (id_user, id_kamar, check_in, check_out, jumlah_orang, status, total_harga) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iississ", $user_id, $room_id, $check_in, $check_out, $jumlah_orang, $status, $total_harga);

if ($stmt->execute()) {
    $reservation_id = $conn->insert_id;
    
    // Update room availability
    $conn->query("UPDATE kamar SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id_kamar = $room_id");
    
    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil dibuat',
        'data' => ['id_reservation' => $reservation_id]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal membuat reservasi']);
}
?>
