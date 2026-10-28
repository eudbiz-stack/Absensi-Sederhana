<?php
// File: modules/absensi/detail.php

// panggil file koneksi database
require_once "./config/database.php";

// ambil ID absensi dari URL
$id_absensi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// query untuk menampilkan detail absensi dengan join user
$query = mysqli_query($mysqli, "
    SELECT a.*, u.username, u.namalengkap, u.jabatan, u.jeniskelamin
    FROM tbl_absensi a 
    JOIN tbl_user u ON a.id_user = u.id_user 
    WHERE a.id_absensi = '$id_absensi'
") or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

// cek apakah data ada
if (mysqli_num_rows($query) == 0) {
    echo "<script>";
    echo "alert('Data absensi tidak ditemukan!');";
    echo "window.location = 'main.php?module=absensi';";
    echo "</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

// Hitung total jam kerja jika sudah check out
$total_jam_kerja = null;
$total_menit = 0;
if ($data['jam_masuk'] && $data['jam_keluar']) {
    $masuk = new DateTime($data['jam_masuk']);
    $keluar = new DateTime($data['jam_keluar']);
    $diff = $keluar->diff($masuk);
    $total_jam_kerja = $diff->format('%h jam %i menit');
    $total_menit = ($diff->h * 60) + $diff->i;
}

// Ambil setting jam kerja untuk perbandingan
$query_jam_kerja = mysqli_query($mysqli, "SELECT * FROM tbl_jam_kerja WHERE is_active = 1 LIMIT 1");
$jam_kerja = mysqli_fetch_assoc($query_jam_kerja);

// Hitung keterlambatan jika ada
$keterlambatan = null;
if ($data['jam_masuk'] && $jam_kerja) {
    $jam_masuk_actual = new DateTime($data['jam_masuk']);
    $jam_masuk_seharusnya = new DateTime($jam_kerja['jam_masuk']);

    if ($jam_masuk_actual > $jam_masuk_seharusnya) {
        $diff_telat = $jam_masuk_actual->diff($jam_masuk_seharusnya);
        $keterlambatan = $diff_telat->format('%h jam %i menit');
    }
}

// Hitung pulang awal jika ada
$pulang_awal = null;
if ($data['jam_keluar'] && $jam_kerja) {
    $jam_keluar_actual = new DateTime($data['jam_keluar']);
    $jam_keluar_seharusnya = new DateTime($jam_kerja['jam_keluar']);

    if ($jam_keluar_actual < $jam_keluar_seharusnya) {
        $diff_awal = $jam_keluar_seharusnya->diff($jam_keluar_actual);
        $pulang_awal = $diff_awal->format('%h jam %i menit');
    }
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-eye text-primary mr-2"></i>Detail Absensi
            </h1>
            <p class="mb-0 text-muted">Informasi lengkap data absensi karyawan</p>
        </div>
        <div class="btn-group">
            <a href="?module=absensi" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
            <a href="?module=absensi&action=export&id=<?php echo $id_absensi; ?>" class="btn btn-info">
                <i class="fas fa-download mr-1"></i>Export
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print mr-1"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Informasi Karyawan -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user mr-2"></i>Informasi Karyawan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%" class="font-weight-bold">Nama Lengkap</td>
                                    <td width="5%">:</td>
                                    <td><?php echo htmlspecialchars($data['namalengkap']); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Username</td>
                                    <td>:</td>
                                    <td>
                                        <code><?php echo htmlspecialchars($data['username']); ?></code>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Jenis Kelamin</td>
                                    <td>:</td>
                                    <td>
                                        <?php
                                        if ($data['jeniskelamin'] == 'L') {
                                            echo '<span class="badge badge-primary"><i class="fas fa-male mr-1"></i>Laki-laki</span>';
                                        } else {
                                            echo '<span class="badge badge-info"><i class="fas fa-female mr-1"></i>Perempuan</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%" class="font-weight-bold">Jabatan</td>
                                    <td width="5%">:</td>
                                    <td>
                                        <span class="badge badge-secondary p-2">
                                            <?php echo htmlspecialchars($data['jabatan']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">ID Karyawan</td>
                                    <td>:</td>
                                    <td>
                                        <code>#<?php echo str_pad($data['id_user'], 4, '0', STR_PAD_LEFT); ?></code>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Avatar</td>
                                    <td>:</td>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($data['namalengkap']); ?>&background=4e73df&color=ffffff&size=50"
                                            class="rounded-circle" width="50" height="50" alt="Avatar">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Summary -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-pie mr-2"></i>Status Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                            <h5 class="font-weight-bold">
                                <?php echo date('d F Y', strtotime($data['tanggal'])); ?>
                            </h5>
                            <p class="text-muted small">
                                <?php echo date('l', strtotime($data['tanggal'])); ?>
                            </p>
                        </div>

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-right">
                                    <div class="h6 font-weight-bold text-success">
                                        <?php echo $data['status_masuk'] ?: 'Belum Check In'; ?>
                                    </div>
                                    <small class="text-muted">Status Masuk</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h6 font-weight-bold text-warning">
                                    <?php echo $data['status_keluar'] ?: 'Belum Check Out'; ?>
                                </div>
                                <small class="text-muted">Status Keluar</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Absensi -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clock mr-2"></i>Detail Waktu Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Check In -->
                            <div class="border p-3 rounded mb-3 bg-light">
                                <h6 class="font-weight-bold text-success mb-3">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Check In
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="40%" class="font-weight-bold">Waktu</td>
                                        <td width="5%">:</td>
                                        <td>
                                            <?php if ($data['jam_masuk']) : ?>
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo date('H:i:s', strtotime($data['jam_masuk'])); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="text-muted">Belum check in</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Status</td>
                                        <td>:</td>
                                        <td>
                                            <?php if ($data['status_masuk']) : ?>
                                                <span class="badge badge-<?php echo $data['status_masuk'] == 'Tepat Waktu' ? 'success' : 'warning'; ?>">
                                                    <i class="fas fa-<?php echo $data['status_masuk'] == 'Tepat Waktu' ? 'check' : 'exclamation-triangle'; ?> mr-1"></i>
                                                    <?php echo $data['status_masuk']; ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if ($keterlambatan) : ?>
                                        <tr>
                                            <td class="font-weight-bold text-warning">Keterlambatan</td>
                                            <td>:</td>
                                            <td>
                                                <span class="text-warning font-weight-bold">
                                                    <i class="fas fa-clock mr-1"></i><?php echo $keterlambatan; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Check Out -->
                            <div class="border p-3 rounded mb-3 bg-light">
                                <h6 class="font-weight-bold text-warning mb-3">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Check Out
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="40%" class="font-weight-bold">Waktu</td>
                                        <td width="5%">:</td>
                                        <td>
                                            <?php if ($data['jam_keluar']) : ?>
                                                <span class="badge badge-warning p-2">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo date('H:i:s', strtotime($data['jam_keluar'])); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="text-muted">Belum check out</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Status</td>
                                        <td>:</td>
                                        <td>
                                            <?php if ($data['status_keluar']) : ?>
                                                <span class="badge badge-<?php echo $data['status_keluar'] == 'Normal' ? 'success' : 'warning'; ?>">
                                                    <i class="fas fa-<?php echo $data['status_keluar'] == 'Normal' ? 'check' : 'exclamation-triangle'; ?> mr-1"></i>
                                                    <?php echo $data['status_keluar']; ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if ($pulang_awal) : ?>
                                        <tr>
                                            <td class="font-weight-bold text-danger">Pulang Awal</td>
                                            <td>:</td>
                                            <td>
                                                <span class="text-danger font-weight-bold">
                                                    <i class="fas fa-clock mr-1"></i><?php echo $pulang_awal; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Total Jam Kerja -->
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-primary text-white rounded">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h5 class="font-weight-bold">
                                        <?php echo $total_jam_kerja ?: 'Belum Selesai'; ?>
                                    </h5>
                                    <small>Total Jam Kerja</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-success text-white rounded">
                                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                    <h5 class="font-weight-bold">
                                        <?php echo $total_menit; ?> Menit
                                    </h5>
                                    <small>Total Menit</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-info text-white rounded">
                                    <i class="fas fa-percentage fa-2x mb-2"></i>
                                    <h5 class="font-weight-bold">
                                        <?php
                                        if ($total_menit > 0) {
                                            $target_menit = 8 * 60; // 8 jam = 480 menit
                                            $persentase = min(100, round(($total_menit / $target_menit) * 100));
                                            echo $persentase . '%';
                                        } else {
                                            echo '0%';
                                        }
                                        ?>
                                    </h5>
                                    <small>Pencapaian Target</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Keterangan -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-sticky-note mr-2"></i>Keterangan
                    </h6>
                </div>
                <div class="card-body">
                    <textarea class="form-control border-0 bg-light" rows="6" readonly
                        placeholder="Tidak ada keterangan"><?php echo htmlspecialchars($data['keterangan'] ?: ''); ?></textarea>

                    <?php if (empty($data['keterangan'])) : ?>
                        <div class="text-center text-muted mt-3">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">Tidak ada keterangan tambahan</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informasi Sistem -->
            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-database mr-2"></i>Informasi Sistem
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="font-weight-bold">UUID</td>
                            <td>:</td>
                            <td>
                                <code class="text-xs"><?php echo htmlspecialchars($data['uuid_absensi']); ?></code>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat</td>
                            <td>:</td>
                            <td>
                                <small>
                                    <i class="fas fa-calendar-plus mr-1 text-success"></i>
                                    <?php echo date('d M Y, H:i:s', strtotime($data['created_at'])); ?>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Diperbarui</td>
                            <td>:</td>
                            <td>
                                <small>
                                    <i class="fas fa-calendar-edit mr-1 text-info"></i>
                                    <?php echo date('d M Y, H:i:s', strtotime($data['updated_at'])); ?>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Record ID</td>
                            <td>:</td>
                            <td>
                                <span class="badge badge-primary">#<?php echo str_pad($data['id_absensi'], 6, '0', STR_PAD_LEFT); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart (if available) -->
    <?php if ($total_jam_kerja) : ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-chart-line mr-2"></i>Analisis Kinerja Harian
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border-right">
                                    <div class="h4 font-weight-bold text-success">
                                        <?php echo $data['jam_masuk'] ? date('H:i', strtotime($data['jam_masuk'])) : '-'; ?>
                                    </div>
                                    <small class="text-muted">Waktu Masuk</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-right">
                                    <div class="h4 font-weight-bold text-warning">
                                        <?php echo $data['jam_keluar'] ? date('H:i', strtotime($data['jam_keluar'])) : '-'; ?>
                                    </div>
                                    <small class="text-muted">Waktu Keluar</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-right">
                                    <div class="h4 font-weight-bold text-primary">
                                        <?php echo $total_jam_kerja ?: '-'; ?>
                                    </div>
                                    <small class="text-muted">Total Kerja</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="h4 font-weight-bold text-info">
                                    <?php
                                    $efisiensi = 0;
                                    if ($total_menit > 0) {
                                        $target_menit = 8 * 60;
                                        $efisiensi = min(100, round(($total_menit / $target_menit) * 100));
                                    }
                                    echo $efisiensi . '%';
                                    ?>
                                </div>
                                <small class="text-muted">Efisiensi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    @media print {

        .btn,
        .card-header,
        .bg-light {
            display: none !important;
        }

        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            margin-bottom: 1rem !important;
        }

        .card-body {
            padding: 1rem !important;
        }

        .text-primary,
        .text-success,
        .text-warning,
        .text-info,
        .text-danger {
            color: #000 !important;
        }

        .badge {
            border: 1px solid #000 !important;
            background-color: #fff !important;
            color: #000 !important;
        }
    }

    .badge-info {
        background-color: #e83e8c;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }

    .card {
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .border-right {
        border-right: 1px solid #e3e6f0 !important;
    }

    @media (max-width: 768px) {
        .border-right {
            border-right: none !important;
            border-bottom: 1px solid #e3e6f0 !important;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
        }
    }
</style>