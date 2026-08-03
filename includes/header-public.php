<?php
// Shared public header — used by all public PHP pages
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';
}
$_pageTitle = isset($pageTitle) ? $pageTitle . ' — VMS Go Vista' : 'VMS Go Vista | Travel & Tour';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/fav.svg">
<title><?= htmlspecialchars($_pageTitle) ?></title>

<!-- PERFORMANCE: Preload critical assets -->
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

<!-- PERFORMANCE: Critical CSS loaded first (render-blocking by design) -->
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/plugins/fontawesome.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/plugins/swiper.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/fonts/custom-font.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/plugins/magnific-popup.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/plugins/metismenu.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/vendor/bootstrap.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/vendor/animate.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/plugins/odometer.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/plugins/nice-select.css">    <!-- Google Fonts (sync to prevent FOUT / font-size shift) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= isset($bodyClass) ? htmlspecialchars($bodyClass) : 'home-bg' ?>">
<!-- preloader -->
<div class="preloader">
    <div class="loader">
        <?php for ($i=1;$i<=20;$i++): ?><span style="--i:<?=$i?>;"></span><?php endfor; ?>
        <div class="loader-plane"></div>
    </div>
</div>
