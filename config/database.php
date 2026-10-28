<?php
// File: config/database.php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_absensi';

// Membuat koneksi ke database
$mysqli = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset("utf8");
