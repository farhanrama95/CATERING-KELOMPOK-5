<?php
session_start();
include 'koneksi.php';
 
if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}
 
$tanggal = date('Y-m-d');
if (!empty($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];
}
 
$safe_tanggal = htmlspecialchars($tanggal);
 
$stmt = mysqli_prepare($koneksi, "SELECT orders.*, users.nama_lengkap 
                                   FROM orders 
                                   JOIN users ON orders.user_id = users.id 
                                   WHERE DATE(orders.tanggal_pesan) = ?
                                   ORDER BY orders.tanggal_pesan DESC");
mysqli_stmt_bind_param($stmt, "s", $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
 
$rows = [];
$total_harian = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $total_harian += $row['total_harga'];
    $rows[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        body {
            background: #f5f0e8;
            padding: 20px;
            font-family: Arial, sans-serif;
            margin: 0;
        }
 
        .container {
            background: white;
            padding: 28px;
            border-radius: 8px;
            max-width: 900px;
            margin: 0 auto;
            width: 95%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
 
        .container h2 {
            color: #2d4a3e;
            margin-bottom: 20px;
        }
 
        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
 
        .filter-form label {
            font-size: 14px;
            color: #2d4a3e;
            font-weight: bold;
        }
 
        .filter-form input[type="date"] {
            padding: 7px 10px;
            border: 1.5px solid #b8c9b8;
            border-radius: 6px;
            font-size: 14px;
            color: #2d4a3e;
            outline: none;
        }
 
        .filter-form input[type="date"]:focus {
            border-color: #2d4a3e;
        }
 
        .btn-filter {
            padding: 7px 16px;
            background: #2d4a3e;
            color: #f0e8d0;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-filter:hover { background: #3b6b54; }
 
        .btn-print {
            padding: 7px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #218838; }
 
        .table-wrapper { overflow-x: auto; }
 
        .table-laporan {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
            font-size: 14px;
        }
 
        .table-laporan th {
            background: #2d4a3e;
            color: #f0e8d0;
            padding: 11px 12px;
            text-align: left;
            border: 1px solid #ccc;
        }
 
        .table-laporan td {
            border: 1px solid #ccc;
            padding: 10px 12px;
        }
 
        .table-laporan tr:nth-child(even) td {
            background: #f5f0e8;
        }
 
        .table-laporan tr:hover td {
            background: #e8f0e8;
        }
 
        .row-total td {
            font-weight: bold;
            background: #f0e8d0 !important;
        }
 
        .text-right { text-align: right; }
 
        .empty-msg {
            color: #5a7a6a;
            padding: 16px 0;
            font-size: 14px;
        }
 
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            color: #2d4a3e;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-back:hover { text-decoration: underline; }
 
        @media (max-width: 600px) {
            .filter-form { flex-direction: column; align-items: flex-start; }
        }
 
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Laporan Penjualan Harian</h2>
 
        <form method="GET" class="filter-form no-print">
            <label for="tanggal">Pilih Tanggal:</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= $safe_tanggal ?>">
            <button type="submit" class="btn-filter">Filter</button>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak Laporan</button>
        </form>
 
        <?php if (empty($rows)) : ?>
            <p class="empty-msg">Tidak ada pesanan pada tanggal <?= $safe_tanggal ?>.</p>
        <?php else : ?>
            <div class="table-wrapper">
                <table class="table-laporan">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Nama Pelanggan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td>#<?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="row-total">
                            <td colspan="2" class="text-right">Total Omzet Hari Ini:</td>
                            <td>Rp <?= number_format($total_harian, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
 
        <a href="admin.php" class="btn-back no-print">← Kembali ke Dashboard</a>
    </div>
</body>
</html>
 