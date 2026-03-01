<?php
session_start();
require_once '../database/koneksi.php';


header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['available' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['available' => false, 'error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$admin_id = (int)($_POST['admin_id'] ?? 0);

if (empty($email)) {
    echo json_encode(['available' => false, 'error' => 'Email kosong']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['available' => false, 'error' => 'Format email tidak valid']);
    exit;
}

try {
    $stmt = mysqli_prepare($conn, "SELECT id FROM admin WHERE email = ? AND id != ?");

    if (!$stmt) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'si', $email, $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'available' => false,
            'message' => 'Email sudah digunakan'
        ]);
    } else {
        echo json_encode([
            'available' => true,
            'message' => 'Email tersedia'
        ]);
    }

    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    error_log('check_email.php error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'available' => false,
        'error' => 'Gagal memeriksa email: ' . $e->getMessage()
    ]);
}
