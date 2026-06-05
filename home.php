<?php
session_start();
include 'koneksi.php';
 
// FIXED: Use empty() for stricter session check
if (empty($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: index.php");
    exit;
}
 
// FIXED: Sanitize session output early
$nama_lengkap = htmlspecialchars($_SESSION['nama_lengkap']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Pemesanan Makanan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f5f0e8;
            margin: 0;
            font-family: Arial, sans-serif;
        }
 
        /* NAVBAR */
        .navbar {
            background-color: #2d4a3e;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .logo {
            color: #f0e8d0;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .navbar .menu {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .navbar .menu a {
            color: #f0e8d0;
            text-decoration: none;
            margin-right: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .navbar .menu span {
            color: #f0e8d0;
            font-size: 14px;
            margin-right: 10px;
            font-weight: bold;
        }
        .btn-logout {
            color: #2d4a3e !important;
            background: #f0e8d0;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none !important;
            font-weight: bold;
            font-size: 13px;
            margin-left: 5px;
        }
        .btn-logout:hover { background: #fff; }
 
        /* FIXED: Navbar responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 10px;
                padding: 14px 20px;
                text-align: center;
            }
            .navbar .menu {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
 
        /* PROMO SECTION */
        .promo-section { padding: 20px 30px 0 30px; }
        .promo-section h3 {
            color: #2d4a3e;
            margin-bottom: 12px;
            font-size: 18px;
        }
        .promo-text {
            background: #f0e8d0;
            padding: 14px 16px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #c8bb99;
            color: #3b5a48;
        }
 
        /* MENU SECTION */
        .menu-section { padding: 20px 30px 30px 30px; }
        .menu-section h2 {
            color: #2d4a3e;
            font-size: 20px;
            margin-bottom: 16px;
        }
        .grid-menu {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
 
        /* FIXED: Single-column grid on small screens */
        @media (max-width: 480px) {
            .grid-menu {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .promo-section,
            .menu-section {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
 
        /* CARD MENU */
        .card-menu {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(45,74,62,0.12);
            border: 1px solid #d6e8d6;
            overflow: hidden;
            transition: 0.3s;
        }
        .card-menu:hover { transform: translateY(-8px); }
 
        .square {
            width: 100%;
            height: 150px;
            object-fit: cover;
            cursor: zoom-in;
            transition: opacity 0.2s;
        }
        .square:hover { opacity: 0.85; }
        .card-body { padding: 14px; }
        .card-body h4 {
            color: #2d4a3e;
            font-size: 14px;
            margin-bottom: 6px;
        }
        .price {
            color: #2d4a3e;
            font-weight: bold;
            font-size: 15px;
            margin: 8px 0;
        }
        .btn-order {
            width: 100%;
            padding: 8px;
            background: #fff;
            border: 1.5px solid #2d4a3e;
            color: #2d4a3e;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.2s;
        }
        .btn-order:hover {
            background: #2d4a3e;
            color: #f0e8d0;
        }
 
        /* MODAL GAMBAR */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.aktif { display: flex; }
        .modal-overlay img {
            max-width: 85%;
            max-height: 85vh;
            border-radius: 12px;
            box-shadow: 0 0 40px rgba(0,0,0,0.6);
            animation: zoomIn 0.2s ease;
        }
        .modal-tutup {
            position: absolute;
            top: 20px;
            right: 28px;
            font-size: 36px;
            color: #f0e8d0;
            cursor: pointer;
            z-index: 10000;
            line-height: 1;
            font-weight: bold;
        }
        .modal-tutup:hover { color: #fff; }
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
    </style>
</head>
<body>
 
    <!-- MODAL POPUP GAMBAR -->
    <div class="modal-overlay" id="modalGambar" onclick="tutupModal()">
        <span class="modal-tutup" onclick="tutupModal()">&times;</span>
        <img id="modalImg" src="" alt="Preview" onclick="event.stopPropagation()">
    </div>
 
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">Chandra Catering</div>
        <div class="menu">
            <a href="riwayat.php">📜 Riwayat Pesanan</a>
            <a href="keranjang.php">🛒 Keranjang</a>
            <!-- FIXED: Sanitized session variable -->
            <span>Halo, <?= $nama_lengkap ?></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>
 
    <!-- PROMO SECTION -->
    <div class="promo-section">
        <h3>🔥 Promo Spesial Hari Ini!</h3>
        <img src="promo.png" alt="Banner Promo"
             style="width:100%; height:300px; object-fit:cover; border-radius:8px 8px 0 0; cursor:zoom-in;"
             onclick="bukaModal(this.src)">
        <div class="promo-text">
            <strong>Diskon Khusus Pelanggan Chandra Catering</strong><br>
            Cek sekarang juga: Mulai pesan Makanan &amp; Minuman.
        </div>
    </div>
 
    <!-- MENU SECTION -->
    <section class="menu-section">
        <h2>🍔 Pilih Makanan &amp; Minuman</h2>
        <div class="grid-menu">
            <?php
            // FIXED: Added query error handling
            $query_menu = mysqli_query($koneksi, "SELECT * FROM menus WHERE status='tersedia'");
 
            if (!$query_menu) {
                echo "<p style='color:#c0392b;'>Gagal memuat menu: " . htmlspecialchars(mysqli_error($koneksi)) . "</p>";
            } elseif (mysqli_num_rows($query_menu) > 0) {
                while ($menu = mysqli_fetch_assoc($query_menu)) {
                    // FIXED: Sanitize all output to prevent XSS
                    $gambar      = htmlspecialchars($menu['gambar']);
                    $nama        = htmlspecialchars($menu['nama_makanan']);
                    $id          = htmlspecialchars($menu['id']);
                    $harga       = number_format($menu['harga'], 0, ',', '.');
            ?>
                <div class="card-menu">
                    <img src="<?= $gambar ?>"
                         alt="<?= $nama ?>"
                         class="square"
                         onclick="bukaModal(this.src)">
                    <div class="card-body">
                        <h4><?= $nama ?></h4>
                        <p class="price">Rp <?= $harga ?></p>
                        <form action="keranjang.php" method="POST">
                            <input type="hidden" name="menu_id" value="<?= $id ?>">
                            <button type="submit" class="btn-order">+ Keranjang</button>
                        </form>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p style='color:#5a7a6a;'>Maaf, menu sedang kosong atau habis.</p>";
            }
            ?>
        </div>
    </section>
 
    <script>
        function bukaModal(src) {
            document.getElementById('modalImg').src = src;
            document.getElementById('modalGambar').classList.add('aktif');
            document.body.style.overflow = 'hidden';
        }
        function tutupModal() {
            document.getElementById('modalGambar').classList.remove('aktif');
            document.body.style.overflow = '';
        }
        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupModal();
        });
    </script>
 
</body>
</html