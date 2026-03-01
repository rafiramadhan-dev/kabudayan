<?php
session_start();
include "../database/koneksi.php";

if (isset($_SESSION['id'])) {
    header('Location: ../admin/dashboard.php?id=' . $_SESSION['id']);
    exit();
}

// Error Message
$error = "";

// Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email salah!";
    } else {
        $stmtLogin = $koneksi->prepare("SELECT * FROM admin WHERE email = ? AND password = ?");
        $stmtLogin->bind_param("ss", $email, $password);
        $stmtLogin->execute();
        $resultLogin = $stmtLogin->get_result();

        if ($dataLogin = $resultLogin->fetch_assoc()) {
            $_SESSION['id'] = $dataLogin['id'];
            header("location: ../admin/dashboard.php?id=" . $dataLogin['id']);
            exit();
        } else {
            $error = "Email atau password salah!";
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
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Login</title>
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
        .login-container {
            background-color: var(--light);
            border-radius: 24px;
            padding: 1rem;
            box-shadow: 0 4px 30px 0 rgba(0, 0, 0, 10%);
            display: grid;
            grid-template-columns: 1fr 2fr;
            width: 100%;
            height: 100%;
            overflow: hidden;
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

        /* Login Section */
        .login-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-section form {
            color: var(--dark);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            width: 100%;
            max-width: 375px;
        }

        /* Head */
        .head {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: .5rem;
            flex-direction: column;
        }

        /* Logo */
        .logo {
            background-color: var(--red);
            border-radius: .5rem;
            color: var(--light);
            padding: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Text */
        .login-title {
            font-weight: 600;
            letter-spacing: -1.5px;
            text-align: center;
        }

        .login-subtitle {
            text-align: center;
            font-size: 1.1rem;
            letter-spacing: -.25px;
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

        /* .input-icon-right {
            position: absolute;
            right: 18px;
            top: 70%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            pointer-events: none;
            z-index: 2;
        }

        .form-group input.form-input {
            padding-right: 45px;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 18px;
        } */

        /* Foot */
        .foot {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: .5rem;
            flex-direction: column;
        }

        /* Login Button */
        .login-button {
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

        .login-button:hover {
            background: var(--red-dark);
        }

        /* Lupa Password */
        .forgot-password {
            align-self: flex-end;
        }

        .forgot-password a {
            color: var(--red);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline 2px solid var(--red);
            outline-offset: 5px;
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
    <!-- Login Container -->
    <div class="login-container">

        <!-- Ilustrasi -->
        <div class="illustration-section">
            <img src="../assets/svg/login.svg" alt="Login Illustration">
        </div>

        <!-- Login Form -->
        <div class="login-section">
            <form method="POST" action="">

                <div class="head">
                    <!-- Logo -->
                    <div class="logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 512 512">
                            <rect width="512" height="512" fill="none" />
                            <path fill="currentColor" d="M247 23.82v18.71c-50.7 3.94-87.9 40.63-93.2 77.67h204.5C353 83.16 315.7 46.46 265 42.53V23.82zM153.1 138.2v16.3c3.2 1.7 5.9 4.2 7.7 6.8c3.3 4.9 5 10.5 6.1 16.1c2.1 11.4 2.2 20.5 2.2 31.8v71H183v-78l.8-18c2.6-14.8 11.6-26.7 23.2-34.5c8.5-5.7 18.3-9.4 28.6-11.5zm123.3 0c10.3 2.1 20.1 5.8 28.6 11.5c11.6 7.8 20.6 19.7 23.2 34.5l.8 18v78h14v-71c0-10.7.3-22.5 2.2-31.8c1.1-5.6 2.8-11.2 6.1-16.1c1.8-2.6 4.5-5.1 7.7-6.8v-16.3zm-20.4 16c-14.5 0-28.9 3.8-39 10.5c-7.6 5-12.8 11.2-14.9 19.5h107.8c-2.1-8.3-7.3-14.5-14.9-19.5c-10.1-6.7-24.5-10.5-39-10.5m-111.3 16.1c-11.9 1.7-26.8 8.9-38 17.5c-5.3 4.1-9.79 8.5-12.9 12.4h57.1c-.1-6.5-.5-13.4-1.6-19.2c-1.1-3.6-1.7-8.4-4.6-10.7m222.7 0c-2.6 2.3-4 7.7-4.6 10.7c-1.1 5.8-1.5 12.7-1.6 19.2h57c-3.1-3.9-7.5-8.3-12.9-12.4c-11.2-8.6-26-15.8-37.9-17.5M201 202.2v78h9c.8-.7 1.6-1.4 2.4-2q4.5-3.6 9.6-6.3v-34.7c0-8 6-12 12-12s12 4 12 12v27.6c3.2-.4 6.5-.6 10-.6s6.8.2 10 .6v-27.6c0-8 6-12 12-12s12 4 12 12v34.7q5.1 2.7 9.6 6.3c.8.6 1.6 1.3 2.4 2h9v-78zm-112 16v62h62.1v-62zm272 0v62h62v-62zm-237 7c6 0 12 4 12 12v32h-24v-32c0-8 6-12 12-12m264 0c6 0 12 4 12 12v32h-24v-32c0-8 6-12 12-12m-132 57c-14.5 0-24 3.3-32.4 10s-15.8 17.6-23.5 33l-2.5 5H137v30h238v-30h-60.7l-2.5-5c-7.7-15.4-15.1-26.3-23.5-33s-17.8-10-32.3-10m-176 16c-13 0-22.25 6.2-28.97 14.6c-3.88 4.9-6.49 10.5-8.12 15.4H119v-16h67.6c2.7-5 5.4-9.7 8.2-14zm237.1 0c2.8 4.3 5.5 9 8.2 14H393v16h76.1c-1.6-4.9-4.2-10.5-8.1-15.4c-6.7-8.4-16-14.6-29-14.6zM41 346.2v46h31.89c1.36-3.2 3.34-6.1 5.56-8.6c4.13-4.8 9.31-8.8 14.92-12.1c8.23-4.9 17.13-8.7 25.63-10.4v-14.9zm352 0v14.9c8.5 1.7 17.4 5.5 25.6 10.4c5.6 3.3 10.8 7.3 15 12.1c2.2 2.5 4.2 5.4 5.5 8.6H471v-46zm-265 32c-5 0-16.6 3.4-25.4 8.7c-2.74 1.7-5.11 3.5-7.2 5.3h321.2c-2.1-1.8-4.5-3.6-7.2-5.3c-8.8-5.3-20.4-8.7-25.4-8.7zm-89.51 32l-10 30H87v-30H71zm66.51 0v78h94.1c.7-28.4 4.6-50.6 12.8-67c2-4 4.4-7.7 7.1-11zm151 0c-13 0-21 5.2-27.9 19c-6.3 12.5-10 32.5-10.8 59h77.5c-.6-26.7-3.4-47-9.1-59.2c-6.3-13.7-13.8-18.8-29.7-18.8m39.1 0c2.7 3.3 5 7.1 6.9 11.2c7.7 16.7 10.5 38.7 10.9 66.8H407v-78zm129.9 0v30h58.5l-10-30H441zm-293 11c6 0 12 4 12 12v32h-24v-32c0-8 6-12 12-12m40 0c6 0 12 4 12 12v32h-24v-32c0-8 6-12 12-12m168 0c6 0 12 4 12 12v32h-24v-32c0-8 6-12 12-12m40 0c6 0 12 4 12 12v32h-24v-32c0-8 6-12 12-12m-355 37v30h62v-30zm400 0v30h62v-30z" />
                        </svg>
                    </div>

                    <!-- Text -->
                    <div class="text">
                        <h1 class="login-title">Selamat datang kembali!</h1>
                        <p class="login-subtitle">Silahkan masuk untuk mengakses akun Anda.</p>
                    </div>
                </div>

                <!-- Input Box Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="text" id="email" name="email" class="form-input" placeholder="Email" required>
                    <!-- <i class="fa-regular fa-envelope input-icon-right"></i> -->
                </div>

                <!-- Input Box Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Password" required>
                    <!-- <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fa-regular fa-eye" id="toggleIcon"></i>
                    </button> -->
                </div>

                <!-- Error Message -->
                <?php if (!empty($error)) : ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="foot">
                    <!-- Button Login -->
                    <button type="submit" name="login" class="login-button">Log in</button>

                    <!-- Forgot Password -->
                    <div class="forgot-password">
                        <a href="../auth/lupaPassword.php?step=email">Lupa Password?</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');


            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script> -->

</body>

</html>