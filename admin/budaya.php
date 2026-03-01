<?php
session_start();
require_once '../database/koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kategori = isset($_GET['filter']) ? $_GET['filter'] : '';

$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $where .= " AND (nama LIKE ? OR kategori LIKE ? OR deskripsi LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($filter_kategori)) {
    $where .= " AND kategori = ?";
    $params[] = $filter_kategori;
    $types .= "s";
}

$count_sql = "SELECT COUNT(*) as total FROM data_budaya $where";
$count_stmt = mysqli_prepare($conn, $count_sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

$sql = "SELECT * FROM data_budaya $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budaya - Kebudayaan</title>
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
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeIn 0.1s ease forwards;
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
        }

        .content-header h1 {
            font-size: 28px;
            color: #1f2937;
            font-weight: 600;
        }

        .top-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 20px;
        }

        .search-and-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 300px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-csv,
        .btn-print {
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
        }

        .btn-csv:hover,
        .btn-print:hover {
            background: #b91c1c;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
            transform: translateY(-1px);
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

        .search-input::placeholder {
            color: #9ca3af;
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

        .toolbar-right {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px 8px 0 0;
            font-size: 14px;
            color: var(--red);
            background: white;
            cursor: pointer;
            min-width: 150px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 35px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
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
            margin-top: 20px;
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

        .btn-detail {
            color: #dc2626;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .btn-detail:hover {
            color: #b91c1c;
            text-decoration: underline;
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
                max-width: 100%;
            }

            .toolbar-right {
                flex-direction: column;
                width: 100%;
            }

            .filter-select,
            .add-btn {
                width: 100%;
            }

            table {
                font-size: 12px;
            }

            thead th,
            tbody td {
                padding: 8px;
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

        .admin-table {
            animation: fadeIn 0.6s ease;
        }

        @media print {

            .sidebar,
            .mobile-toggle,
            .top-controls,
            .pagination {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 20px !important;
            }

            .content-header {
                margin-bottom: 20px;
            }

            .admin-table {
                box-shadow: none !important;
                border: 1px solid #000;
            }

            table {
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 8px !important;
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
        <div class="content-header">
            <div class="header-icon-container">
                <img src="../assets/svg/budaya-red.svg" alt="Dashboard Icon" class="header-icon">
            </div>
            <h1 id="pageTitle">Budaya</h1>
        </div>

        <div class="top-controls">
            <div class="search-and-actions">
                <form class="search-container" method="GET">
                    <input type="text" name="search" class="search-input" placeholder="Cari budaya..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <div class="action-buttons">
                    <button class="btn-csv" onclick="downloadCSV()">
                        <i class="fas fa-download"></i>
                        CSV
                    </button>
                    <button class="btn-print" onclick="printTable()">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                </div>
            </div>
            <div class="toolbar-right">
                <form method="GET" action="">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <select name="filter" class="filter-select" onchange="this.form.submit()">
                        <option value="">Kategori</option>
                        <option value="Tari Klasik" <?= $filter_kategori == 'Tari Klasik' ? 'selected' : '' ?>>Tari Tradisional</option>
                        <option value="Alat Musik" <?= $filter_kategori == 'Alat Musik' ? 'selected' : '' ?>>Alat Musik</option>
                        <option value="Lagu Klasik" <?= $filter_kategori == 'Lagu Klasik' ? 'selected' : '' ?>>Lagu Tradisional</option>
                        <option value="Dongeng" <?= $filter_kategori == 'Dongeng' ? 'selected' : '' ?>>Dongeng</option>
                        <option value="Makanan Khas" <?= $filter_kategori == 'Makanan Khas' ? 'selected' : '' ?>>Makanan Tradisional</option>
                        <option value="Minuman Khas" <?= $filter_kategori == 'Minuman Khas' ? 'selected' : '' ?>>Minuman Tradisional</option>
                        <option value="Pakaian Adat" <?= $filter_kategori == 'Pakaian Adat' ? 'selected' : '' ?>>Pakaian Adat</option>
                        <option value="Rumah Adat" <?= $filter_kategori == 'Rumah Adat' ? 'selected' : '' ?>>Rumah Adat</option>
                        <option value="Destinasi" <?= $filter_kategori == 'Destinasi' ? 'selected' : '' ?>>Destinasi</option>
                    </select>
                </form>

                <a href="../function/tambah_budaya.php" class="add-btn">
                    <i class="fas fa-plus"></i>
                    Tambah
                </a>
            </div>
        </div>

        <div class="admin-table">
            <div class="table-header">
                <h2 class="table-title">Data Budaya</h2>
            </div>

            <table id="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++ ?>.</td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                <td><?= date('F, d-Y', strtotime($row['tanggal'])) ?></td>
                                <td>
                                    <a href="../function/detail_budaya.php?id=<?= $row['id'] ?>" class="btn-detail">Lihat Detail</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                                <br>
                                Tidak ada data budaya
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <?php
                $search_param = '';
                if (!empty($search)) $search_param .= '&search=' . urlencode($search);
                if (!empty($filter_kategori)) $search_param .= '&filter=' . urlencode($filter_kategori);
                ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $search_param ?>" class="pagination-btn">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <span class="pagination-btn active"><?= $page ?> / <?= $total_pages ?></span>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $search_param ?>" class="pagination-btn">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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

        function downloadCSV() {
            const table = document.getElementById('data-table');
            let csv = [];
            const rows = table.querySelectorAll('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = [],
                    cols = rows[i].querySelectorAll('td, th');

                for (let j = 0; j < cols.length - 1; j++) {
                    let text = cols[j].innerText.replace(/"/g, '""');
                    row.push('"' + text + '"');
                }

                csv.push(row.join(','));
            }

            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');

            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'data_budaya.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        function printTable() {
            window.print();
        }
    </script>

</body>
</html>