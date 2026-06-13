<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';

// FIXED: Use empty() for stricter session check
if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

// FIXED: Only accept POST requests — GET requests cannot safely perform deletions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php");
    exit;
}

// FIXED: CSRF check, sama seperti form di admin.php
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: admin.php");
    exit;
}

// FIXED: Validate that order_id exists and is a positive integer
if (empty($_POST['order_id']) || !ctype_digit(strval($_POST['order_id']))) {
    header("Location: admin.php");
    exit;
}

$id = (int) $_POST['order_id'];

// FIXED: Kolom di tabel orders adalah id_orders, bukan id
$stmt = mysqli_prepare($koneksi, "DELETE FROM orders WHERE id_orders = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: admin.php?pesan=berhasil_hapus");
} else {
    // FIXED: Log error server-side instead of exposing it to the browser
    error_log("Gagal hapus order #$id: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    header("Location: admin.php?pesan=gagal_hapus");
}
exit;
?>