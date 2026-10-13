<?php
$conn = mysqli_connect("localhost", "root", "", "db_ruang_bicara");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>