<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include "koneksi/koneksi.php";

$menu = $_GET['menu'] ?? 'laba_rugi';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Cetak Laporan</title>

<style>
body{
    font-family: Arial, sans-serif;
    padding:40px;
    font-size:13px;
}

h4{
    text-align:center;
    margin-bottom:30px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:20px;
}

table th, table td{
    border:1px solid #000;
    padding:6px;
}

@media print {
    button{ display:none; }
}
</style>
</head>
<body>

<button onclick="window.print()">Print / Save PDF</button>

<h4>LAPORAN KEUANGAN</h4>

<?php
switch($menu){
    case 'buku_besar':
        include "keuangan/buku_besar.php";
        break;
    case 'laba_rugi':
        include "keuangan/laba_rugi.php";
        break;
    case 'ekuitas':
        include "keuangan/perubahan_ekuitas.php";
        break;
    case 'neraca':
        include "keuangan/posisi_keuangan.php";
        break;
    case 'arus_kas':
        include "keuangan/arus_kas.php";
        break;
    case 'calk':
        include "keuangan/calk.php";
        break;
    default:
        include "keuangan/jurnal_umum.php";
}
?>

<script>
window.onload = function(){
    window.print();
}
</script>

</body>
</html>