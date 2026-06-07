<?php
session_start();
if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == true) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Pemesanan Makanan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="ChatGPT Image May 2, 2026, 11_39_59 AM.png">
    <style>
        body.bg-login {
            background-color: #2d4a3e;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 720px;
            min-height: 520px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
        }
        .login-left {
            width: 38%;
            background-color: #2d4a3e;
            border-right: 1px solid rgba(255,255,255,0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 24px;
            gap: 14px;
        }
        .login-left .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid rgba(240,232,208,0.3);
            background: rgba(240,232,208,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            overflow: hidden;
        }
        .login-left .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .login-left .brand-name {
            color: #f0e8d0;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
        }
        .login-left .brand-sub {
            color: rgba(240,232,208,0.6);
            font-size: 12px;
            text-align: center;
            line-height: 1.6;
        }
        .login-right {
            width: 62%;
            background-color: #f0e8d0;
            padding: 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right h2 {
            color: #2d4a3e;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .login-right .subtitle {
            color: #7a8c7a;
            font-size: 12px;
            margin-bottom: 18px;
        }
        .login-right .form-group {
            margin-bottom: 12px;
        }
        .login-right .form-group label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #3b5a48;
            margin-bottom: 4px;
        }
        .login-right .form-group input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #b8c9b8;
            border-radius: 8px;
            font-size: 13px;
            background: #fff;
            color: #2d4a3e;
            outline: none;
            box-sizing: border-box;
        }
        .login-right .form-group input:focus {
            border-color: #2d4a3e;
        }
        .login-right .form-group input::placeholder {
            color: #aaa;
        }
        .login-right .btn-primary {
            width: 100%;
            padding: 11px;
            background: #2d4a3e;
            color: #f0e8d0;
            border: none;
            border-radius: 8px;
            margin-top: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }
        .login-right .btn-primary:hover {
            background: #3b6b54;
        }
 
        @media (max-width: 640px) {
            .login-wrapper {
                flex-direction: column;
                min-height: unset;
            }
            .login-left {
                width: 100%;
                padding: 24px 20px;
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            .login-left .logo-circle {
                width: 70px;
                height: 70px;
                font-size: 28px;
            }
            .login-left .brand-name {
                font-size: 17px;
            }
            .login-right {
                width: 100%;
                padding: 24px 20px;
            }
            .login-right h2 {
                font-size: 18px;
            }
            .login-right .btn-primary {
                font-size: 14px;
            }
        }
 
        @media (max-width: 360px) {
            .login-left {
                padding: 18px 16px;
            }
            .login-right {
                padding: 18px 16px;
            }
            .login-right .form-group input {
                font-size: 12px;
                padding: 8px 10px;
            }
            .login-right .btn-primary {
                font-size: 13px;
                padding: 10px;
            }
        }
    </style>
</head>
<body class="bg-login">
    <div class="login-wrapper">
 
        <div class="login-left">
            <div class="logo-circle">
                <img src="logo.png" alt="Logo" onerror="this.parentElement.innerHTML='🍽️'">
            </div>
            <div class="brand-name">Chandra Catering</div>
            <div class="brand-sub">Daftar sekarang &amp;<br>mulai memesan!</div>
        </div>
 
        <div class="login-right">
            <h2>Daftar Akun Baru</h2>
            <p class="subtitle">Silakan lengkapi data diri untuk mulai memesan</p>
 
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'username_digunakan'): ?>
                <p style="color:#c0392b; font-size:12px; margin-bottom:10px;">Username sudah digunakan. Silakan pilih username lain.</p>
            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'gagal'): ?>
                <p style="color:#c0392b; font-size:12px; margin-bottom:10px;">Terjadi kesalahan. Silakan coba lagi.</p>
            <?php endif; ?>
 
            <form action="proses_register.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required placeholder="Masukkan nama lengkap">
                </div>
                <div class="form-group">
                    <label>No. Telepon / WA</label>
                    <input type="text" name="no_telp" required placeholder="Contoh: 08123456789">
                </div>
                <div class="form-group">
                    <label>Alamat Pengiriman Default</label>
                    <input type="text" name="alamat" required placeholder="Masukkan alamat domisili">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Buat username untuk login">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Buat password" minlength="8">
                </div>
 
                <button type="submit" class="btn-primary">Daftar Sekarang</button>
            </form>
 
            <p style="text-align:center; margin-top:15px; font-size:12px; color:#5a7a6a;">
                Sudah punya akun?
                <a href="index.php" style="color:#2d4a3e; text-decoration:none; font-weight:bold;">Login di sini</a>
            </p>
        </div>
 
    </div>
</body>
</html>
 