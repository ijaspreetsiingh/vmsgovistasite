<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in? Go to dashboard
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
    <title>Admin Login — VMS Go Vista</title>
    <!-- Custom Font css -->
    <link rel="stylesheet preload" href="../assets/fonts/custom-font.css" as="style">
    <!-- bootstrap css -->
    <link rel="stylesheet preload" href="../assets/css/vendor/bootstrap.min.css" as="style">
    <!-- fontawesome css -->
    <link rel="stylesheet preload" href="../assets/css/plugins/fontawesome.min.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
    <style>
        /* ============================================================
           VMS ADMIN LOGIN — premium redesign (theme: #003A59 / #C9A567)
           ============================================================ */
        :root {
            --vms-navy: #003A59;
            --vms-navy-deep: #01293f;
            --vms-gold: #C9A567;
            --vms-gold-soft: #e0c38c;
            --vms-cream: #f7f4ee;
            --vms-white: #ffffff;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--vms-navy-deep);
            color: var(--vms-navy);
            overflow-x: hidden;
        }

        /* ---------- Split layout ---------- */
        .vms-login-wrap {
            display: flex;
            min-height: 100vh;
            width: 100%;
            background: var(--vms-navy-deep);
        }

        /* ---------- Left visual panel ---------- */
        .vms-login-visual {
            position: relative;
            flex: 1 1 55%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            overflow: hidden;
            background:
                linear-gradient(165deg, rgba(0,58,89,.72) 0%, rgba(0,58,89,.38) 45%, rgba(201,165,103,.22) 100%),
                url('../assets/images/banner/bg-10.webp') center / cover no-repeat;
        }
        .vms-login-visual::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(1,41,63,0) 55%, rgba(1,41,63,.85) 100%);
            pointer-events: none;
        }
        .vms-visual-top, .vms-visual-bottom { position: relative; z-index: 2; }

        .vms-visual-logo {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }
        .vms-visual-logo img {
            height: 74px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 24px rgba(0,0,0,.35));
        }
        .vms-visual-logo .logo-text {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            letter-spacing: -0.3px;
            font-family: 'Playfair Display', serif;
        }
        .vms-visual-logo .logo-text small {
            display: block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 3.5px;
            color: var(--vms-gold-soft);
            text-transform: uppercase;
            font-family: 'Inter', sans-serif;
            margin-top: 2px;
        }

        .vms-visual-quote {
            max-width: 460px;
            margin: 0 0 28px;
        }
        .vms-visual-quote h2 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: clamp(30px, 3.2vw, 46px);
            line-height: 1.18;
            color: #fff;
            margin: 0 0 18px;
        }
        .vms-visual-quote h2 em {
            font-style: italic;
            color: var(--vms-gold-soft);
        }
        .vms-visual-quote p {
            color: rgba(255,255,255,.82);
            font-size: 15px;
            line-height: 1.7;
            margin: 0;
            font-weight: 400;
        }

        .vms-visual-stats {
            display: flex;
            gap: 40px;
        }
        .vms-visual-stats .stat b {
            display: block;
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            line-height: 1;
        }
        .vms-visual-stats .stat b span { color: var(--vms-gold-soft); }
        .vms-visual-stats .stat small {
            font-size: 12px;
            color: rgba(255,255,255,.7);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-top: 8px;
            display: block;
        }

        /* ---------- Right form panel ---------- */
        .vms-login-form-side {
            flex: 0 1 45%;
            min-width: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 56px;
            background: var(--vms-cream);
            position: relative;
            overflow-y: auto;
        }
        .vms-login-form-side::before {
            content: '';
            position: absolute;
            top: -120px;
            right: -120px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,165,103,.16), transparent 65%);
            pointer-events: none;
        }
        .vms-login-form-side::after {
            content: '';
            position: absolute;
            bottom: -140px;
            left: -140px;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,58,89,.10), transparent 65%);
            pointer-events: none;
        }

        .vms-login-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,.9);
            border-radius: 24px;
            padding: 44px 44px 38px;
            box-shadow:
                0 30px 70px -30px rgba(0,58,89,.35),
                0 8px 24px -12px rgba(0,58,89,.12),
                inset 0 1px 0 rgba(255,255,255,.8);
        }

        .vms-card-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 26px;
        }
        .vms-card-logo img {
            height: 64px;
            width: auto;
            object-fit: contain;
            margin-bottom: 6px;
        }
        .vms-card-logo .card-brand {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--vms-navy);
            letter-spacing: -0.2px;
        }

        .vms-card-title {
            text-align: center;
            margin-bottom: 6px;
        }
        .vms-card-title h4 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--vms-navy);
            margin: 0;
            letter-spacing: -0.3px;
        }
        .vms-card-title p {
            color: #7a8894;
            font-size: 13.5px;
            margin: 8px 0 0;
        }
        .vms-title-rule {
            width: 56px;
            height: 3px;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--vms-gold), var(--vms-gold-soft));
            margin: 14px auto 28px;
        }

        .vms-alert {
            background: #fdeceb;
            border: 1px solid #f5c6c2;
            color: #c0392b;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: vms-shake .4s ease;
        }
        @keyframes vms-shake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .vms-field { margin-bottom: 20px; }
        .vms-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--vms-navy);
            margin-bottom: 8px;
            letter-spacing: .2px;
        }
        .vms-input-wrap {
            position: relative;
        }
        .vms-input-wrap > i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9fb0bc;
            font-size: 15px;
            pointer-events: none;
            transition: color .25s ease;
        }
        .vms-input-wrap input {
            width: 100%;
            height: 52px;
            border: 1.5px solid #e2e8ee;
            background: #fff;
            border-radius: 12px;
            padding: 0 16px 0 44px;
            font-size: 14.5px;
            color: var(--vms-navy);
            outline: none;
            transition: border-color .25s ease, box-shadow .25s ease;
            font-family: 'Inter', sans-serif;
        }
        .vms-input-wrap input::placeholder { color: #a6b4bf; font-weight: 400; }
        .vms-input-wrap input:focus {
            border-color: var(--vms-gold);
            box-shadow: 0 0 0 4px rgba(201,165,103,.18);
        }
        .vms-input-wrap input:focus + i,
        .vms-input-wrap:focus-within > i { color: var(--vms-gold); }

        .vms-row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 24px;
        }
        .vms-check {
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            font-size: 13px;
            color: #5c6b76;
            font-weight: 500;
            user-select: none;
        }
        .vms-check input {
            appearance: none;
            -webkit-appearance: none;
            width: 17px;
            height: 17px;
            border: 1.5px solid #cfd8df;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            transition: all .2s ease;
            margin: 0;
        }
        .vms-check input:checked {
            background: var(--vms-navy);
            border-color: var(--vms-navy);
        }
        .vms-check input:checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 10px;
            color: #fff;
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .vms-forgot {
            font-size: 13px;
            font-weight: 600;
            color: var(--vms-gold);
            text-decoration: none;
            transition: color .2s ease;
        }
        .vms-forgot:hover { color: var(--vms-navy); }

        /* ---------- CTA button (header "Book now" style) ---------- */
        .vms-login-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 54px;
            padding: 0 8px 0 24px;
            border: none;
            border-radius: 999px;
            background: var(--vms-navy);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .2px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: background .3s ease, transform .25s ease, box-shadow .3s ease;
            box-shadow: 0 14px 30px -12px rgba(0,58,89,.55);
        }
        .vms-login-btn .btn-arrow {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--vms-gold);
            color: var(--vms-navy);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            transition: transform .3s ease, background .3s ease, color .3s ease;
        }
        .vms-login-btn:hover {
            background: var(--vms-navy-deep);
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -14px rgba(0,58,89,.6);
        }
        .vms-login-btn:hover .btn-arrow {
            transform: rotate(45deg);
            background: var(--vms-gold-soft);
        }
        .vms-login-btn:active { transform: translateY(0); }

        .vms-card-foot {
            text-align: center;
            margin-top: 26px;
            font-size: 12.5px;
            color: #93a1ac;
        }
        .vms-card-foot a {
            color: var(--vms-navy);
            font-weight: 600;
            text-decoration: none;
        }
        .vms-card-foot a:hover { color: var(--vms-gold); }

        .vms-credit {
            position: fixed;
            bottom: 18px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
            pointer-events: none;
            text-align: center;
            font-size: 11.5px;
            color: rgba(255,255,255,.65);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            white-space: nowrap;
            max-width: calc(100vw - 24px);
            overflow: hidden;
            text-overflow: ellipsis;
            background: rgba(1,41,63,.45);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.12);
            padding: 8px 18px;
            border-radius: 999px;
        }
        @media (max-width: 991px) {
            .vms-credit {
                color: rgba(0,58,89,.75);
                background: rgba(255,255,255,.7);
                border-color: rgba(0,58,89,.1);
            }
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 991px) {
            .vms-login-visual { display: none; }
            .vms-login-form-side {
                flex: 1 1 100%;
                min-width: 0;
                padding: 32px 20px;
            }
            .vms-login-card { padding: 36px 26px 32px; }
        }
    </style>
</head>

<body>

    <div class="vms-login-wrap">
        <!-- LEFT VISUAL -->
        <div class="vms-login-visual">
            <div class="vms-visual-top">
                <a href="<?= SITE_URL ?>" class="vms-visual-logo">
                    <img src="../assets/newlogo.png" alt="VMS Go Vista" class="vms-logo-img">
                    <span class="logo-text">VMS Go Vista<small>Admin Portal</small></span>
                </a>
            </div>
            <div class="vms-visual-bottom">
                <div class="vms-visual-quote">
                    <h2>Your journeys,<br>crafted with <em>care.</em></h2>
                    <p>Sign in to manage packages, enquiries, invoices and everything that keeps your travel business moving forward.</p>
                </div>
                <div class="vms-visual-stats">
                    <div class="stat"><b>1000<span>+</span></b><small>Happy Travelers</small></div>
                    <div class="stat"><b>50<span>+</span></b><small>Destinations</small></div>
                    <div class="stat"><b>24<span>/7</span></b><small>Travel Support</small></div>
                </div>
            </div>
        </div>

        <!-- RIGHT FORM -->
        <div class="vms-login-form-side">
            <div class="vms-login-card">
                <div class="vms-card-logo">
                    <img src="../assets/newlogo.png" alt="VMS Go Vista" class="vms-logo-img">
                    <span class="card-brand">VMS Go Vista</span>
                </div>

                <div class="vms-card-title">
                    <h4>Admin Sign In</h4>
                    <p>Welcome back! Please enter your credentials.</p>
                </div>
                <div class="vms-title-rule"></div>

                <?php if ($error): ?>
                    <div class="vms-alert"><i class="fa-solid fa-circle-exclamation"></i><span><?= e($error) ?></span></div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <div class="vms-field">
                        <label for="femail">Email Address</label>
                        <div class="vms-input-wrap">
                            <input type="email" name="email" id="femail" placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                    </div>
                    <div class="vms-field">
                        <label for="fname">Password</label>
                        <div class="vms-input-wrap">
                            <input type="password" name="password" id="fname" placeholder="Enter your password" autocomplete="current-password" required>
                            <i class="fa-solid fa-lock"></i>
                        </div>
                    </div>

                    <div class="vms-row-between">
                        <label class="vms-check">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="vms-forgot">Forgot password?</a>
                    </div>

                    <button type="submit" class="vms-login-btn">
                        <span>Sign In to Dashboard</span>
                        <span class="btn-arrow"><i class="fa-solid fa-arrow-up"></i></span>
                    </button>
                </form>

                <div class="vms-card-foot">
                    Need help? <a href="<?= SITE_URL ?>/contact">Contact Support</a>
                </div>
            </div>
        </div>
    </div>

    <p class="vms-credit">© <?= date('Y') ?> VMS GO VISTA PVT LTD — Admin Panel</p>

    <!-- jquery js -->
    <script defer src="../assets/js/plugins/jquery.min.js"></script>
    <script defer src="../assets/js/plugins/bootstrap.min.js"></script>
</body>

</html>
