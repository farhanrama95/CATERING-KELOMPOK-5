<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'guard_pelanggan.php';
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: index.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan - Pemesanan Makanan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        body { background-color: #f5f0e8; margin: 0; font-family: Arial, sans-serif; }
        .navbar {
            background-color: #2d4a3e;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .logo { color: #f0e8d0; font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .navbar .menu { display: flex; align-items: center; gap: 10px; }
        .navbar .menu a { color: #f0e8d0; text-decoration: none; font-size: 14px; margin-right: 15px; }
        .navbar .menu a:hover { color: #fff; }
        .navbar .menu span { color: #f0e8d0; font-size: 14px; font-weight: bold; }
        .container { padding: 24px 30px; }
        .container h2 { color: #2d4a3e; font-size: 22px; margin-bottom: 16px; }
        .riwayat-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(45,74,62,0.12);
            border: 1px solid #d6e8d6;
            overflow-x: auto;
        }
        .table-riwayat { width: 100%; border-collapse: collapse; }
        .table-riwayat th, .table-riwayat td { padding: 12px 15px; border: 1px solid #d6e8d6; text-align: left; font-size: 14px; }
        .table-riwayat th { background-color: #2d4a3e; color: #f0e8d0; font-weight: bold; }
        .table-riwayat tr:nth-child(even) { background-color: #f5f0e8; }
        .table-riwayat tr:hover { background-color: #e8f0e8; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
        .status-menunggu  { background-color: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .status-diproses  { background-color: #d1ecf1; color: #0c5460; border: 1px solid #17a2b8; }
        .status-dikirim   { background-color: #cce5ff; color: #004085; border: 1px solid #007bff; }
        .status-selesai   { background-color: #d4edda; color: #155724; border: 1px solid #28a745; }
        .status-dibatalkan{ background-color: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">Chandra Catering</div>
    <div class="menu">
        <a href="home.php">🏠 Kembali ke Beranda</a>
        <span>Halo, <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
    </div>
</nav>

<div class="container">
    <h2>📜 Riwayat Pesananku</h2>
    <div class="riwayat-container">
        <table class="table-riwayat">
            <thead>
                <tr>
                    <th>No Order</th>
                    <th>Tanggal</th>
                    <th>Total Belanja</th>
                    <th>Metode Pembayaran</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ✅ Prepared statement + kolom id_users
                $stmt = mysqli_prepare($koneksi, "SELECT * FROM orders WHERE id_users = ? ORDER BY tanggal_pesan DESC");
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status_class = 'status-menunggu';
                        if ($row['status_pesanan'] == 'diproses')   $status_class = 'status-diproses';
                        if ($row['status_pesanan'] == 'dikirim')    $status_class = 'status-dikirim';
                        if ($row['status_pesanan'] == 'selesai')    $status_class = 'status-selesai';
                        if ($row['status_pesanan'] == 'dibatalkan') $status_class = 'status-dibatalkan';
                ?>
                <tr>
                    <td>#<?= (int)$row['id_orders'] ?></td>    <!-- ✅ id_orders -->
                    <td><?= date('d M Y, H:i', strtotime($row['tanggal_pesan'])) ?></td>
                    <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>  <!-- ✅ subtotal -->
                    <td><?= htmlspecialchars($row['metode_pembayaran']) ?></td>
                    <td><span class="badge <?= $status_class ?>"><?= strtoupper(htmlspecialchars($row['status_pesanan'])) ?></span></td>
                </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; color:#5a7a6a; padding:20px;'>Kamu belum pernah memesan makanan.</td></tr>";
                }
                mysqli_stmt_close($stmt);
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>