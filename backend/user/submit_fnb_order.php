<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die('Invalid request');
}

$user_id = $_SESSION['user_id'];
$id_fnb_menu = (int)$_POST['id_fnb_menu'];
$id_reservation = (int)$_POST['id_reservation'];
$qty = (int)$_POST['qty'];
$catatan = sanitize($_POST['catatan'] ?? '');

// Validate reservation belongs to user
$stmt = $conn->prepare("SELECT id_reservation FROM reservation WHERE id_reservation = ? AND id_user = ?");
$stmt->bind_param("ii", $id_reservation, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['error'] = 'Reservation tidak valid!';
    header('Location: fnb_ordering.php');
    exit();
}

// Get menu item details
$stmt = $conn->prepare("SELECT * FROM fnb_menu WHERE id_fnb_menu = ?");
$stmt->bind_param("i", $id_fnb_menu);
$stmt->execute();
$menu = $stmt->get_result()->fetch_assoc();

if (!$menu) {
    $_SESSION['error'] = 'Menu item tidak ditemukan!';
    header('Location: fnb_ordering.php');
    exit();
}

// Insert F&B order
$item_name = $menu['nama_item'];
$price = $menu['harga'];

$stmt = $conn->prepare("
    INSERT INTO fnb_order (id_reservation, item, qty, harga, status, created_at)
    VALUES (?, ?, ?, ?, 'pending', NOW())
");
$stmt->bind_param("isii", $id_reservation, $item_name, $qty, $price);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Order berhasil dikirim! Menunggu konfirmasi dari admin.';
} else {
    $_SESSION['error'] = 'Gagal membuat order. Silakan coba lagi.';
}

header('Location: fnb_ordering.php');
exit();
?>
