<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['id'];
$konselor_id = mysqli_real_escape_string($conn, $_POST['konselor_id']);
$tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
$jam = mysqli_real_escape_string($conn, $_POST['jam']);
$keluhan = mysqli_real_escape_string($conn, $_POST['keluhan']);
$no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);

if (!preg_match('/^[0-9]{10,13}$/', $no_telp)) {
    $_SESSION['error'] = "Nomor telepon tidak valid. Masukkan 10-13 digit angka.";
    header("Location: dashboard.php");
    exit;
}

$cek = mysqli_query($conn, "SELECT * FROM booking 
                            WHERE konselor_id = '$konselor_id' 
                            AND tanggal = '$tanggal' 
                            AND jam = '$jam' 
                            AND status != 'ditolak'");

if (mysqli_num_rows($cek) > 0) {
    $_SESSION['error'] = "Maaf, jadwal tersebut sudah dibooking. Pilih jadwal lain.";
    header("Location: dashboard.php");
    exit;
}

$query = "INSERT INTO booking (user_id, konselor_id, tanggal, jam, keluhan, no_telp, status) 
          VALUES ('$user_id', '$konselor_id', '$tanggal', '$jam', '$keluhan', '$no_telp', 'pending')";

if (mysqli_query($conn, $query)) {
    $_SESSION['success'] = "Booking berhasil dikirim. Silakan tunggu konfirmasi dari konselor.";
} else {
    $_SESSION['error'] = "Gagal melakukan booking: " . mysqli_error($conn);
}

header("Location: dashboard.php");
exit;
?>