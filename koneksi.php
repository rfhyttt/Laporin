<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "laporinjasa";

$koneksi = mysqli_connect($server,$username,$password,$database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>