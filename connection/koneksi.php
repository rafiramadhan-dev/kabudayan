<?php
$host     = "localhost"; 
$user     = "root";       
$password = "";         
$database   = "budaya_kelompok";  

$koneksi = new mysqli($host, $user, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} else {
    // echo "Koneksi terhubung";
}
?>
