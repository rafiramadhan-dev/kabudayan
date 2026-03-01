<?php
session_start();
require_once '../database/koneksi.php';

$query = "
    SELECT t1.*
    FROM data_budaya t1
    INNER JOIN (
        SELECT kategori, MAX(created_at) AS max_created
        FROM data_budaya
        GROUP BY kategori
        LIMIT 9
    ) t2 ON t1.kategori = t2.kategori AND t1.created_at = t2.max_created
    ORDER BY t1.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebudayaan - Eksplorasi Budaya Dari Jawa Tengah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
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
            margin: 48px auto;
            padding: 0 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 48px;
            color: #cb1e22;
            margin-bottom: 24px;
            font-family: "Lora", serif;
            font-weight: 600;
            line-height: 1.2;
        }

        .hero-content p {
            color: #4a4a4a;
            margin-bottom: 32px;
            line-height: 1.8;
            font-family: "Satoshi";
            font-size: 16px;
        }

        .btn-primary {
            background: #cb1e22;
            color: #fff;
            padding: 14px 35px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            font-family: "Satoshi";
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(203, 30, 34, 0.3);
        }

        .btn-primary:hover {
            background: #a01619;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(203, 30, 34, 0.4);
        }

        .hero-images img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .must-visit {
            background: #f8f8f8;
            padding: 64px 32px;
        }

        .must-visit-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: center;
        }

        .visit-images img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .visit-content h2 {
            font-size: 35px;
            color: #cb1e22;
            margin-bottom: 24px;
            font-weight: 700;
            line-height: 1.2;
        }

        .visit-content p {
            color: #5a5a5a;
            line-height: 1.8;
            margin-bottom: 24px;
            font-family: "Satoshi";
            font-size: 18px;
        }

        .content-section {
            max-width: 1200px;
            margin: 64px auto;
            padding: 0 32px;
        }

        .section-title {
            text-align: center;
            font-size: 30px;
            color: #1a1a1a;
            margin-bottom: 48px;
            font-weight: 400;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            font-family: "Satoshi";
            gap: 32px;
        }

        .content-card {
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

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .content-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .card-content {
            padding: 24px;
        }

        .card-category {
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

        .no-data-message {
            grid-column: 1/-1;
            text-align: center;
            color: #666;
            padding: 40px 20px;
            font-size: 16px;
        }

        @media (max-width: 768px) {

            .hero,
            .must-visit-container {
                grid-template-columns: 1fr;
            }

            .hero-content h1,
            .visit-content h2 {
                font-size: 32px;
            }

            .hero-images img,
            .visit-images img {
                height: 300px;
            }

            .content-grid {
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
        <div class="hero-content">
            <h1>Eksplorasi Budaya Dari Jawa Tengah!</h1>
            <p>Kabudayan menyajikan konten-konten budaya terkait Jawa Tengah. Dimulai dari tradisi, kuliner, hingga kesenian khas daerah.</p>
            <a href="../user/tradisi.php" class="btn-primary">Jelajahi</a>
        </div>
        <div class="hero-images">
            <img src="../assets/svg/user3.png" alt="Budaya Jawa Tengah">
        </div>
    </section>

    <section class="must-visit">
        <div class="must-visit-container">
            <div class="visit-images">
                <img src="../assets/svg/user1.svg" alt="Budaya Tradisional">
            </div>
            <div class="visit-content">
                <h2>Menelusuri Tiga Wajah Budaya Jawa Tengah</h2>
                <p>Tiga wajah budaya Jawa Tengah terdiri dari tradisi yang diwariskan turun-temurun, kesenian yang menggambarkan nilai dan keindahan, serta kuliner yang mencerminkan kekayaan cita rasa daerah.</p>
            </div>
        </div>
    </section>

    <section class="content-section" id="explore">
        <h2 class="section-title">Ragam Konten Budaya</h2>
        <div class="content-grid">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $id = $row['id'];
                    $nama = htmlspecialchars($row['nama']);
                    $kategori = htmlspecialchars($row['kategori']);
                    $gambar = htmlspecialchars($row['gambar']);

                    if (!empty($gambar)) {
                        $gambar_path = "../assets/img/budaya/" . $gambar;
                    } else {
                        $gambar_path = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23f0f0f0' width='400' height='300'/%3E%3Ctext fill='%23999' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3ENo Image%3C/text%3E%3C/svg%3E";
                    }
            ?>
                    <a href="detail.php?id=<?php echo $id; ?>" class="content-card">
                        <img src="<?php echo $gambar_path; ?>"
                            alt="<?php echo $nama; ?>"
                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27400%27 height=%27300%27%3E%3Crect fill=%27%23f0f0f0%27 width=%27400%27 height=%27300%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27%3ENo Image%3C/text%3E%3C/svg%3E';">
                        <div class="card-content">
                            <div class="card-category"><?php echo $kategori; ?></div>
                            <h3 class="card-title"><?php echo $nama; ?></h3>
                        </div>
                    </a>
            <?php
                }
            } else {
                echo '<div class="no-data-message">Belum ada data budaya. Silakan tambahkan data melalui dashboard admin.</div>';
            }
            ?>
        </div>
    </section>

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
mysqli_close($conn);
?>