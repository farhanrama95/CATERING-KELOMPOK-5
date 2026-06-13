<?php
require_once 'guard_pelanggan.php';

include 'koneksi.php';

if (empty($_SESSION['status_login']) || empty($_SESSION['keranjang'])) {
    header("Location: home.php");
    exit;
}

$user_id           = $_SESSION['user_id'];
$alamat            = $_POST['alamat'];
$metode_pembayaran = $_POST['metode_pembayaran'];

// 1. Hitung total harga (pakai prepared statement, kolom sesuai SQL: id_menus, harga)
$total_harga = 0;
foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
    $id_menu = (int)$id_menu;
    $jumlah  = (int)$jumlah;

    $stmt = mysqli_prepare($koneksi, "SELECT harga FROM menus WHERE id_menus = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_menu);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $m = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($m) {
        $total_harga += $m['harga'] * $jumlah;
    }
}

// 2. Upload bukti transfer (jika ada)
$bukti_transfer = NULL;
if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
    $folder = 'uploads/bukti/';
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ekstensi  = pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION);
    $nama_file = 'bukti_' . $user_id . '_' . time() . '.' . $ekstensi;
    $tujuan    = $folder . $nama_file;

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($ekstensi), $allowed)) {
        if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $tujuan)) {
            $bukti_transfer = $tujuan;
        }
    }
}

// 3. Simpan ke tabel orders
// Kolom disesuaikan dengan struktur SQL:
// orders(id_users, subtotal, status_pesanan, metode_pembayaran, alamat_pengiriman, bukti_transfer, tanggal_pesan)
$stmt_order = mysqli_prepare($koneksi, "INSERT INTO orders 
    (id_users, subtotal, status_pesanan, metode_pembayaran, alamat_pengiriman, bukti_transfer, tanggal_pesan) 
    VALUES (?, ?, 'menunggu pembayaran', ?, ?, ?, NOW())");

mysqli_stmt_bind_param(
    $stmt_order,
    "idsss",
    $user_id,
    $total_harga,
    $metode_pembayaran,
    $alamat,
    $bukti_transfer
);

mysqli_stmt_execute($stmt_order) or die("Error Query Order: " . mysqli_error($koneksi));
mysqli_stmt_close($stmt_order);

$order_id = mysqli_insert_id($koneksi);

// 4. Simpan detail pesanan
// Kolom disesuaikan dengan struktur SQL:
// order_details(id_orders, id_menus, jumlah, harga, subtotal)
foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
    $id_menu = (int)$id_menu;
    $jumlah  = (int)$jumlah;

    $stmt = mysqli_prepare($koneksi, "SELECT harga FROM menus WHERE id_menus = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_menu);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $menu   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$menu) continue;

    $harga    = (float)$menu['harga'];
    $subtotal = $harga * $jumlah;

    $stmt2 = mysqli_prepare($koneksi, "INSERT INTO order_details (id_orders, id_menus, jumlah, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt2, "iiidd", $order_id, $id_menu, $jumlah, $harga, $subtotal);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
}

unset($_SESSION['keranjang']);
header("Location: sukses.php");
exit;
?>