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
$nama = sanitize($data['nama'] ?? '');
$email = sanitize($data['email'] ?? '');
$no_telp = sanitize($data['no_telp'] ?? '');
$no_identitas = sanitize($data['no_identitas'] ?? '');
$current_password = $data['current_password'] ?? '';
$new_password = $data['new_password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

// Validation
if (!$nama || !$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nama dan email wajib diisi']);
    exit;
}

// Check if email is changed and already exists
$stmt = $conn->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email sudah digunakan']);
    exit;
}

// Get current user
$stmt = $conn->prepare("SELECT password FROM user WHERE id_user = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle password change
if (!empty($new_password)) {
    if (empty($current_password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Masukkan password lama']);
        exit;
    }
    
    if (!verifyPassword($current_password, $user['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password lama tidak sesuai']);
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password baru tidak cocok']);
        exit;
    }
    
    $hashedPassword = hashPassword($new_password);
    $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ?, no_telp = ?, no_identitas = ?, password = ? WHERE id_user = ?");
    $stmt->bind_param("sssssi", $nama, $email, $no_telp, $no_identitas, $hashedPassword, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ?, no_telp = ?, no_identitas = ? WHERE id_user = ?");
    $stmt->bind_param("ssssi", $nama, $email, $no_telp, $no_identitas, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['nama'] = $nama;
    $_SESSION['email'] = $email;
    
    echo json_encode([
        'success' => true,
        'message' => 'Profil berhasil diperbarui'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil']);
}
?>
