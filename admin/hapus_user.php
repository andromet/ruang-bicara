<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "DELETE FROM users WHERE id='$id' AND role != 'admin'";

if (mysqli_query($conn, $query)) {
    $_SESSION['success'] = "User berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus user: " . mysqli_error($conn);
}

header("Location: dashboard.php");
exit;
?>