<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status_login']) || empty($_SESSION['keranjang'])) {
    header("Location: home.php");
    exit;
}

$user_id           = $_SESSION['user_id'];
$alamat            = mysqli_real_escape_string($koneksi, $_POST['alamat']);
$metode_pembayaran = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);

// 1. Hitung total harga
$total_harga = 0;
foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
    $q = mysqli_query($koneksi, "SELECT harga FROM menus WHERE id='$id_menu'");
    $m = mysqli_fetch_assoc($q);
    $total_harga += $m['harga'] * $jumlah;
}

// 2. Upload bukti transfer (jika ada)
$bukti_transfer = NULL;
if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
    $folder = 'uploads/bukti/';
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ekstensi   = pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION);
    $nama_file  = 'bukti_' . $user_id . '_' . time() . '.' . $ekstensi;
    $tujuan     = $folder . $nama_file;

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($ekstensi), $allowed)) {
        if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $tujuan)) {
            $bukti_transfer = $tujuan;
        }
    }
}

// 3. Simpan ke tabel orders
// PASTIKAN kolom status_pesanan ada di tabel orders kamu
$bukti_db = $bukti_transfer ? "'$bukti_transfer'" : "NULL";
$query_order = "INSERT INTO orders (user_id, total_harga, metode_pembayaran, alamat_pengiriman, bukti_transfer, status_pesanan, tanggal_pesan) 
                VALUES ('$user_id', '$total_harga', '$metode_pembayaran', '$alamat', $bukti_db, 'menunggu pembayaran', NOW())";

mysqli_query($koneksi, $query_order) or die("Error Query Order: " . mysqli_error($koneksi));

$order_id = mysqli_insert_id($koneksi);
foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
    $id_menu = (int)$id_menu;
    $jumlah  = (int)$jumlah;
 
    $stmt = mysqli_prepare($koneksi, "SELECT harga FROM menus WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_menu);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $menu   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
 
    if (!$menu) continue;
 
    $harga    = (int)$menu['harga'];
    $subtotal = $harga * $jumlah;
 
    $stmt2 = mysqli_prepare($koneksi, "INSERT INTO order_details (order_id, menu_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt2, "iiiii", $order_id, $id_menu, $jumlah, $harga, $subtotal);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
}
 
unset($_SESSION['keranjang']);
header("Location: sukses.php");
exit;
?>
 