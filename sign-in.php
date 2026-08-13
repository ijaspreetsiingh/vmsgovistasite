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
<title>Admin Sign In — VMS Go Vista</title>
<link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/newlogo.png">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/fonts/custom-font.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/vendor/bootstrap.min.css" as="style">
<link rel="stylesheet preload" href="<?= SITE_URL ?>/assets/css/plugins/fontawesome.min.css" as="style">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
<style>
    :root {
        --vms-navy: #003A59;
        --vms-navy-deep: #01293f;
        --vms-gold: #C9A567;
        --vms-gold-soft: #e0c38c;
        --vms-cream: #f7f4ee;
        --vms-white: #ffffff;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        min-height: 100vh;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--vms-navy-deep);
        color: var(--vms-navy);
        overflow-x: hidden;
    }

    .vms-login-container {
        display: flex;
        min-height: 100vh;
        width: 100%;
        background: var(--vms-navy-deep);
    }

    .vms-visual-side {
        position: relative;
        flex: 1 1 50%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 48px 56px;
        overflow: hidden;
        background:
            linear-gradient(165deg, rgba(0,58,89,.75) 0%, rgba(0,58,89,.40) 45%, rgba(201,165,103,.25) 100%),
            url('<?= SITE_URL ?>/assets/images/banner/bg-10.webp') center / cover no-repeat;
    }

    .vms-visual-side::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(1,41,63,0) 55%, rgba(1,41,63,.88) 100%);
        pointer-events: none;
    }

    .vms-visual-top, .vms-visual-bottom { position: relative; z-index: 2; }

    .vms-brand-logo {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
    }

    .vms-brand-logo img {
        height: 70px;
        width: auto;
        object-fit: contain;
        filter: drop-shadow(0 8px 24px rgba(0,0,0,.35));
    }

    .vms-brand-logo .brand-text {
        font-size: 22px;
        font-weight: 600;
        color: #fff;
        letter-spacing: -0.3px;
        font-family: 'Playfair Display', serif;
    }

    .vms-brand-logo .brand-text small {
        display: block;
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 3px;
        color: var(--vms-gold-soft);
        text-transform: uppercase;
        font-family: 'Inter', sans-serif;
        margin-top: 2px;
    }

    .vms-welcome-text {
        max-width: 480px;
        margin: 0 0 32px;
    }

    .vms-welcome-text h2 {
        font-family: 'Playfair Display', serif;
        font-weight: 600;
        font-size: clamp(28px, 3vw, 42px);
        line-height: 1.2;
        color: #fff;
        margin: 0 0 16px;
    }

    .vms-welcome-text h2 em {
        font-style: italic;
        color: var(--vms-gold-soft);
    }

    .vms-welcome-text p {
        color: rgba(255,255,255,.85);
        font-size: 15px;
        line-height: 1.7;
        margin: 0;
        font-weight: 400;
    }

    .vms-stats-row {
        display: flex;
        gap: 36px;
    }

    .vms-stat-item b {
        display: block;
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        line-height: 1;
    }

    .vms-stat-item b span { color: var(--vms-gold-soft); }
    .vms-stat-item small {
        font-size: 11px;
        color: rgba(255,255,255,.7);
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 6px;
        display: block;
    }

    .vms-form-side {
        flex: 0 1 50%;
        min-width: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 56px;
        background: var(--vms-cream);
        position: relative;
        overflow-y: auto;
    }

    .vms-form-side::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(201,165,103,.14), transparent 65%);
        pointer-events: none;
    }

    .vms-form-side::after {
        content: '';
        position: absolute;
        bottom: -120px;
        left: -120px;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0,58,89,.08), transparent 65%);
        pointer-events: none;
    }

    .vms-login-card {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 420px;
        background: rgba(255,255,255,.94);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,.9);
        border-radius: 20px;
        padding: 40px 40px 36px;
        box-shadow:
            0 28px 64px -28px rgba(0,58,89,.32),
            0 8px 20px -10px rgba(0,58,89,.10),
            inset 0 1px 0 rgba(255,255,255,.8);
    }

    .vms-card-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .vms-card-header img {
        height: 56px;
        width: auto;
        object-fit: contain;
        margin-bottom: 8px;
    }

    .vms-card-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 700;
        color: var(--vms-navy);
        margin: 0;
        letter-spacing: -0.2px;
    }

    .vms-card-header p {
        color: #7a8894;
        font-size: 13px;
        margin: 6px 0 0;
    }

    .vms-divider {
        width: 48px;
        height: 3px;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--vms-gold), var(--vms-gold-soft));
        margin: 16px auto 24px;
    }

    .vms-error-alert {
        background: #fdeceb;
        border: 1px solid #f5c6c2;
        color: #c0392b;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: vmsShake .4s ease;
    }

    @keyframes vmsShake {
        0%,100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }

    .vms-form-group { margin-bottom: 18px; }

    .vms-form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--vms-navy);
        margin-bottom: 7px;
        letter-spacing: .1px;
    }

    .vms-input-group {
        position: relative;
    }

    .vms-input-group i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9fb0bc;
        font-size: 14px;
        pointer-events: none;
        transition: color .25s ease;
    }

    .vms-input-group input {
        width: 100%;
        height: 48px;
        border: 1.5px solid #e2e8ee;
        background: #fff;
        border-radius: 10px;
        padding: 0 14px 0 40px;
        font-size: 14px;
        color: var(--vms-navy);
        outline: none;
        transition: border-color .25s ease, box-shadow .25s ease;
        font-family: 'Inter', sans-serif;
    }

    .vms-input-group input::placeholder { color: #a6b4bf; font-weight: 400; }

    .vms-input-group input:focus {
        border-color: var(--vms-gold);
        box-shadow: 0 0 0 3px rgba(201,165,103,.15);
    }

    .vms-input-group input:focus + i,
    .vms-input-group:focus-within > i { color: var(--vms-gold); }

    .vms-form-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 2px 0 22px;
    }

    .vms-checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 12.5px;
        color: #5c6b76;
        font-weight: 500;
        user-select: none;
    }

    .vms-checkbox-label input {
        appearance: none;
        -webkit-appearance: none;
        width: 16px;
        height: 16px;
        border: 1.5px solid #cfd8df;
        border-radius: 4px;
        cursor: pointer;
        position: relative;
        transition: all .2s ease;
        margin: 0;
    }

    .vms-checkbox-label input:checked {
        background: var(--vms-navy);
        border-color: var(--vms-navy);
    }

    .vms-checkbox-label input:checked::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 9px;
        color: #fff;
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vms-forgot-link {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--vms-gold);
        text-decoration: none;
        transition: color .2s ease;
    }

    .vms-forgot-link:hover { color: var(--vms-navy); }

    .vms-submit-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 50px;
        padding: 0 8px 0 20px;
        border: none;
        border-radius: 999px;
        background: var(--vms-navy);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: .1px;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: background .3s ease, transform .25s ease, box-shadow .3s ease;
        box-shadow: 0 12px 28px -10px rgba(0,58,89,.5);
    }

    .vms-submit-btn .btn-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--vms-gold);
        color: var(--vms-navy);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        transition: transform .3s ease, background .3s ease, color .3s ease;
    }

    .vms-submit-btn:hover {
        background: var(--vms-navy-deep);
        transform: translateY(-2px);
        box-shadow: 0 18px 36px -12px rgba(0,58,89,.55);
    }

    .vms-submit-btn:hover .btn-icon {
        transform: rotate(45deg);
        background: var(--vms-gold-soft);
    }

    .vms-submit-btn:active { transform: translateY(0); }

    .vms-card-footer {
        text-align: center;
        margin-top: 22px;
        font-size: 12px;
        color: #93a1ac;
    }

    .vms-card-footer a {
        color: var(--vms-navy);
        font-weight: 600;
        text-decoration: none;
    }

    .vms-card-footer a:hover { color: var(--vms-gold); }

    .vms-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: var(--vms-gold);
        text-decoration: none;
        transition: color .2s ease;
        margin-top: 16px;
    }

    .vms-back-link:hover { color: #fff; }

    .vms-footer-credit {
        position: fixed;
        bottom: 16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 5;
        pointer-events: none;
        text-align: center;
        font-size: 11px;
        color: rgba(255,255,255,.6);
        letter-spacing: 1.2px;
        text-transform: uppercase;
        white-space: nowrap;
        background: rgba(1,41,63,.4);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border: 1px solid rgba(255,255,255,.1);
        padding: 6px 16px;
        border-radius: 999px;
    }

    @media (max-width: 991px) {
        .vms-visual-side { display: none; }
        .vms-form-side {
            flex: 1 1 100%;
            min-width: 0;
            padding: 28px 16px;
        }
        .vms-login-card { padding: 32px 24px 28px; }
        .vms-footer-credit {
            color: rgba(0,58,89,.7);
            background: rgba(255,255,255,.65);
            border-color: rgba(0,58,89,.08);
        }
    }
</style>
</head>
<body>

<div class="vms-login-container">
    <!-- Visual Side -->
    <div class="vms-visual-side">
        <div class="vms-visual-top">
            <a href="<?= SITE_URL ?>/index-three.php" class="vms-brand-logo">
                <img src="<?= SITE_URL ?>/assets/newlogo.png" alt="VMS Go Vista">
                <span class="brand-text">VMS Go Vista<small>Admin Portal</small></span>
            </a>
        </div>
        <div class="vms-visual-bottom">
            <div class="vms-welcome-text">
                <h2>Your journeys,<br>crafted with <em>care.</em></h2>
                <p>Sign in to manage packages, enquiries, invoices and everything that keeps your travel business moving forward.</p>
            </div>
            <div class="vms-stats-row">
                <div class="vms-stat-item"><b>1000<span>+</span></b><small>Happy Travelers</small></div>
                <div class="vms-stat-item"><b>50<span>+</span></b><small>Destinations</small></div>
                <div class="vms-stat-item"><b>24<span>/7</span></b><small>Support</small></div>
            </div>
        </div>
    </div>

    <!-- Form Side -->
    <div class="vms-form-side">
        <div class="vms-login-card">
            <div class="vms-card-header">
                <img src="<?= SITE_URL ?>/assets/newlogo.png" alt="VMS Go Vista">
                <h3>Admin Sign In</h3>
                <p>Welcome back! Please enter your credentials.</p>
            </div>
            <div class="vms-divider"></div>

            <?php if ($error): ?>
                <div class="vms-error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <div class="vms-form-group">
                    <label for="femail">Email Address</label>
                    <div class="vms-input-group">
                        <input type="email" name="email" id="femail" placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                </div>
                <div class="vms-form-group">
                    <label for="fpass">Password</label>
                    <div class="vms-input-group">
                        <input type="password" name="password" id="fpass" placeholder="Enter your password" autocomplete="current-password" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>

                <div class="vms-form-options">
                    <label class="vms-checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="vms-forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="vms-submit-btn">
                    <span>Sign In to Dashboard</span>
                    <span class="btn-icon"><i class="fa-solid fa-arrow-up"></i></span>
                </button>
            </form>

            <div class="vms-card-footer">
                <a href="<?= SITE_URL ?>/contact">Need help? Contact Support</a>
            </div>

            <div style="text-align: center;">
                <a href="<?= SITE_URL ?>/index-three.php" class="vms-back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to website
                </a>
            </div>
        </div>
    </div>
</div>

<p class="vms-footer-credit">© <?= date('Y') ?> VMS GO VISTA PVT LTD — Admin Panel</p>

<script defer src="<?= SITE_URL ?>/assets/js/plugins/jquery.min.js"></script>
<script defer src="<?= SITE_URL ?>/assets/js/plugins/bootstrap.min.js"></script>
</body>
</html>
