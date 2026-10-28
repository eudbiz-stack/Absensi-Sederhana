<?php
// File: modules/absensi/export_excel.php

// panggil file koneksi database
require_once "./config/database.php";

// Header for Excel file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan_Absensi_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// untuk filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$id_user_filter = isset($_GET['id_user']) ? (int)$_GET['id_user'] : '';

// build WHERE clause
$where_conditions = array();
$where_conditions[] = "DATE_FORMAT(a.tanggal, '%Y-%m') = '$bulan'";

if (!empty($id_user_filter)) {
    $where_conditions[] = "a.id_user = '$id_user_filter'";
}

// Query untuk detail absensi
$query_detail = mysqli_query($mysqli, "
    SELECT 
        a.*,
        u.namalengkap,
        u.username,
        u.jabatan
    FROM tbl_absensi a 
    JOIN tbl_user u ON a.id_user = u.id_user 
    WHERE DATE_FORMAT(a.tanggal, '%Y-%m') = '$bulan'" .
    (!empty($id_user_filter) ? " AND a.id_user = '$id_user_filter'" : "") . "
    ORDER BY a.tanggal DESC, u.namalengkap ASC
") or die('Ada kesalahan pada query detail : ' . mysqli_error($mysqli));

// Query untuk ringkasan
$query_summary = mysqli_query($mysqli, "
    SELECT 
        u.id_user,
        u.namalengkap,
        u.jabatan,
        COUNT(a.id_absensi) as total_kehadiran,
        SUM(CASE WHEN a.status_masuk = 'Tepat Waktu' THEN 1 ELSE 0 END) as tepat_waktu,
        SUM(CASE WHEN a.status_masuk = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
        SUM(CASE WHEN a.status_keluar = 'Pulang Awal' THEN 1 ELSE 0 END) as pulang_awal
    FROM tbl_user u 
    LEFT JOIN tbl_absensi a ON u.id_user = a.id_user AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$bulan'
    " . (!empty($id_user_filter) ? "WHERE u.id_user = '$id_user_filter'" : "") . "
    GROUP BY u.id_user, u.namalengkap, u.jabatan
    ORDER BY u.namalengkap ASC
") or die('Ada kesalahan pada query ringkasan : ' . mysqli_error($mysqli));

// Hitung total hari kerja dalam bulan
$tahun_bulan = explode('-', $bulan);
$tahun = $tahun_bulan[0];
$bulan_num = $tahun_bulan[1];
$total_hari = cal_days_in_month(CAL_GREGORIAN, $bulan_num, $tahun);

$hari_kerja = 0;
for ($i = 1; $i <= $total_hari; $i++) {
    $hari = date('N', mktime(0, 0, 0, $bulan_num, $i, $tahun));
    if ($hari <= 5) { // Senin-Jumat
        $hari_kerja++;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .summary {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN ABSENSI KARYAWAN</h2>
        <h3>Periode: <?php echo date('F Y', strtotime($bulan . '-01')); ?></h3>
        <p>Generated on: <?php echo date('d F Y H:i:s'); ?></p>
    </div>

    <!-- Ringkasan Laporan -->
    <div class="summary">
        <h3>RINGKASAN LAPORAN</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Total Hadir</th>
                    <th>Tepat Waktu</th>
                    <th>Terlambat</th>
                    <th>Alpha</th>
                    <th>Pulang Awal</th>
                    <th>Persentase Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($summary = mysqli_fetch_assoc($query_summary)) :
                    $alpha = $hari_kerja - $summary['total_kehadiran'];
                    $persentase = ($hari_kerja > 0) ? round(($summary['total_kehadiran'] / $hari_kerja) * 100, 1) : 0;
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($summary['namalengkap']); ?></td>
                        <td><?php echo htmlspecialchars($summary['jabatan']); ?></td>
                        <td><?php echo $summary['total_kehadiran']; ?></td>
                        <td><?php echo $summary['tepat_waktu']; ?></td>
                        <td><?php echo $summary['terlambat']; ?></td>
                        <td><?php echo $alpha; ?></td>
                        <td><?php echo $summary['pulang_awal']; ?></td>
                        <td><?php echo $persentase; ?>%</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Detail Absensi -->
    <div class="detail">
        <h3>DETAIL ABSENSI</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th>Username</th>
                    <th>Jabatan</th>
                    <th>Jam Masuk</th>
                    <th>Status Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status Keluar</th>
                    <th>Total Jam Kerja</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($detail = mysqli_fetch_assoc($query_detail)) :
                    // Hitung total jam kerja
                    $total_jam = '-';
                    if ($detail['jam_masuk'] && $detail['jam_keluar']) {
                        $masuk = new DateTime($detail['jam_masuk']);
                        $keluar = new DateTime($detail['jam_keluar']);
                        $diff = $keluar->diff($masuk);
                        $total_jam = $diff->format('%h:%i');
                    }
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($detail['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($detail['namalengkap']); ?></td>
                        <td><?php echo htmlspecialchars($detail['username']); ?></td>
                        <td><?php echo htmlspecialchars($detail['jabatan']); ?></td>
                        <td><?php echo $detail['jam_masuk'] ? date('H:i:s', strtotime($detail['jam_masuk'])) : '-'; ?></td>
                        <td><?php echo $detail['status_masuk'] ?: '-'; ?></td>
                        <td><?php echo $detail['jam_keluar'] ? date('H:i:s', strtotime($detail['jam_keluar'])) : '-'; ?></td>
                        <td><?php echo $detail['status_keluar'] ?: '-'; ?></td>
                        <td><?php echo $total_jam; ?></td>
                        <td><?php echo htmlspecialchars($detail['keterangan'] ?: '-'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Total Hari Kerja dalam periode: <?php echo $hari_kerja; ?> hari</li>
            <li>Jam kerja dihitung dari check in sampai check out</li>
            <li>Alpha = Total hari kerja - Total kehadiran</li>
            <li>Persentase kehadiran = (Total hadir / Total hari kerja) x 100%</li>
        </ul>
    </div>
</body>

</html>