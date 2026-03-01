<?php
session_start();
require_once '../database/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_data'])) {
    $nama = trim($_POST['nama'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $video_url = trim($_POST['video_url'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $admin_id = $_SESSION['admin_id'];
    
    if (empty($nama) || empty($kategori) || empty($tanggal)) {
        $error_message = "Nama, Kategori, dan Tanggal wajib diisi!";
    } else {
        $check_sql = "SELECT id FROM data_budaya WHERE nama = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, 's', $nama);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Nama budaya '$nama' sudah ada! Gunakan nama yang berbeda.";
        } else {
            $gambar = null;
            
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
                $uploadDir = "../assets/img/budaya/";
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file_info = pathinfo($_FILES['gambar']['name']);
                $ext = strtolower($file_info['extension'] ?? '');
                
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $newName = "budaya_" . time() . "." . $ext;
                    $target = $uploadDir . $newName;
                    
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                        $gambar = $newName;
                    } else {
                        $error_message = "Gagal mengupload gambar!";
                    }
                } else {
                    $error_message = "Format file tidak didukung! Gunakan JPG, PNG, atau JPEG.";
                }
            }
            
            if (empty($error_message)) {
                $sql = 'INSERT INTO data_budaya (nama, kategori, gambar, video_url, deskripsi, tanggal, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)';
                $stmt = mysqli_prepare($conn, $sql);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'ssssssi', $nama, $kategori, $gambar, $video_url, $deskripsi, $tanggal, $admin_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        header('Location: ../admin/budaya.php?added=1');
                        exit;
                    } else {
                        $error_message = "Gagal menambahkan data: " . mysqli_error($conn);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Budaya - Kebudayaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Sidebar Styles */
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

        .menu-item .icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .menu-divider {
            height: 1px;
            background: #e5e5e5;
            margin: 20px;
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

        .icon-header {
            color: #dc2626;
            font-size: 28px;
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
            max-width: 600px;
            animation: fadeIn 0.6s ease;
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
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
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
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #1f2937;
            transition: border-color 0.2s ease;
            background: white;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: #9ca3af;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .file-upload-btn {
            background: #dc2626;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
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
            display: none;
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
            display: none;
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
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
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
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
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
    </style>
</head>

<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
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

            <a href="../admin/admin.php" class="menu-item" data-light="../assets/svg/admin-light.svg" data-thick="../assets/svg/admin-thick.svg">
                <img src="../assets/svg/admin-light.svg" alt="Admin" class="icon">
                <span>Admin</span>
            </a>

            <a href="../admin/budaya.php" class="menu-item active" data-light="../assets/svg/budaya-light.svg" data-thick="../assets/svg/budaya-thick.svg">
                <img src="../assets/svg/budaya-thick.svg" alt="Budaya" class="icon">
                <span>Budaya</span>
            </a>

            <div class="menu-divider"></div>

            <a href="../auth/logout.php" class="menu-item" data-light="../assets/svg/logout-light.svg" data-thick="../assets/svg/logout-thick.svg">
                <img src="../assets/svg/logout-light.svg" alt="Logout" class="icon">
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Tambah Data Budaya Baru</h2>
                <p class="form-subtitle">Isi formulir berikut ini untuk menambahkan data budaya baru.</p>

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
                        <label class="form-label" for="nama">Nama</label>
                        <input type="text" id="nama" name="nama" class="form-input" placeholder="Nama" required
                            value="<?= htmlspecialchars($nama ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-select" required>
                            <option value="">Kategori</option>
                            <option value="Tari Klasik" <?= (isset($kategori) && $kategori == 'Tari Klasik') ? 'selected' : '' ?>>Tari Tradisional</option>
                            <option value="Alat Musik" <?= (isset($kategori) && $kategori == 'Alat Musik') ? 'selected' : '' ?>>Alat Musik</option>
                            <option value="Lagu Klasik" <?= (isset($kategori) && $kategori == 'Lagu Klasik') ? 'selected' : '' ?>>Lagu Tradisional</option>
                            <option value="Dongeng" <?= (isset($kategori) && $kategori == 'Dongeng') ? 'selected' : '' ?>>Dongeng</option>
                            <option value="Makanan Khas" <?= (isset($kategori) && $kategori == 'Makanan Khas') ? 'selected' : '' ?>>Makanan Tradisional</option>
                            <option value="Minuman Khas" <?= (isset($kategori) && $kategori == 'Minuman Khas') ? 'selected' : '' ?>>Minuman Tradisional</option>
                            <option value="Pakaian Adat" <?= (isset($kategori) && $kategori == 'Pakaian Adat') ? 'selected' : '' ?>>Pakaian Adat</option>
                            <option value="Rumah Adat" <?= (isset($kategori) && $kategori == 'Rumah Adat') ? 'selected' : '' ?>>Rumah Adat</option>
                            <option value="Destinasi" <?= (isset($kategori) && $kategori == 'Destinasi') ? 'selected' : '' ?>>Destinasi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gambar</label>
                        <div>
                            <label for="gambar" class="file-upload-btn">
                                <i class="fas fa-upload"></i>
                                Upload
                            </label>
                            <input type="file" id="gambar" name="gambar" class="file-upload-input"
                                accept=".jpg,.jpeg,.png" onchange="showFilePreview(this)">
                        </div>
                        <div class="file-upload-info">Ekstensi JPG, PNG, dan JPEG</div>
                        <div id="file-preview" class="file-preview"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="video_url">Video</label>
                        <input type="url" id="video_url" name="video_url" class="form-input" placeholder="Video"
                            value="<?= htmlspecialchars($video_url ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-textarea" placeholder="Placeholder"><?= htmlspecialchars($deskripsi ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-input" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-actions">
                        <a href="../admin/budaya.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Batalkan
                        </a>
                        <button type="submit" name="tambah_data" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i>
                            Tambah Data
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
                    }
                });
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
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
    </script>
</body>

</html>
