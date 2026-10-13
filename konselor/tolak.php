<?php
include '../config/koneksi.php';

$id = $_GET['id'];

// langsung hapus
mysqli_query($conn, "DELETE FROM booking WHERE id='$id'");

header("Location: dashboard.php");
?>