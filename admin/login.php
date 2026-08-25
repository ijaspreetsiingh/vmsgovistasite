<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in? Go to dashboard
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/index.php');
}

// Rate limiting: 5 attempts per 15 minutes per IP
function checkRateLimit(): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'login_ratelimit_' . md5($ip);
    $data = $_SESSION[$key] ?? ['count' => 0, 'window' => time()];
    if (time() - $data['window'] > 900) { // 15 min window
        $data = ['count' => 0, 'window' => time()];
    }
    if ($data['count'] >= 5) {
        return false;
    }
    $data['count']++;
    $_SESSION[$key] = $data;
    return true;
}

function resetRateLimit(): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'login_ratelimit_' . md5($ip);
    unset($_SESSION[$key]);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkRateLimit()) {
        $error = 'Too many login attempts. Please try again in 15 minutes.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            $error = 'Please enter both email and password.';
        } else {
            $result = loginUser($email, $password);
            if ($result['success']) {
                resetRateLimit();
                redirect(SITE_URL . '/admin/index.php');
            } else {
                $error = $result['message'];
            }
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
        :root {
            --vms-navy: #003A59;
            --vms-navy-dark: #022a40;
            --vms-gold: #C9A567;
            --vms-gold-light: #e0c38c;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: #fff;
            border-radius: 12px;
            padding: 48px 40px;
            box-shadow: 0 10px 40px rgba(0,58,89,0.12);
            border: 1px solid rgba(0,58,89,0.08);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-section img {
            height: 65px;
            width: auto;
            margin-bottom: 14px;
        }

        .logo-section h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--vms-navy);
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .logo-section p {
            font-size: 14px;
            color: #6b7280;
            font-weight: 400;
        }

        .alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--vms-navy);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .form-group input {
            width: 100%;
            height: 48px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
            color: #374151;
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .form-group input:focus {
            border-color: var(--vms-gold);
            box-shadow: 0 0 0 3px rgba(201,165,103,0.15);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
            font-size: 13px;
        }

        .form-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #4b5563;
            font-weight: 500;
        }

        .form-options input[type="checkbox"] {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: var(--vms-navy);
        }

        .form-options a {
            color: var(--vms-gold);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .form-options a:hover {
            color: var(--vms-navy);
        }

        .submit-btn {
            width: 100%;
            height: 50px;
            background: var(--vms-navy);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            letter-spacing: 0.3px;
        }

        .submit-btn:hover {
            background: var(--vms-navy-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,58,89,0.2);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 24px 0;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }

        .footer a {
            color: var(--vms-navy);
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            color: var(--vms-gold);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 36px 28px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="../assets/newlogo.png" alt="VMS Go Vista">
                <h1>Admin Login</h1>
                <p>VMS Go Vista</p>
            </div>

            <?php if ($error): ?>
                <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <div class="form-group">
                    <label for="femail">Email Address</label>
                    <input type="email" name="email" id="femail" placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="fname">Password</label>
                    <input type="password" name="password" id="fname" placeholder="Enter your password" autocomplete="current-password" required>
                </div>

                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="submit-btn">Sign In</button>
            </form>

            <div class="footer">
                Need help? <a href="<?= SITE_URL ?>/contact">Contact Support</a>
            </div>
        </div>
    </div>

</body>

</html>
