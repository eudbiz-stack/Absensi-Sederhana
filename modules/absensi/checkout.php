<?php
// File: modules/absensi/checkout.php

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

// Cek status check in dan check out
$belum_checkin = !$sudah_absen || !$data_absen['jam_masuk'];
$sudah_checkout = $sudah_absen && $data_absen['jam_keluar'];
?>

<div class="container-fluid">
    <!-- judul halaman -->
    <h1 class="h4 mb-4 text-gray-800">
        <i class="fas fa-sign-out-alt fa-fw mr-2"></i>Check Out Absensi
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
                    <h6 class="m-0 font-weight-bold">Status Absensi Hari Ini</h6>
                </div>
                <div class="card-body">
                    <?php if ($belum_checkin) : ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Anda belum melakukan check in hari ini!
                        </div>
                        <p class="text-muted">Silakan lakukan check in terlebih dahulu sebelum check out.</p>
                        <a href="?module=absensi&action=checkin" class="btn btn-success">
                            <i class="fas fa-sign-in-alt mr-1"></i>Check In
                        </a>
                    <?php else : ?>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%">Check In</td>
                                <td width="5%">:</td>
                                <td>
                                    <?php echo date('H:i:s', strtotime($data_absen['jam_masuk'])); ?>
                                    <span class="badge badge-<?php echo $data_absen['status_masuk'] == 'Tepat Waktu' ? 'success' : 'warning'; ?> ml-2">
                                        <?php echo $data_absen['status_masuk']; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Check Out</td>
                                <td>:</td>
                                <td>
                                    <?php if ($sudah_checkout) : ?>
                                        <?php echo date('H:i:s', strtotime($data_absen['jam_keluar'])); ?>
                                        <span class="badge badge-<?php echo $data_absen['status_keluar'] == 'Normal' ? 'success' : 'warning'; ?> ml-2">
                                            <?php echo $data_absen['status_keluar']; ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-muted">Belum check out</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Jam Kerja</td>
                                <td>:</td>
                                <td>
                                    <?php if ($sudah_checkout) : ?>
                                        <?php
                                        $masuk = new DateTime($data_absen['jam_masuk']);
                                        $keluar = new DateTime($data_absen['jam_keluar']);
                                        $diff = $keluar->diff($masuk);
                                        echo $diff->format('%h jam %i menit');
                                        ?>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Check Out Absensi</h6>
        </div>
        <div class="card-body text-center">
            <?php if ($belum_checkin) : ?>
                <!-- Belum check in -->
                <div class="py-4">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mb-3"></i>
                    <h4>Belum Check In</h4>
                    <p class="text-muted">Anda harus check in terlebih dahulu sebelum bisa check out</p>
                </div>

                <a href="?module=absensi&action=checkin" class="btn btn-success btn-lg mr-2">
                    <i class="fas fa-sign-in-alt mr-1"></i>Check In Dulu
                </a>
                <a href="?module=absensi" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>

            <?php elseif ($sudah_checkout) : ?>
                <!-- Sudah check out -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Anda sudah melakukan check out pada pukul <?php echo date('H:i:s', strtotime($data_absen['jam_keluar'])); ?>
                    dengan status:
                    <span class="badge badge-<?php echo $data_absen['status_keluar'] == 'Normal' ? 'success' : 'warning'; ?>">
                        <?php echo $data_absen['status_keluar']; ?>
                    </span>
                </div>

                <div class="py-4">
                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                    <h4>Check Out Berhasil</h4>
                    <p class="text-muted">Waktu Check Out: <?php echo date('H:i:s', strtotime($data_absen['jam_keluar'])); ?></p>
                    <?php
                    $masuk = new DateTime($data_absen['jam_masuk']);
                    $keluar = new DateTime($data_absen['jam_keluar']);
                    $diff = $keluar->diff($masuk);
                    ?>
                    <p class="text-muted">Total Jam Kerja: <?php echo $diff->format('%h jam %i menit'); ?></p>
                </div>

                <a href="?module=absensi" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>

            <?php else : ?>
                <!-- Form check out -->
                <div class="py-4">
                    <i class="fas fa-clock fa-5x text-warning mb-3"></i>
                    <h4>Lakukan Check Out</h4>
                    <p class="text-muted">Klik tombol di bawah untuk melakukan absensi keluar</p>
                    <p class="text-muted">
                        <small>Check In: <?php echo date('H:i:s', strtotime($data_absen['jam_masuk'])); ?></small>
                    </p>
                </div>

                <form action="modules/absensi/proses_checkout.php" method="post" id="checkout-form">
                    <input type="hidden" name="id_absensi" value="<?php echo $data_absen['id_absensi']; ?>">
                    <input type="hidden" name="jam_keluar" id="jam_keluar">

                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <textarea name="keterangan_keluar" class="form-control" rows="3"
                            placeholder="Masukkan keterangan jika diperlukan..."><?php echo htmlspecialchars($data_absen['keterangan']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg mr-2" onclick="setCurrentTime()">
                        <i class="fas fa-sign-out-alt mr-2"></i>Check Out Sekarang
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

    // Set waktu saat check out
    function setCurrentTime() {
        const now = new Date();
        const timeString = now.toTimeString().split(' ')[0];
        document.getElementById('jam_keluar').value = timeString;
    }

    // Update waktu setiap detik
    setInterval(updateTime, 1000);
    updateTime(); // Initial call

    // Konfirmasi sebelum check out
    document.getElementById('checkout-form')?.addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda yakin ingin melakukan check out sekarang?')) {
            e.preventDefault();
        }
    });
</script>