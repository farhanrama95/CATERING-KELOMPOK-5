<?php
session_start();
include 'koneksi.php';
 
if (!isset($_SESSION['status_login']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}
 
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Menu - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f5f0e8; margin: 0; font-family: Arial, sans-serif; }
 
        .admin-nav {
            background-color: #2d4a3e;
            padding: 14px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav .logo { color: #f0e8d0; font-size: 18px; font-weight: bold; }
        .admin-nav .menu a {
            color: #f0e8d0;
            text-decoration: none;
            margin-right: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .admin-nav .menu a:hover { color: #fff; }
        .btn-logout-nav {
            background: #f0e8d0;
            color: #2d4a3e !important;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13px;
        }
        .btn-logout-nav:hover { background: #fff !important; }
 
        .container { padding: 24px 30px; }
        .container h2 { color: #2d4a3e; font-size: 20px; margin-bottom: 14px; }
 
        .form-container {
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(45,74,62,0.12);
            border: 1px solid #d6e8d6;
        }
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #3b5a48;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #b8c9b8;
            border-radius: 8px;
            font-size: 14px;
            color: #2d4a3e;
            outline: none;
            box-sizing: border-box;
            background: #fff;
        }
        .form-group input:focus,
        .form-group select:focus { border-color: #2d4a3e; }
 
        .file-upload-box {
            border: 2px dashed #b8c9b8;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            background: #f5f0e8;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .file-upload-box:hover { border-color: #2d4a3e; }
        .file-upload-box input[type="file"] { display: none; }
        .file-upload-box .upload-label {
            cursor: pointer;
            font-size: 13px;
            color: #5a7a6a;
        }
        .file-upload-box .upload-label span {
            color: #2d4a3e;
            font-weight: bold;
            text-decoration: underline;
        }
 
        .btn-simpan {
            background: #2d4a3e;
            color: #f0e8d0;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-simpan:hover { background: #3b6b54; }
 
        .table-admin {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(45,74,62,0.12);
            border: 1px solid #d6e8d6;
        }
        .table-admin th, .table-admin td {
            padding: 12px 15px;
            border: 1px solid #d6e8d6;
            text-align: left;
            font-size: 14px;
        }
        .table-admin th {
            background-color: #2d4a3e;
            color: #f0e8d0;
            font-weight: bold;
        }
        .table-admin tr:nth-child(even) { background-color: #f5f0e8; }
        .table-admin tr:hover { background-color: #e8f0e8; }
 
        .btn-danger {
            background: #fff;
            color: #c0392b;
            border: 1.5px solid #c0392b;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-danger:hover { background: #c0392b; color: white; }
    </style>
</head>
<body>
 
    <nav class="admin-nav">
        <div class="logo">👨‍🍳 Admin</div>
        <div class="menu">
            <a href="admin.php">📋 Pesanan Masuk</a>
            <a href="manajemen_menu.php">🍽️ Kelola Menu</a>
            <a href="logout.php" class="btn-logout-nav">Logout</a>
        </div>
    </nav>
 
    <div class="container">
        <h2>Tambah Menu Baru</h2>
        <div class="form-container">
            <form action="proses_tambah_menu.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label>Nama Makanan / Minuman</label>
                    <input type="text" name="nama_makanan" required placeholder="Contoh: Nasi Kuning...">
                </div>
                <div class="form-group">
                    <label>Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" required placeholder="Deskripsi makanan...">
                </div>
                <div class="form-group" style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" required placeholder="Contoh: 15000">
                    </div>
                    <div style="flex:1;">
                        <label>Kategori</label>
                        <select name="kategori">
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Cemilan">Cemilan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Upload Gambar Makanan</label>
                    <div class="file-upload-box" onclick="document.getElementById('input-gambar').click()">
                        <input type="file" id="input-gambar" name="gambar" accept="image/*" required onchange="tampilNamaFile(this)">
                        <span class="upload-label" id="label-file">
                            📁 <span>Klik untuk pilih gambar</span> (JPG, PNG, dll)
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn-simpan">+ Simpan Menu</button>
            </form>
        </div>
 
        <h2>Daftar Menu Tersedia</h2>
        <table class="table-admin">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = mysqli_query($koneksi, "SELECT * FROM menus ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($query)) {
                ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($row['gambar']); ?>" width="50" height="50" style="object-fit:cover; border-radius:6px; border:1px solid #d6e8d6;"></td>
                    <td><?= htmlspecialchars($row['nama_makanan']); ?></td>
                    <td><?= htmlspecialchars($row['kategori']); ?></td>
                    <td>Rp <?= number_format((int)$row['harga'], 0, ',', '.'); ?></td>
                    <td>
                        <form action="hapus_menu.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                            <button type="submit" class="btn-danger">🗑️ Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
 
    <script>
        function tampilNamaFile(input) {
            if (input.files && input.files[0]) {
                document.getElementById('label-file').innerHTML = '✅ <strong>' + input.files[0].name + '</strong>';
            }
        }
    </script>
 
</body>
</html>
 