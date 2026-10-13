<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$action = $_POST['action'];
$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$nis_nip = mysqli_real_escape_string($conn, $_POST['nis_nip']);
$role = mysqli_real_escape_string($conn, $_POST['role']);

if ($action == 'add') {
    $password = $_POST['password']; // Password plain text
    
    $check = mysqli_query($conn, "SELECT id FROM users WHERE nis_nip = '$nis_nip'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "NIS/NIP sudah terdaftar!";
        header("Location: dashboard.php");
        exit;
    }
    
    // Hapus kolom created_at karena tidak ada di tabel
    $query = "INSERT INTO users (nama, nis_nip, password, role) 
              VALUES ('$nama', '$nis_nip', '$password', '$role')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "User berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan user: " . mysqli_error($conn);
    }
    
} elseif ($action == 'edit') {
    $user_id = $_POST['user_id'];
    $password = !empty($_POST['password']) ? $_POST['password'] : '';
    
    if (!empty($password)) {
        $query = "UPDATE users SET nama='$nama', nis_nip='$nis_nip', password='$password', role='$role' WHERE id='$user_id'";
    } else {
        $query = "UPDATE users SET nama='$nama', nis_nip='$nis_nip', role='$role' WHERE id='$user_id'";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "User berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal mengupdate user: " . mysqli_error($conn);
    }
}

header("Location: dashboard.php");
exit;
?>