<?php
session_start();
require_once '../database/koneksi.php';

$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'Tari Klasik';

$query = "SELECT * FROM data_budaya WHERE kategori = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $kategori);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebudayaan - Kesenian</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Lora", serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #ffffff;
        }

        header {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-radius: 0 0 10px 10px;
        }

        .header-nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 20px;
            font-weight: bold;
            color: #000;
            font-family: "Lora", serif;
            text-decoration: none;
        }

        .header-logo img {
            width: 33px;
            height: 33px;
            object-fit: contain;
            filter: brightness(0) saturate(100%) invert(13%) sepia(96%) saturate(5194%) hue-rotate(352deg) brightness(91%) contrast(101%);
        }

        .header-logo span {
            margin-left: 3px;
        }

        .header-menu {
            list-style: none;
            display: flex;
            gap: 32px;
        }

        .header-menu li a {
            text-decoration: none;
            color: #1a1a1a;
            font-weight: 400;
            font-size: 16px;
            font-family: "Satoshi";
            transition: color 0.3s;
        }

        .header-menu li a:hover {
            color: #cb1e22;
        }

        .hero {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 32px;
        }

        .hero img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            display: block;
            border-radius: 10px;
        }

        .container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            padding: 50px 80px;
            gap: 60px;
        }

        .sidebar {
            width: 200px;
            flex-shrink: 0;
            animation: slideInLeft 0.6s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .sidebar h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 12px;
            animation: fadeInUp 0.5s ease-out backwards;
        }

        .sidebar ul li:nth-child(1) {
            animation-delay: 0.1s;
        }

        .sidebar ul li:nth-child(2) {
            animation-delay: 0.2s;
        }

        .sidebar ul li:nth-child(3) {
            animation-delay: 0.3s;
        }

        .sidebar ul li:nth-child(4) {
            animation-delay: 0.4s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #666;
            font-size: 15px;
            padding: 10px 14px;
            display: block;
            border-radius: 5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar ul li a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #cb1e22;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: #cb1e22;
            background-color: #fff5f5;
            transform: translateX(5px);
            box-shadow: 0 1px 4px rgba(203, 30, 34, 0.1);
        }

        .sidebar ul li a:hover::before {
            transform: scaleY(1);
        }

        .sidebar ul li a.active {
            color: #cb1e22;
            font-size: 16px;
            font-weight: 410;
            background-color: #ffe5e5;
            animation: pulseActive 2s ease-in-out infinite;
        }

        @keyframes pulseActive {

            0%,
            100% {
                box-shadow: 0 1px 4px rgba(203, 30, 34, 0.2);
            }

            50% {
                box-shadow: 0 2px 6px rgba(203, 30, 34, 0.3);
            }
        }

        .content {
            flex: 1;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e0e0e0;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .card-content {
            padding: 24px;
        }

        .card-date {
            color: #cb1e22;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-title {
            font-size: 18px;
            color: #1a1a1a;
            font-weight: 500;
            line-height: 1.4;
        }

        .no-data-message {
            grid-column: 1/-1;
            text-align: center;
            color: #666;
            padding: 60px 20px;
            font-size: 16px;
        }

        .site-footer {
            background: #f8f8f8;
            border-top: 1px solid #e0e0e0;
            margin-top: 64px;
            padding: 24px 0 12px;
        }

        .site-footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .site-footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .site-footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .site-footer-brand img {
            width: 33px;
            height: 33px;
            object-fit: contain;
            filter: brightness(0) saturate(100%) invert(13%) sepia(96%) saturate(5194%) hue-rotate(352deg) brightness(91%) contrast(101%);
        }

        .site-footer-brand span {
            font-size: 20px;
            font-weight: 700;
            margin-left: 3px;
            line-height: 1;
        }

        .site-footer-links {
            display: flex;
            gap: 60px;
        }

        .site-footer-column h3 {
            font-size: 14px;
            font-family: "Satoshi";
            font-weight: 700;
            margin-bottom: 8px;
        }

        .site-footer-column ul {
            list-style: none;
        }

        .site-footer-column ul li {
            margin-bottom: 6px;
            font-family: "Satoshi";
        }

        .site-footer-column a {
            color: #5a5a5a;
            text-decoration: none;
            font-size: 13px;
        }

        .site-footer-column a:hover {
            color: #cb1e22;
        }

        .site-footer-bottom {
            padding-top: 12px;
            border-top: 1px solid #e0e0e0;
            color: #7a7a7a;
            font-size: 12px;
        }

        @media (max-width: 1024px) {
            .card-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header-nav {
                padding: 16px 20px;
            }

            .header-menu {
                gap: 20px;
            }

            .hero {
                margin: 20px auto;
                padding: 0 20px;
            }

            .hero img {
                height: 300px;
            }

            .container {
                flex-direction: column;
                padding: 35px 20px;
            }

            .sidebar {
                width: 100%;
            }

            .card-grid {
                grid-template-columns: 1fr;
            }

            .site-footer-content {
                flex-direction: column;
                gap: 24px;
            }

            .site-footer-links {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav class="header-nav">
            <a href="../user/dashboard.php" class="header-logo">
                <img src="../assets/svg/logo.svg" alt="Logo Kebudayaan">
                <span>Kebudayaan</span>
            </a>
            <ul class="header-menu">
                <li><a href="../user/tradisi.php">Tradisi</a></li>
                <li><a href="../user/kuliner.php">Kuliner</a></li>
                <li><a href="../user/kesenian.php">Kesenian</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <img src="../assets/img/kesenian.png" alt="Kesenian Jawa Tengah">
    </section>

    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="kesenian.php?kategori=Tari Klasik" class="<?php echo ($kategori == 'Tari Klasik') ? 'active' : ''; ?>">Tari Klasik</a></li>
                <li><a href="kesenian.php?kategori=Lagu Klasik" class="<?php echo ($kategori == 'Lagu Klasik') ? 'active' : ''; ?>">Lagu Klasik</a></li>
                <li><a href="kesenian.php?kategori=Alat Musik" class="<?php echo ($kategori == 'Alat Musik') ? 'active' : ''; ?>">Alat Musik</a></li>
                <li><a href="kesenian.php?kategori=Dongeng" class="<?php echo ($kategori == 'Dongeng') ? 'active' : ''; ?>">Dongeng</a></li>
            </ul>
        </aside>

        <main class="content">
            <div class="card-grid">
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $nama = htmlspecialchars($row['nama']);
                        $created_at = date('m-Y', strtotime($row['created_at']));
                        $gambar = htmlspecialchars($row['gambar']);

                        // PATH DISESUAIKAN: assets/img/budaya/
                        if (!empty($gambar)) {
                            $gambar_path = "../assets/img/budaya/" . $gambar;
                        } else {
                            $gambar_path = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23f0f0f0' width='400' height='300'/%3E%3Ctext fill='%23999' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3ENo Image%3C/text%3E%3C/svg%3E";
                        }
                ?>
                        <a href="detail.php?id=<?php echo $id; ?>" class="card">
                            <img src="<?php echo $gambar_path; ?>"
                                alt="<?php echo $nama; ?>"
                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27300%27%3E%3Crect fill=%27%23f0f0f0%27 width=%27400%27 height=%27300%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27%3ENo Image%3C/text%3E%3C/svg%3E';">
                            <div class="card-content">
                                <p class="card-date">DIPOSTING <?php echo $created_at; ?></p>
                                <h3 class="card-title"><?php echo $nama; ?></h3>
                            </div>
                        </a>
                <?php
                    }
                } else {
                    echo '<div class="no-data-message">Belum ada data untuk kategori ' . htmlspecialchars($kategori) . '. Silakan tambahkan data melalui dashboard admin.</div>';
                }
                ?>
            </div>
        </main>
    </div>

    <footer class="site-footer">
        <div class="site-footer-container">
            <div class="site-footer-content">
                <div class="site-footer-brand">
                    <a href="../user/dashboard.php" class="header-logo">
                        <img src="../assets/svg/logo.svg" alt="Logo Kebudayaan">
                        <span>Kebudayaan</span>
                    </a>
                </div>
                <nav class="site-footer-links"></nav>
            </div>
            <div class="site-footer-bottom">
                © 2025 Kebudayaan. Seluruh hak cipta dilindungi
            </div>
        </div>
    </footer>

</body>

</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>