<?php
// File: modules/absensi/proses_checkin.php

// panggil file koneksi database
require_once "../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ambil data dari formulir atau sesi login
    $id_user = mysqli_real_escape_string($mysqli, $_POST['id_user']);
    $jam_masuk = mysqli_real_escape_string($mysqli, $_POST['jam_masuk']);
    $keterangan_masuk = mysqli_real_escape_string($mysqli, trim($_POST['keterangan_masuk']));
    $tanggal_absen = date('Y-m-d');

    // Validasi input
    if (empty($id_user) || empty($jam_masuk)) {
        echo "<script>";
        echo "alert('Data tidak lengkap!');";
        echo "window.history.back();";
        echo "</script>";
        exit;
    }

    // Cek apakah user sudah check in hari ini
    $cek_absen = mysqli_query($mysqli, "SELECT * FROM tbl_absensi 
                                        WHERE id_user = '$id_user' 
                                        AND DATE(jam_masuk) = '$tanggal_absen'");
    if (mysqli_num_rows($cek_absen) > 0) {
        echo "<script>";
        echo "alert('Anda sudah melakukan check in hari ini!');";
        echo "window.location = '../../main.php?module=absensi';";
        echo "</script>";
        exit;
    }

    // Ambil setting jam kerja default
    $query_jam_kerja = mysqli_query($mysqli, "SELECT * FROM tbl_jam_kerja WHERE is_active = 1 LIMIT 1");
    $jam_kerja = mysqli_fetch_assoc($query_jam_kerja);

    // Tentukan status masuk (tepat waktu, terlambat)
    $status_masuk = 'Tepat Waktu';
    if ($jam_masuk > $jam_kerja['batas_terlambat']) {
        $status_masuk = 'Terlambat';
    }

    // Generate UUID untuk absensi
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );

    // Insert record absensi
    $insert = mysqli_query($mysqli, "INSERT INTO tbl_absensi (
    uuid_absensi,
    id_user,
    tanggal,
    jam_masuk,
    status_masuk,
    keterangan,
    created_at,
    updated_at
) VALUES (
    '$uuid',
    '$id_user',
    '$tanggal_absen',
    '$jam_masuk',
    '$status_masuk',
    '$keterangan_masuk',
    NOW(),
    NOW()
)") or die('Ada kesalahan pada query insert : ' . mysqli_error($mysqli));


    // Cek hasil query
    if ($insert) {
        // berhasil
        echo "<script>";
        echo "alert('Check in berhasil! Status: $status_masuk');";
        echo "window.location = '../../main.php?module=absensi&action=checkin';";
        echo "</script>";
    } else {
        // gagal
        echo "<script>";
        echo "alert('Check in gagal!');";
        echo "window.history.back();";
        echo "</script>";
    }
} else {
    // jika akses langsung ke file ini, redirect ke halaman utama
    header('location: ../../main.php?module=absensi');
}
