<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';

if (empty($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Dashboard Admin - Pesanan Masuk</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        .navbar-admin {
            background-color: #1a3028;
            padding: 0 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo { color: white; font-weight: bold; font-size: 16px; }
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
        }
        .hamburger:hover { background: rgba(255,255,255,0.1); }
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 20px;
            list-style: none;
        }
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }
        .nav-menu a:hover { text-decoration: underline; }
        @media (max-width: 767px) {
            .hamburger { display: flex; }
            .nav-menu {
                display: none;
                position: absolute;
                top: 56px;
                left: 0;
                right: 0;
                background-color: #1a3028;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                padding: 8px 0;
                border-top: 1px solid rgba(255,255,255,0.1);
                z-index: 999;
            }
            .nav-menu.open { display: flex; }
            .nav-menu li { width: 100%; }
            .nav-menu a {
                display: block;
                padding: 14px 20px;
                border-bottom: 1px solid rgba(255,255,255,0.06);
                font-size: 15px;
            }
            .nav-menu a:hover { background: rgba(255,255,255,0.08); text-decoration: none; }
        }
        .btn-logout {
            background: #c93b1d;
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
        }
        @media (max-width: 767px) {
            .btn-logout { margin: 8px 16px; padding: 12px 20px; border-radius: 6px; text-align: center; }
        }
        .admin-container {
            padding: 20px;
            background: white;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .table-admin {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        .table-admin th, .table-admin td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }
        .table-admin th { background-color: #cdc7ab; color: #2d4a3e; font-weight: 600; }
        .table-admin tr:hover { background-color: #f5f0e8; }
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-menunggu { background-color: #ffc107; color: #333; }
        .badge-diproses { background-color: #17a2b8; color: white; }
        .badge-dikirim  { background-color: #007bff; color: white; }
        .badge-selesai  { background-color: #28a745; color: white; }
        .btn-detail { padding: 5px 10px; text-decoration: none; background: #007bff; color: white; border-radius: 4px; font-size: 13px; }
        .btn-hapus  { padding: 5px 10px; text-decoration: none; background: #dc3545; color: white; border-radius: 4px; font-size: 13px; }
        .btn-simpan { padding: 5px 10px; background: #2d4a3e; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-simpan:hover { background: #1a3028; }
        .status-form { display: flex; gap: 5px; align-items: center; }
        .status-form select { padding: 5px 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 13px; }
        .action-cell { white-space: nowrap; display: flex; gap: 6px; }
    </style>
</head>
<body style="background-color: #4a5d4e;">
    <nav class="navbar-admin">
        <div class="logo">👨‍🍳 Admin</div>
        <button class="hamburger" id="btn-hamburger" onclick="toggleMenu()" aria-label="Buka menu">☰</button>
        <ul class="nav-menu" id="nav-menu">
            <li><a href="manajemen_menu.php">🍽️ Kelola Menu</a></li>
            <li><a href="laporan_harian.php">📊 Laporan Harian</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container container--wide">
        <h2 style="color:white">Daftar Pesanan Masuk</h2>
        <div class="admin-container">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>ID Order</th>
                        <th>Nama Pelanggan</th>
                        <th>Waktu Pesan</th>
                        <th>Total Tagihan</th>
                        <th>Metode Pembayaran</th>
                        <th>Status</th>
                        <th>Ubah Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ✅ JOIN pakai kolom yang benar: id_users
                    $query = mysqli_query($koneksi, "SELECT orders.*, users.nama_lengkap 
                                                     FROM orders 
                                                     JOIN users ON orders.id_users = users.id_users 
                                                     ORDER BY orders.tanggal_pesan DESC");

                    if (!$query) {
                        die("Query error: " . htmlspecialchars(mysqli_error($koneksi)));
                    }

                    while ($row = mysqli_fetch_assoc($query)) {
                        $id     = (int)$row['id_orders'];                         // ✅ id_orders
                        $nama   = htmlspecialchars($row['nama_lengkap']);
                        $tanggal= htmlspecialchars($row['tanggal_pesan']);
                        $metode = htmlspecialchars($row['metode_pembayaran']);
                        $status = htmlspecialchars($row['status_pesanan']);
                        $total  = number_format($row['subtotal'], 0, ',', '.');   // ✅ subtotal

                        $badgeClass = match($row['status_pesanan']) {
                            'menunggu pembayaran' => 'badge-menunggu',
                            'diproses'            => 'badge-diproses',
                            'dikirim'             => 'badge-dikirim',
                            'selesai'             => 'badge-selesai',
                            default               => 'badge-menunggu',
                        };
                    ?>
                    <tr>
                        <td>#<?= $id ?></td>
                        <td><?= $nama ?></td>
                        <td><?= $tanggal ?></td>
                        <td>Rp <?= $total ?></td>
                        <td><?= $metode ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= $status ?></span></td>
                        <td>
                            <form action="update_status.php" method="POST" class="status-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="order_id" value="<?= $id ?>">
                                <select name="status_pesanan">
                                    <option value="menunggu pembayaran" <?= $row['status_pesanan'] == 'menunggu pembayaran' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                    <option value="diproses"            <?= $row['status_pesanan'] == 'diproses'            ? 'selected' : '' ?>>Diproses</option>
                                    <option value="dikirim"             <?= $row['status_pesanan'] == 'dikirim'             ? 'selected' : '' ?>>Dikirim</option>
                                    <option value="selesai"             <?= $row['status_pesanan'] == 'selesai'             ? 'selected' : '' ?>>Selesai</option>
                                </select>
                                <button type="submit" class="btn-simpan">Simpan</button>
                            </form>
                        </td>
                        <td>
                            <div class="action-cell">
                                <a href="detail_pesanan.php?id=<?= $id ?>" class="btn-detail">Lihat Detail</a>
                                <form action="hapus_order.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus pesanan #<?= $id ?>?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="order_id" value="<?= $id ?>">
                                    <button type="submit" class="btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const menu   = document.getElementById('nav-menu');
            const tombol = document.getElementById('btn-hamburger');
            menu.classList.toggle('open');
            tombol.textContent = menu.classList.contains('open') ? '✕' : '☰';
        }
        document.addEventListener('click', function(e) {
            const navbar = document.querySelector('.navbar-admin');
            const menu   = document.getElementById('nav-menu');
            if (!navbar.contains(e.target)) {
                menu.classList.remove('open');
                document.getElementById('btn-hamburger').textContent = '☰';
            }
        });
    </script>
</body>
</html>