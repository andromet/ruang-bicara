<?php
include '../config/koneksi.php';

$id = $_GET['id'];

// hapus karena sudah selesai
mysqli_query($conn, "DELETE FROM booking WHERE id='$id'");

header("Location: dashboard.php");
?>