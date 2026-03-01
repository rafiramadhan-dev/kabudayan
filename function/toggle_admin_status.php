<?php
session_start();
require_once '../database/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit;
}

$current_admin_id = $_SESSION['admin_id'];
$admin_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';

if ($admin_id <= 0 || !in_array($new_status, ['0', '1', '2'])) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
    exit;
}

if ($admin_id == $current_admin_id && $new_status == '2') {
    echo json_encode(['success' => false, 'message' => 'Tidak dapat menonaktifkan diri sendiri']);
    exit;
}

$check_sql = "SELECT id, session_id FROM admin WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, 'i', $admin_id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);
$admin_data = mysqli_fetch_assoc($result);

$is_online = ($admin_data && $admin_data['session_id'] !== null);

if ($is_online && $new_status == '2') {
    echo json_encode(['success' => false, 'message' => 'Admin sedang login, tidak bisa dinonaktifkan']);
    exit;
}

$update_sql = "UPDATE admin SET verifikasi = ?, status_changed_at = NOW() WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $admin_id);

if (mysqli_stmt_execute($update_stmt)) {
    $status_text = ($new_status == '1') ? 'diaktifkan' : (($new_status == '2') ? 'dinonaktifkan' : 'pending');
    echo json_encode(['success' => true, 'message' => 'Admin berhasil ' . $status_text]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengubah status: ' . mysqli_error($conn)]);
}
?>
