<?php
session_start();
include 'koneksi.php';
 
if (!isset($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}
 
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: admin.php");
    exit;
}
 
if (!isset($_POST['order_id'], $_POST['status_pesanan'])) {
    header("Location: admin.php");
    exit;
}
 
$order_id  = (int)$_POST['order_id'];
$allowed_status = ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
$status_baru = trim($_POST['status_pesanan']);
 
if (!in_array($status_baru, $allowed_status)) {
    header("Location: admin.php");
    exit;
}
 
$stmt = mysqli_prepare($koneksi, "UPDATE orders SET status_pesanan = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $status_baru, $order_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
 
header("Location: admin.php");
exit;
?>
 