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
    header("Location: manajemen_menu.php");
    exit;
}

// FIXED: CSRF check, sama seperti form di manajemen_menu.php
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: manajemen_menu.php");
    exit;
}

// FIXED: Form mengirim 'id_menus', bukan 'id'
if (empty($_POST['id_menus']) || !ctype_digit(strval($_POST['id_menus']))) {
    header("Location: manajemen_menu.php");
    exit;
}

$id = (int) $_POST['id_menus'];

// FIXED: Kolom di tabel menus adalah id_menus, bukan id
$stmt = mysqli_prepare($koneksi, "DELETE FROM menus WHERE id_menus = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: manajemen_menu.php");
exit;
?>