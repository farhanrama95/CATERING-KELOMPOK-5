<?php
include 'koneksi.php';

// Menangkap data dari form
$nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
$no_telp      = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
$alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
$username     = mysqli_real_escape_string($koneksi, $_POST['username']);
$password     = mysqli_real_escape_string($koneksi, $_POST['password']);
$role         = 'pelanggan'; // Paksa role menjadi pelanggan secara otomatis

// Cek apakah username sudah pernah digunakan sebelumnya
$cek_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");

if (mysqli_num_rows($cek_username) > 0) {
    // Jika username sudah ada, kembalikan ke halaman register dengan pesan error (bisa ditambahkan alert nanti)
    echo "<script>alert('Maaf, Username sudah digunakan. Silakan pilih username lain!'); window.location='register.php';</script>";
} else {
    // Jika username tersedia, simpan data ke database
    $query = "INSERT INTO users (username, password, role, nama_lengkap, no_telp, alamat) 
              VALUES ('$username', '$password', '$role', '$nama_lengkap', '$no_telp', '$alamat')";
              
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat mendaftar.'); window.location='register.php';</script>";
    }
}
?>