<?php
// File: modules/absensi/laporan.php

// panggil file koneksi database
require_once "./config/database.php";

// untuk filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$id_user_filter = isset($_GET['id_user']) ? (int)$_GET['id_user'] : '';

// Ambil daftar user untuk filter
$query_users = mysqli_query($mysqli, "SELECT id_user, namalengkap FROM tbl_user ORDER BY namalengkap ASC");

// build WHERE clause
$where_conditions = array();
$where_conditions[] = "DATE_FORMAT(a.tanggal, '%Y-%m') = '$bulan'";

if (!empty($id_user_filter)) {
    $where_conditions[] = "a.id_user = '$id_user_filter'";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Query untuk laporan absensi
$query = mysqli_query($mysqli, "
    SELECT 
        u.id_user,
        u.namalengkap,
        u.jabatan,
        COUNT(a.id_absensi) as total_kehadiran,
        SUM(CASE WHEN a.status_masuk = 'Tepat Waktu' THEN 1 ELSE 0 END) as tepat_waktu,
        SUM(CASE WHEN a.status_masuk = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
        SUM(CASE WHEN a.jam_masuk IS NULL THEN 1 ELSE 0 END) as alpha,
        SUM(CASE WHEN a.status_keluar = 'Pulang Awal' THEN 1 ELSE 0 END) as pulang_awal
    FROM tbl_user u 
    LEFT JOIN tbl_absensi a ON u.id_user = a.id_user AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$bulan'
    " . (!empty($id_user_filter) ? "WHERE u.id_user = '$id_user_filter'" : "") . "
    GROUP BY u.id_user, u.namalengkap, u.jabatan
    ORDER BY u.namalengkap ASC
") or die('Ada kesalahan pada query laporan : ' . mysqli_error($mysqli));

// Hitung total hari kerja dalam bulan
$tahun_bulan = explode('-', $bulan);
$tahun = $tahun_bulan[0];
$bulan_num = $tahun_bulan[1];
$total_hari = cal_days_in_month(CAL_GREGORIAN, $bulan_num, $tahun);

// Hitung hari kerja (tidak termasuk Sabtu dan Minggu)
$hari_kerja = 0;
for ($i = 1; $i <= $total_hari; $i++) {
    $hari = date('N', mktime(0, 0, 0, $bulan_num, $i, $tahun));
    if ($hari <= 5) { // Senin-Jumat
        $hari_kerja++;
    }
}
?>

<div class="container-fluid">
    <!-- judul halaman -->
    <h1 class="h4 mb-4 text-gray-800"><i class="fas fa-chart-bar fa-fw mr-2"></i>Laporan Absensi</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold">Laporan Bulanan</h6>
                </div>
                <div class="col-md-6 text-right">
                    <a href="?module=absensi" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="">
                        <input type="hidden" name="module" value="absensi">
                        <input type="hidden" name="action" value="laporan">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="month" name="bulan" class="form-control" value="<?php echo $bulan; ?>">
                            </div>
                            <div class="col-md-5">
                                <select name="id_user" class="form-control">
                                    <option value="">Semua Karyawan</option>
                                    <?php while ($user = mysqli_fetch_assoc($query_users)) : ?>
                                        <option value="<?php echo $user['id_user']; ?>"
                                            <?php echo ($id_user_filter == $user['id_user']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['namalengkap']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-block" type="submit">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group">
                        <a href="?module=absensi&action=export&bulan=<?php echo $bulan; ?>&id_user=<?php echo $id_user_filter; ?>"
                            class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i>Export Excel
                        </a>
                        <a href="javascript:window.print()" class="btn btn-info btn-sm">
                            <i class="fas fa-print mr-1"></i>Cetak
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Periode -->
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Periode:</strong> <?php echo date('F Y', strtotime($bulan . '-01')); ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <strong>Total Hari Kerja:</strong> <?php echo $hari_kerja; ?> hari
                    </div>
                </div>
            </div>

            <!-- Tabel Laporan -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Karyawan</th>
                            <th width="15%">Jabatan</th>
                            <th width="10%">Total Hadir</th>
                            <th width="10%">Tepat Waktu</th>
                            <th width="10%">Terlambat</th>
                            <th width="10%">Alpha</th>
                            <th width="10%">Pulang Awal</th>
                            <th width="5%">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $total_kehadiran_all = 0;
                        $total_tepat_waktu_all = 0;
                        $total_terlambat_all = 0;
                        $total_alpha_all = 0;
                        $total_pulang_awal_all = 0;

                        while ($data = mysqli_fetch_assoc($query)) :
                            $persentase = ($hari_kerja > 0) ? round(($data['total_kehadiran'] / $hari_kerja) * 100, 1) : 0;

                            $total_kehadiran_all += $data['total_kehadiran'];
                            $total_tepat_waktu_all += $data['tepat_waktu'];
                            $total_terlambat_all += $data['terlambat'];
                            $total_alpha_all += ($hari_kerja - $data['total_kehadiran']);
                            $total_pulang_awal_all += $data['pulang_awal'];
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($data['namalengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data['jabatan']); ?></td>
                                <td>
                                    <span class="badge badge-primary"><?php echo $data['total_kehadiran']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-success"><?php echo $data['tepat_waktu']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-warning"><?php echo $data['terlambat']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-danger"><?php echo ($hari_kerja - $data['total_kehadiran']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?php echo $data['pulang_awal']; ?></span>
                                </td>
                                <td>
                                    <span class="font-weight-bold 
                                          <?php echo $persentase >= 80 ? 'text-success' : ($persentase >= 60 ? 'text-warning' : 'text-danger'); ?>">
                                        <?php echo $persentase; ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($query) == 0) : ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="py-3">
                                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada data laporan untuk periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <?php if (mysqli_num_rows($query) > 0) : ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-3">Ringkasan Laporan</h6>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 font-weight-bold text-primary"><?php echo $total_kehadiran_all; ?></div>
                                            <div class="text-xs text-uppercase">Total Kehadiran</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 font-weight-bold text-success"><?php echo $total_tepat_waktu_all; ?></div>
                                            <div class="text-xs text-uppercase">Tepat Waktu</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 font-weight-bold text-warning"><?php echo $total_terlambat_all; ?></div>
                                            <div class="text-xs text-uppercase">Terlambat</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 font-weight-bold text-danger"><?php echo $total_alpha_all; ?></div>
                                            <div class="text-xs text-uppercase">Alpha</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 font-weight-bold text-secondary"><?php echo $total_pulang_awal_all; ?></div>
                                            <div class="text-xs text-uppercase">Pulang Awal</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 font-weight-bold text-info"><?php echo $hari_kerja; ?></div>
                                            <div class="text-xs text-uppercase">Hari Kerja</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    @media print {

        .btn,
        .card-header,
        .form-group {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>