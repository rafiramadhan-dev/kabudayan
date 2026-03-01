<?php
session_start();
require_once '../database/koneksi.php';



if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_admin_id'])) {
    $_SESSION['error_message'] = 'Akses tidak valid. Silakan mulai dari halaman lupa password.';
    header('Location: ../auth/lupaPassword.php');
    exit;
}



$reset_email = $_SESSION['reset_email'];
$reset_admin_id = $_SESSION['reset_admin_id'];
$reset_username = $_SESSION['reset_username'] ?? 'Admin';



$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['error_message'], $_SESSION['success_message']);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';



    if (empty($newPassword) || empty($confirmPassword)) {
        $error_message = 'Semua field wajib diisi';
    } elseif ($newPassword !== $confirmPassword) {
        $error_message = 'Password tidak cocok';
    } elseif (strlen($newPassword) < 8) {
        $error_message = 'Password harus minimal 8 karakter';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);



        $sql = "UPDATE admin SET password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $hashedPassword, $reset_admin_id);

        if (mysqli_stmt_execute($stmt)) {
            unset($_SESSION['reset_email'], $_SESSION['reset_admin_id'], $_SESSION['reset_username']);
            
            $_SESSION['success_message'] = 'Password berhasil direset! Silakan login dengan password baru.';
            header('Location: ../auth/login.php');
            exit;
        } else {
            $error_message = 'Gagal mengupdate password. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <title>Reset Password</title>
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }



        .container {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            min-height: 500px;
        }



        .form-section {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }



        .illustration-section {
            flex: 1;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }



        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }



        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.813rem;
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



        .form-group {
            margin-bottom: 24px;
        }



        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }



        .input-container {
            position: relative;
        }



        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 45px 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }



        input[type="password"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #dc2626;
            background: white;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }



        input[type="password"]::placeholder,
        input[type="text"]::placeholder {
            color: #9ca3af;
            font-size: 0.875rem;
        }



        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 16px;
            padding: 5px;
            border-radius: 4px;
            transition: color 0.3s ease;
            z-index: 10;
        }



        .toggle-password:hover {
            color: #6b7280;
        }



        .toggle-password:focus {
            outline: none;
        }



        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.938rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }



        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
        }



        .submit-btn:active {
            transform: translateY(0);
        }



        .submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }



        .password-requirements {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 6px;
            line-height: 1.4;
        }



        .password-length {
            margin-top: 8px;
            font-size: 0.813rem;
        }



        .length-valid {
            color: #10b981;
        }



        .length-invalid {
            color: #dc2626;
        }



        .back-link {
            margin-top: 16px;
            text-align: center;
        }



        .back-link a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
        }



        .back-link a:hover {
            text-decoration: underline;
        }

        .decorative-svg {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 1;
            pointer-events: none;
            z-index: 1;
        }


        .decorative-svg img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(1.2) contrast(1.1);
        }



        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 500px;
            }



            .form-section {
                padding: 40px 30px;
            }



            .illustration-section {
                min-height: 300px;
            }



            .illustration {
                transform: scale(0.8);
            }



            h1 {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 0.813rem;
            }
        }
    </style>
</head>



<body>
    <div class="container">
        <div class="form-section">
            <h1>Reset Password</h1>
            <p class="subtitle">Halo <?= htmlspecialchars($reset_username) ?>, silahkan buat password baru untuk akun Anda.</p>



            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>



            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>



            <form method="POST">
                <div class="form-group">
                    <label for="newPassword">Password Baru</label>
                    <div class="input-container">
                        <input type="password" id="newPassword" name="newPassword" placeholder="Password Baru" required minlength="8">
                        <button type="button" class="toggle-password" onclick="togglePassword('newPassword')">
                            <i class="fa-regular fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <div class="password-length" id="passwordLength"></div>
                    <div class="password-requirements">
                        • Password harus minimal 8 karakter
                    </div>
                </div>



                <div class="form-group">
                    <label for="confirmPassword">Konfirmasi Password</label>
                    <div class="input-container">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi Password" required minlength="8">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                            <i class="fa-regular fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>



                <button type="submit" class="submit-btn">Simpan</button>
            </form>



            <div class="back-link">
                <a href="../auth/lupaPassword.php">Kembali</a>
            </div>
        </div>



        <div class="illustration-section">
            <div class="illustration">
                <div class="decorative-svg">
                    <img src="../assets/img/reset.svg" alt="">
                </div>
            </div>
        </div>
    </div>



    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleIcon = fieldId === 'newPassword' ?
                document.getElementById('toggleIcon1') :
                document.getElementById('toggleIcon2');



            if (field.type === 'password') {
                field.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }



        function checkPasswordLength(password) {
            const lengthDiv = document.getElementById('passwordLength');

            if (password.length === 0) {
                lengthDiv.textContent = '';
                lengthDiv.className = 'password-length';
            } else if (password.length < 8) {
                lengthDiv.textContent = `${password.length}/8 karakter (kurang ${8 - password.length})`;
                lengthDiv.className = 'password-length length-invalid';
            } else {
                lengthDiv.textContent = `${password.length} karakter ✓`;
                lengthDiv.className = 'password-length length-valid';
            }
        }



        document.getElementById('newPassword').addEventListener('input', function(e) {
            checkPasswordLength(e.target.value);
        });
    </script>
</body>



</html>