<?php
session_start();
 
if (!empty($_SESSION['status_login']) && $_SESSION['status_login'] === true) {
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
    <title>Login - Sistem Pemesanan Makanan</title>
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
            max-width: 700px;
            min-height: 420px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
        }
 
        .login-left {
            width: 40%;
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
            width: 60%;
            background-color: #f0e8d0;
            padding: 40px 36px;
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
            margin-bottom: 20px;
        }
 
        .login-right .form-group {
            margin-bottom: 13px;
        }
 
        .login-right .form-group label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #3b5a48;
            margin-bottom: 5px;
        }
 
        .login-right .form-group input,
        .login-right .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #b8c9b8;
            border-radius: 8px;
            font-size: 13px;
            background: #fff;
            color: #2d4a3e;
            outline: none;
            box-sizing: border-box;
        }
 
        .login-right .form-group input:focus,
        .login-right .form-group select:focus {
            border-color: #2d4a3e;
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
 
        .error-msg {
            color: #c0392b;
            font-size: 13px;
            margin-bottom: 10px;
            background: #fdecea;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #f5c6cb;
        }
 
        @media (max-width: 640px) {
            .login-wrapper {
                flex-direction: column;
                min-height: unset;
            }
 
            .login-left {
                width: 100%;
                padding: 28px 24px;
                order: -1;
            }
 
            .login-left .logo-circle {
                width: 80px;
                height: 80px;
                font-size: 30px;
            }
 
            .login-right {
                width: 100%;
                padding: 28px 24px;
            }
 
            .login-right h2 {
                font-size: 18px;
            }
        }
 
        @media (max-width: 360px) {
            .login-left {
                padding: 20px 16px;
            }
 
            .login-right {
                padding: 20px 16px;
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
            <div class="brand-sub">Pesan makanan favorit<br>kapan saja, di mana saja</div>
        </div>
 
        <div class="login-right">
            <h2>LOGIN</h2>
            <p class="subtitle">Silakan login untuk melanjutkan ke sistem</p>
 
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'gagal'): ?>
                <p class="error-msg">❌ Username atau password salah.</p>
            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'akses_ditolak'): ?>
                <p class="error-msg">⚠️ Silakan login terlebih dahulu.</p>
            <?php endif; ?>
 
            <form action="proses_login.php" method="POST" autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           required
                           autocomplete="username"
                           placeholder="Username Anda">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="Masukkan Password">
                </div>
                <div class="form-group">
                    <label for="role">Login Sebagai</label>
                    <select id="role" name="role">
                        <option value="pelanggan">Pelanggan</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
 
                <button type="submit" class="btn-primary">Login</button>
 
                <p style="text-align:center; margin-top:15px; font-size:12px; color:#5a7a6a;">
                    Belum punya akun?
                    <a href="register.php" style="color:#2d4a3e; text-decoration:none; font-weight:bold;">Daftar di sini</a>
                </p>
            </form>
        </div>
 
    </div>
</body>
</html>
 