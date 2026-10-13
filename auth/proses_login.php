<?php
session_start();
include '../config/koneksi.php';

$nis_nip = mysqli_real_escape_string($conn, $_POST['nis_nip']);
$password = $_POST['password']; // Password plain text

$query = "SELECT * FROM users WHERE nis_nip='$nis_nip' AND password='$password'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    
    $_SESSION['id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];
    
    if ($user['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($user['role'] == 'konselor') {
        header("Location: ../konselor/dashboard.php");
    } else {
        header("Location: ../siswa/dashboard.php");
    }
} else {
    $_SESSION['error'] = "NIS/NIP atau password salah!";
    header("Location: login.php");
}
exit;
?>