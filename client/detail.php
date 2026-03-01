<?php
session_start();
require_once '../database/koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM data_budaya WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit();
}

$nama = htmlspecialchars($data['nama']);
$kategori = htmlspecialchars($data['kategori']);
$deskripsi = nl2br(htmlspecialchars($data['deskripsi']));
$gambar = htmlspecialchars($data['gambar']);
$video = isset($data['video_url']) ? htmlspecialchars($data['video_url']) : '';
$created_at = date('d F Y', strtotime($data['created_at']));

$is_portrait = false;
if (!empty($gambar)) {
    $gambar_path = "../assets/img/budaya/" . $gambar;
    
    if (file_exists($gambar_path)) {        $image_info = getimagesize($gambar_path);
        if ($image_info !== false) {
            $width = $image_info[0];
            $height = $image_info[1];
            
            if ($height > $width) {
                $is_portrait = true;
            }
        }
    }
} else {
    $gambar_path = "";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebudayaan - <?php echo $nama; ?></title>
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
            line-height: 1.8;
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

        .detail-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 32px;
        }

        .detail-meta {
            text-align: center;
            margin-bottom: 24px;
        }

        .detail-category {
            display: inline-block;
            background: #ffe5e5;
            color: #cb1e22;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-date {
            color: #666;
            font-size: 14px;
            font-family: "Satoshi";
        }

        .detail-title {
            text-align: center;
            font-size: 36px;
            color: #1a1a1a;
            font-weight: 600;
            margin-bottom: 32px;
            font-family: "lora";
            line-height: 1.3;
        }

        .detail-image-landscape {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .detail-image-portrait {
            max-width: 380px;
            max-height: 550px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px;
            margin: 0 auto 20px;
            display: block;
        }

        .video-link {
            margin-bottom: 40px;
        }

        .video-link span {
            color: #666;
            font-size: 15px;
            font-family: "Satoshi";
        }

        .video-link a {
            color: #2962FF;
            word-break: break-all;
            text-decoration: underline;
        }

        .video-link a:hover {
            color: #1e4ed8;
        }

        .detail-content {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 40px;
        }

        .detail-content p {
            margin-bottom: 16px;
        }

        .back-button {
            display: inline-block;
            background: #cb1e22;
            color: #fff;
            padding: 9px 23px;
            border-radius: 40px;
            text-decoration: none;
            font-family: "Satoshi";
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-button:hover {
            background: #a01619;
            transform: translateY(-2px);
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

        @media (max-width: 768px) {
            .header-nav {
                padding: 16px 20px;
            }

            .header-menu {
                gap: 20px;
            }

            .detail-container {
                margin: 40px auto;
                padding: 0 20px;
            }

            .detail-title {
                font-size: 28px;
            }

            .detail-image-landscape {
                max-height: 250px;
            }

            .detail-image-portrait {
                max-width: 100%;
                max-height: 450px;
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

    <div class="detail-container">
        <div class="detail-meta">
            <span class="detail-category"><?php echo $kategori; ?></span>
        </div>

        <h1 class="detail-title"><?php echo $nama; ?></h1>

        <?php if (!empty($gambar_path) && file_exists($gambar_path)): ?>
            <?php if ($is_portrait): ?>
                <img src="<?php echo $gambar_path; ?>" alt="<?php echo $nama; ?>" class="detail-image-portrait">
            <?php else: ?>
                <img src="<?php echo $gambar_path; ?>" alt="<?php echo $nama; ?>" class="detail-image-landscape">
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($video)): ?>
            <div class="video-link">
                <span>Cuplikan : </span>
                <a href="<?php echo $video; ?>" target="_blank"><?php echo $video; ?></a>
            </div>
        <?php endif; ?>

        <div class="detail-content">
            <?php echo $deskripsi; ?>
        </div>
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
