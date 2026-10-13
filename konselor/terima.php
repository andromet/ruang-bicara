<?php
include '../config/koneksi.php';

$id = $_GET['id'];

mysqli_query($conn, "UPDATE booking SET status='diterima' WHERE id='$id'");

header("Location: dashboard.php");
?>