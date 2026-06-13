<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'guard_pelanggan.php';
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: index.php");
    exit;
}

$nama_lengkap = htmlspecialchars($_SESSION['nama_lengkap']);

// Tambah item ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
    if (!ctype_digit(strval($_POST['menu_id']))) {
        header("Location: home.php");
        exit;
    }
    $menu_id = (int)$_POST['menu_id'];

    if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];
    if (isset($_SESSION['keranjang'][$menu_id])) {
        $_SESSION['keranjang'][$menu_id] += 1;
    } else {
        $_SESSION['keranjang'][$menu_id] = 1;
    }
    header("Location: keranjang.php");
    exit;
}

// Kosongkan keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'kosongkan') {
    unset($_SESSION['keranjang']);
    header("Location: keranjang.php");
    exit;
}

// Update jumlah item di keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'update_jumlah') {
    $id_menu = (int)$_POST['id_menu'];
    $jumlah  = (int)$_POST['jumlah'];

    if ($jumlah >= 1 && isset($_SESSION['keranjang'][$id_menu])) {
        $_SESSION['keranjang'][$id_menu] = $jumlah;
    } elseif ($jumlah < 1) {
        unset($_SESSION['keranjang'][$id_menu]); // hapus otomatis jika 0
    }

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
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 10px; padding: 14px 20px; text-align: center; }
            .navbar .menu { flex-wrap: wrap; justify-content: center; }
        }
        .container { padding: 24px 30px; max-width: 900px; margin: 0 auto; width: 95%; }
        .container h2 { color: #2d4a3e; font-size: 22px; margin-bottom: 16px; }
        .table-wrapper { overflow-x: auto; }
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
        .table-keranjang th, .table-keranjang td { padding: 13px 15px; border: 1px solid #d6e8d6; text-align: left; font-size: 14px; }
        .table-keranjang th { background-color: #2d4a3e; color: #f0e8d0; font-weight: bold; }
        .table-keranjang tr:nth-child(even) { background-color: #f5f0e8; }
        .table-keranjang tr:hover { background-color: #e8f0e8; }
        .text-right { text-align: right; }

        /* Tombol +/- */
        .qty-form { display: flex; align-items: center; gap: 6px; margin: 0; }
        .btn-qty {
            width: 28px; height: 28px;
            border: none; border-radius: 5px;
            cursor: pointer; font-size: 16px;
            font-weight: bold; line-height: 1;
            transition: opacity 0.2s;
        }
        .btn-qty:hover { opacity: 0.85; }
        .btn-minus { background-color: #c0392b; color: white; }
        .btn-plus  { background-color: #2d4a3e; color: white; }
        .qty-label { min-width: 28px; text-align: center; font-weight: bold; font-size: 14px; }

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

<nav class="navbar">
    <div class="logo">Chandra Catering</div>
    <div class="menu">
        <a href="home.php">🏠 Kembali ke Beranda</a>
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
                        if (!ctype_digit(strval($id_menu))) continue;
                        $id_menu = (int)$id_menu;

                        $stmt = mysqli_prepare($koneksi, "SELECT * FROM menus WHERE id_menus = ?");
                        mysqli_stmt_bind_param($stmt, "i", $id_menu);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $menu = mysqli_fetch_assoc($result);
                        mysqli_stmt_close($stmt);

                        if (!$menu) continue;

                        $nama_menu = htmlspecialchars($menu['nama_menu']);
                        $subtotal  = $menu['harga'] * $jumlah;
                        $total_belanja += $subtotal;
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $nama_menu ?></td>
                        <td>Rp <?= number_format($menu['harga'], 0, ',', '.') ?></td>
                        <td>
                            <!-- Tombol kurangi -->
                            <form action="keranjang.php" method="POST" class="qty-form">
                                <input type="hidden" name="aksi" value="update_jumlah">
                                <input type="hidden" name="id_menu" value="<?= $id_menu ?>">
                                <button type="submit" name="jumlah" value="<?= $jumlah - 1 ?>"
                                        class="btn-qty btn-minus">−</button>
                                <span class="qty-label"><?= (int)$jumlah ?></span>
                                <button type="submit" name="jumlah" value="<?= $jumlah + 1 ?>"
                                        class="btn-qty btn-plus">+</button>
                            </form>
                        </td>
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