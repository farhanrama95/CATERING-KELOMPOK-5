<?php
session_start();
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: index.php");
    exit;
}

$nama_lengkap = htmlspecialchars($_SESSION['nama_lengkap']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Pemesanan Makanan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f5f0e8; font-family: Arial, sans-serif; }

        .navbar {
            background-color: #2d4a3e;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar .logo { color: #f0e8d0; font-size: 20px; font-weight: bold; letter-spacing: 1px; }

        .hamburger {
            display: none;
            background: none;
            border: none;
            color: #f0e8d0;
            cursor: pointer;
            font-size: 22px;
            padding: 8px;
            border-radius: 6px;
            line-height: 1;
        }
        .hamburger:hover { background: rgba(255,255,255,0.1); }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
        }
        .nav-menu li a,
        .nav-menu li span {
            color: #f0e8d0;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            padding: 6px 10px;
            border-radius: 6px;
            display: block;
        }
        .nav-menu li a:hover { background: rgba(255,255,255,0.1); }
        .btn-logout {
            background: #f0e8d0;
            color: #2d4a3e !important;
            border-radius: 6px;
        }
        .btn-logout:hover { background: #fff !important; }

        @media (max-width: 767px) {
            .navbar { padding: 0 20px; }
            .hamburger { display: flex; }
            .nav-menu {
                display: none;
                position: absolute;
                top: 75px;
                left: 0;
                right: 0;
                background-color: #2d4a3e;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                padding: 8px 0;
                border-top: 1px solid rgba(255,255,255,0.1);
            }
            .nav-menu.open { display: flex; }
            .nav-menu li a,
            .nav-menu li span {
                padding: 14px 20px;
                border-radius: 0;
                border-bottom: 1px solid rgba(255,255,255,0.06);
                font-size: 15px;
            }
            .btn-logout {
                background: transparent !important;
                color: #ff8a7a !important;
                border-bottom: none !important;
            }
        }

        .promo-section { padding: 20px 30px 0; }
        .promo-section h3 { color: #2d4a3e; margin-bottom: 12px; font-size: 18px; }
        .promo-text {
            background: #f0e8d0;
            padding: 14px 16px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #c8bb99;
            color: #3b5a48;
        }

        .menu-section { padding: 20px 30px 30px; }
        .menu-section h2 { color: #2d4a3e; font-size: 20px; margin-bottom: 16px; }

        .grid-menu {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        @media (max-width: 767px) {
            .promo-section, .menu-section { padding-left: 16px; padding-right: 16px; }
            .grid-menu { grid-template-columns: 1fr 1fr; gap: 12px; }
        }

        @media (max-width: 400px) {
            .grid-menu { grid-template-columns: 1fr; }
        }

        .card-menu {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #d6e8d6;
            overflow: hidden;
            transition: transform 0.3s;
        }
        .card-menu:hover { transform: translateY(-6px); }

        .square {
            width: 100%;
            height: 150px;
            object-fit: cover;
            cursor: zoom-in;
            transition: opacity 0.2s;
            display: block;
        }
        .square:hover { opacity: 0.85; }

        .card-body { padding: 14px; }
        .card-body h4 { color: #2d4a3e; font-size: 14px; margin-bottom: 6px; }
        .price { color: #2d4a3e; font-weight: bold; font-size: 15px; margin: 8px 0; }

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
        .btn-order:hover { background: #2d4a3e; color: #f0e8d0; }

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

<div class="modal-overlay" id="modalGambar" onclick="tutupModal()">
    <span class="modal-tutup" onclick="tutupModal()">&times;</span>
    <img id="modalImg" src="" alt="Preview" onclick="event.stopPropagation()">
</div>

<nav class="navbar">
    <div class="logo">Chandra Catering</div>
    <button class="hamburger" id="btn-hamburger" onclick="toggleMenu()" aria-label="Buka menu">☰</button>
    <ul class="nav-menu" id="nav-menu">
        <li><a href="riwayat.php">📜 Riwayat Pesanan</a></li>
        <li><a href="keranjang.php">🛒 Keranjang</a></li>
        <li><span>Halo, <?= $nama_lengkap ?></span></li>
        <li><a href="logout.php" class="btn-logout">Logout</a></li>
    </ul>
</nav>

<div class="promo-section">
    <h3>🔥 Promo Spesial Hari Ini!</h3>
    <img src="promo.png" alt="Banner Promo"
         style="width:100%; object-fit:cover; border-radius:8px 8px 0 0; cursor:zoom-in; display:block;"
         onclick="bukaModal(this.src)">
    <div class="promo-text">
        <strong>Diskon Khusus Pelanggan Chandra Catering</strong><br>
        Cek sekarang juga: Mulai pesan Makanan &amp; Minuman.
    </div>
</div>

<section class="menu-section">
    <h2>🍔 Pilih Makanan &amp; Minuman</h2>
    <div class="grid-menu">
        <?php
        $query_menu = mysqli_query($koneksi, "SELECT * FROM menus WHERE status='tersedia'");

        if (!$query_menu) {
            echo "<p style='color:#c0392b;'>Gagal memuat menu: " . htmlspecialchars(mysqli_error($koneksi)) . "</p>";
        } elseif (mysqli_num_rows($query_menu) > 0) {
            while ($menu = mysqli_fetch_assoc($query_menu)) {
                $gambar = htmlspecialchars($menu['gambar']);
                $nama   = htmlspecialchars($menu['nama_makanan']);
                $id     = htmlspecialchars($menu['id']);
                $harga  = number_format($menu['harga'], 0, ',', '.');
        ?>
            <div class="card-menu">
                <img src="<?= $gambar ?>" alt="<?= $nama ?>" class="square" onclick="bukaModal(this.src)">
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
function toggleMenu() {
    const menu = document.getElementById('nav-menu');
    const btn  = document.getElementById('btn-hamburger');
    menu.classList.toggle('open');
    btn.textContent = menu.classList.contains('open') ? '✕' : '☰';
}

document.addEventListener('click', function(e) {
    const nav  = document.querySelector('.navbar');
    const menu = document.getElementById('nav-menu');
    const btn  = document.getElementById('btn-hamburger');
    if (!nav.contains(e.target)) {
        menu.classList.remove('open');
        btn.textContent = '☰';
    }
});

function bukaModal(src) {
    document.getElementById('modalImg').src = src;
    document.getElementById('modalGambar').classList.add('aktif');
    document.body.style.overflow = 'hidden';
}

function tutupModal() {
    document.getElementById('modalGambar').classList.remove('aktif');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tutupModal();
});
</script>

</body>
</html>