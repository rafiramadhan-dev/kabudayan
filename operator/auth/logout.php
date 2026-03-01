<?php
session_start();
require_once '../database/koneksi.php';

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $sql = "UPDATE admin SET last_activity = NULL, session_id = NULL WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $admin_id);
    mysqli_stmt_execute($stmt);
}

session_destroy();
header('Location: ../auth/login.php');
exit;
?>
