<?php
session_start();
include 'koneksi.php';
 
if (!isset($_POST['username'], $_POST['password'], $_POST['role'], $_POST['csrf_token'])) {
    header("Location: index.php?pesan=gagal");
    exit;
}
 
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: index.php?pesan=gagal");
    exit;
}
 
$allowed_roles = ['pelanggan', 'admin'];
$role = trim($_POST['role']);
 
if (!in_array($role, $allowed_roles)) {
    header("Location: index.php?pesan=gagal");
    exit;
}
 
$username = trim($_POST['username']);
 
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ? AND role = ?");
mysqli_stmt_bind_param($stmt, "ss", $username, $role);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
 
if ($data && password_verify($_POST['password'], $data['password'])) {
    session_regenerate_id(true);
 
    $_SESSION['user_id']      = $data['id_users'];
    $_SESSION['username']     = $data['username'];
    $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
    $_SESSION['role']         = $data['role'];
    $_SESSION['status_login'] = true;
 
    if ($data['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: home.php");
    }
    exit;
} else {
    header("Location: index.php?pesan=gagal");
    exit;
}
?>
 