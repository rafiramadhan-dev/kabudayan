<?php
session_start();
require_once '../database/koneksi.php';

$error = '';
$step = isset($_GET['step']) ? $_GET['step'] : 'email';

// Proses email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak sesuai!";
    } else {
        $stmtEmail = $koneksi->prepare('SELECT id FROM admin WHERE email = ?');
        $stmtEmail->bind_param('s', $email);
        $stmtEmail->execute();
        $resultEmail = $stmtEmail->get_result();

        if ($dataEmail = $resultEmail->fetch_assoc()) {
            $_SESSION['reset_password'] = $email;
            header("Location: lupaPassword.php?step=password");
            exit();
        } else {
            $error = 'Email tidak ditemukan!';
        }
    }
}

// Proses password 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new-password'])) {
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];

    if (strlen($newPassword) < 8) {
        $error = 'Password minimal 8 karakter!';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password tidak cocok!';
    } else {
        $stmtPassword = $koneksi->prepare('UPDATE admin SET password = ? WHERE email = ?');
        $stmtPassword->bind_param('ss', $newPassword, $_SESSION['reset_password']);
        $stmtPassword->execute();

        unset($_SESSION['reset_password']);
        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <style>
        :root {
            --dark: #212136;
            --light: #ffffff;
            --gray-dark: #9D9FA0;
            --gray: #DFE1E3;
            --gray-light: #F3F5F7;
            --red-dark: #9E171A;
            --red: #CB1E22;
            --red-light: #FEDBDB;
            --green: #09D25D;
            --green-light: #C6FFC6;
            --yellow: #FFB700;
            --yellow-light: #FFF4B2;
            --serif: "Lora";
            --sans-serif: "Satoshi";
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: var(--sans-serif);
        }

        body {
            background-color: var(--gray-light);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 4rem 4.5rem;
        }

        /* Container */
        .container {
            background-color: var(--light);
            border-radius: 24px;
            padding: 1rem;
            box-shadow: 0 4px 30px 0 rgba(0, 0, 0, 10%);
            display: grid;
            grid-template-columns: 2fr 1fr;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Form Section */
        .form-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .form-section form {
            color: var(--dark);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            width: 100%;
            max-width: 375px;
        }

        /* Ilustration */
        .illustration-section {
            background: var(--red);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .illustration-section img {
            width: 350px;
            height: 350px;
        }

        /* Text */
        .title {
            font-weight: 600;
            letter-spacing: -1.5px;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            font-size: 1.1rem;
            letter-spacing: -.25px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.813rem;
            font-weight: 500;
        }

        /* Input Box & Label */
        .form-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: .5rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 1rem;
        }

        .form-input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1px solid var(--gray);
            border-radius: .5rem;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: 3px solid var(--red);
            outline-offset: 2px;
        }

        /* Button */
        .submit-btn {
            width: 100%;
            padding: .75rem;
            background-color: var(--red);
            color: white;
            border: none;
            border-radius: .5rem;
            font-size: 1rem;
            font-weight: 550;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background: var(--red-dark);
        }

        /* Error Message */
        .error-message {
            background-color: var(--red-light);
            color: var(--red);
            padding: .75rem 1rem;
            border-radius: 8px;
            text-align: left;
            width: 100%;
            font-size: .9rem;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <?php if ($step === 'email') : ?>

        <!-- Container -->
        <div class="container">
            <div class="form-section">
                <form method="POST">
                    <!-- Text -->
                    <div class="text">
                        <h1 class="title">Lupa password Anda?</h1>
                        <p class="subtitle">Masukkan email untuk mereset password Anda.</p>
                    </div>

                    <!-- Input Box -->
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input class="form-input" type="text" id="email" name="email" placeholder="Email" required>
                    </div>

                    <!-- Error Message -->
                    <?php if (!empty($error)) : ?>
                        <div class="error-message">
                            <?= $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Button -->
                    <button type="submit" class="submit-btn">Kirim</button>
                </form>
            </div>

            <!-- Ilustrasi -->
            <div class="illustration-section">
                <img src="../assets/svg/lupa_password.svg" alt="">
            </div>
        </div>

    <?php elseif ($step === 'password') : ?>

        <!-- Container -->
        <div class="container">
            <div class="form-section">
                <form method="POST">
                    <!-- Text -->
                    <div class="text">
                        <h1 class="title">Ubah password Anda!</h1>
                        <p class="subtitle">Silahkan buat password baru untuk akun Anda.</p>
                    </div>

                    <!-- Input Box -->
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input class="form-input" type="password" name="new-password" placeholder="Password Baru" required>
                        <label class="form-label">Konfirmasi Password</label>
                        <input class="form-input" type="password" name="confirm-password" placeholder="Konfirmasi Password" required>
                    </div>

                    <!-- Error Message -->
                    <?php if (!empty($error)) : ?>
                        <div class="error-message">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <!-- Button -->
                    <button type="submit" class="submit-btn">Simpan</button>
                </form>
            </div>

            <!-- Ilustrasi -->
            <div class="illustration-section">
                <img src="../assets/svg/reset_password.svg" alt="">
            </div>
        </div>

    <?php endif ?>
</body>

</html>