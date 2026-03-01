<?php
session_start();
require_once '../database/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$current_admin_id = $_SESSION['admin_id'];

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where_clause = "WHERE email LIKE ? OR username LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
    $types = 'ss';
}

$count_sql = "SELECT COUNT(*) as total FROM admin $where_clause";
$count_stmt = mysqli_prepare($conn, $count_sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

//untuk deteksi online berdasarkan session
$sql = "SELECT id, email, username, verifikasi, foto_profil, session_id
        FROM admin $where_clause ORDER BY id ASC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);

$bind_params = $params;
$bind_params[] = $limit;
$bind_params[] = $offset;
$bind_types = $types . 'ii';

mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kebudayaan</title>
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

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: none;
        }

        .alert.show {
            display: block;
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

        .top-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 20px;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 40px;
            font-size: 14px;
            background: white;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 8px;
            cursor: pointer;
            color: #6b7280;
            font-size: 16px;
            transition: color 0.2s ease;
        }

        .search-btn:hover {
            color: #dc2626;
        }

        .add-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
        }

        .add-btn:hover {
            background: #b91c1c;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
            transform: translateY(-1px);
        }

        .admin-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            background: white;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #fef2f2;
            padding: 12px 20px;
            text-align: left;
            font-weight: 600;
            color: #dc2626;
            font-size: 16px;
            border-bottom: none;
        }

        td {
            padding: 15px 20px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:nth-child(even) {
            background-color: #fefefe;
        }

        tbody tr:nth-child(odd) {
            background-color: white;
        }

        tr:hover {
            background-color: var(--gray-light) !important;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 50px;
        }

        .status-online {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .status-offline {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .status-disabled {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .status-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-description {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 50px;
        }

        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
            background: var(--gray-dark);
            border-radius: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border: none;
            outline: none;
        }

        .toggle-switch.active {
            background: var(--red-dark);
        }

        .toggle-switch::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .toggle-switch.active::before {
            transform: translateX(20px);
        }

        .toggle-switch:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 20px;
            background: white;
            margin-top: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pagination-btn:hover {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .pagination-btn.active {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }

        .pagination-btn:disabled {
            background: #f9fafb;
            color: #9ca3af;
            cursor: not-allowed;
            border-color: #e5e7eb;
        }

        .no-data {
            padding: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
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

            .top-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                max-width: none;
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

            .admin-table {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
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
    <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo"><img src="../assets/svg/Logo.svg" alt="Logo Kebudayaan"></div>
            <div class="title">Kebudayaan</div>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item" data-light="../assets/svg/dashboard-light.svg" data-thick="../assets/svg/dashboard-thick.svg">
                <img src="../assets/svg/dashboard-light.svg" alt="Dashboard" class="icon"><span>Dashboard</span>
            </a>
            <a href="profile.php" class="menu-item" data-light="../assets/svg/profile-light.svg" data-thick="../assets/svg/profile-thick.svg">
                <img src="../assets/svg/profile-light.svg" alt="Profile" class="icon"><span>Profile</span>
            </a>
            <a href="admin.php" class="menu-item active" data-light="../assets/svg/admin-light.svg" data-thick="../assets/svg/admin-thick.svg">
                <img src="../assets/svg/admin-thick.svg" alt="Admin" class="icon"><span>Admin</span>
            </a>
            <a href="budaya.php" class="menu-item" data-light="../assets/svg/budaya-light.svg" data-thick="../assets/svg/budaya-thick.svg">
                <img src="../assets/svg/budaya-light.svg" alt="Budaya" class="icon"><span>Budaya</span>
            </a>
            <div class="menu-divider"></div>
            <a href="../auth/logout.php" class="menu-item" data-light="../assets/svg/logout-light.svg" data-thick="../assets/svg/logout-thick.svg">
                <img src="../assets/svg/logout-light.svg" alt="Logout" class="icon"><span>Logout</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="content-header">
            <div class="header-icon-container">
                <img src="../assets/svg/admin-thick.svg" alt="Dashboard Icon" class="header-icon">
            </div>
            <h1>Admin</h1>
        </div>

        <div id="alertContainer"></div>

        <div class="top-controls">
            <form class="search-container" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Cari admin..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
            <a href="../function/tambah_admin.php" class="add-btn"><i class="fas fa-plus"></i> Tambah</a>
        </div>

        <div class="admin-table">
            <div class="table-header">
                <h2 class="table-title">Aktifasi Admin</h2>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php
                        $no = $offset + 1;
                        while ($admin = mysqli_fetch_assoc($result)):
                            $is_current = ($admin['id'] == $current_admin_id);
                            $verifikasi = $admin['verifikasi'];

                            $is_online = ($admin['session_id'] !== null);

                            if ($verifikasi == '1') {
                                if ($is_online) {
                                    // Admin sedang login
                                    $status_class = 'status-online';
                                    $status_text = 'On';
                                    $status_description = 'Login';
                                } else {
                                    // Admin aktif tapi tidak login
                                    $status_class = 'status-offline';
                                    $status_text = 'Off';
                                    $status_description = '';
                                }
                                $toggle_active = true;
                            } elseif ($verifikasi == '0') {
                                $status_class = 'status-disabled';
                                $status_text = 'Disabled';
                                $status_description = 'Pending';
                                $toggle_active = false;
                            } else {
                                $status_class = 'status-disabled';
                                $status_text = 'Disabled';
                                $status_description = 'Dinonaktifkan';
                                $toggle_active = false;
                            }
                        ?>
                            <tr data-admin-id="<?= $admin['id'] ?>">
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td><?= htmlspecialchars($admin['username']) ?></td>
                                <td>
                                    <div class="status-container">
                                        <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                        <?php if ($status_description): ?>
                                            <span class="status-description"><?= $status_description ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <button type="button"
                                        class="toggle-switch <?= $toggle_active ? 'active' : '' ?>"
                                        data-admin-id="<?= $admin['id'] ?>"
                                        data-current-status="<?= $verifikasi ?>"
                                        <?= $is_current ? 'disabled title="Tidak dapat menonaktifkan diri sendiri"' : '' ?>
                                        onclick="toggleAdminStatus(<?= $admin['id'] ?>, '<?= $verifikasi ?>', <?= $is_online ? 'true' : 'false' ?>)">
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                                <br>Tidak ada data admin
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn"><i class="fas fa-angle-double-left"></i></a>
                    <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn"><i class="fas fa-angle-left"></i></a>
                <?php endif; ?>
                <span class="pagination-btn active"><?= $page ?> / <?= $total_pages ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn"><i class="fas fa-angle-right"></i></a>
                    <a href="?page=<?= $total_pages ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn"><i class="fas fa-angle-double-right"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        const currentAdminId = <?= $current_admin_id ?>;

        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebarHoverEffect();
            updateAdminActivity();
            setInterval(updateAdminActivity, 10000);
            setInterval(checkAdminStatus, 5000);
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

        function updateAdminActivity() {
            fetch('../function/update_admin_activity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to update activity:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function checkAdminStatus() {
            fetch('../function/check_admin_status.php')
                .then(response => response.json())
                .catch(error => console.error('Error:', error));
        }

        function toggleAdminStatus(adminId, currentStatus, isOnline) {
            let newStatus;
            if (currentStatus == '1') {
                if (isOnline && adminId != currentAdminId) {
                    showAlert('Admin sedang login, tidak bisa dinonaktifkan', 'error');
                    return;
                }
                newStatus = '2';
            } else {
                newStatus = '1';
            }

            if (adminId == currentAdminId && newStatus == '2') {
                showAlert('Tidak dapat menonaktifkan diri sendiri', 'error');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin mengubah status admin ini?')) {
                return;
            }

            fetch('../function/toggle_admin_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `admin_id=${adminId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Terjadi kesalahan saat mengubah status', 'error');
                });
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            alertContainer.innerHTML = `
                <div class="alert ${alertClass} show">
                    <i class="fas ${icon}"></i>
                    ${message}
                </div>
            `;

            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    alert.classList.remove('show');
                }
            }, 5000);
        }
    </script>
</body>

</html>
