<?php
session_start();
require_once '../database/koneksi.php';

header('Content-Type: application/json');

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $session_id = session_id();
    
    $sql = "UPDATE admin SET last_activity = NOW(), session_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $session_id, $admin_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
}
?>
