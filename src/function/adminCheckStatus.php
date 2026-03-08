<?php
    require_once '../database/koneksi.php';

    $delete_pending = "DELETE FROM admin WHERE verifikasi = '0' AND status_changed_at IS NOT NULL AND TIMESTAMPDIFF(SECOND, status_changed_at, NOW()) > 10";
    mysqli_query($conn, $delete_pending);

    $delete_inactive = "DELETE FROM admin WHERE verifikasi = '2' AND status_changed_at IS NOT NULL AND TIMESTAMPDIFF(SECOND, status_changed_at, NOW()) > 10";
    mysqli_query($conn, $delete_inactive);

    echo json_encode(['success' => true]);
?>
