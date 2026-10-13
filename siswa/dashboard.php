<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

$nama_siswa = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Siswa';
$user_id = $_SESSION['id'];

// Hitung statistik untuk dashboard
$query_total = "SELECT COUNT(*) as total FROM booking WHERE user_id = '$user_id'";
$result_total = mysqli_query($conn, $query_total);
$total_booking = mysqli_fetch_assoc($result_total)['total'];

$query_pending = "SELECT COUNT(*) as pending FROM booking WHERE user_id = '$user_id' AND status = 'pending'";
$result_pending = mysqli_query($conn, $query_pending);
$total_pending = mysqli_fetch_assoc($result_pending)['pending'];

$query_diterima = "SELECT COUNT(*) as diterima FROM booking WHERE user_id = '$user_id' AND status = 'diterima'";
$result_diterima = mysqli_query($conn, $query_diterima);
$total_diterima = mysqli_fetch_assoc($result_diterima)['diterima'];

$query_selesai = "SELECT COUNT(*) as selesai FROM booking WHERE user_id = '$user_id' AND status = 'selesai'";
$result_selesai = mysqli_query($conn, $query_selesai);
$total_selesai = mysqli_fetch_assoc($result_selesai)['selesai'];

// Cek booking hari ini
$hari_ini = date('Y-m-d');
$query_hari_ini = "SELECT COUNT(*) as hari_ini FROM booking WHERE user_id = '$user_id' AND tanggal = '$hari_ini' AND status != 'ditolak'";
$result_hari_ini = mysqli_query($conn, $query_hari_ini);
$booking_hari_ini = mysqli_fetch_assoc($result_hari_ini)['hari_ini'];

// Ambil booking siswa + nama konselor
$query_booking = "SELECT booking.*, k.nama AS nama_konselor 
                   FROM booking 
                   JOIN users k ON booking.konselor_id = k.id 
                   WHERE booking.user_id = '$user_id' 
                   ORDER BY booking.id DESC";
$data = mysqli_query($conn, $query_booking);

if (!$data) {
    $data = null;
}

// Ambil list konselor
$query_konselor = "SELECT * FROM users WHERE role='konselor'";
$konselor = mysqli_query($conn, $query_konselor);

if (!$konselor) {
    $konselor = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Dashboard Siswa - Ruang Bicara</title>
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
        <h1 class="title">Halo, <?= htmlspecialchars($nama_siswa); ?></h1>
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
                <div class="stat-label">Diterima</div>
            </div>
            <div class="stat-icon">✓</div>
        </div>
        
        <div class="stat-card blue">
            <div class="stat-info">
                <div class="stat-value"><?= $booking_hari_ini; ?></div>
                <div class="stat-label">Booking Hari Ini</div>
            </div>
            <div class="stat-icon">📅</div>
        </div>
    </div>

    <div class="mb-4">
        <button class="btn" onclick="openModal()">+ Booking Konselor</button>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <span>📋</span>
                Riwayat Booking
            </h3>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Konselor</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Keluhan</th>
                        <th>No Telepon</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    if($data && mysqli_num_rows($data) > 0) {
                        while($row = mysqli_fetch_assoc($data)) { 
                    ?>
                    <tr>
                        <td data-label="No"><?= $no++; ?></td>
                        <td data-label="Konselor"><?= htmlspecialchars($row['nama_konselor']); ?></td>
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
                    </tr>
                    <?php 
                        } 
                    } else { 
                    ?>
                    <tr>
                        <td colspan="7" class="text-center">Belum ada riwayat booking</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL BOOKING -->
<div id="modalBooking" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Booking Konselor</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        
        <form action="proses_booking.php" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="konselor_id">Pilih Konselor</label>
                    <select name="konselor_id" id="konselor_id" required>
                        <option value="">-- Pilih Konselor --</option>
                        <?php 
                        if($konselor && mysqli_num_rows($konselor) > 0) {
                            mysqli_data_seek($konselor, 0);
                            while($k = mysqli_fetch_assoc($konselor)) { 
                        ?>
                            <option value="<?= $k['id']; ?>">
                                <?= htmlspecialchars($k['nama']); ?>
                            </option>
                        <?php 
                            } 
                        } else {
                        ?>
                            <option value="" disabled>Belum ada konselor tersedia</option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" min="<?= date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="jam">Jam</label>
                    <input type="time" name="jam" id="jam" min="08:00" max="14:00" step="900" required>
                    <small>Jam layanan: 08:00 - 14:00</small>
                </div>

                <div class="form-group">
                    <label for="keluhan">Keluhan</label>
                    <textarea name="keluhan" id="keluhan" rows="3" placeholder="Ceritakan keluhan Anda di sini..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="no_telp">Nomor Telepon</label>
                    <input type="tel" name="no_telp" id="no_telp" placeholder="Contoh: 081234567890" pattern="[0-9]{10,13}" required>
                    <small>Masukkan nomor telepon aktif (10-13 digit angka)</small>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn">Kirim Booking</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById("modalBooking").classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeModal() {
    document.getElementById("modalBooking").classList.remove("active");
    document.body.style.overflow = "";
}

window.onclick = function(event) {
    const modal = document.getElementById("modalBooking");
    if (event.target == modal) {
        closeModal();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const today = new Date().toISOString().split('T')[0];
    const tanggalInput = document.getElementById("tanggal");
    if(tanggalInput) {
        tanggalInput.setAttribute("min", today);
    }
    
    const noTelpInput = document.getElementById("no_telp");
    if(noTelpInput) {
        noTelpInput.addEventListener("input", function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>

</body>
</html>