<?php
require_once 'guard_pelanggan.php';

session_start();
include 'koneksi.php';
 
// FIXED: Use empty() for stricter session check
if (empty($_SESSION['status_login']) || empty($_SESSION['keranjang'])) {
    header("Location: home.php");
    exit;
}
 
$total_belanja = 0;
foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
    // FIXED: SQL injection — use prepared statement instead of string interpolation
    $stmt = mysqli_prepare($koneksi, "SELECT harga FROM menus WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_menu);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $m = mysqli_fetch_assoc($result);
    if ($m) {
        $total_belanja += $m['harga'] * $jumlah;
    }
    mysqli_stmt_close($stmt);
}
 
$total_fmt = number_format($total_belanja, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Chandra Catering</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        body { background-color: #f5f0e8; margin: 0; font-family: Arial, sans-serif; }
 
        .navbar { background-color: #2d4a3e; padding: 14px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #f0e8d0; font-size: 20px; font-weight: bold; }
        .navbar .menu a { color: #f0e8d0; text-decoration: none; font-size: 14px; font-weight: bold; }
 
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 10px; padding: 14px 20px; text-align: center; }
        }
 
        .container { padding: 24px 30px; max-width: 750px; margin: 0 auto; width: 95%; }
        .container h2 { color: #2d4a3e; font-size: 22px; margin-bottom: 16px; }
 
        .checkout-box { background: #fff; padding: 24px; border-radius: 10px; box-shadow: 0 2px 8px rgba(45,74,62,0.12); border: 1px solid #d6e8d6; margin-bottom: 20px; }
 
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: bold; color: #3b5a48; margin-bottom: 6px; }
        .form-group textarea {
            width: 100%; padding: 10px 12px; border: 1.5px solid #b8c9b8;
            border-radius: 8px; height: 80px; font-size: 14px; color: #2d4a3e;
            outline: none; box-sizing: border-box; resize: vertical; font-family: Arial, sans-serif;
        }
        .form-group textarea:focus { border-color: #2d4a3e; }
 
        .metode-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 8px; }
 
        @media (max-width: 480px) {
            .metode-grid { grid-template-columns: repeat(2, 1fr); }
        }
 
        .metode-card { border: 2px solid #d6e8d6; border-radius: 10px; padding: 14px 10px; text-align: center; cursor: pointer; transition: all 0.2s; background: #fff; }
        .metode-card:hover { border-color: #2d4a3e; background: #f0ede4; }
        .metode-card.aktif { border-color: #2d4a3e; background: #e8f0e8; box-shadow: 0 0 0 3px rgba(45,74,62,0.15); }
        .metode-card .icon { font-size: 28px; margin-bottom: 6px; }
        .metode-card .nama { font-size: 12px; font-weight: bold; color: #2d4a3e; }
 
        .panel-bayar { display: none; margin-top: 20px; border-radius: 12px; overflow: hidden; }
        .panel-bayar.aktif { display: block; }
 
        .panel-cod { background: #2d4a3e; padding: 28px; color: #f0e8d0; border-radius: 12px; text-align: center; }
        .panel-cod .big-icon { font-size: 56px; margin-bottom: 12px; }
        .panel-cod h3 { font-size: 20px; margin-bottom: 8px; }
        .panel-cod p { font-size: 14px; opacity: 0.85; line-height: 1.7; }
        .panel-cod .total-cod { font-size: 26px; font-weight: bold; margin: 16px 0; color: #fff; }
 
        .panel-bank { background: #fff; border: 2px solid #d6e8d6; border-radius: 12px; overflow: hidden; }
        .panel-bank .bank-header { padding: 18px 24px; display: flex; align-items: center; gap: 14px; }
        .panel-bank .bank-logo { width: 56px; height: 56px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; color: white; }
        .logo-bca { background: #003f82; }
        .logo-bri { background: #003399; }
        .panel-bank .bank-name { font-size: 18px; font-weight: bold; }
        .panel-bank .bank-sub { font-size: 12px; color: #7a8c7a; }
        .panel-bank .bank-body { padding: 0 24px 24px; }
        .panel-bank .label-rek { font-size: 12px; color: #7a8c7a; margin-bottom: 4px; }
        .panel-bank .nomor-rek { font-size: 26px; font-weight: bold; color: #2d4a3e; letter-spacing: 3px; margin-bottom: 4px; }
        .panel-bank .atas-nama { font-size: 13px; color: #5a7a6a; margin-bottom: 16px; }
        .panel-bank .total-row { background: #f5f0e8; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .panel-bank .total-row span { font-size: 13px; color: #5a7a6a; }
        .panel-bank .total-row strong { font-size: 18px; color: #2d4a3e; }
        .btn-salin { background: #2d4a3e; color: #f0e8d0; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: bold; width: 100%; transition: background 0.2s; }
        .btn-salin:hover { background: #3b6b54; }
        .panel-bank .catatan { font-size: 12px; color: #7a8c7a; margin-top: 10px; text-align: center; }
 
        .panel-ewallet { border-radius: 12px; overflow: hidden; }
        .ewallet-header { padding: 22px 24px; display: flex; align-items: center; gap: 14px; }
        .ewallet-logo { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; font-weight: bold; }
        .logo-gopay { background: #00aa5b; }
        .logo-ovo { background: #4c2a86; }
        .logo-spay { background: #ee4d2d; }
        .ewallet-name { font-size: 18px; font-weight: bold; }
        .ewallet-sub { font-size: 12px; opacity: 0.7; }
        .ewallet-body { background: #fff; padding: 0 24px 24px; }
        .ewallet-body .label-no { font-size: 12px; color: #7a8c7a; margin-bottom: 4px; margin-top: 16px; }
        .ewallet-body .nomor-hp { font-size: 26px; font-weight: bold; letter-spacing: 2px; color: #2d4a3e; margin-bottom: 4px; }
        .ewallet-body .atas-nama { font-size: 13px; color: #5a7a6a; margin-bottom: 16px; }
        .ewallet-body .total-row { background: #f5f0e8; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .ewallet-body .total-row span { font-size: 13px; color: #5a7a6a; }
        .ewallet-body .total-row strong { font-size: 18px; color: #2d4a3e; }
        .ewallet-body .catatan { font-size: 12px; color: #7a8c7a; text-align: center; margin-top: 10px; }
 
        .upload-bukti {
            display: none; margin-top: 16px; background: #f5f0e8;
            border: 2px dashed #b8c9b8; border-radius: 10px; padding: 20px; text-align: center;
        }
        .upload-bukti.aktif { display: block; }
        .upload-bukti label { display: block; font-size: 13px; font-weight: bold; color: #3b5a48; margin-bottom: 10px; }
        .upload-bukti input[type="file"] { display: none; }
        .btn-upload { background: #fff; border: 2px solid #2d4a3e; color: #2d4a3e; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: bold; transition: all 0.2s; display: inline-block; }
        .btn-upload:hover { background: #2d4a3e; color: #f0e8d0; }
        .preview-bukti { margin-top: 14px; display: none; }
        .preview-bukti img { max-width: 100%; max-height: 220px; border-radius: 8px; border: 2px solid #d6e8d6; object-fit: contain; }
        .nama-file { font-size: 12px; color: #5a7a6a; margin-top: 6px; }
 
        .notif-salin { display: none; color: #2d4a3e; font-size: 12px; text-align: center; margin-top: 6px; font-weight: bold; }
 
        .btn-pesan { width: 100%; padding: 13px; background: #2d4a3e; color: #f0e8d0; border: none; border-radius: 8px; margin-top: 8px; cursor: pointer; font-size: 15px; font-weight: bold; transition: background 0.2s; }
        .btn-pesan:hover { background: #3b6b54; }
    </style>
</head>
<body>
 
<nav class="navbar">
    <div class="logo">Chandra Catering</div>
    <div class="menu"><a href="keranjang.php">⬅️ Kembali ke Keranjang</a></div>
</nav>
 
<!-- FIXED: Removed premature </div> that was closing .container before the form -->
<div class="container">
    <h2>Penyelesaian Pesanan</h2>
 
    <form action="proses_checkout.php" method="POST" enctype="multipart/form-data">
 
        <div class="checkout-box">
            <div class="form-group">
                <label>📍 Alamat Pengiriman Lengkap</label>
                <textarea name="alamat" required placeholder="Masukkan alamat lengkap tujuan pengiriman..."></textarea>
            </div>
        </div>
 
        <div class="checkout-box">
            <div class="form-group">
                <label>💳 Pilih Metode Pembayaran</label>
                <div class="metode-grid">
                    <!-- FIXED: Pass event explicitly instead of relying on implicit global -->
                    <div class="metode-card" onclick="pilihMetode(event, 'COD', false)">
                        <div class="icon">💵</div><div class="nama">COD</div>
                    </div>
                    <div class="metode-card" onclick="pilihMetode(event, 'Transfer BCA', true)">
                        <div class="icon">🏦</div><div class="nama">Transfer BCA</div>
                    </div>
                    <div class="metode-card" onclick="pilihMetode(event, 'Transfer BRI', true)">
                        <div class="icon">🏦</div><div class="nama">Transfer BRI</div>
                    </div>
                    <div class="metode-card" onclick="pilihMetode(event, 'GoPay', true)">
                        <div class="icon">💚</div><div class="nama">GoPay</div>
                    </div>
                    <div class="metode-card" onclick="pilihMetode(event, 'OVO', true)">
                        <div class="icon">💜</div><div class="nama">OVO</div>
                    </div>
                    <div class="metode-card" onclick="pilihMetode(event, 'ShopeePay', true)">
                        <div class="icon">🧡</div><div class="nama">ShopeePay</div>
                    </div>
                </div>
                <input type="hidden" name="metode_pembayaran" id="metode_input" required>
 
                <!-- PANEL COD -->
                <div class="panel-bayar" id="panel-COD">
                    <div class="panel-cod">
                        <div class="big-icon">🛵</div>
                        <h3>Cash on Delivery</h3>
                        <p>Bayar langsung ke kurir saat pesanan tiba.<br>Siapkan uang pas ya!</p>
                        <div class="total-cod">Rp <?= $total_fmt ?></div>
                        <p style="font-size:12px; opacity:0.7;">Total yang harus dibayar ke kurir</p>
                    </div>
                </div>
 
                <!-- PANEL BCA -->
                <div class="panel-bayar" id="panel-Transfer BCA">
                    <div class="panel-bank">
                        <div class="bank-header" style="background:#e8f0ff;">
                            <div class="bank-logo logo-bca">BCA</div>
                            <div>
                                <div class="bank-name" style="color:#003f82;">Bank BCA</div>
                                <div class="bank-sub">Transfer ke rekening berikut</div>
                            </div>
                        </div>
                        <div class="bank-body">
                            <div class="label-rek">Nomor Rekening</div>
                            <div class="nomor-rek">1234 5678 90</div>
                            <div class="atas-nama">a.n. Chandra Catering</div>
                            <div class="total-row"><span>Total Transfer</span><strong>Rp <?= $total_fmt ?></strong></div>
                            <button type="button" class="btn-salin" onclick="salin('1234567890','notif-bca')">📋 Salin Nomor Rekening</button>
                            <div class="notif-salin" id="notif-bca">✅ Nomor rekening disalin!</div>
                            <div class="catatan">⚠️ Upload bukti transfer di bawah ini</div>
                        </div>
                    </div>
                </div>
 
                <!-- PANEL BRI -->
                <div class="panel-bayar" id="panel-Transfer BRI">
                    <div class="panel-bank">
                        <div class="bank-header" style="background:#e8eeff;">
                            <div class="bank-logo logo-bri">BRI</div>
                            <div>
                                <div class="bank-name" style="color:#003399;">Bank BRI</div>
                                <div class="bank-sub">Transfer ke rekening berikut</div>
                            </div>
                        </div>
                        <div class="bank-body">
                            <div class="label-rek">Nomor Rekening</div>
                            <div class="nomor-rek">0987 6543 21</div>
                            <div class="atas-nama">a.n. Chandra Catering</div>
                            <div class="total-row"><span>Total Transfer</span><strong>Rp <?= $total_fmt ?></strong></div>
                            <button type="button" class="btn-salin" onclick="salin('0987654321','notif-bri')">📋 Salin Nomor Rekening</button>
                            <div class="notif-salin" id="notif-bri">✅ Nomor rekening disalin!</div>
                            <div class="catatan">⚠️ Upload bukti transfer di bawah ini</div>
                        </div>
                    </div>
                </div>
 
                <!-- PANEL GOPAY -->
                <div class="panel-bayar" id="panel-GoPay">
                    <div class="panel-ewallet">
                        <div class="ewallet-header" style="background:#00aa5b;color:white;">
                            <div class="ewallet-logo logo-gopay">G</div>
                            <div><div class="ewallet-name">GoPay</div><div class="ewallet-sub">Transfer ke nomor berikut</div></div>
                        </div>
                        <div class="ewallet-body">
                            <div class="label-no">Nomor GoPay</div>
                            <div class="nomor-hp">0812-3456-7890</div>
                            <div class="atas-nama">a.n. Chandra Catering</div>
                            <div class="total-row"><span>Total Transfer</span><strong>Rp <?= $total_fmt ?></strong></div>
                            <button type="button" class="btn-salin" style="background:#00aa5b;" onclick="salin('081234567890','notif-gopay')">📋 Salin Nomor GoPay</button>
                            <div class="notif-salin" id="notif-gopay">✅ Nomor disalin!</div>
                            <div class="catatan">⚠️ Upload bukti transfer di bawah ini</div>
                        </div>
                    </div>
                </div>
 
                <!-- PANEL OVO -->
                <div class="panel-bayar" id="panel-OVO">
                    <div class="panel-ewallet">
                        <div class="ewallet-header" style="background:#4c2a86;color:white;">
                            <div class="ewallet-logo logo-ovo">OVO</div>
                            <div><div class="ewallet-name">OVO</div><div class="ewallet-sub">Transfer ke nomor berikut</div></div>
                        </div>
                        <div class="ewallet-body">
                            <div class="label-no">Nomor OVO</div>
                            <div class="nomor-hp">0812-3456-7890</div>
                            <div class="atas-nama">a.n. Chandra Catering</div>
                            <div class="total-row"><span>Total Transfer</span><strong>Rp <?= $total_fmt ?></strong></div>
                            <button type="button" class="btn-salin" style="background:#4c2a86;" onclick="salin('081234567890','notif-ovo')">📋 Salin Nomor OVO</button>
                            <div class="notif-salin" id="notif-ovo">✅ Nomor disalin!</div>
                            <div class="catatan">⚠️ Upload bukti transfer di bawah ini</div>
                        </div>
                    </div>
                </div>
 
                <!-- PANEL SHOPEEPAY -->
                <div class="panel-bayar" id="panel-ShopeePay">
                    <div class="panel-ewallet">
                        <div class="ewallet-header" style="background:#ee4d2d;color:white;">
                            <div class="ewallet-logo logo-spay">SP</div>
                            <div><div class="ewallet-name">ShopeePay</div><div class="ewallet-sub">Transfer ke nomor berikut</div></div>
                        </div>
                        <div class="ewallet-body">
                            <div class="label-no">Nomor ShopeePay</div>
                            <div class="nomor-hp">0812-3456-7890</div>
                            <div class="atas-nama">a.n. Chandra Catering</div>
                            <div class="total-row"><span>Total Transfer</span><strong>Rp <?= $total_fmt ?></strong></div>
                            <button type="button" class="btn-salin" style="background:#ee4d2d;" onclick="salin('081234567890','notif-spay')">📋 Salin Nomor ShopeePay</button>
                            <div class="notif-salin" id="notif-spay">✅ Nomor disalin!</div>
                            <div class="catatan">⚠️ Upload bukti transfer di bawah ini</div>
                        </div>
                    </div>
                </div>
 
                <!-- UPLOAD BUKTI TRANSFER -->
                <div class="upload-bukti" id="upload-bukti">
                    <label>📎 Upload Bukti Transfer</label>
                    <input type="file" name="bukti_transfer" id="input-bukti" accept="image/*" onchange="previewBukti(this)">
                    <button type="button" class="btn-upload" onclick="document.getElementById('input-bukti').click()">
                        📁 Pilih Foto Bukti Transfer
                    </button>
                    <div class="preview-bukti" id="preview-bukti">
                        <img id="img-preview" src="" alt="Preview Bukti">
                        <div class="nama-file" id="nama-file"></div>
                    </div>
                </div>
 
            </div>
        </div>
 
        <button type="submit" class="btn-pesan">✅ Buat Pesanan Sekarang</button>
 
    </form>
</div><!-- end .container -->
 
<script>
    // FIXED: Accept event as explicit parameter instead of relying on implicit global
    function pilihMetode(e, metode, butuhBukti) {
        document.querySelectorAll('.metode-card').forEach(function(c) {
            c.classList.remove('aktif');
        });
        e.currentTarget.classList.add('aktif');
        document.getElementById('metode_input').value = metode;
 
        document.querySelectorAll('.panel-bayar').forEach(function(p) {
            p.classList.remove('aktif');
        });
        var panel = document.getElementById('panel-' + metode);
        if (panel) panel.classList.add('aktif');
 
        var uploadBox = document.getElementById('upload-bukti');
        if (butuhBukti) {
            uploadBox.classList.add('aktif');
        } else {
            uploadBox.classList.remove('aktif');
            document.getElementById('input-bukti').value = '';
            document.getElementById('preview-bukti').style.display = 'none';
        }
    }
 
    function previewBukti(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
 
            // FIXED: Client-side file validation (type and size — max 5MB)
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('File harus berupa gambar (JPG, PNG, GIF, WEBP).');
                input.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB.');
                input.value = '';
                return;
            }
 
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('img-preview').src = e.target.result;
                document.getElementById('preview-bukti').style.display = 'block';
                document.getElementById('nama-file').textContent = '📄 ' + file.name;
            };
            reader.readAsDataURL(file);
        }
    }
 
    function salin(teks, idNotif) {
        navigator.clipboard.writeText(teks).then(function() {
            var el = document.getElementById(idNotif);
            el.style.display = 'block';
            setTimeout(function() { el.style.display = 'none'; }, 2500);
        }).catch(function() {
            // FIXED: Fallback for browsers that block clipboard API
            alert('Salin manual: ' + teks);
        });
    }
</script>
 
</body>
</html>
 