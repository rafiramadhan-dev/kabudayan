<?php
session_start();
require_once '../database/koneksi.php';


if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}


$error_message = '';
$success_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buat_akun'])) {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua field wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 8) {
        $error_message = "Password minimal 8 karakter!";
    } else {
        $check_email = mysqli_prepare($conn, "SELECT id FROM admin WHERE email = ?");
        mysqli_stmt_bind_param($check_email, 's', $email);
        mysqli_stmt_execute($check_email);
        $result_email = mysqli_stmt_get_result($check_email);


        if (mysqli_num_rows($result_email) > 0) {
            $error_message = "Email sudah terdaftar!";
        } else {
            $check_username = mysqli_prepare($conn, "SELECT id FROM admin WHERE username = ?");
            mysqli_stmt_bind_param($check_username, 's', $username);
            mysqli_stmt_execute($check_username);
            $result_username = mysqli_stmt_get_result($check_username);


            if (mysqli_num_rows($result_username) > 0) {
                $error_message = "Username sudah digunakan!";
            } else {
                $foto_profil = 'default.png';


                if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
                    $uploadDir = "../assets/img/profile/";
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);


                    $file_info = pathinfo($_FILES['foto_profil']['name']);
                    $ext = strtolower($file_info['extension'] ?? '');


                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg'])) {
                        $newName = "admin_" . time() . "." . $ext;
                        $target = $uploadDir . $newName;


                        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target)) {
                            $foto_profil = $newName;
                        }
                    }
                }


                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $verifikasi = '0'; 


                $sql = 'INSERT INTO admin (email, username, password, verifikasi, foto_profil, status_changed_at) VALUES (?, ?, ?, ?, ?, NOW())';
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'sssss', $email, $username, $hashed_password, $verifikasi, $foto_profil);


                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "Admin berhasil ditambahkan! Silakan aktifkan dalam 10 detik.";
                        $email = $username = $password = $confirm_password = '';
                    } else {
                        $error_message = "Gagal menambahkan admin: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_message = "Error prepare statement: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Buat Akun - Kebudayaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --dark: #212136;
            --red: #CB1E22;
            --gray-dark: #9D9FA0;
            --gray: #DFE1E3;
            --gray-light: #F3F5F7;
            --light: #FFFFFF;
            --red-light: #FEDBDB;
            --red-dark: #9E171A;
            --green-light: #C6FFC6;
            --green: #09D25D;
            --yellow-light: #FFF4B2;
            --yellow: #FFB700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Satoshi';
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header .logo {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: var(--red);
            padding: 4px;
        }

        .sidebar-header .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            filter: brightness(0) invert(1);
        }

        .sidebar-header .title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .sidebar-menu .icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            transition: 0.3s;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            font-size: 14px;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeIn 0.1s ease forwards;
        }

        .menu-item:hover {
            background-color: #fef2f2;
            color: #dc2626;
            border-left-color: #dc2626;
        }

        .menu-item.active {
            background-color: #fee2e2;
            color: #dc2626;
            border-left-color: #dc2626;
            font-weight: 500;
        }

        .menu-divider {
            height: 1px;
            background: #e5e5e5;
            margin: 20px 20px;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(220, 38, 38, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .menu-item:hover::before {
            left: 100%;
        }

        .menu-item .icon,
        .menu-item span {
            position: relative;
            z-index: 1;
        }

        .menu-item:nth-child(1) {
            animation-delay: 0.1s;
        }

        .menu-item:nth-child(2) {
            animation-delay: 0.2s;
        }

        .menu-item:nth-child(3) {
            animation-delay: 0.3s;
        }

        .menu-item:nth-child(4) {
            animation-delay: 0.4s;
        }

        .menu-item:nth-child(5) {
            animation-delay: 0.5s;
        }

        .menu-item:nth-child(6) {
            animation-delay: 0.6s;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .content-header i {
            color: #dc2626;
            font-size: 24px;
        }

        .content-header h1 {
            font-size: 28px;
            color: #1f2937;
            font-weight: 600;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .form-title {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #1f2937;
            transition: border-color 0.2s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .file-upload-container {
            position: relative;
            display: inline-block;
        }

        .file-upload-btn {
            background: #dc2626;
            color: white;
            padding: 10px 20px;
            border-radius: 40px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            transition: background-color 0.2s ease;
        }

        .file-upload-btn:hover {
            background: #b91c1c;
        }

        .file-upload-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-upload-info {
            color: #6b7280;
            font-size: 12px;
            margin-top: 6px;
        }

        .file-preview {
            margin-top: 10px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 13px;
            color: #374151;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: #dc2626;
            color: white;
        }

        .btn-primary:hover {
            background: #b91c1c;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 80px 20px 30px;
            }

            .content-wrapper {
                align-items: flex-start;
                padding: 20px 0;
            }

            .form-container {
                padding: 20px;
                max-width: 100%;
            }

            .form-actions {
                flex-direction: column;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container {
            animation: fadeIn 0.6s ease;
        }

        .alert i {
            margin-right: 8px;
        }

        .btn i {
            margin-right: 8px;
        }

        .file-upload-btn i {
            margin-right: 8px;
        }

        .file-preview i {
            margin-right: 8px;
        }

        .content-header i {
            margin-right: 12px;
        }
    </style>


</head>


<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>


    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>


    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="../assets/svg/Logo.svg" alt="Logo Kebudayaan">
            </div>
            <div class="title">Kebudayaan</div>
        </div>


        <nav class="sidebar-menu">
            <a href="../admin/dashboard.php" class="menu-item" data-light="../assets/svg/dashboard-light.svg" data-thick="../assets/svg/dashboard-thick.svg">
                <img src="../assets/svg/dashboard-light.svg" alt="Dashboard" class="icon">
                <span>Dashboard</span>
            </a>
            <a href="../admin/profile.php" class="menu-item" data-light="../assets/svg/profile-light.svg" data-thick="../assets/svg/profile-thick.svg">
                <img src="../assets/svg/profile-light.svg" alt="Profile" class="icon">
                <span>Profile</span>
            </a>
            <a href="../admin/admin.php" class="menu-item active" data-light="../assets/svg/admin-light.svg" data-thick="../assets/svg/admin-thick.svg">
                <img src="../assets/svg/admin-thick.svg" alt="Admin" class="icon">
                <span>Admin</span>
            </a>
            <a href="../admin/budaya.php" class="menu-item" data-light="../assets/svg/budaya-light.svg" data-thick="../assets/svg/budaya-thick.svg">
                <img src="../assets/svg/budaya-light.svg" alt="Budaya" class="icon">
                <span>Budaya</span>
            </a>
            <div class="menu-divider"></div>
            <a href="../auth/logout.php" class="menu-item" data-light="../assets/svg/logout-light.svg" data-thick="../assets/svg/logout-thick.svg">
                <img src="../assets/svg/logout-light.svg" alt="Logout" class="icon">
                <span>Logout</span>
            </a>
        </nav>
    </div>


    <main class="main-content">
        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Buat Akun</h2>
                <p class="form-subtitle">Akun ini digunakan khusus untuk pengelolaan dan pengatur utama.</p>


                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>


                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>


                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Email" required
                            value="<?= htmlspecialchars($email ?? '') ?>">
                    </div>


                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Username"
                            required value="<?= htmlspecialchars($username ?? '') ?>">
                    </div>


                    <div class="form-group">
                        <label>Foto Profile</label>
                        <div class="file-upload-container">
                            <label for="foto_profil" class="file-upload-btn">
                                <i class="fas fa-upload"></i>
                                Tambah
                            </label>
                            <input type="file" id="foto_profil" name="foto_profil" class="file-upload-input"
                                accept=".jpg,.jpeg,.png,.svg" onchange="showFilePreview(this)">
                        </div>
                        <div class="file-upload-info">Ekstensi JPG, PNG, JPEG, dan SVG</div>
                        <div id="file-preview" class="file-preview" style="display: none;"></div>
                    </div>


                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Password"
                            required minlength="8">
                    </div>


                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                            placeholder="Konfirmasi Password" required minlength="8">
                    </div>


                    <div class="form-group">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-input" value="<?= date('Y-m-d') ?>"
                            readonly>
                    </div>


                    <div class="form-actions">
                        <a href="../admin/admin.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Batalkan
                        </a>
                        <button type="submit" name="buat_akun" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Buat Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebarHoverEffect();
        });

        function initializeSidebarHoverEffect() {
            const menuItems = document.querySelectorAll(".menu-item");

            menuItems.forEach(item => {
                const img = item.querySelector("img");
                const light = item.getAttribute("data-light");
                const thick = item.getAttribute("data-thick");

                if (!img || !light || !thick) return;

                if (item.classList.contains("active")) {
                    img.src = thick;
                } else {
                    img.src = light;
                }

                item.addEventListener("mouseenter", () => {
                    img.src = thick;
                });

                item.addEventListener("mouseleave", () => {
                    if (!item.classList.contains("active")) {
                        img.src = light;
                    } else {
                        img.src = thick;
                    }
                });

                item.addEventListener("click", () => {
                    menuItems.forEach(el => {
                        el.classList.remove("active");
                        const elImg = el.querySelector("img");
                        const elLight = el.getAttribute("data-light");
                        if (elImg && elLight) {
                            elImg.src = elLight;
                        }
                    });
                    item.classList.add("active");
                    img.src = thick;
                });
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        function showFilePreview(input) {
            const preview = document.getElementById('file-preview');

            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                const fileSize = (input.files[0].size / 1024).toFixed(2);

                preview.innerHTML = `
                <i class="fas fa-file-image"></i>
                <strong>${fileName}</strong> (${fileSize} KB)
            `;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>