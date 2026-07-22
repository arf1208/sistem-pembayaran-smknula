<?php
require_once 'config/database.php';

// Cek apakah session petugas sudah terdaftar
if (isset($_SESSION['id_petugas'])) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>