<?php
session_start();
include 'koneksi.php';

// Load Cloudinary
require_once 'vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

// Konfigurasi Cloudinary
Configuration::instance([
    'cloud' => [
        'cloud_name' => 'rocket', 
        'api_key'    => '215114387294939',   
        'api_secret' => 'IB_ETMM2mqL6aMdqXMsDLHlfrss',   
    ],
    'url' => [
        'secure' => true
    ]
]);

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

// Validasi MIME type
$allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo        = finfo_open(FILEINFO_MIME_TYPE);
$mime_type    = finfo_file($finfo, $_FILES['gambar']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_mime)) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}

// Validasi ukuran file
$max_size = 2 * 1024 * 1024;
if ($_FILES['gambar']['size'] > $max_size) {
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}

// Upload ke Cloudinary
try {
    $uploadApi  = new UploadApi();
    $uploadResult = $uploadApi->upload($_FILES['gambar']['tmp_name'], [
        'folder'          => 'menu_makanan',        // folder di Cloudinary
        'public_id'       => bin2hex(random_bytes(16)),
        'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'transformation'  => [
            ['width' => 800, 'height' => 800, 'crop' => 'limit'] // opsional: resize otomatis
        ]
    ]);

    // Ambil URL gambar dari Cloudinary
    $path_gambar = $uploadResult['secure_url'];

} catch (\Exception $e) {
    // Gagal upload ke Cloudinary
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}

// Simpan URL Cloudinary ke database
$stmt = mysqli_prepare($koneksi, "INSERT INTO menus (nama_makanan, deskripsi, harga, gambar, kategori, status) VALUES (?, ?, ?, ?, ?, 'tersedia')");
mysqli_stmt_bind_param($stmt, "ssiss", $nama_makanan, $deskripsi, $harga, $path_gambar, $kategori);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: manajemen_menu.php?status=sukses");
    exit;
} else {
    mysqli_stmt_close($stmt);
    header("Location: manajemen_menu.php?status=gagal");
    exit;
}
?>