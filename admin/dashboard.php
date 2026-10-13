<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Statistik keseluruhan
$query_total_siswa = "SELECT COUNT(*) as total FROM users WHERE role = 'siswa'";
$result_siswa = mysqli_query($conn, $query_total_siswa);
$total_siswa = mysqli_fetch_assoc($result_siswa)['total'];

$query_total_konselor = "SELECT COUNT(*) as total FROM users WHERE role = 'konselor'";
$result_konselor = mysqli_query($conn, $query_total_konselor);
$total_konselor = mysqli_fetch_assoc($result_konselor)['total'];

$query_total_booking = "SELECT COUNT(*) as total FROM booking";
$result_booking = mysqli_query($conn, $query_total_booking);
$total_booking = mysqli_fetch_assoc($result_booking)['total'];

$query_pending = "SELECT COUNT(*) as pending FROM booking WHERE status = 'pending'";
$result_pending = mysqli_query($conn, $query_pending);
$total_pending = mysqli_fetch_assoc($result_pending)['pending'];

$query_selesai = "SELECT COUNT(*) as selesai FROM booking WHERE status = 'selesai'";
$result_selesai = mysqli_query($conn, $query_selesai);
$total_selesai = mysqli_fetch_assoc($result_selesai)['selesai'];

$query_hari_ini = "SELECT COUNT(*) as hari_ini FROM booking WHERE tanggal = CURDATE()";
$result_hari_ini = mysqli_query($conn, $query_hari_ini);
$booking_hari_ini = mysqli_fetch_assoc($result_hari_ini)['hari_ini'];

// Ambil semua booking dengan detail user
$query_booking = "SELECT booking.*, 
                  siswa.nama AS nama_siswa, 
                  siswa.nis_nip AS nis_siswa,
                  konselor.nama AS nama_konselor
                  FROM booking 
                  JOIN users siswa ON booking.user_id = siswa.id 
                  JOIN users konselor ON booking.konselor_id = konselor.id 
                  ORDER BY booking.id DESC";
$data_booking = mysqli_query($conn, $query_booking);

// Ambil semua user untuk ditampilkan
$query_users = "SELECT * FROM users ORDER BY role, nama";
$data_users = mysqli_query($conn, $query_users);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Admin Dashboard - Ruang Bicara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 0.5rem;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            color: var(--gray-500);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }
        .tab-btn.active {
            background: var(--purple-primary);
            color: white;
        }
        .tab-btn:hover:not(.active) {
            background: var(--purple-lightest);
            color: var(--purple-primary);
        }
        .tab-pane {
            display: none;
            animation: fadeInUp 0.3s ease;
        }
        .tab-pane.active {
            display: block;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }
        .modal-lg .modal-content {
            max-width: 600px;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="header">
        <h1 class="title">Admin Dashboard</h1>
        <a href="../auth/logout.php" class="btn btn-outline">Logout</a>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-info">
                <div class="stat-value"><?= $total_siswa; ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>
            <div class="stat-icon">👨‍🎓</div>
        </div>
        
        <div class="stat-card blue">
            <div class="stat-info">
                <div class="stat-value"><?= $total_konselor; ?></div>
                <div class="stat-label">Total Konselor</div>
            </div>
            <div class="stat-icon">👨‍🏫</div>
        </div>
        
        <div class="stat-card green">
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
        
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-value"><?= $booking_hari_ini; ?></div>
                <div class="stat-label">Booking Hari Ini</div>
            </div>
            <div class="stat-icon">📅</div>
        </div>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('booking')">📋 Seluruh Booking</button>
        <button class="tab-btn" onclick="showTab('users')">👥 Kelola User</button>
    </div>

    <!-- TAB 1: SELURUH BOOKING -->
    <div id="tab-booking" class="tab-pane active">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Seluruh Booking</h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>NIS</th>
                            <th>Konselor</th>
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
                        if(mysqli_num_rows($data_booking) > 0) {
                            while($row = mysqli_fetch_assoc($data_booking)) { 
                        ?>
                        <tr>
                            <td data-label="No"><?= $no++; ?></td>
                            <td data-label="Siswa"><?= htmlspecialchars($row['nama_siswa']); ?></td>
                            <td data-label="NIS"><?= htmlspecialchars($row['nis_siswa']); ?></td>
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
                            <td data-label="Aksi">
                                <a class="btn btn-sm btn-danger" href="hapus_booking.php?id=<?= $row['id']; ?>" onclick="return confirm('Hapus booking ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                        <tr>
                            <td colspan="10" class="text-center">Belum ada booking</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: KELOLA USER -->
    <div id="tab-users" class="tab-pane">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar User</h3>
                <button class="btn" onclick="openUserModal('add')">+ Tambah User</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIS / NIP</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        if(mysqli_num_rows($data_users) > 0) {
                            while($user = mysqli_fetch_assoc($data_users)) { 
                        ?>
                        <tr>
                            <td data-label="No"><?= $no++; ?></td>
                            <td data-label="Nama"><?= htmlspecialchars($user['nama']); ?></td>
                            <td data-label="NIS/NIP"><?= htmlspecialchars($user['nis_nip']); ?></td>
                            <td data-label="Role">
                                <?php if($user['role'] == 'siswa') { ?>
                                    <span class="badge badge-approved">Siswa</span>
                                <?php } elseif($user['role'] == 'konselor') { ?>
                                    <span class="badge badge-completed">Konselor</span>
                                <?php } else { ?>
                                    <span class="badge badge-pending">Admin</span>
                                <?php } ?>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-secondary" onclick="openUserModal('edit', <?= $user['id']; ?>, '<?= htmlspecialchars($user['nama']); ?>', '<?= $user['nis_nip']; ?>', '<?= $user['role']; ?>')">Edit</button>
                                    <?php if($user['role'] != 'admin'): ?>
                                        <a class="btn btn-sm btn-danger" href="hapus_user.php?id=<?= $user['id']; ?>" onclick="return confirm('Hapus user ini?')">Hapus</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else { 
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada user</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT USER -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah User</h3>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm" action="proses_user.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="userId">
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="nis_nip">NIS / NIP</label>
                    <input type="text" name="nis_nip" id="nis_nip" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak ingin mengubah">
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="siswa">Siswa</option>
                        <option value="konselor">Konselor</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Batal</button>
                <button type="submit" class="btn">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    if(tabName === 'booking') {
        document.querySelector('.tab-btn:first-child').classList.add('active');
        document.getElementById('tab-booking').classList.add('active');
    } else {
        document.querySelector('.tab-btn:last-child').classList.add('active');
        document.getElementById('tab-users').classList.add('active');
    }
}

function openUserModal(action, id = null, nama = '', nis_nip = '', role = '') {
    const modal = document.getElementById('userModal');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const userId = document.getElementById('userId');
    const namaInput = document.getElementById('nama');
    const nisNipInput = document.getElementById('nis_nip');
    const passwordInput = document.getElementById('password');
    const roleSelect = document.getElementById('role');
    
    if(action === 'add') {
        modalTitle.textContent = 'Tambah User';
        formAction.value = 'add';
        userId.value = '';
        namaInput.value = '';
        nisNipInput.value = '';
        passwordInput.value = '';
        passwordInput.placeholder = 'Masukkan password';
        passwordInput.required = true;
        roleSelect.value = 'siswa';
    } else {
        modalTitle.textContent = 'Edit User';
        formAction.value = 'edit';
        userId.value = id;
        namaInput.value = nama;
        nisNipInput.value = nis_nip;
        passwordInput.value = '';
        passwordInput.placeholder = 'Kosongkan jika tidak ingin mengubah';
        passwordInput.required = false;
        roleSelect.value = role;
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
    document.body.style.overflow = '';
}

window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target == modal) {
        closeUserModal();
    }
}
</script>

</body>
</html>