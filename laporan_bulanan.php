<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

// Default: bulan & tahun sekarang
$bulan = date('m');
$tahun = date('Y');

if (!empty($_GET['bulan']) && preg_match('/^(0[1-9]|1[0-2])$/', $_GET['bulan'])) {
    $bulan = $_GET['bulan'];
}
if (!empty($_GET['tahun']) && preg_match('/^\d{4}$/', $_GET['tahun'])) {
    $tahun = $_GET['tahun'];
}

$safe_bulan = htmlspecialchars($bulan);
$safe_tahun = htmlspecialchars($tahun);

$nama_bulan_list = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$nama_bulan = $nama_bulan_list[$bulan] ?? '';

// ✅ JOIN pakai id_users, filter per bulan & tahun
$stmt = mysqli_prepare($koneksi, "SELECT orders.*, users.nama_lengkap 
                                   FROM orders 
                                   JOIN users ON orders.id_users = users.id_users 
                                   WHERE MONTH(orders.tanggal_pesan) = ? 
                                     AND YEAR(orders.tanggal_pesan) = ?
                                   ORDER BY orders.tanggal_pesan ASC");
mysqli_stmt_bind_param($stmt, "ss", $bulan, $tahun);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$rows = [];
$total_bulanan = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $total_bulanan += $row['subtotal']; // ✅ subtotal
    $rows[] = $row;
}
mysqli_stmt_close($stmt);

// Rekap per tanggal (opsional, untuk ringkasan harian dalam bulan tersebut)
$rekap_harian = [];
foreach ($rows as $row) {
    $tgl = date('Y-m-d', strtotime($row['tanggal_pesan']));
    if (!isset($rekap_harian[$tgl])) {
        $rekap_harian[$tgl] = ['jumlah_order' => 0, 'total' => 0];
    }
    $rekap_harian[$tgl]['jumlah_order'] += 1;
    $rekap_harian[$tgl]['total'] += $row['subtotal'];
}

// Untuk dropdown tahun (5 tahun ke belakang sampai tahun sekarang)
$tahun_sekarang = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        body { background: #f5f0e8; padding: 20px; font-family: Arial, sans-serif; margin: 0; }
        .container { background: white; padding: 28px; border-radius: 8px; max-width: 900px; margin: 0 auto; width: 95%; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .container h2 { color: #2d4a3e; margin-bottom: 20px; }
        .filter-form { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .filter-form label { font-size: 14px; color: #2d4a3e; font-weight: bold; }
        .filter-form select { padding: 7px 10px; border: 1.5px solid #b8c9b8; border-radius: 6px; font-size: 14px; color: #2d4a3e; outline: none; background: #fff; }
        .filter-form select:focus { border-color: #2d4a3e; }
        .btn-filter { padding: 7px 16px; background: #2d4a3e; color: #f0e8d0; border: none; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-filter:hover { background: #3b6b54; }
        .btn-print { padding: 7px 16px; background: #28a745; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-print:hover { background: #218838; }
        .table-wrapper { overflow-x: auto; margin-bottom: 24px; }
        .table-laporan { width: 100%; border-collapse: collapse; min-width: 500px; font-size: 14px; }
        .table-laporan th { background: #2d4a3e; color: #f0e8d0; padding: 11px 12px; text-align: left; border: 1px solid #ccc; }
        .table-laporan td { border: 1px solid #ccc; padding: 10px 12px; }
        .table-laporan tr:nth-child(even) td { background: #f5f0e8; }
        .table-laporan tr:hover td { background: #e8f0e8; }
        .row-total td { font-weight: bold; background: #f0e8d0 !important; }
        .text-right { text-align: right; }
        .empty-msg { color: #5a7a6a; padding: 16px 0; font-size: 14px; }
        .btn-back { display: inline-block; margin-top: 20px; color: #2d4a3e; font-weight: bold; font-size: 14px; text-decoration: none; }
        .btn-back:hover { text-decoration: underline; }
        h3.sub-judul { color: #2d4a3e; font-size: 16px; margin: 24px 0 10px; }
        @media (max-width: 600px) { .filter-form { flex-direction: column; align-items: flex-start; } }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Laporan Penjualan Bulanan</h2>

        <form method="GET" class="filter-form no-print">
            <label for="bulan">Bulan:</label>
            <select id="bulan" name="bulan">
                <?php foreach ($nama_bulan_list as $key => $label) : ?>
                    <option value="<?= $key ?>" <?= $key == $bulan ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <label for="tahun">Tahun:</label>
            <select id="tahun" name="tahun">
                <?php for ($y = $tahun_sekarang; $y >= $tahun_sekarang - 4; $y--) : ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <button type="submit" class="btn-filter">Filter</button>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak Laporan</button>
        </form>

        <?php if (empty($rows)) : ?>
            <p class="empty-msg">Tidak ada pesanan pada bulan <?= htmlspecialchars($nama_bulan) ?> <?= $safe_tahun ?>.</p>
        <?php else : ?>

            <!-- REKAP HARIAN DALAM BULAN -->
            <h3 class="sub-judul">Rekap Per Tanggal — <?= htmlspecialchars($nama_bulan) ?> <?= $safe_tahun ?></h3>
            <div class="table-wrapper">
                <table class="table-laporan">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Order</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap_harian as $tgl => $data) : ?>
                        <tr>
                            <td><?= date('d-m-Y', strtotime($tgl)) ?></td>
                            <td><?= $data['jumlah_order'] ?></td>
                            <td>Rp <?= number_format($data['total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- DETAIL SEMUA ORDER -->
            <h3 class="sub-judul">Detail Pesanan</h3>
            <div class="table-wrapper">
                <table class="table-laporan">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Tanggal</th>
                            <th>Nama Pelanggan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td>#<?= (int)$row['id_orders'] ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="row-total">
                            <td colspan="3" class="text-right">Total Omzet Bulan Ini:</td>
                            <td>Rp <?= number_format($total_bulanan, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <a href="admin.php" class="btn-back no-print">← Kembali ke Dashboard</a>
    </div>
</body>
</html>