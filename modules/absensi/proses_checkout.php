<?php
// File: modules/absensi/proses_checkout.php

// panggil file koneksi database
require_once "../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ambil data dari formulir
    $id_absensi = mysqli_real_escape_string($mysqli, $_POST['id_absensi']);
    $jam_keluar = mysqli_real_escape_string($mysqli, $_POST['jam_keluar']);
    $keterangan_keluar = mysqli_real_escape_string($mysqli, trim($_POST['keterangan_keluar']));

    // Validasi input
    if (empty($id_absensi) || empty($jam_keluar)) {
        echo "<script>";
        echo "alert('Data tidak lengkap!');";
        echo "window.history.back();";
        echo "</script>";
        exit;
    }

    // Cek apakah record absensi ada dan belum check out
    $cek_absen = mysqli_query($mysqli, "SELECT * FROM tbl_absensi WHERE id_absensi = '$id_absensi'");
    if (mysqli_num_rows($cek_absen) == 0) {
        echo "<script>";
        echo "alert('Data absensi tidak ditemukan!');";
        echo "window.location = '../../main.php?module=absensi';";
        echo "</script>";
        exit;
    }

    $data_absen = mysqli_fetch_assoc($cek_absen);
    if ($data_absen['jam_keluar']) {
        echo "<script>";
        echo "alert('Anda sudah melakukan check out hari ini!');";
        echo "window.location = '../../main.php?module=absensi';";
        echo "</script>";
        exit;
    }

    // Ambil setting jam kerja default
    $query_jam_kerja = mysqli_query($mysqli, "SELECT * FROM tbl_jam_kerja WHERE is_active = 1 LIMIT 1");
    $jam_kerja = mysqli_fetch_assoc($query_jam_kerja);

    // Tentukan status keluar (normal atau pulang awal)
    $status_keluar = 'Normal';
    if ($jam_keluar < $jam_kerja['batas_pulang_awal']) {
        $status_keluar = 'Pulang Awal';
    }

    // Update record absensi dengan jam keluar
    $update = mysqli_query($mysqli, "UPDATE tbl_absensi SET 
                                    jam_keluar = '$jam_keluar',
                                    status_keluar = '$status_keluar',
                                    keterangan = '$keterangan_keluar',
                                    updated_at = NOW()
                                    WHERE id_absensi = '$id_absensi'")
        or die('Ada kesalahan pada query update : ' . mysqli_error($mysqli));

    // Cek hasil query
    if ($update) {
        // berhasil
        echo "<script>";
        echo "alert('Check out berhasil! Status: $status_keluar');";
        echo "window.location = '../../main.php?module=absensi&action=checkout';";
        echo "</script>";
    } else {
        // gagal
        echo "<script>";
        echo "alert('Check out gagal!');";
        echo "window.history.back();";
        echo "</script>";
    }
} else {
    // jika akses langsung ke file ini, redirect ke halaman utama
    header('location: ../../main.php?module=absensi');
}
