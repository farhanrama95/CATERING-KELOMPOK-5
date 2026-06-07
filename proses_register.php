<?php
include 'koneksi.php';

// Menangkap data dari form
$nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
$no_telp      = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
$alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
$username     = mysqli_real_escape_string($koneksi, $_POST['username']);

// BARU: Tangkap password polos lalu acak menggunakan standar PASSWORD_BCRYPT
$password_polos = $_POST['password'];
$password_hash  = password_hash($password_polos, PASSWORD_BCRYPT);

$role         = 'pelanggan'; // Paksa role menjadi pelanggan secara otomatis

// Cek apakah username sudah pernah digunakan sebelumnya
$cek_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");

if (mysqli_num_rows($cek_username) > 0) {
    echo "<script>alert('Maaf, Username sudah digunakan. Silakan pilih username lain!'); window.location='register.php';</script>";
} else {
    // PERBAIKAN: Variabel '$password' diganti menjadi '$password_hash' agar tersimpan aman
    $query = "INSERT INTO users (username, password, role, nama_lengkap, no_telp, alamat) 
              VALUES ('$username', '$password_hash', '$role', '$nama_lengkap', '$no_telp', '$alamat')";
              
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat mendaftar.'); window.location='register.php';</script>";
    }
}
?>
