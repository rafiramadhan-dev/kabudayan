<?php
session_start();
require_once '../database/koneksi.php';


if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}


$error_message = '';
$success_message = '';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$from = isset($_GET['from']) ? $_GET['from'] : 'budaya';
$back_url = ($from === 'dashboard') ? '../admin/dashboard.php' : '../admin/budaya.php';
$back_text = ($from === 'dashboard') ? '' : '';


if ($id <= 0) {
    header('Location: ../admin/budaya.php');
    exit;
}

$sql = "SELECT * FROM data_budaya WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) == 0) {
    header('Location: ../admin/budaya.php');
    exit;
}


$data = mysqli_fetch_assoc($result);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_data'])) {
    $nama = trim($_POST['nama'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $video_url = trim($_POST['video_url'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';


    if (empty($nama) || empty($kategori) || empty($tanggal)) {
        $error_message = "Nama, Kategori, dan Tanggal wajib diisi!";
    } else {
        $gambar = $data['gambar']; 


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
                    if (!empty($data['gambar']) && file_exists($uploadDir . $data['gambar'])) {
                        unlink($uploadDir . $data['gambar']);
                    }
                    $gambar = $newName;
                } else {
                    $error_message = "Gagal mengupload gambar!";
                }
            } else {
                $error_message = "Format file tidak didukung! Gunakan JPG, PNG, atau JPEG.";
            }
        }


        if (empty($error_message)) {
            $update_sql = 'UPDATE data_budaya SET nama = ?, kategori = ?, gambar = ?, video_url = ?, deskripsi = ?, tanggal = ? WHERE id = ?';
            $update_stmt = mysqli_prepare($conn, $update_sql);


            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, 'ssssssi', $nama, $kategori, $gambar, $video_url, $deskripsi, $tanggal, $id);


                if (mysqli_stmt_execute($update_stmt)) {
                    header('Location: ../admin/budaya.php?updated=1');
                    exit;
                } else {
                    $error_message = "Gagal memperbarui data: " . mysqli_error($conn);
                }


                mysqli_stmt_close($update_stmt);
            } else {
                $error_message = "Error prepare statement: " . mysqli_error($conn);
            }
        }
    }
}


// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_data'])) {
    if (!empty($data['gambar'])) {
        $gambar_path = "../assets/img/budaya/" . $data['gambar'];
        if (file_exists($gambar_path)) {
            unlink($gambar_path);
        }
    }


    $delete_sql = 'DELETE FROM data_budaya WHERE id = ?';
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, 'i', $id);


    if (mysqli_stmt_execute($delete_stmt)) {
        header('Location: ../admin/budaya.php?deleted=1');
        exit;
    } else {
        $error_message = "Gagal menghapus data: " . mysqli_error($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="id">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Data Budaya - Kebudayaan</title>
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
            background: var(--red);
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


        .current-file {
            margin-top: 10px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 13px;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }


        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
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
            background: #6b7280;
            color: white;
        }


        .btn-primary:hover {
            background: var(--gray-dark);
        }


        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }


        .btn-secondary:hover {
            background: #e5e7eb;
        }


        .btn-danger {
            background: var(--red);
            color: white;
        }


        .btn-danger:hover {
            background: var(--red-dark);
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


        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }


        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease;
        }


        .modal-header {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }


        .modal-body {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 24px;
        }


        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
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


        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }


            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>


<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>


    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Konfirmasi Hapus</div>
            <div class="modal-body">Apakah Anda yakin ingin menghapus data budaya ini?</div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                    Batal
                </button>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="delete_data" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>


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


    <main class="main-content">
        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Detail Data Budaya</h2>
                <p class="form-subtitle">Gunakan formulir ini untuk memperbarui informasi budaya.</p>


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
                            value="<?= htmlspecialchars($data['nama']) ?>">
                    </div>


                    <div class="form-group">
                        <label class="form-label" for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-select" required>
                            <option value="">Kategori</option>
                            <option value="Tari Klasik" <?= $data['kategori'] == 'Tari Klasik' ? 'selected' : '' ?>>Tari Tradisional</option>
                            <option value="Alat Musik" <?= $data['kategori'] == 'Alat Musik' ? 'selected' : '' ?>>Alat Musik</option>
                            <option value="Lagu Klasik" <?= $data['kategori'] == 'Lagu Klasik' ? 'selected' : '' ?>>Lagu Tradisional</option>
                            <option value="Dongeng" <?= $data['kategori'] == 'Dongeng' ? 'selected' : '' ?>>Dongeng</option>
                            <option value="Makanan Khas" <?= $data['kategori'] == 'Makanan Khas' ? 'selected' : '' ?>>Makanan Tradisional</option>
                            <option value="Minuman Khas" <?= $data['kategori'] == 'Minuman Khas' ? 'selected' : '' ?>>Minuman Tradisional</option>
                            <option value="Pakaian Adat" <?= $data['kategori'] == 'Pakaian Adat' ? 'selected' : '' ?>>Pakaian Adat</option>
                            <option value="Rumah Adat" <?= $data['kategori'] == 'Rumah Adat' ? 'selected' : '' ?>>Rumah Adat</option>
                            <option value="Destinasi" <?= $data['kategori'] == 'Destinasi' ? 'selected' : '' ?>>Destinasi</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <label class="form-label">Gambar</label>
                        <?php if (!empty($data['gambar'])): ?>
                            <div class="current-file">
                                <i class="fas fa-file-image"></i>
                                <span>File saat ini: <strong><?= htmlspecialchars($data['gambar']) ?></strong></span>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top: 10px;">
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
                            value="<?= htmlspecialchars($data['video_url']) ?>">
                    </div>


                    <div class="form-group">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-textarea" placeholder="Placeholder"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                    </div>


                    <div class="form-group">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-input" value="<?= htmlspecialchars($data['tanggal']) ?>" required>
                    </div>


                    <div class="form-actions">
                        <a href="<?= $back_url ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Kembali <?= $back_text ?>
                        </a>
                        <div style="display: flex; gap: 12px;">
                            <button type="submit" name="update_data" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Perbarui
                            </button>
                            <button type="button" class="btn btn-danger" onclick="openModal()">
                                <i class="fas fa-trash"></i>
                                Hapus
                            </button>
                        </div>
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


        function openModal() {
            document.getElementById('deleteModal').style.display = 'block';
        }


        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>


</html>