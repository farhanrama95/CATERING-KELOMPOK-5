Hapus menu · PHP
<?php
session_start();
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
 
// FIXED: Validate that id exists and is a positive integer
if (empty($_POST['id']) || !ctype_digit(strval($_POST['id']))) {
    header("Location: manajemen_menu.php");
    exit;
}
 
$id = (int) $_POST['id'];
 
// FIXED: Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($koneksi, "DELETE FROM menus WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
 
header("Location: manajemen_menu.php");
exit;
?>
