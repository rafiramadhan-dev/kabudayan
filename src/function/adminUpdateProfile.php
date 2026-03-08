<?php
session_start();
require_once '../database/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan');
}

if (!isset($_POST['id']) || !ctype_digit((string)$_POST['id'])) {
    http_response_code(400);
    exit('ID tidak valid');
}

$id = (int)$_POST['id'];

if (isset($_POST['update_akun'])) {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');

    if ($email === '' || $username === '') {
        $_SESSION['flash_message'] = "Email dan Username wajib diisi";
        header('Location: ../admin/profile.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_message'] = "Format email tidak valid";
        header('Location: ../admin/profile.php');
        exit;
    }

    $cek_email = mysqli_prepare($conn, "SELECT id FROM admin WHERE email = ? AND id <> ?");
    mysqli_stmt_bind_param($cek_email, 'si', $email, $id);
    mysqli_stmt_execute($cek_email);
    $res_email = mysqli_stmt_get_result($cek_email);
    if (mysqli_fetch_assoc($res_email)) {
        $_SESSION['flash_message'] = "Email sudah digunakan oleh admin lain";
        header('Location: ../admin/profile.php');
        exit;
    }

    $cek_username = mysqli_prepare($conn, "SELECT id FROM admin WHERE username = ? AND id <> ?");
    mysqli_stmt_bind_param($cek_username, 'si', $username, $id);
    mysqli_stmt_execute($cek_username);
    $res_username = mysqli_stmt_get_result($cek_username);
    if (mysqli_fetch_assoc($res_username)) {
        $_SESSION['flash_message'] = "Username sudah dipakai";
        header('Location: ../admin/profile.php');
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE admin SET email = ?, username = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $email, $username, $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_message'] = "Informasi akun berhasil diperbarui";
    } else {
        $_SESSION['flash_message'] = "Gagal memperbarui informasi akun";
    }

    header('Location: ../admin/profile.php');
    exit;
}

    if (isset($_POST['ubah_password'])) {
    $p1 = $_POST['password_baru'] ?? '';
    $p2 = $_POST['password_konfirmasi'] ?? '';

    if ($p1 === '' || $p2 === '') {
        $_SESSION['flash_message'] = "Password wajib diisi";
        header('Location: ../admin/profile.php');
        exit;
    }
    if ($p1 !== $p2) {
        $_SESSION['flash_message'] = "Konfirmasi password tidak cocok";
        header('Location: ../admin/profile.php');
        exit;
    }
    if (strlen($p1) < 8 || strlen($p1) > 72) {
        $_SESSION['flash_message'] = "Panjang password 8–72 karakter";
        header('Location: ../admin/profile.php');
        exit;
    }

    $hash = password_hash($p1, PASSWORD_DEFAULT);
    if ($hash === false) {
        $_SESSION['flash_message'] = "Gagal memproses password";
        header('Location: ../admin/profile.php');
        exit;
    }

    $stmt = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_message'] = "Password berhasil diperbarui";
    } else {
        $_SESSION['flash_message'] = "Gagal mengubah password";
    }

    header('Location: ../admin/profile.php');
    exit;
}

if (isset($_POST['ubah_foto'])) {
    $uploadDir = "../assets/img/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $file_info = pathinfo($_FILES['foto']['name']);
        $ext = strtolower($file_info['extension'] ?? '');

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['flash_message'] = "Format file tidak didukung. Gunakan JPG, PNG, GIF";
            header("Location: ../admin/profile.php");
            exit;
        }

        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            $_SESSION['flash_message'] = "Ukuran file terlalu besar. Maksimal 5MB";
            header("Location: ../admin/profile.php");
            exit;
        }

        $image_info = getimagesize($_FILES['foto']['tmp_name']);
        if ($image_info === false) {
            $_SESSION['flash_message'] = "File yang diupload bukan gambar yang valid";
            header("Location: ../admin/profile.php");
            exit;
        }

        $newName = "profile_" . $id . "_" . time() . "." . $ext;
        $target = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
            $q = mysqli_query($conn, "SELECT foto_profil FROM admin WHERE id = $id");
            if ($q) {
                $old_data = mysqli_fetch_assoc($q);
                $old = $old_data['foto_profil'] ?? '';
                if ($old && $old !== 'default.png' && file_exists($uploadDir . $old)) {
                    unlink($uploadDir . $old);
                }
            }

            $stmt = mysqli_prepare($conn, "UPDATE admin SET foto_profil = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $newName, $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_message'] = "Foto profil berhasil diperbarui";
            } else {
                $_SESSION['flash_message'] = "Gagal menyimpan foto";
            }
        } else {
            $_SESSION['flash_message'] = "Gagal upload file";
        }
    } else {
        $error_message = "";
        switch ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) {
            case UPLOAD_ERR_NO_FILE:
                $error_message = "Tidak ada file yang dipilih";
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = "File terlalu besar";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = "File hanya terupload sebagian";
                break;
            default:
                $error_message = "Terjadi kesalahan saat upload file";
        }
        $_SESSION['flash_message'] = $error_message;
    }

    header("Location: ../admin/profile.php");
    exit;
}

$_SESSION['flash_message'] = "Error: Aksi tidak valid";
header("Location: ../admin/profile.php");
exit;
?>
