<?php
// File: modules/absensi/checkin.php

// panggil file koneksi database
require_once "./config/database.php";

// Ambil ID user dari session
$current_user_id = $_SESSION['user_id'];

// Cek apakah user sudah absen hari ini
$tanggal_hari_ini = date('Y-m-d');
$cek_absen = mysqli_query($mysqli, "SELECT * FROM tbl_absensi WHERE id_user = '$current_user_id' AND tanggal = '$tanggal_hari_ini'");
$sudah_absen = mysqli_num_rows($cek_absen) > 0;
$data_absen = mysqli_fetch_assoc($cek_absen);

// Ambil data user
$query_user = mysqli_query($mysqli, "SELECT * FROM tbl_user WHERE id_user = '$current_user_id'");
$user = mysqli_fetch_assoc($query_user);

// Ambil setting jam kerja default
$query_jam_kerja = mysqli_query($mysqli, "SELECT * FROM tbl_jam_kerja WHERE is_active = 1 LIMIT 1");
$jam_kerja = mysqli_fetch_assoc($query_jam_kerja);
?>

<div class="container-fluid">
    <!-- judul halaman -->
    <h1 class="h4 mb-4 text-gray-800">
        <i class="fas fa-sign-in-alt fa-fw mr-2"></i>Check In Absensi
    </h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Informasi Karyawan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%">Nama</td>
                            <td width="5%">:</td>
                            <td><?php echo htmlspecialchars($user['namalengkap']); ?></td>
                        </tr>
                        <tr>
                            <td>Username</td>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                        </tr>
                        <tr>
                            <td>Jabatan</td>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($user['jabatan']); ?></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td><?php echo date('d F Y'); ?></td>
                        </tr>
                        <tr>
                            <td>Waktu</td>
                            <td>:</td>
                            <td><span id="current-time"></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Jam Kerja</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%">Shift</td>
                            <td width="5%">:</td>
                            <td><?php echo htmlspecialchars($jam_kerja['nama_shift']); ?></td>
                        </tr>
                        <tr>
                            <td>Jam Masuk</td>
                            <td>:</td>
                            <td><?php echo date('H:i', strtotime($jam_kerja['jam_masuk'])); ?></td>
                        </tr>
                        <tr>
                            <td>Jam Keluar</td>
                            <td>:</td>
                            <td><?php echo date('H:i', strtotime($jam_kerja['jam_keluar'])); ?></td>
                        </tr>
                        <tr>
                            <td>Batas Terlambat</td>
                            <td>:</td>
                            <td><?php echo date('H:i', strtotime($jam_kerja['batas_telat'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Check In Absensi</h6>
        </div>
        <div class="card-body text-center">
            <?php if ($sudah_absen && $data_absen['jam_masuk']) : ?>
                <!-- Jika sudah check in -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Anda sudah melakukan check in pada pukul <?php echo date('H:i:s', strtotime($data_absen['jam_masuk'])); ?>
                    dengan status:
                    <span class="badge badge-<?php echo $data_absen['status_masuk'] == 'Tepat Waktu' ? 'success' : 'warning'; ?>">
                        <?php echo $data_absen['status_masuk']; ?>
                    </span>
                </div>

                <div class="py-4">
                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                    <h4>Check In Berhasil</h4>
                    <p class="text-muted">Waktu Check In: <?php echo date('H:i:s', strtotime($data_absen['jam_masuk'])); ?></p>
                </div>

                <a href="?module=absensi" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
                <a href="?module=absensi&action=checkout" class="btn btn-warning">
                    <i class="fas fa-sign-out-alt mr-1"></i>Check Out
                </a>
            <?php else : ?>
                <!-- Form check in -->
                <div class="py-4">
                    <i class="fas fa-clock fa-5x text-primary mb-3"></i>
                    <h4>Lakukan Check In</h4>
                    <p class="text-muted">Klik tombol di bawah untuk melakukan absensi masuk</p>
                </div>

                <form action="modules/absensi/proses_checkin.php" method="post" id="checkin-form">
                    <input type="hidden" name="id_user" value="<?php echo $current_user_id; ?>">
                    <input type="hidden" name="tanggal" value="<?php echo $tanggal_hari_ini; ?>">
                    <input type="hidden" name="jam_masuk" id="jam_masuk">

                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3"
                            placeholder="Masukkan keterangan jika diperlukan..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg mr-2" onclick="setCurrentTime()">
                        <i class="fas fa-fingerprint mr-2"></i>Check In Sekarang
                    </button>
                    <a href="?module=absensi" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left mr-1"></i>Batal
                    </a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Update waktu real-time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID');
        document.getElementById('current-time').textContent = timeString;
    }

    // Set waktu saat check in
    function setCurrentTime() {
        const now = new Date();
        const timeString = now.toTimeString().split(' ')[0];
        document.getElementById('jam_masuk').value = timeString;
    }

    // Update waktu setiap detik
    setInterval(updateTime, 1000);
    updateTime(); // Initial call

    // Konfirmasi sebelum check in
    document.getElementById('checkin-form').addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda yakin ingin melakukan check in sekarang?')) {
            e.preventDefault();
        }
    });
</script>