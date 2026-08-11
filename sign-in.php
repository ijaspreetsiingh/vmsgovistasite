<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Already logged in → go to admin
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — VMS Go Vista</title>
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/plugins/swiper.min.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/fonts/custom-font.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/plugins/magnific-popup.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/plugins/metismenu.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/vendor/bootstrap.min.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/vendor/animate.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/plugins/odometer.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/plugins/fontawesome.min.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/plugins/nice-select.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/style.css" as="style">
</head>
<body class="home-bg">
<!-- Preloader -->
<div class="preloader">
    <div class="loader">
        <?php for($i=1;$i<=20;$i++): ?><span style="--i:<?=$i?>;"></span><?php endfor; ?>
        <div class="loader-plane"></div>
    </div>
</div>

<div class="sign-in-area">
    <!-- Left image — same as original design -->
    <div class="image radius-10 overflow-hidden">
        <img src="<?= SITE_URL ?>/assets/images/contact/01.webp" width="1245" alt="">
    </div>

    <!-- Right form -->
    <div class="sign-in-form radius-10 overflow-hidden body-bg-one">
        <div class="logo-area">
            <a href="<?= SITE_URL ?>/index-three.php">
                <img src="<?= SITE_URL ?>/assets/images/logo/02.svg" alt="VMS Go Vista">
            </a>
        </div>
        <h4 class="title">Admin Sign In</h4>

        <?php if ($error): ?>
        <div style="background:rgba(248,81,73,.1);border:1px solid rgba(248,81,73,.3);color:#c0392b;border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:14px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?>
        </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="single-input-wrapper">
                <label for="femail">Email *</label>
                <input type="email" id="femail" name="email"
                       placeholder="Your email address"
                       value="<?= e($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>
            <div class="single-input-wrapper">
                <label for="fpass">Password *</label>
                <input type="password" id="fpass" name="password"
                       placeholder="Enter your password" required>
            </div>
            <div class="single-input-area d-flex align-items-center justify-content-between">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-btn">Forgot your password?</a>
            </div>
            <button type="submit" class="rts-btn btn-primary m-w-100 text-center justify-content-center with-arrow">
                Sign In <i class="fa-regular fa-arrow-up up-right"></i>
            </button>
        </form>

        <p style="text-align:center;margin-top:20px;font-size:13px;color:#999;">
            <a href="<?= SITE_URL ?>/index-three.php" style="color:#BA6827;">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to website
            </a>
        </p>
    </div>
</div>

<div id="anywhere-home"></div>
<div class="progress-wrap">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition:stroke-dashoffset 10ms linear;stroke-dasharray:307.919,307.919;stroke-dashoffset:307.919;"></path>
    </svg>
</div>

<script defer src="<?= SITE_URL ?>/assets/js/plugins/jquery.min.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/plugins/bootstrap.min.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/plugins/metismenu.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/vendor/waypoint.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/plugins/swiper.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/plugins/smoothscroll.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/vendor/wow.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
