<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'konselor') {
    header("Location: ../auth/login.php");
    exit;
}

$konselor_id = $_SESSION['id'];
$nama_konselor = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Konselor';

// Hitung statistik untuk dashboard
$query_total = "SELECT COUNT(*) as total FROM booking WHERE konselor_id = '$konselor_id'";
$result_total = mysqli_query($conn, $query_total);
$total_booking = mysqli_fetch_assoc($result_total)['total'];

$query_pending = "SELECT COUNT(*) as pending FROM booking WHERE konselor_id = '$konselor_id' AND status = 'pending'";
$result_pending = mysqli_query($conn, $query_pending);
$total_pending = mysqli_fetch_assoc($result_pending)['pending'];

$query_diterima = "SELECT COUNT(*) as diterima FROM booking WHERE konselor_id = '$konselor_id' AND status = 'diterima'";
$result_diterima = mysqli_query($conn, $query_diterima);
$total_diterima = mysqli_fetch_assoc($result_diterima)['diterima'];

$query_selesai = "SELECT COUNT(*) as selesai FROM booking WHERE konselor_id = '$konselor_id' AND status = 'selesai'";
$result_selesai = mysqli_query($conn, $query_selesai);
$total_selesai = mysqli_fetch_assoc($result_selesai)['selesai'];

// Cek jadwal hari ini
$hari_ini = date('Y-m-d');
$query_hari_ini = "SELECT COUNT(*) as hari_ini FROM booking WHERE konselor_id = '$konselor_id' AND tanggal = '$hari_ini' AND status != 'ditolak'";
$result_hari_ini = mysqli_query($conn, $query_hari_ini);
$jadwal_hari_ini = mysqli_fetch_assoc($result_hari_ini)['hari_ini'];

// Ambil booking khusus konselor ini + nama siswa
$query = "SELECT booking.*, u.nama AS nama_siswa 
          FROM booking 
          JOIN users u ON booking.user_id = u.id 
          WHERE booking.konselor_id = '$konselor_id' 
          ORDER BY booking.id DESC";

$data = mysqli_query($conn, $query);

if (!$data) {
    $data = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Dashboard Konselor - Ruang Bicara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span>✓</span>
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <span>⚠</span>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="header">
        <h1 class="title">Dashboard Konselor</h1>
        <a href="../auth/logout.php" class="btn btn-outline">Logout</a>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-info">
                <div class="stat-value"><?= $total_booking; ?></div>
                <div class="stat-label">Total Booking</div>
            </div>
            <div class="stat-icon">📋</div>
        </div>
        
        <div class="stat-card orange">
            <div class="stat-info">
                <div class="stat-value"><?= $total_pending; ?></div>
                <div class="stat-label">Menunggu Konfirmasi</div>
            </div>
            <div class="stat-icon">⏳</div>
        </div>
        
        <div class="stat-card green">
            <div class="stat-info">
                <div class="stat-value"><?= $total_diterima; ?></div>
                <div class="stat-label">Dalam Proses</div>
            </div>
            <div class="stat-icon">✓</div>
        </div>
        
        <div class="stat-card blue">
            <div class="stat-info">
                <div class="stat-value"><?= $jadwal_hari_ini; ?></div>
                <div class="stat-label">Jadwal Hari Ini</div>
            </div>
            <div class="stat-icon">📅</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <span>📋</span>
                Daftar Booking Konseling
            </h3>
        </div>
        
        <div class="table-container">
            <?php if($data === null): ?>
                <div class="alert alert-error">
                    <p>Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Keluhan</th>
                            <th>No Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        if(mysqli_num_rows($data) > 0) {
                            while($row = mysqli_fetch_assoc($data)) { 
                        ?>
                        <tr>
                            <td data-label="No"><?= $no++; ?></td>
                            <td data-label="Siswa"><?= htmlspecialchars($row['nama_siswa']); ?></td>
                            <td data-label="Tanggal"><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td data-label="Jam"><?= htmlspecialchars($row['jam']); ?></td>
                            <td data-label="Keluhan"><?= htmlspecialchars($row['keluhan']); ?></td>
                            <td data-label="No Telepon"><?= htmlspecialchars($row['no_telp']); ?></td>
                            <td data-label="Status">
                                <?php if ($row['status'] == 'pending') { ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php } elseif ($row['status'] == 'diterima') { ?>
                                    <span class="badge badge-approved">Diterima</span>
                                <?php } elseif ($row['status'] == 'selesai') { ?>
                                    <span class="badge badge-completed">Selesai</span>
                                <?php } elseif ($row['status'] == 'ditolak') { ?>
                                    <span class="badge badge-rejected">Ditolak</span>
                                <?php } ?>
                            </td>
                            <td data-label="Aksi">
                                <div class="flex gap-2">
                                    <?php if ($row['status'] == 'pending') { ?>
                                        <a class="btn btn-success" href="terima.php?id=<?= $row['id']; ?>" onclick="return confirm('Terima booking ini?')">Terima</a>
                                        <a class="btn btn-danger" href="tolak.php?id=<?= $row['id']; ?>" onclick="return confirm('Tolak booking ini?')">Tolak</a>
                                    <?php } elseif ($row['status'] == 'diterima') { ?>
                                        <a class="btn" href="selesai.php?id=<?= $row['id']; ?>" onclick="return confirm('Selesaikan konseling?')">Selesai</a>
                                    <?php } else { ?>
                                        <span class="badge">-</span>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada booking masuk</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .flex {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

</body>
</html>