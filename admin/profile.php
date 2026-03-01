<?php
session_start();
require_once '../database/koneksi.php';

$admin_id = $_SESSION['admin_id'] ?? 1;

$sql  = "SELECT id, email, username, foto_profil FROM admin WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $admin_id);
mysqli_stmt_execute($stmt);
$res   = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($res);

if (!$admin) {
    http_response_code(404);
    exit('Admin tidak ditemukan');
}

$tanggal_pembuatan = date('Y-m-d');

$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile</title>

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
            background: var(--red);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
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

        .sidebar-menu .icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            transition: 0.3s;
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

        .menu-item i {
            width: 18px;
            font-size: 16px;
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

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .content-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .header-icon-container {
            width: 42px;
            height: 42px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header-icon-container .header-icon {
            width: 36px;
            height: 36px;
            filter: brightness(0) saturate(100%) invert(23%) sepia(89%) saturate(2134%) hue-rotate(346deg) brightness(98%) contrast(94%);
        }

        .content-header h1 {
            font-size: 28px;
            color: #1f2937;
            font-weight: 600;
        }

        .profile-container {
            max-width: 1200px;
            display: flex;
            gap: 30px;
            animation: fadeIn 0.2s ease;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
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

        .profile-photo-card {
            width: 300px;
            flex-shrink: 0;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .photo-section {
            padding: 30px;
            text-align: center;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .profile-photo-container {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            margin: 0 auto 20px;
            flex-shrink: 0;
        }

        .profile-photo {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            border-radius: 50%;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 50%;
        }

        .profile-photo-container:hover .overlay {
            opacity: 1;
        }

        .profile-name {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .forms-column {
            flex: 1;
        }

        .info-section {
            padding: 30px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .section-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
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
        }

        .form-input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-input.error {
            border-color: #dc2626;
            background-color: #fef2f2;
        }

        .form-input.error:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
        }

        .form-input:read-only {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }

        .form-input:read-only:focus {
            border-color: #d1d5db;
            box-shadow: none;
        }

        .date-input {
            position: relative;
        }

        .date-input input {
            padding-right: 40px;
        }

        .password-requirements {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
            line-height: 1.4;
        }

        .btn-save {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            float: right;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save:hover {
            background: #b91c1c;
        }

        .btn-save:disabled {
            background: #9ca3af;
            cursor: not-allowed;
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

        .email-check-result {
            font-size: 12px;
            margin-top: 6px;
            font-weight: 500;
        }

        .email-available {
            color: #059669;
        }

        .email-taken {
            color: #dc2626;
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

            .profile-container {
                flex-direction: column;
                gap: 20px;
            }

            .profile-photo-card {
                width: 100%;
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

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(220, 38, 38, 0.1),
                    transparent);
            transition: left 0.2s ease;
        }

        .menu-item:hover::before {
            left: 100%;
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
            <a href="dashboard.php" class="menu-item" data-light="../assets/svg/dashboard-light.svg" data-thick="../assets/svg/dashboard-thick.svg">
                <img src="../assets/svg/dashboard-light.svg" alt="Dashboard" class="icon" />
                <span>Dashboard</span>
            </a>
            <a href="profile.php" class="menu-item active" data-light="../assets/svg/profile-light.svg" data-thick="../assets/svg/profile-thick.svg">
                <img src="../assets/svg/profile-thick.svg" alt="Profile" class="icon" />
                <span>Profile</span>
            </a>
            <a href="admin.php" class="menu-item" data-light="../assets/svg/admin-light.svg" data-thick="../assets/svg/admin-thick.svg">
                <img src="../assets/svg/admin-light.svg" alt="Admin" class="icon" />
                <span>Admin</span>
            </a>
            <a href="budaya.php" class="menu-item" data-light="../assets/svg/budaya-light.svg" data-thick="../assets/svg/budaya-thick.svg">
                <img src="../assets/svg/budaya-light.svg" alt="Budaya" class="icon" />
                <span>Budaya</span>
            </a>
            <div class="menu-divider"></div>
            <a href="../auth/logout.php" class="menu-item" data-light="../assets/svg/logout-light.svg" data-thick="../assets/svg/logout-thick.svg">
                <img src="../assets/svg/logout-light.svg" alt="Logout" class="icon" />
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="content-header">
            <div class="header-icon-container">
                <img src="../assets/svg/profile-thick.svg" alt="Dashboard Icon" class="header-icon">
            </div>
            <h1 id="pageTitle">Profile</h1>
        </div>

        <div class="profile-container">
            <div class="profile-photo-card">
                <div class="info-card">
                    <div class="photo-section">
                        <h3 class="section-title">Foto Profile</h3>

                        <div class="profile-photo-container">
                            <div class="profile-photo"
                                style="background-image: url('../assets/img/profile/<?= htmlspecialchars($admin['foto_profil'] ?? "default.png") ?>');">
                            </div>
                            <div class="overlay">
                                <span><i class="fa-solid fa-pen"></i></span>
                            </div>
                            <form method="post" action="../function/profil_update.php" enctype="multipart/form-data" id="formFoto">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>" />
                                <input type="file" name="foto" id="fileInput" accept="image/*" hidden />
                                <input type="hidden" name="ubah_foto" value="1" />
                            </form>
                        </div>

                        <div class="profile-name"><?= htmlspecialchars($admin['username']) ?></div>
                    </div>
                </div>
            </div>
            <div class="forms-column">
                <div class="info-card">
                    <?php if ($flash_message): ?>
                        <div class="alert <?= strpos($flash_message, 'berhasil') !== false ? 'alert-success' : 'alert-error' ?>">
                            <?= htmlspecialchars($flash_message) ?>
                        </div>
                    <?php endif; ?>

                    <div class="info-section">
                        <h3 class="section-title">Informasi Akun</h3>

                        <form method="post" action="../function/profil_update.php" id="form-akun">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>" />
                            <input type="hidden" name="update_akun" value="1" />
                            <div class="form-group">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" id="email" class="form-input" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required />
                                <div id="email-check-result" class="email-check-result"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="username">Username</label>
                                <input type="text" id="username" class="form-input" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required />
                            </div>
                            <div class="form-group date-input readonly">
                                <label class="form-label" for="tanggal">Tanggal</label>
                                <input type="date" id="tanggal" class="form-input" name="tanggal" value="<?= $tanggal_pembuatan ?>" readonly />
                            </div>
                            <button type="submit" class="btn-save" id="save-akun-btn" style="margin-top: 20px;">
                                <i class="fas fa-save"></i>
                                Simpan
                            </button>
                            <div style="clear: both;"></div>
                        </form>
                    </div>

                    <div class="info-section">
                        <h3 class="section-title">Informasi Password</h3>

                        <form method="post" action="../function/profil_update.php" id="form-password">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($admin['id']) ?>" />
                            <div class="form-group">
                                <label class="form-label" for="password_baru">Password Baru</label>
                                <input type="password" id="password_baru" class="form-input" name="password_baru" minlength="8" maxlength="72" placeholder="Password Baru" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="password_konfirmasi">Konfirmasi Password</label>
                                <input type="password" id="password_konfirmasi" class="form-input" name="password_konfirmasi" minlength="8" maxlength="72" placeholder="Konfirmasi Password" required />
                            </div>
                            <div class="password-requirements">
                                <strong>Syarat Mengganti Password :</strong><br />
                                • Password harus memiliki 8-12 karakter.<br />
                                • Password harus memiliki huruf, angka dan simbol.
                            </div>
                            <button type="submit" class="btn-save" name="ubah_password" style="margin-top: 20px;">
                                <i class="fas fa-save"></i>
                                Simpan
                            </button>
                            <div style="clear: both;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebarHoverEffect();
            initializeEmailValidation();
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
                    img.src = item.classList.contains("active") ? thick : light;
                });
                item.addEventListener("click", () => {
                    menuItems.forEach(el => {
                        el.classList.remove("active");
                        const elImg = el.querySelector("img");
                        const elLight = el.getAttribute("data-light");
                        if (elImg && elLight) elImg.src = elLight;
                    });
                    item.classList.add("active");
                    img.src = thick;
                });
            });
        }

        function initializeEmailValidation() {
            const emailInput = document.getElementById('email');
            const emailCheckResult = document.getElementById('email-check-result');
            const originalEmail = '<?= $admin['email'] ?>';
            let emailCheckTimeout;

            emailInput.addEventListener('input', function() {
                const email = this.value.trim();

                clearTimeout(emailCheckTimeout);
                emailInput.classList.remove('error');
                emailCheckResult.innerHTML = '';
                emailCheckResult.classList.remove('email-available', 'email-taken');

                if (email === originalEmail) {
                    return;
                }

                if (email === '') {
                    return;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    emailCheckResult.innerHTML = 'Format email tidak valid';
                    emailCheckResult.classList.add('email-taken');
                    emailInput.classList.add('error');
                    return;
                }

                emailCheckTimeout = setTimeout(() => {
                    checkEmailAvailability(email);
                }, 500);
            });
        }

        function checkEmailAvailability(email) {
            const emailCheckResult = document.getElementById('email-check-result');
            const emailInput = document.getElementById('email');

            emailCheckResult.innerHTML = 'Memeriksa ketersediaan email...';
            emailCheckResult.classList.remove('email-available', 'email-taken');

            fetch('../function/check_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email) + '&admin_id=<?= $admin['id'] ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        emailCheckResult.innerHTML = 'Email tersedia';
                        emailCheckResult.classList.add('email-available');
                        emailInput.classList.remove('error');
                    } else {
                        emailCheckResult.innerHTML = 'Email sudah digunakan';
                        emailCheckResult.classList.add('email-taken');
                        emailInput.classList.add('error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    emailCheckResult.innerHTML = 'Gagal memeriksa email';
                    emailCheckResult.classList.add('email-taken');
                });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
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
    <script>
        const container = document.querySelector('.profile-photo-container');
        const fileInput = document.getElementById('fileInput');
        const formFoto = document.getElementById('formFoto');

        container.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                formFoto.submit();
            }
        });
    </script>

</body>

</html>
