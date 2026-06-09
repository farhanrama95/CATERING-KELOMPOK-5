<?php
// guard_pelanggan.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['role'] !== 'pelanggan') {
    header('Location: admin.php');
    exit;
}
?>