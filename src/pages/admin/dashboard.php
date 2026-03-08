<?php
session_start();
require '../database/koneksi.php';

// Ambil Id login
$id = $_GET['id'] ?? $_SESSION['id'];

$stmtLogin = $koneksi->prepare('SELECT * FROM admin WHERE id = ?');
$stmtLogin ->bind_param('i', $id);
$stmtLogin ->execute();
$resultLogin = $stmtLogin->get_result();
$dataLogin = $resultLogin->fetch_assoc();

try {
    $admin_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM admin");
    $total_admin = $admin_query ? mysqli_fetch_assoc($admin_query)['total'] : 0;

    $budaya_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data_budaya");
    $total_budaya = $budaya_query ? mysqli_fetch_assoc($budaya_query)['total'] : 0;

    $chart_data = [];
    $kategori_query = mysqli_query($koneksi, "SELECT kategori, COUNT(*) as total FROM data_budaya WHERE kategori IS NOT NULL AND kategori != '' GROUP BY kategori ORDER BY total DESC");
    if ($kategori_query && mysqli_num_rows($kategori_query) > 0) {
        while ($row = mysqli_fetch_assoc($kategori_query)) {
            $chart_data[$row['kategori']] = (int)$row['total'];
        }
    }
    if (empty($chart_data)) {
        $chart_data = [
            'Tari Klasik' => 0,
            'Alat Musik' => 0,
            'Lagu Klasik' => 0,
            'Dongeng' => 0,
            'Makanan Khas' => 0,
            'Minuman Khas' => 0,
            'Pakaian Adat' => 0,
            'Rumah Adat' => 0,
            'Destinasi' => 0
        ];
    }

    $latest_budaya = [];
    $latest_query = mysqli_query($koneksi, "SELECT id, nama, kategori, gambar, created_at FROM data_budaya ORDER BY created_at DESC LIMIT 10");
    if ($latest_query && mysqli_num_rows($latest_query) > 0) {
        while ($row = mysqli_fetch_assoc($latest_query)) {
            $latest_budaya[] = $row;
        }
    }
} catch (Exception $e) {
    error_log('Dashboard error: ' . $e->getMessage());
    $total_admin = 0;
    $total_budaya = 0;
    $chart_data = [
        'Tari Klasik' => 0,
        'Alat Musik' => 0,
        'Lagu Klasik' => 0,
        'Dongeng' => 0,
        'Makanan Khas' => 0,
        'Minuman Khas' => 0,
        'Pakaian Adat' => 0,
        'Rumah Adat' => 0,
        'Destinasi' => 0
    ];
    $latest_budaya = [];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Kebudayaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            font-family: 'Satoshi', sans-serif;
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
            filter: brightness(0) saturate(100%) invert(23%) sepia(89%) saturate(2134%) hue-rotate(346deg) brightness(98%) contrast(94%);
        }

        .content-header h1 {
            font-size: 28px;
            color: #1f2937;
            font-weight: 600;
        }

        .stats-section {
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #dc2626;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            background: #dc2626;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content h3 {
            font-size: 32px;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 4px;
        }

        .stat-content p {
            color: #1f2937;
            font-size: 16px;
            font-weight: 600;
        }

        .budaya-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-header i {
            color: #dc2626;
            font-size: 20px;
        }

        .section-header h2 {
            color: #1f2937;
            font-size: 20px;
            font-weight: 600;
        }

        .budaya-list {
            display: grid;
            gap: 12px;
            max-height: 310px;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .budaya-list::-webkit-scrollbar {
            display: none;
        }

        .budaya-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none;
            color: inherit;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .budaya-item:hover {
            background-color: #fef2f2;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
        }

        .budaya-item:last-child {
            border-bottom: none;
        }

        .budaya-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            overflow: hidden;
            flex-shrink: 0;
        }

        .budaya-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .budaya-icon.no-image {
            background: #dc2626;
        }

        .budaya-info {
            flex: 1;
        }

        .budaya-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .budaya-category {
            font-size: 12px;
            color: #6b7280;
        }

        .budaya-time {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
        }

        .chart-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            position: relative;
            width: 900px;
            max-width: 100%;
            height: 370px;
            margin-top: 20px;
            padding: 0 20px;
        }

        #downloadChart {
            background: var(--gray);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            float: right;
            margin-bottom: 12px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        #downloadChart:hover {
            background-color: var(--gray-dark);
            box-shadow: 0 4px 5px rgba(0, 0, 0, 0.4);
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
    <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo"><img src="../assets/svg/Logo.svg" alt="Logo Kebudayaan" /></div>
            <div class="title">Kebudayaan</div>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item active" data-light="../assets/svg/dashboard-light.svg" data-thick="../assets/svg/dashboard-thick.svg">
                <img src="../assets/svg/dashboard-thick.svg" alt="Dashboard" class="icon" />
                <span>Dashboard</span>
            </a>
            <a href="profile.php" class="menu-item" data-light="../assets/svg/profile-light.svg" data-thick="../assets/svg/profile-thick.svg">
                <img src="../assets/svg/profile-light.svg" alt="Profile" class="icon" />
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
                <img src="../assets/svg/dashboard-thick.svg" alt="Dashboard Icon" class="header-icon" />
            </div>
            <h1>Dashboard</h1>
        </div>
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card admin">
                    <div class="stat-icon admin"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <h3><?= $total_admin ?></h3>
                        <p>Total Data Admin</p>
                    </div>
                </div>
                <div class="stat-card budaya">
                    <div class="stat-icon budaya">
                        <img src="../assets/svg/Logo.svg" alt="Budaya Icon" style="width: 24px; height: 24px; filter: brightness(0) invert(1);" />
                    </div>
                    <div class="stat-content">
                        <h3><?= $total_budaya ?></h3>
                        <p>Akumulasi Data Budaya</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="budaya-section">
            <div class="section-header">
                <i class="fas fa-list"></i>
                <h2>Daftar Data Baru</h2>
            </div>
            <div class="budaya-list">
                <?php if (!empty($latest_budaya)): ?>
                    <?php foreach ($latest_budaya as $budaya):
                        $created_time = strtotime($budaya['created_at']);
                        $current_time = time();
                        $time_diff = $current_time - $created_time;
                        if ($time_diff < 60) {
                            $time_text = "Baru saja";
                        } elseif ($time_diff < 3600) {
                            $minutes = floor($time_diff / 60);
                            $time_text = $minutes . " menit yang lalu";
                        } elseif ($time_diff < 86400) {
                            $hours = floor($time_diff / 3600);
                            $time_text = $hours . " jam yang lalu";
                        } else {
                            $days = floor($time_diff / 86400);
                            $time_text = $days . " hari yang lalu";
                        }
                    ?>
                        <a href="../function/detail_budaya.php?id=<?= $budaya['id'] ?>&from=dashboard" class="budaya-item">
                            <div class="budaya-icon <?= empty($budaya['gambar']) ? 'no-image' : '' ?>">
                                <?php if (!empty($budaya['gambar'])): ?>
                                    <img src="../assets/img/budaya/<?= htmlspecialchars($budaya['gambar']) ?>" alt="<?= htmlspecialchars($budaya['nama']) ?>" />
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </div>
                            <div class="budaya-info">
                                <div class="budaya-name"><?= htmlspecialchars($budaya['nama']) ?></div>
                                <div class="budaya-category"><?= htmlspecialchars($budaya['kategori']) ?></div>
                            </div>
                            <div class="budaya-time"><?= $time_text ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 12px; color: #d1d5db;"></i>
                        <p>Belum ada data budaya</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="chart-section">
            <div class="section-header">
                <div style="display:flex; align-items:center;">
                    <i class="fas fa-chart-bar"></i>
                    <h2>Perbandingan Kategori</h2>
                </div>
                <div style="margin-left:auto;">
                    <i class="fas fa-download" id="downloadChart" style="cursor:pointer; font-size: 20px; color:#111;"></i>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebarHoverEffect();

            const labels = <?= json_encode(array_keys($chart_data)) ?>;
            const dataValues = <?= json_encode(array_values($chart_data)) ?>;
            const ctx = document.getElementById('barChart').getContext('2d');
            const barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: '#dc2626',
                        borderRadius: 8,
                        barPercentage: 0.65,
                        categoryPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#dc2626',
                            titleColor: '#fff',
                            bodyColor: '#fff'
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#212136',
                                font: {
                                    size: 12,
                                    weight: 'original'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 2,
                                color: '#dc2626',
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                color: '#ccc',
                                borderDash: [5, 5]
                            }
                        }
                    }
                }
            });

            document.getElementById('downloadChart').addEventListener('click', function() {
                const link = document.createElement('a');
                link.href = barChart.toBase64Image({
                    backgroundColor: 'white'
                });
                link.download = 'chart-data-budaya.png';
                link.click();
            });
        });

        function initializeSidebarHoverEffect() {
            const menuItems = document.querySelectorAll(".menu-item");
            menuItems.forEach(item => {
                const img = item.querySelector("img.icon");
                const lightSrc = item.getAttribute("data-light");
                const thickSrc = item.getAttribute("data-thick");
                if (!img || !lightSrc || !thickSrc) return;
                if (item.classList.contains("active")) {
                    img.src = thickSrc;
                } else {
                    img.src = lightSrc;
                }
                item.addEventListener("mouseenter", function() {
                    img.src = thickSrc;
                });
                item.addEventListener("mouseleave", function() {
                    if (item.classList.contains("active")) {
                        img.src = thickSrc;
                    } else {
                        img.src = lightSrc;
                    }
                });
            });
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>

</html>