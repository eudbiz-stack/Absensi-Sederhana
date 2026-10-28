<?php
// File: modules/absensi/index.php

// panggil file koneksi database
require_once "./config/database.php";

// untuk pagination
$batas_halaman = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$mulai = ($halaman - 1) * $batas_halaman;

// untuk filter tanggal
$tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : date('Y-m-01');
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : date('Y-m-d');

// untuk pencarian
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($mysqli, $_GET['cari']) : '';

// build WHERE clause
$where_conditions = array();
$where_conditions[] = "DATE(a.tanggal) >= '$tanggal_dari'";
$where_conditions[] = "DATE(a.tanggal) <= '$tanggal_sampai'";

if (!empty($cari)) {
    $where_conditions[] = "(u.namalengkap LIKE '%$cari%' OR u.username LIKE '%$cari%')";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// query untuk menampilkan data absensi dengan join user
$query = mysqli_query($mysqli, "
    SELECT a.*, u.username, u.namalengkap, u.jabatan 
    FROM tbl_absensi a 
    JOIN tbl_user u ON a.id_user = u.id_user 
    $where_clause 
    ORDER BY a.tanggal DESC, a.jam_masuk DESC 
    LIMIT $mulai, $batas_halaman
") or die('Ada kesalahan pada query tampil data : ' . mysqli_error($mysqli));

// query untuk menghitung total data
$query_jumlah = mysqli_query($mysqli, "
    SELECT COUNT(*) as jumlah 
    FROM tbl_absensi a 
    JOIN tbl_user u ON a.id_user = u.id_user 
    $where_clause
") or die('Ada kesalahan pada query hitung data : ' . mysqli_error($mysqli));
$data_jumlah = mysqli_fetch_assoc($query_jumlah);
$jumlah_data = $data_jumlah['jumlah'];
$jumlah_halaman = ceil($jumlah_data / $batas_halaman);
?>

<div class="container-fluid">
    <!-- judul halaman -->
    <h1 class="h4 mb-4 text-gray-800"><i class="fas fa-clock fa-fw mr-2"></i>Data Absensi</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold">Data Absensi Karyawan</h6>
                </div>
                <div class="col-md-6 text-right">
                    <a href="?module=absensi&action=checkin" class="btn btn-success btn-sm">
                        <i class="fas fa-sign-in-alt mr-1"></i>Check In
                    </a>
                    <a href="?module=absensi&action=checkout" class="btn btn-warning btn-sm">
                        <i class="fas fa-sign-out-alt mr-1"></i>Check Out
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter dan Pencarian -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <form method="GET" action="">
                        <input type="hidden" name="module" value="absensi">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="date" name="tanggal_dari" class="form-control"
                                    placeholder="Tanggal Dari" value="<?php echo $tanggal_dari; ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="tanggal_sampai" class="form-control"
                                    placeholder="Tanggal Sampai" value="<?php echo $tanggal_sampai; ?>">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="cari" class="form-control"
                                    placeholder="Cari nama karyawan..." value="<?php echo htmlspecialchars($cari); ?>">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-block" type="submit">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-right">
                    <?php if (!empty($cari) || $tanggal_dari != date('Y-m-01') || $tanggal_sampai != date('Y-m-d')) : ?>
                        <a href="?module=absensi" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times mr-1"></i>Reset Filter
                        </a>
                    <?php endif; ?>
                    <a href="?module=absensi&action=export" class="btn btn-info btn-sm">
                        <i class="fas fa-download mr-1"></i>Export Excel
                    </a>
                </div>
            </div>

            <!-- Tabel Data Absensi -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Tanggal</th>
                            <th width="20%">Nama Karyawan</th>
                            <th width="15%">Jabatan</th>
                            <th width="10%">Jam Masuk</th>
                            <th width="10%">Jam Keluar</th>
                            <th width="12%">Status Masuk</th>
                            <th width="12%">Status Keluar</th>
                            <th width="4%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $mulai + 1;
                        while ($data = mysqli_fetch_assoc($query)) :
                            // Format tanggal
                            $tanggal_format = date('d/m/Y', strtotime($data['tanggal']));

                            // Format jam
                            $jam_masuk = $data['jam_masuk'] ? date('H:i', strtotime($data['jam_masuk'])) : '-';
                            $jam_keluar = $data['jam_keluar'] ? date('H:i', strtotime($data['jam_keluar'])) : '-';
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $tanggal_format; ?></td>
                                <td><?php echo htmlspecialchars($data['namalengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data['jabatan']); ?></td>
                                <td>
                                    <?php echo $jam_masuk; ?>
                                </td>
                                <td>
                                    <?php echo $jam_keluar; ?>
                                </td>
                                <td>
                                    <?php if ($data['status_masuk']) : ?>
                                        <span class="badge badge-<?php echo $data['status_masuk'] == 'Tepat Waktu' ? 'success' : 'warning'; ?>">
                                            <?php echo $data['status_masuk']; ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($data['status_keluar']) : ?>
                                        <span class="badge badge-<?php echo $data['status_keluar'] == 'Normal' ? 'success' : 'warning'; ?>">
                                            <?php echo $data['status_keluar']; ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?module=absensi&action=detail&id=<?php echo $data['id_absensi']; ?>"
                                        class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($query) == 0) : ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="py-3">
                                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada data absensi pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($jumlah_halaman > 1) : ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <!-- Previous -->
                        <?php if ($halaman > 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?module=absensi&halaman=<?php echo ($halaman - 1); ?>&tanggal_dari=<?php echo $tanggal_dari; ?>&tanggal_sampai=<?php echo $tanggal_sampai; ?><?php echo !empty($cari) ? '&cari=' . urlencode($cari) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $jumlah_halaman; $i++) : ?>
                            <li class="page-item <?php echo ($halaman == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?module=absensi&halaman=<?php echo $i; ?>&tanggal_dari=<?php echo $tanggal_dari; ?>&tanggal_sampai=<?php echo $tanggal_sampai; ?><?php echo !empty($cari) ? '&cari=' . urlencode($cari) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next -->
                        <?php if ($halaman < $jumlah_halaman) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?module=absensi&halaman=<?php echo ($halaman + 1); ?>&tanggal_dari=<?php echo $tanggal_dari; ?>&tanggal_sampai=<?php echo $tanggal_sampai; ?><?php echo !empty($cari) ? '&cari=' . urlencode($cari) : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        Menampilkan <?php echo $mulai + 1; ?> - <?php echo min($mulai + $batas_halaman, $jumlah_data); ?>
                        dari <?php echo $jumlah_data; ?> data absensi
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .badge-warning {
        background-color: #f6c23e;
    }
</style>