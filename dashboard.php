<?php
// File: dashboard.php

// panggil file koneksi database
require_once "./config/database.php";

// Query untuk statistik dashboard
$today = date('Y-m-d');
$this_month = date('Y-m');

// Total karyawan
$query_total_user = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM tbl_user");
$total_user = mysqli_fetch_assoc($query_total_user)['total'];

// Absensi hari ini
$query_today = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM tbl_absensi WHERE tanggal = '$today'");
$absensi_today = mysqli_fetch_assoc($query_today)['total'];

// Yang sudah check in hari ini
$query_checkin = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM tbl_absensi WHERE tanggal = '$today' AND jam_masuk IS NOT NULL");
$checkin_today = mysqli_fetch_assoc($query_checkin)['total'];

// Yang sudah check out hari ini
$query_checkout = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM tbl_absensi WHERE tanggal = '$today' AND jam_keluar IS NOT NULL");
$checkout_today = mysqli_fetch_assoc($query_checkout)['total'];

// Terlambat hari ini
$query_terlambat = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM tbl_absensi WHERE tanggal = '$today' AND status_masuk = 'Terlambat'");
$terlambat_today = mysqli_fetch_assoc($query_terlambat)['total'];

// Absensi terbaru
$query_recent = mysqli_query($mysqli, "
    SELECT a.*, u.namalengkap, u.jabatan 
    FROM tbl_absensi a 
    JOIN tbl_user u ON a.id_user = u.id_user 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

// Yang belum absen hari ini
$query_belum_absen = mysqli_query($mysqli, "
    SELECT u.* 
    FROM tbl_user u 
    LEFT JOIN tbl_absensi a ON u.id_user = a.id_user AND a.tanggal = '$today'
    WHERE a.id_absensi IS NULL
    LIMIT 5
");
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
    </a>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Total Karyawan -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Karyawan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_user; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check In Hari Ini -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Check In Hari Ini</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $checkin_today; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check Out Hari Ini -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Check Out Hari Ini</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $checkout_today; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-sign-out-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terlambat Hari Ini -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Terlambat Hari Ini</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $terlambat_today; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Content Row -->
<div class="row">

    <!-- Absensi Terbaru -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Absensi Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Tanggal</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($recent = mysqli_fetch_assoc($query_recent)) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($recent['namalengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($recent['jabatan']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($recent['tanggal'])); ?></td>
                                    <td>
                                        <?php echo $recent['jam_masuk'] ? date('H:i', strtotime($recent['jam_masuk'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo $recent['jam_keluar'] ? date('H:i', strtotime($recent['jam_keluar'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($recent['status_masuk']) : ?>
                                            <span class="badge badge-<?php echo $recent['status_masuk'] == 'Tepat Waktu' ? 'success' : 'warning'; ?>">
                                                <?php echo $recent['status_masuk']; ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if (mysqli_num_rows($query_recent) == 0) : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data absensi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-right">
                    <a href="main.php?module=absensi" class="btn btn-primary btn-sm">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Yang Belum Absen -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Belum Absen Hari Ini</h6>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($query_belum_absen) > 0) : ?>
                    <?php while ($belum = mysqli_fetch_assoc($query_belum_absen)) : ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-warning">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="font-weight-bold"><?php echo htmlspecialchars($belum['namalengkap']); ?></div>
                                <div class="text-gray-600 small"><?php echo htmlspecialchars($belum['jabatan']); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <div class="text-right mt-3">
                        <span class="text-muted small">
                            Total: <?php echo ($total_user - $checkin_today); ?> karyawan
                        </span>
                    </div>
                <?php else : ?>
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Semua karyawan sudah absen!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="main.php?module=absensi&action=checkin" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-sign-in-alt mr-2"></i>Check In
                    </a>
                    <a href="main.php?module=absensi&action=checkout" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-sign-out-alt mr-2"></i>Check Out
                    </a>
                    <a href="main.php?module=absensi&action=laporan" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-chart-bar mr-2"></i>Laporan
                    </a>
                    <a href="main.php?module=user&action=tambah" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus mr-2"></i>Tambah User
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .icon-circle {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-block {
        display: block;
        width: 100%;
    }
</style>