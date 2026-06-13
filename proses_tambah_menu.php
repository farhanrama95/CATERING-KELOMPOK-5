<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

// ✅ Cek field nama_menu (bukan nama_makanan)
if (!isset($_POST['nama_menu'], $_POST['deskripsi'], $_POST['harga'], $_POST['kategori'])) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}

if (!isset($_SESSION['csrf_token'], $_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}

$nama_menu = trim($_POST['nama_menu']); // ✅ nama_menu
$deskripsi = trim($_POST['deskripsi']);
$harga     = (int)$_POST['harga'];
$kategori  = trim($_POST['kategori']);

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

if ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}

// ✅ Upload lokal ke folder uploads/ (sesuai pola data yang sudah ada di DB)
$folder = 'uploads/';
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$ekstensi  = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
$nama_file = time() . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($_FILES['gambar']['name'], PATHINFO_FILENAME)) . '.' . $ekstensi;
$tujuan    = $folder . $nama_file;

if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}

$path_gambar = $tujuan;

// ✅ INSERT pakai nama_menu (bukan nama_makanan)
$stmt = mysqli_prepare($koneksi, "INSERT INTO menus (nama_menu, deskripsi, harga, gambar, kategori, status) VALUES (?, ?, ?, ?, ?, 'tersedia')");
mysqli_stmt_bind_param($stmt, "ssiss", $nama_menu, $deskripsi, $harga, $path_gambar, $kategori);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: manajemen_menu.php?status=sukses");
} else {
    mysqli_stmt_close($stmt);
    header("Location: manajemen_menu.php?status=gagal");
}
exit;
?>