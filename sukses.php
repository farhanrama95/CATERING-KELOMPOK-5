<?php
session_start();
include 'koneksi.php';
 
if (!isset($_SESSION['status_login'])) {
    header("Location: index.php");
    exit;
}
 
$user_id = (int)$_SESSION['user_id'];
 
$stmt = mysqli_prepare($koneksi, "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order  = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
 
if (!$order) {
    header("Location: home.php");
    exit;
}
 
$order_id = (int)$order['id'];
 
$stmt2 = mysqli_prepare($koneksi, "SELECT od.*, m.nama_makanan FROM order_details od JOIN menus m ON od.menu_id = m.id WHERE od.order_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $order_id);
mysqli_stmt_execute($stmt2);
$detail_result = mysqli_stmt_get_result($stmt2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        body {
            background-color: #2d4a3e;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 30px 16px;
            box-sizing: border-box;
        }
        .wrapper {
            width: 100%;
            max-width: 420px;
        }
        .sukses-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .sukses-header .icon { font-size: 60px; }
        .sukses-header h2 { color: #f0e8d0; font-size: 20px; margin: 10px 0 4px; }
        .sukses-header p { color: rgba(240,232,208,0.7); font-size: 13px; }
        .struk {
            background: #fff;
            border-radius: 4px 4px 0 0;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .struk-header {
            background: #003f82;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .struk-header .bca-logo {
            background: #fff;
            color: #003f82;
            font-size: 16px;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 6px;
            letter-spacing: 1px;
        }
        .struk-header .bca-info { color: white; }
        .struk-header .bca-info .title { font-size: 14px; font-weight: bold; }
        .struk-header .bca-info .sub { font-size: 11px; opacity: 0.75; margin-top: 2px; }
        .status-bar {
            background: #e8f5e9;
            border-bottom: 1px solid #c8e6c9;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #2e7d32;
            font-weight: bold;
        }
        .struk-body { padding: 18px 20px; }
        .struk-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 7px 0;
            border-bottom: 1px dashed #eee;
            font-size: 13px;
        }
        .struk-row:last-child { border-bottom: none; }
        .struk-row .label { color: #888; flex: 1; }
        .struk-row .value { color: #222; font-weight: bold; text-align: right; flex: 1.2; }
        .divider {
            border: none;
            border-top: 2px dashed #ddd;
            margin: 14px 0;
        }
        .item-list { margin: 10px 0; }
        .item-list .item-header {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #aaa;
            padding-bottom: 6px;
            border-bottom: 1px solid #eee;
            margin-bottom: 6px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            padding: 4px 0;
            color: #444;
        }
        .item-row .item-nama { flex: 2; }
        .item-row .item-qty { flex: 0.5; text-align: center; color: #888; }
        .item-row .item-sub { flex: 1; text-align: right; font-weight: bold; color: #2d4a3e; }
        .total-box {
            background: #003f82;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 14px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-box .total-label { color: rgba(255,255,255,0.8); font-size: 13px; }
        .total-box .total-nilai { color: #fff; font-size: 20px; font-weight: bold; }
        .struk-footer {
            text-align: center;
            padding: 12px 20px 18px;
            background: #f9f9f9;
            border-top: 1px dashed #ddd;
        }
        .struk-footer .no-order { font-size: 11px; color: #aaa; margin-bottom: 6px; }
        .struk-footer .tanggal { font-size: 11px; color: #aaa; }
        .struk-footer .terima-kasih { font-size: 13px; color: #003f82; font-weight: bold; margin-top: 8px; }
        .struk-zigzag {
            height: 16px;
            background: linear-gradient(135deg, #fff 25%, transparent 25%) -10px 0,
                        linear-gradient(225deg, #fff 25%, transparent 25%) -10px 0,
                        linear-gradient(315deg, #fff 25%, transparent 25%),
                        linear-gradient(45deg,  #fff 25%, transparent 25%);
            background-size: 20px 20px;
            background-color: #2d4a3e;
        }
        .btn-group { margin-top: 20px; display: flex; gap: 10px; }
        .btn-home {
            background-color: #f0e8d0;
            color: #2d4a3e;
            padding: 12px;
            text-decoration: none;
            border-radius: 8px;
            display: block;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            flex: 1;
            transition: background 0.2s;
        }
        .btn-home:hover { background-color: #fff; }
        .btn-print {
            background-color: #003f82;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            display: block;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            flex: 1;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background-color: #0056b3; }
        @media print {
            body { background: white; padding: 0; }
            .btn-group { display: none; }
            .sukses-header { display: none; }
            .struk-zigzag { display: none; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
 
        <div class="sukses-header">
            <div class="icon">✅</div>
            <h2>Pembayaran Berhasil!</h2>
            <p>Struk transaksi kamu ada di bawah ini</p>
        </div>
 
        <div class="struk">
 
            <div class="struk-header">
                <div class="bca-logo">BCA</div>
                <div class="bca-info">
                    <div class="title">KlikBCA / m-BCA</div>
                    <div class="sub">Bukti Pembayaran Transfer</div>
                </div>
            </div>
 
            <div class="status-bar">
                ✅ Transaksi Berhasil
            </div>
 
            <div class="struk-body">
 
                <div class="struk-row">
                    <span class="label">No. Pesanan</span>
                    <span class="value">#<?= str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="struk-row">
                    <span class="label">Tanggal</span>
                    <span class="value"><?= htmlspecialchars(date('d M Y, H:i', strtotime($order['tanggal_pesan']))); ?> WIB</span>
                </div>
                <div class="struk-row">
                    <span class="label">Kepada</span>
                    <span class="value">Chandra Catering</span>
                </div>
                <div class="struk-row">
                    <span class="label">Metode</span>
                    <span class="value"><?= htmlspecialchars($order['metode_pembayaran']); ?></span>
                </div>
                <div class="struk-row">
                    <span class="label">Alamat Kirim</span>
                    <span class="value" style="font-size:12px;"><?= htmlspecialchars($order['alamat_pengiriman']); ?></span>
                </div>
 
                <hr class="divider">
 
                <div style="font-size:12px; color:#888; margin-bottom:8px; font-weight:bold;">DETAIL PESANAN</div>
                <div class="item-list">
                    <div class="item-header">
                        <span style="flex:2;">Menu</span>
                        <span style="flex:0.5; text-align:center;">Qty</span>
                        <span style="flex:1; text-align:right;">Subtotal</span>
                    </div>
                    <?php while ($item = mysqli_fetch_assoc($detail_result)) : ?>
                    <div class="item-row">
                        <span class="item-nama"><?= htmlspecialchars($item['nama_makanan']); ?></span>
                        <span class="item-qty"><?= (int)$item['jumlah']; ?>x</span>
                        <span class="item-sub">Rp <?= number_format((int)$item['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
 
                <div class="total-box">
                    <span class="total-label">Total Pembayaran</span>
                    <span class="total-nilai">Rp <?= number_format((int)$order['total_harga'], 0, ',', '.'); ?></span>
                </div>
 
            </div>
 
            <div class="struk-footer">
                <div class="no-order">No. Ref: BCA<?= htmlspecialchars(date('YmdHis', strtotime($order['tanggal_pesan']))); ?></div>
                <div class="tanggal"><?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($order['tanggal_pesan']))); ?></div>
                <div class="terima-kasih">Terima Kasih Telah Memesan 🙏</div>
            </div>
        </div>
 
        <div class="struk-zigzag"></div>
 
        <div class="btn-group">
            <a href="home.php" class="btn-home">🏠 Pesan Lagi</a>
            <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
        </div>
 
    </div>
</body>
</html>
<?php mysqli_stmt_close($stmt2); ?>