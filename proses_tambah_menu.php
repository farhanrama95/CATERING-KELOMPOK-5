<?php
session_start();
include 'koneksi.php';
 
if (!isset($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}
 
if (!isset($_POST['nama_makanan'], $_POST['deskripsi'], $_POST['harga'], $_POST['kategori'])) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
 
if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
 
$nama_makanan = trim($_POST['nama_makanan']);
$deskripsi    = trim($_POST['deskripsi']);
$harga        = (int)$_POST['harga'];
$kategori     = trim($_POST['kategori']);
 
$allowed_kategori = ['Makanan', 'Minuman', 'Cemilan'];
if (!in_array($kategori, $allowed_kategori)) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
 
if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
 
$allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo        = finfo_open(FILEINFO_MIME_TYPE);
$mime_type    = finfo_file($finfo, $_FILES['gambar']['tmp_name']);
finfo_close($finfo);
 
if (!in_array($mime_type, $allowed_mime)) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
 
$max_size = 2 * 1024 * 1024;
if ($_FILES['gambar']['size'] > $max_size) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
 
$ext         = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
$nama_file   = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
$path_gambar = "uploads/" . $nama_file;
 
if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $path_gambar)) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
 
$stmt = mysqli_prepare($koneksi, "INSERT INTO menus (nama_makanan, deskripsi, harga, gambar, kategori, status) VALUES (?, ?, ?, ?, ?, 'tersedia')");
mysqli_stmt_bind_param($stmt, "ssiss", $nama_makanan, $deskripsi, $harga, $path_gambar, $kategori);
 
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: manajemen_menu.php?status=sukses");
    exit;
} else {
    mysqli_stmt_close($stmt);
    if (file_exists($path_gambar)) {
        unlink($path_gambar);
    }
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
?>
 