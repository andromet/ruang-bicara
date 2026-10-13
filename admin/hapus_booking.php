<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$query = "DELETE FROM booking WHERE id='$id'";

if (mysqli_query($conn, $query)) {
    $_SESSION['success'] = "Booking berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus booking: " . mysqli_error($conn);
}

header("Location: dashboard.php");
exit;
?>