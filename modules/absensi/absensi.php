<?php
// File: modules/absensi/absensi.php

// cek parameter action
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'checkin':
            include "modules/absensi/checkin.php";
            break;
        case 'checkout':
            include "modules/absensi/checkout.php";
            break;
        case 'detail':
            include "modules/absensi/detail.php";
            break;
        case 'laporan':
            include "modules/absensi/laporan.php";
            break;
        case 'export':
            include "modules/absensi/export_excel.php";
            break;
        default:
            include "modules/absensi/index.php";
            break;
    }
} else {
    // default action adalah menampilkan daftar absensi
    include "modules/absensi/index.php";
}
