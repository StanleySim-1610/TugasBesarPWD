<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['email']) && $_SESSION['email'] === 'admin@gmail.com';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../user/dashboard.php');
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}


function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function calculateDays($checkIn, $checkOut) {
    $date1 = new DateTime($checkIn);
    $date2 = new DateTime($checkOut);
    $interval = $date1->diff($date2);
    return $interval->days;
}
?>
