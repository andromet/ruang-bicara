<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'siswa') {
        header("Location: siswa/dashboard.php");
    } else {
        header("Location: konselor/dashboard.php");
    }
} else {
    header("Location: auth/login.php");
}
?>