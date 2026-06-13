<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: admin.php");
    exit;
}

if (!isset($_POST['order_id'], $_POST['status_pesanan'])) {
    header("Location: admin.php");
    exit;
}

$order_id = (int)$_POST['order_id'];

// ✅ Harus match persis dengan nilai ENUM di tabel orders
$allowed_status = ['menunggu pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
$status_baru    = trim($_POST['status_pesanan']);

if (!in_array($status_baru, $allowed_status)) {
    header("Location: admin.php");
    exit;
}

// ✅ WHERE id_orders (bukan id)
$stmt = mysqli_prepare($koneksi, "UPDATE orders SET status_pesanan = ? WHERE id_orders = ?");
mysqli_stmt_bind_param($stmt, "si", $status_baru, $order_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: admin.php");
exit;
?>