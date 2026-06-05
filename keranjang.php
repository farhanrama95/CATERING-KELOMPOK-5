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
 
// Handle: Tambah item ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
    // FIXED: Validate menu_id is a positive integer before storing in session
    if (!ctype_digit(strval($_POST['menu_id']))) {
        header("Location: home.php");
        exit;
    }
    $menu_id = (int) $_POST['menu_id'];
 
    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }
    if (isset($_SESSION['keranjang'][$menu_id])) {
        $_SESSION['keranjang'][$menu_id] += 1;
    } else {
        $_SESSION['keranjang'][$menu_id] = 1;
    }
    header("Location: keranjang.php");
    exit;
}
 
// FIXED: Kosongkan keranjang uses POST instead of GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'kosongkan') {
    unset($_SESSION['keranjang']);
    header("Location: keranjang.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Pemesanan Makanan</title>
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
            gap: 10px;
        }
        .navbar .menu a {
            color: #f0e8d0;
            text-decoration: none;
            font-size: 14px;
            margin-right: 15px;
        }
        .navbar .menu a:hover { color: #fff; }
        .navbar .menu span {
            color: #f0e8d0;
            font-size: 14px;
            font-weight: bold;
        }
 
        /* FIXED: Navbar responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 10px;
                padding: 14px 20px;
                text-align: center;
            }
            .navbar .menu { flex-wrap: wrap; justify-content: center; }
        }
 
        /* CONTAINER */
        .container {
            padding: 24px 30px;
            max-width: 900px;
            margin: 0 auto;
            width: 95%;
        }
        .container h2 {
            color: #2d4a3e;
            font-size: 22px;
            margin-bottom: 16px;
        }
 
        /* FIXED: Table wrapper for mobile scroll */
        .table-wrapper { overflow-x: auto; }
 
        /* TABEL */
        .table-keranjang {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(45,74,62,0.12);
            border: 1px solid #d6e8d6;
            min-width: 500px;
        }
        .table-keranjang th, .table-keranjang td {
            padding: 13px 15px;
            border: 1px solid #d6e8d6;
            text-align: left;
            font-size: 14px;
        }
        .table-keranjang th {
            background-color: #2d4a3e;
            color: #f0e8d0;
            font-weight: bold;
        }
        .table-keranjang tr:nth-child(even) { background-color: #f5f0e8; }
        .table-keranjang tr:hover { background-color: #e8f0e8; }
        .text-right { text-align: right; }
 
        /* TOMBOL */
        .btn-kosongkan {
            background-color: #fff;
            color: #c0392b;
            border: 2px solid #c0392b;
            padding: 10px 18px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 20px;
            font-weight: bold;
            font-size: 13px;
            margin-right: 10px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            transition: all 0.2s;
        }
        .btn-kosongkan:hover { background-color: #c0392b; color: white; }
 
        .btn-checkout {
            background-color: #2d4a3e;
            color: #f0e8d0;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-top: 20px;
            font-weight: bold;
            font-size: 13px;
            transition: all 0.2s;
        }
        .btn-checkout:hover { background-color: #3b6b54; }
    </style>
</head>
<body>
 
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">Chandra Catering</div>
        <div class="menu">
            <a href="home.php">🏠 Kembali ke Beranda</a>
            <!-- FIXED: Sanitized session variable -->
            <span>Halo, <?= $nama_lengkap ?></span>
        </div>
    </nav>
 
    <div class="container">
        <h2>🛒 Keranjang Belanja</h2>
 
        <?php if (empty($_SESSION['keranjang'])) : ?>
            <p style="margin-top:20px; color:#5a7a6a;">
                Keranjang kamu masih kosong. Yuk
                <a href="home.php" style="color:#2d4a3e; font-weight:bold;">pilih makanan</a> dulu!
            </p>
        <?php else : ?>
            <div class="table-wrapper">
                <table class="table-keranjang">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Menu</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $total_belanja = 0;
                        foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) :
                            // FIXED: Validate each session key is a positive integer
                            if (!ctype_digit(strval($id_menu))) continue;
                            $id_menu = (int) $id_menu;
 
                            // FIXED: Prepared statement to prevent SQL injection
                            $stmt = mysqli_prepare($koneksi, "SELECT * FROM menus WHERE id = ?");
                            mysqli_stmt_bind_param($stmt, "i", $id_menu);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            $menu = mysqli_fetch_assoc($result);
                            mysqli_stmt_close($stmt);
 
                            // FIXED: Skip if menu no longer exists in DB
                            if (!$menu) continue;
 
                            // FIXED: Sanitize output
                            $nama_menu = htmlspecialchars($menu['nama_makanan']);
                            $subtotal  = $menu['harga'] * $jumlah;
                            $total_belanja += $subtotal;
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $nama_menu ?></td>
                                <td>Rp <?= number_format($menu['harga'], 0, ',', '.') ?></td>
                                <td><?= (int) $jumlah ?></td>
                                <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background-color:#f0e8d0;">
                            <td colspan="4" class="text-right"><strong>Total Keseluruhan</strong></td>
                            <td><strong style="color:#2d4a3e;">Rp <?= number_format($total_belanja, 0, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
 
            <!-- FIXED: Kosongkan uses POST form instead of GET link -->
            <form action="keranjang.php" method="POST" style="display:inline;"
                  onsubmit="return confirm('Yakin ingin mengosongkan keranjang?')">
                <input type="hidden" name="aksi" value="kosongkan">
                <button type="submit" class="btn-kosongkan">🗑️ Kosongkan Keranjang</button>
            </form>
 
            <a href="checkout.php" class="btn-checkout">✅ Lanjut Checkout</a>
        <?php endif; ?>
    </div>
 
</body>
</html>
 