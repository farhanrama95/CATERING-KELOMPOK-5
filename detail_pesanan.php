<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

if (empty($_GET['id']) || !ctype_digit(strval($_GET['id']))) {
    header("Location: admin.php");
    exit;
}

$order_id = (int)$_GET['id'];

// ✅ JOIN pakai id_users dan id_orders
$stmt = mysqli_prepare($koneksi, "SELECT orders.*, users.nama_lengkap 
                                   FROM orders 
                                   JOIN users ON orders.id_users = users.id_users 
                                   WHERE orders.id_orders = ?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order  = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    header("Location: admin.php");
    exit;
}

$safe_order_id = (int)$order['id_orders'];
$safe_nama     = htmlspecialchars($order['nama_lengkap']);
$safe_alamat   = htmlspecialchars($order['alamat_pengiriman']);
$safe_total    = number_format($order['subtotal'], 0, ',', '.'); // ✅ subtotal
$safe_metode   = htmlspecialchars($order['metode_pembayaran'] ?? '-');
$safe_status   = htmlspecialchars($order['status_pesanan'] ?? '-');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $safe_order_id ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        body { background: #f5f0e8; padding: 20px; font-family: Arial, sans-serif; margin: 0; }
        .container { background: white; padding: 28px; border-radius: 8px; max-width: 800px; margin: 0 auto; width: 95%; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .nota-header { text-align: center; margin-bottom: 20px; }
        .nota-header h2 { margin: 0; font-size: 22px; }
        .nota-header p  { margin: 4px 0 0; color: #555; font-size: 14px; }
        .nota-header hr { border: 1px solid #333; margin-top: 12px; }
        .info-pesanan { margin-bottom: 16px; }
        .info-pesanan p { margin: 6px 0; font-size: 14px; color: #2d4a3e; }
        .table-detail { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        .table-detail thead tr { background: #2d4a3e; color: white; }
        .table-detail th, .table-detail td { border: 1px solid #ccc; padding: 10px 12px; text-align: left; }
        .table-detail tfoot tr { font-weight: bold; background: #f9f9f9; }
        .table-detail .text-right { text-align: right; }
        .table-wrapper { overflow-x: auto; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .btn-print { padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 14px; font-weight: bold; }
        .btn-print:hover { background: #218838; }
        .btn-back { padding: 10px 20px; background: #6c757d; color: white; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; }
        .btn-back:hover { background: #5a6268; }
        @media print {
            .btn-group { display: none !important; }
            body { background: white; padding: 0; }
            .container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="nota-header">
        <h2>NOTA PESANAN</h2>
        <p>Chandra Catering - Sistem Pemesanan</p>
        <hr>
    </div>

    <div class="info-pesanan">
        <p><strong>Order ID:</strong> #<?= $safe_order_id ?></p>
        <p><strong>Pelanggan:</strong> <?= $safe_nama ?></p>
        <p><strong>Alamat:</strong> <?= $safe_alamat ?></p>
        <p><strong>Metode Pembayaran:</strong> <?= $safe_metode ?></p>
        <p><strong>Status:</strong> <?= $safe_status ?></p>
        <p><strong>Tanggal Cetak:</strong> <?= date('d M Y, H:i') ?></p>
    </div>

    <div class="table-wrapper">
        <table class="table-detail">
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ✅ JOIN pakai id_menus dan id_orders, ambil nama_menu
                $stmt2 = mysqli_prepare($koneksi, "SELECT order_details.*, menus.nama_menu 
                                                    FROM order_details 
                                                    JOIN menus ON order_details.id_menus = menus.id_menus 
                                                    WHERE order_details.id_orders = ?");
                mysqli_stmt_bind_param($stmt2, "i", $order_id);
                mysqli_stmt_execute($stmt2);
                $result2 = mysqli_stmt_get_result($stmt2);

                if (!$result2) {
                    echo "<tr><td colspan='4' style='color:#c0392b;'>Gagal memuat detail: " . htmlspecialchars(mysqli_error($koneksi)) . "</td></tr>";
                } else {
                    while ($d = mysqli_fetch_assoc($result2)) {
                        $nama_menu = htmlspecialchars($d['nama_menu']);        // ✅ nama_menu
                        $jumlah    = (int)$d['jumlah'];
                        $harga_sat = number_format($d['harga'], 0, ',', '.'); // ✅ harga
                        $subtotal  = number_format($d['subtotal'], 0, ',', '.');
                ?>
                <tr>
                    <td><?= $nama_menu ?></td>
                    <td><?= $jumlah ?></td>
                    <td>Rp <?= $harga_sat ?></td>
                    <td>Rp <?= $subtotal ?></td>
                </tr>
                <?php
                    }
                }
                mysqli_stmt_close($stmt2);
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Total Keseluruhan:</td>
                    <td>Rp <?= $safe_total ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="btn-group">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Nota</button>
        <a href="admin.php" class="btn-back">← Kembali</a>
    </div>
</div>
</body>
</html>