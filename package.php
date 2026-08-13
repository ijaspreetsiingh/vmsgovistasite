<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// ── Filters from GET ──────────────────────────────────────────
$perPage     = (int)($_GET['per_page'] ?? 9);
$perPage     = in_array($perPage, [9, 10, 12, 18, 24, 48, 50, 100], true) ? $perPage : 9;
// Reset page when per-page changes so we never overshoot the last page
$prevPerPage = (int)($_GET['old_per_page'] ?? $perPage);
$page        = ($_GET['per_page'] ?? null) !== null && $prevPerPage !== $perPage
              ? 1
              : max(1, (int)($_GET['page'] ?? 1));
$destination = trim($_GET['destination'] ?? '');
$tour_type   = trim($_GET['tour_type']   ?? '');
$min_price   = trim($_GET['min_price']   ?? '');
$max_price   = trim($_GET['max_price']   ?? '');
$days        = trim($_GET['days']        ?? '');

$filters = [
    'destination' => $destination,
    'tour_type'   => $tour_type,
    'min_price'   => $min_price,
    'max_price'   => $max_price,
    'days'        => $days,
];

$result   = getAllPublishedPackages($filters, $page, $perPage);
$packages = $result['packages'];
$total    = $result['total'];
$lastPage = $result['last_page'];
// Safety clamp: never request a page beyond the last one — refetch so the
// listing still shows real packages when the URL asked for an out-of-range page
if ($page > $lastPage && $lastPage > 0) {
    $page    = $lastPage;
    $result   = getAllPublishedPackages($filters, $page, $perPage);
    $packages = $result['packages'];
    $total    = $result['total'];
    $lastPage = $result['last_page'];
}

$destinations = getSettings('destination');
$tourTypes    = getSettings('tour_type');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore domestic &amp; international tour packages by VMS Go Vista — Kashmir, Goa, Kerala, Andaman, Ladakh and more at the best prices.">
    <link rel="canonical" href="https://vmsgovista.com/package">
    <title>Tour Packages – VMS Go Vista</title>
    <link rel="icon" type="image/png" href="assets/fav.png">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Tour Packages – VMS Go Vista">
    <meta property="og:description" content="Explore domestic &amp; international tour packages by VMS Go Vista.">
    <meta property="og:url" content="https://vmsgovista.com/package">
    <meta property="og:site_name" content="VMS Go Vista">
    <link rel="stylesheet preload" href="assets/css/plugins/swiper.min.css" as="style">
    <link rel="stylesheet preload" href="assets/fonts/custom-font.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet preload" href="assets/css/plugins/magnific-popup.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/metismenu.css" as="style">
    <link rel="stylesheet preload" href="assets/css/vendor/bootstrap.min.css" as="style">
    <link rel="stylesheet preload" href="assets/css/vendor/animate.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/odometer.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/fontawesome.min.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/nice-select.css" as="style">
    <link rel="stylesheet preload" href="assets/css/style.css" as="style">
    <link rel="stylesheet preload" href="assets/css/bromo-theme.css" as="style">
    <link rel="preload" href="assets/images/package.webp" as="image">
    <style>
        /* Page-specific overrides */
        body.dark-mode .rts-tour-area,body.dark-mode section{background-color:#0d1117!important;}
        .rts-tour-area{background:var(--color-bg-1)!important;}
        /* dark mode sidebar */
        body.dark-mode .left-sidebar-area{background:#161b22!important;border-color:#30363d!important;}
        body.dark-mode .tour-wrapper{background:#161b22!important;border-color:#30363d!important;}
        body.dark-mode .tour-wrapper .title a{color:#e6edf3!important;}
        body.dark-mode .rts-breadcrumb-area{background:#0d1117!important;}
        /* Uniform image sizing - same height for ALL tour card images */
        .tour-wrapper .image-area { position: relative !important; height: 220px !important; overflow: hidden !important; }
        .tour-wrapper .image-area a { display: block !important; width: 100% !important; height: 100% !important; }
        .tour-wrapper .image-area img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; }
        .tour-wrapper .image-area .tag { position: absolute !important; top: 16px !important; left: 16px !important; z-index: 2 !important; }
        @media (max-width: 767px) {
            .tour-wrapper .image-area { height: 200px !important; }
        }
        /* ===== STICKY HEADER ===== */
        .bromo-header.header--sticky.sticky{
            position:fixed!important;top:0;left:0;right:0;
            display:grid;grid-template-columns:1fr auto 1fr;
            background:rgb(255 255 255 / 36%);
            backdrop-filter:blur(14px);
            box-shadow:0 2px 20px rgba(0,0,0,0.08);
            border-bottom:1px solid rgba(0,0,0,0.06);
            animation:bromoStickyIn .3s ease;
        }
        .bromo-book-btn a:hover .bromo-arrow{background:#003A59;color:#fff;}
        .bromo-filter-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1.5px solid rgba(0,58,89,0.15);
            background: rgba(0,58,89,0.06);
            color: #003A59;
            font-size: 15px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .25s ease;
            margin-right: 8px;
        }
        .bromo-filter-btn:hover {
            background: #003A59;
            border-color: #003A59;
            color: #fff;
            transform: translateY(-1px);
        }
        .bromo-filter-btn.active {
            background: #003A59;
            border-color: #003A59;
            color: #fff;
        }
        body.dark-mode .bromo-filter-btn {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.15);
            color: #e6edf3;
        }
        body.dark-mode .bromo-filter-btn:hover,
        body.dark-mode .bromo-filter-btn.active {
            background: #003A59;
            border-color: #003A59;
            color: #fff;
        }
        .bromo-header.header--sticky.sticky .bromo-logo span{color:#003A59;}
        .bromo-header.header--sticky.sticky .bromo-logo-icon{background:#003A59;}
        .bromo-header.header--sticky.sticky .bromo-logo-icon svg path,
        .bromo-header.header--sticky.sticky .bromo-logo-icon svg circle{fill:#fff;}
        .bromo-header.header--sticky.sticky .bromo-nav{background:rgba(0,58,89,0.06);border-color:rgba(0,58,89,0.1);}
        .bromo-header.header--sticky.sticky .bromo-nav a{color:rgba(0,58,89,0.8);}
        .bromo-header.header--sticky.sticky .bromo-nav a:hover,
        .bromo-header.header--sticky.sticky .bromo-nav a.active{background:#003A59;color:#fff;}
        .bromo-header.header--sticky.sticky .bromo-book-btn a{background:rgba(0,58,89,0.06);border-color:rgba(0,58,89,0.12);color:#003A59;}
        .bromo-header.header--sticky.sticky .bromo-book-btn a:hover{background:#003A59;color:#fff;}
        .bromo-header.header--sticky.sticky .bromo-book-btn a .bromo-arrow{background:#003A59;color:#fff;}
        .bromo-header.header--sticky.sticky .bromo-book-btn a:hover .bromo-arrow{background:#fff;color:#003A59;}
        .bromo-header.header--sticky.sticky .bromo-mobile-menu{background:rgba(0,58,89,0.06);border-color:rgba(0,58,89,0.1);}
        .bromo-header.header--sticky.sticky .bromo-mobile-menu span{color:#003A59;}
        body.dark-mode .bromo-header.header--sticky.sticky{background:rgba(13,17,23,0.9);border-bottom-color:rgba(255,255,255,0.06);}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-logo span{color:#e6edf3;}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-logo-icon{background:#e6edf3;}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-logo-icon svg path,
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-logo-icon svg circle{fill:#0d1117;}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-nav{background:rgba(255,255,255,0.06);border-color:rgba(255,255,255,0.08);}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-nav a{color:rgba(255,255,255,0.8);}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-nav a:hover,
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-nav a.active{background:rgba(255,255,255,0.12);color:#fff;}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-book-btn a{background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.14);color:#e6edf3;}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-book-btn a:hover{background:rgba(255,255,255,0.15);color:#fff;}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-book-btn a .bromo-arrow{background:#e6edf3;color:#0d1117;}
        body.dark-mode .bromo-header.header--sticky.sticky .bromo-book-btn a:hover .bromo-arrow{background:#fff;color:#0d1117;}

        /* ===== STICKY HEADER — MOBILE LAYOUT FIX ===== */
        @media(max-width:991px){
            .bromo-header.header--sticky.sticky{display:flex;justify-content:space-between;grid-template-columns:none;}
        }

        /* ===== VMS FOOTER — COOLDOCK STYLE ===== */
        .vms-footer{position:relative;background:whitesmoke;padding:0;overflow:hidden;font-family:var(--font-body);}
        .vms-footer-toppill{position:absolute;top:-28px;left:50%;transform:translateX(-50%);z-index:5;background:#003A59;border-radius:999px;padding:8px 8px 8px 20px;display:flex;align-items:center;gap:22px;box-shadow:0 8px 30px rgba(0,58,89,0.25);}
        .vms-footer-toppill .tp-logo{display:flex;align-items:center;gap:8px;color:#fff;font-weight:600;font-size:14px;}
        .vms-footer-toppill .tp-logo-box{width:22px;height:22px;background:#fff;border-radius:6px;display:flex;align-items:center;justify-content:center;}
        .vms-footer-toppill .tp-logo-box svg{width:14px;height:14px;}
        .vms-footer-toppill .tp-nav{display:flex;align-items:center;gap:18px;}
        .vms-footer-toppill .tp-nav a{color:rgba(255,255,255,0.85);font-size:13px;font-weight:500;text-decoration:none;transition:color .2s;}
        .vms-footer-toppill .tp-nav a:hover{color:#fff;}
        .vms-footer-toppill .tp-download{background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:8px 16px;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,0.2);}
        .vms-footer-toppill .tp-download:hover{background:rgba(255,255,255,0.25);}
        .vms-footer-nominee{position:absolute;top:120px;right:0;z-index:4;background:#003A59;color:#fff;padding:20px 8px;border-radius:8px 0 0 8px;writing-mode:vertical-rl;transform:rotate(180deg);font-size:12px;font-weight:600;letter-spacing:2px;}
        .vms-footer-content{position:relative;z-index:3;max-width:1100px;margin:0 auto;padding-top:80px;padding-bottom:40px;display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:60px;}
        .vms-brand{display:flex;flex-direction:column;gap:12px;}
        .vms-logo{display:flex;align-items:center;gap:10px;}
        .vms-logo-icon{width:38px;height:38px;border-radius:10px;background:#003A59;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;}
        .vms-logo-icon svg{width:20px;height:20px;}
        .vms-logo-text{font-size:19px;font-weight:700;color:#003A59;letter-spacing:-0.3px;}
        .vms-tagline{font-size:22px;font-weight:700;color:#003A59;margin:6px 0 4px;letter-spacing:-0.4px;line-height:1.3;font-family:var(--font-body)!important;}
        .vms-desc{font-size:13.5px;color:#555;line-height:1.65;margin:0 0 8px;max-width:340px;}
        .vms-cta-btn{display:inline-flex;align-items:center;gap:14px;background:rgba(0,58,89,0.14);backdrop-filter:blur(16px);border:1px solid rgba(0,58,89,0.28);color:#003A59;font-size:13px;font-weight:500;padding:10px 10px 10px 24px;border-radius:999px;text-decoration:none;margin-top:4px;transition:background .22s,color .22s,border-color .22s;width:fit-content;}
        .vms-cta-btn:hover{background:#003A59;color:#fff;}
        .vms-cta-btn .vms-btn-arrow{width:32px;height:32px;background:#003A59;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;transition:background .22s,color .22s;}
        .vms-cta-btn:hover .vms-btn-arrow{background:#fff;color:#003A59;}
        .vms-footer-bottom{position:relative;z-index:3;max-width:1100px;margin:0 auto;padding:24px 0;border-top:1px solid rgba(0,58,89,0.06);display:flex;align-items:center;justify-content:space-between;gap:20px;}
        .vms-credit{display:flex;align-items:center;gap:6px;font-size:12.5px;color:#666;}
        .vms-author{color:#555;font-weight:400;display:inline-flex;align-items:center;gap:6px;}
        .vms-author-avatar{width:20px;height:20px;border-radius:50%;background:#003A59;display:inline-block;}
        .vms-footer-col h5{font-size:14px;font-weight:700;color:#003A59;margin:0 0 18px;}
        .vms-footer-links{list-style:none;padding:0;margin:0;}
        .vms-footer-links li{margin-bottom:11px;}
        .vms-footer-links a{font-size:13.5px;color:#555;text-decoration:none;transition:color .2s;}
        .vms-footer-links a:hover{color:#003A59;}
        @keyframes vmsBgShift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
        .vms-video-section{position:relative;z-index:1;width:100%;height:520px;overflow:hidden;margin-top:-210px;background:linear-gradient(135deg,#667eea 0%,#764ba2 50%,#f093fb 100%);background-size:400% 400%;animation:vmsBgShift 15s ease infinite;}
        .vms-video-section .vms-video-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;}
        .vms-video-gradient{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,whitesmoke 0%,rgba(245,245,245,0.92) 12%,rgba(245,245,245,0.35) 22%,rgba(245,245,245,0.08) 32%,rgba(0,0,0,0.2) 48%,rgba(0,0,0,0.5) 78%,#000 100%);}
        .vms-big-text{position:absolute;bottom:80px;left:0;right:0;z-index:2;text-align:center;font-size:clamp(80px,12vw,140px);font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:0.05em;text-transform:uppercase;text-shadow:0 2px 20px rgba(0,0,0,0.3);pointer-events:none;font-family:var(--font-heading);margin:0;padding:0 30px;white-space:nowrap;}
        body.dark-mode .vms-footer{background:#0d1117;}
        body.dark-mode .vms-logo-text,body.dark-mode .vms-tagline,body.dark-mode .vms-footer-col h5,body.dark-mode .vms-footer-links a{color:#e6edf3;}
        body.dark-mode .vms-desc,body.dark-mode .vms-credit{color:#8b949e;}
        body.dark-mode .vms-author{color:#e6edf3;}
        body.dark-mode .vms-cta-btn{background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.14);color:#e6edf3;}
        body.dark-mode .vms-cta-btn:hover{background:#003A59;color:#fff;}
        body.dark-mode .vms-cta-btn .vms-btn-arrow{background:#e6edf3;color:#0d1117;}
        body.dark-mode .vms-cta-btn:hover .vms-btn-arrow{background:#fff;color:#003A59;}
        body.dark-mode .vms-logo-icon{background:#003A59;color:#fff;}
        body.dark-mode .vms-video-gradient{background:linear-gradient(180deg,#0d1117 0%,rgba(13,17,23,0.92) 12%,rgba(13,17,23,0.35) 22%,rgba(13,17,23,0.08) 32%,rgba(0,0,0,0.2) 48%,rgba(0,0,0,0.5) 78%,#000 100%);}
        body.dark-mode .vms-big-text{color:#fff;}
        body.dark-mode .vms-footer-toppill{background:#003A59;}
        body.dark-mode .vms-footer-toppill .tp-logo{color:#fff;}
        body.dark-mode .vms-footer-toppill .tp-logo-box{background:#fff;}
        body.dark-mode .vms-footer-toppill .tp-nav a{color:rgba(255,255,255,0.85);}
        body.dark-mode .vms-footer-toppill .tp-nav a:hover{color:#fff;}
        body.dark-mode .vms-footer-toppill .tp-download{background:rgba(255,255,255,0.15);color:#fff;}
        body.dark-mode .vms-footer-nominee{background:#003A59;color:#fff;}
        @media(max-width:1199px){
            .vms-footer-toppill{display:none;}
            .vms-footer-nominee{display:none;}
            .vms-footer-content{padding:60px 32px 32px;gap:40px;}
        }
        @media(min-width:1024px) and (max-width:1199px){
            .vms-big-text{font-size:clamp(60px,10vw,110px);bottom:70px;}
        }
        @media(max-width:991px){
            .vms-footer-content{grid-template-columns:1fr 1fr;gap:32px;padding:40px 28px 80px;}
            .vms-brand{grid-column:span 2;}
            .vms-video-section{height:360px;margin-top:-120px;}
            .vms-big-text{font-size:clamp(40px,8vw,70px);bottom:45px;}
        }
        @media(max-width:767px){
            .vms-video-section{height:320px;margin-top:-105px;}
            .vms-big-text{font-size:clamp(30px,6vw,50px);bottom:35px;}
        }
        @media(max-width:575px){
            .vms-footer-content{grid-template-columns:1fr;gap:26px;padding:28px 22px 60px;}
            .vms-brand{grid-column:span 1;}
            .vms-video-section{height:280px;margin-top:-90px;}
            .vms-big-text{font-size:clamp(22px,5vw,35px);bottom:25px;}
        }

        /* CTA Section — PROFESSIONAL REDESIGN */
        .bromo-cta-section {
            padding: 0 20px;
        }
        .bromo-cta-wrap {
            position: relative;
            width: 100%;
            max-width: 1400px;
            height: 600px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 32px;
        }
        .bromo-cta-bg::after {
            background: linear-gradient(135deg, rgba(0,20,40,0.82) 0%, rgba(0,40,60,0.65) 40%, rgba(0,30,50,0.75) 100%) !important;
        }
        .bromo-cta-content {
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            text-align: left;
        }
        .bromo-cta-content .cta-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.18);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.9);
            margin-bottom: 24px;
        }
        .bromo-cta-content .cta-eyebrow i {
            font-size: 10px;
            color: #003A59;
        }
        .bromo-cta-content h2 { 
            color: #fff !important;
            font-size: clamp(32px, 4vw, 52px) !important;
            font-weight: 800 !important;
            line-height: 1.12 !important;
            margin-bottom: 20px !important;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
            letter-spacing: -0.5px;
        }
        .bromo-cta-content .cta-desc {
            color: rgba(255,255,255,0.8) !important;
            font-size: 16px !important;
            line-height: 1.7 !important;
            max-width: 520px !important;
            margin-bottom: 32px !important;
            text-shadow: 0 1px 8px rgba(0,0,0,0.2);
        }
        .bromo-cta-content .cta-buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }
        .bromo-cta-content .cta-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            border-radius: 50px;
            background: #003A59;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            border: 2px solid #003A59;
            transition: all .3s ease;
        }
        .bromo-cta-content .cta-btn-primary:hover {
            background: #002B43;
            border-color: #002B43;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0,58,89,0.45);
        }
        .bromo-cta-content .cta-btn-primary i {
            font-size: 13px;
            transition: transform .3s;
        }
        .bromo-cta-content .cta-btn-primary:hover i {
            transform: translate(2px, -2px);
        }
        .bromo-cta-content .cta-btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 28px;
            border-radius: 50px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255,255,255,0.3);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all .3s ease;
        }
        .bromo-cta-content .cta-btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-2px);
        }
        body.dark-mode .bromo-cta-content .cta-btn-primary {
            background: #003A59;
            border-color: #003A59;
        }
        body.dark-mode .bromo-cta-content .cta-btn-primary:hover {
            background: #002B43;
        }
        body.dark-mode .bromo-cta-content .cta-btn-secondary {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.2);
        }
        body.dark-mode .bromo-cta-content .cta-btn-secondary:hover {
            background: rgba(255,255,255,0.18);
        }
        @media (max-width: 991px) {
            .bromo-cta-wrap { height: auto; min-height: 480px; }
            .bromo-cta-content { padding: 48px 36px; align-items: center; text-align: center; }
            .bromo-cta-content .cta-desc { margin-left: auto; margin-right: auto; }
            .bromo-cta-content .cta-buttons { justify-content: center; }
        }
        @media (max-width: 575px) {
            .bromo-cta-content { padding: 36px 20px; }
            .bromo-cta-content .cta-buttons { flex-direction: column; width: 100%; }
            .bromo-cta-content .cta-btn-primary,
            .bromo-cta-content .cta-btn-secondary { justify-content: center; width: 100%; }
        }
        @keyframes bromoStickyIn{0%{transform:translateY(-15px)}100%{transform:translateY(0)}}
        /* ===== SEARCH BAR — PROFESSIONAL REDESIGN (theme navy #003A59) ===== */
        /* All rules scoped under the full page class chain (.is__home__one.home-hiking.inner)
           so they beat the base stylesheet's .home-hiking / .home-hiking.inner selectors. */
        .advance__search__section.is__home__one.home-hiking.inner {
            position: relative;
            /* above sticky sidebar (99) & back-to-top (8); below header (1000) — so open
               dropdowns never paint under the tour listing / floating buttons */
            z-index: 150;
            bottom: auto;
            left: auto;
            transform: none;
            margin-top: -72px;
            max-width: 1290px;
            width: 100%;
            display: none;
        }
        .advance__search__section.is__home__one.home-hiking.inner.vms-filter-visible {
            display: block;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 14px;
            background: #ffffff;
            border: 1px solid rgba(0,58,89,0.12);
            border-radius: 18px;
            box-shadow: 0 18px 50px -20px rgba(0,58,89,0.28);
            backdrop-filter: none;
            padding: 24px 26px;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select {
            width: auto;
            flex: 1;
            min-width: 0;
            z-index: 1; /* base; active select rises above siblings so its dropdown is never covered */
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select.active {
            z-index: 40;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .tag {
            margin-bottom: 8px;
            color: #003A59;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.4px;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .custom-select-trigger {
            background: #f7fafc;
            border: 1px solid rgba(0,58,89,0.18);
            border-radius: 12px;
            color: #003A59;
            padding: 14px 42px 14px 16px;
            font-size: 14px;
            font-weight: 600;
            min-height: 48px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .custom-select-trigger:hover {
            border-color: rgba(0,58,89,0.4);
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select.active .custom-select-trigger {
            border-color: #003A59;
            box-shadow: 0 0 0 3px rgba(0,58,89,0.12);
            background: #fff;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .dropdown-icon {
            right: 16px;
            top: 50%;
            transform: translateY(-50%) rotate(0);
            color: #003A59;
            transition: transform .3s ease, color .2s;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select.active .dropdown-icon {
            transform: translateY(-50%) rotate(-180deg);
            color: #C9A567;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options {
            top: calc(100% + 6px);
            z-index: 50;
            background: #fff;
            border: 1px solid rgba(0,58,89,0.12);
            border-radius: 12px;
            box-shadow: 0 20px 45px -18px rgba(0,58,89,0.35);
            max-height: 250px;
            overflow-y: auto;
            margin: 0; /* kill the base margin-top:5px — top offset already set */
            padding: 6px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-6px);
            transition: opacity .25s ease, transform .25s ease, height .35s ease, visibility .25s;
            scrollbar-width: thin;
            scrollbar-color: rgba(0,58,89,0.25) transparent;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select.active .custom-options {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options::-webkit-scrollbar {
            width: 5px;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options::-webkit-scrollbar-thumb {
            background: rgba(0,58,89,0.22);
            border-radius: 99px;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options::-webkit-scrollbar-thumb:hover {
            background: rgba(0,58,89,0.4);
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option {
            position: relative;
            padding: 11px 36px 11px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            color: #33414f;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option + .option {
            border-top: 1px solid rgba(0,58,89,0.05);
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option:hover {
            background: rgba(0,58,89,0.07);
            color: #003A59;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option.selected {
            background: #003A59;
            color: #fff;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option.selected::after {
            content: "\f00c";
            font-family: "Font Awesome 6 Pro";
            font-weight: 900;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: #C9A567;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .button-area {
            display: flex;
            align-items: stretch;
        }
        .advance__search__section.is__home__one.home-hiking.inner .form-area .button-area .vms-glass-btn {
            width: auto;
            height: 100%;
            min-height: 48px;
            padding: 12px 12px 12px 28px;
            font-size: 14px;
        }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area {
            background: #161b22;
            border-color: rgba(255,255,255,0.1);
        }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .tag { color: #e6edf3; }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .custom-select-trigger {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.14);
            color: #e6edf3;
        }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select.active .custom-select-trigger {
            border-color: #e6edf3;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.08);
        }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .dropdown-icon { color: #e6edf3; }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options {
            background: #161b22;
            border-color: rgba(255,255,255,0.1);
        }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option { color: #c9d1d9; }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option + .option { border-top-color: rgba(255,255,255,0.06); }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option:hover { background: rgba(255,255,255,0.07); color: #e6edf3; }
        body.dark-mode .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options .option.selected {
            background: #003A59;
            color: #fff;
        }
        @media (max-width: 1199px) {
            .advance__search__section.is__home__one.home-hiking.inner .form-area { flex-wrap: wrap; }
            .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select { flex: 1 1 40%; }
        }
        @media (max-width: 767px) {
            .advance__search__section.is__home__one.home-hiking.inner { margin-top: -36px; }
            .advance__search__section.is__home__one.home-hiking.inner .form-area {
                padding: 18px 16px;
                gap: 12px;
                border-radius: 16px;
            }
            .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select { flex: 1 1 100%; }
            .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-select .custom-select-trigger {
                min-height: 46px;
                font-size: 14px;
            }
            .advance__search__section.is__home__one.home-hiking.inner .form-area .custom-options {
                max-height: 220px;
            }
            .advance__search__section.is__home__one.home-hiking.inner .form-area .button-area { width: 100%; }
            .advance__search__section.is__home__one.home-hiking.inner .form-area .button-area .vms-glass-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* ===== GLASS PILL BUTTONS — index-three.php style ===== */
        .vms-glass-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px 12px 12px 26px;
            border-radius: 999px;
            background: rgba(0,58,89,0.12);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1.5px solid rgba(0,58,89,0.3);
            color: #003A59;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background .22s ease, color .22s ease, border-color .22s ease, transform .22s ease, box-shadow .22s ease;
            width: -webkit-fill-available;
            font-family: inherit;
        }
        .vms-glass-btn:hover {
            background: #003A59;
            color: #fff;
            border-color: #003A59;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px -10px rgba(0,58,89,.45);
        }
        .vms-glass-btn .vms-glass-arrow {
            width: 34px;
            height: 34px;
            background: #003A59;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            flex-shrink: 0;
            transition: all .22s ease;
        }
        .vms-glass-btn:hover .vms-glass-arrow {
            background: #fff;
            color: #003A59;
        }
        .vms-glass-btn .vms-glass-arrow svg { width: 15px; height: 15px; display: block; }
        .vms-glass-btn .vms-glass-arrow svg path { fill: currentColor; }
        .vms-glass-btn.w-100 { width: 100%; justify-content: center; }
        body.dark-mode .vms-glass-btn {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.18);
            color: #e6edf3;
        }
        body.dark-mode .vms-glass-btn:hover {
            background: #003A59;
            border-color: #003A59;
            color: #fff;
        }
        body.dark-mode .vms-glass-btn .vms-glass-arrow {
            background: #e6edf3;
            color: #0d1117;
        }
        body.dark-mode .vms-glass-btn:hover .vms-glass-arrow {
            background: #fff;
            color: #003A59;
        }
        .vms-logo-img{
            height: 70px;
            width: auto;
        }
        /* Header logo text — hide when zoomed 110%+ */
        .bromo-logo span {
            white-space: nowrap;
            font-size: 16px;
        }
        @media (min-width: 768px) and (max-width: 1400px) {
            .bromo-logo span { display: none !important; }
        }
        @media (max-width: 767px) {
            .bromo-logo span { display: block !important; font-size: 12px !important; white-space: nowrap !important; }
            .bromo-header .bromo-logo { gap: 6px; }
            .vms-video-section { overflow: hidden !important; }
            body { overflow-x: hidden !important; }
        }
        @media (min-width: 768px) {
            .vms-video-section { overflow: visible !important; }
        }
        .vms-big-text{
            font-family: 'Sunsive', sans-serif !important;
            font-size: clamp(40px, 7vw, 90px) !important;
            font-weight: 700 !important;
            bottom: 60px !important;
            text-align: center !important;
            width: 100%;
            position: absolute;
            left: 0;
            white-space: nowrap !important;
            padding: 0 30px !important;
        }
        @media (min-width: 1024px) and (max-width: 1199px) {
            .vms-big-text { font-size: clamp(28px, 5vw, 70px) !important; bottom: 50px !important; }
        }
        @media (max-width: 991px) {
            .vms-big-text { font-size: clamp(20px, 4vw, 45px) !important; bottom: 35px !important; }
        }
        @media (max-width: 767px) {
            .vms-big-text { font-size: clamp(18px, 4vw, 35px) !important; bottom: 25px !important; }
        }
        @media (max-width: 575px) {
            .vms-big-text { font-size: clamp(24px, 5vw, 40px) !important; bottom: 25px !important; }
        }
        @media (max-width: 400px) {
            .vms-big-text { font-size: clamp(20px, 4.5vw, 32px) !important; bottom: 20px !important; }
        }
    </style>
    <link rel="stylesheet" href="assets/css/loader.css">
    <link rel="stylesheet" href="assets/css/page-transition.css">
</head>
<body class="with-sidebar" data-turbo-cache="false">

<!-- ===== CORPORATE LOADER (fast — never blocks the page) ===== -->
<div class="vms-preloader" id="vmsPreloader">
    <div class="vms-preloader-logo">
        <img src="assets/newlogo.png" alt="VMS Go Vista">
    </div>
    <div class="vms-preloader-brand" style="font-family: Sunsive;">VMS Go Vista Pvt Ltd</div>
    <div class="vms-preloader-bar">
        <div class="vms-preloader-bar-fill" id="vmsLoaderFill"></div>
    </div>
</div>

<!-- ===== BROMORISE HEADER ===== -->
<header class="bromo-header header--sticky" id="bromoHeader">
    <a href="." class="bromo-logo">
        <img src="assets/newlogo.png" alt="VMS Go Vista" class="vms-logo-img">
        <span style="font-family: Sunsive;">VMS Go Vista Pvt Ltd</span>
    </a>
    <nav class="bromo-nav">
        <a href=".">Home</a>
        <a href="package" class="active">Packages</a>
        <a href="<?= SITE_URL ?>/service">Services</a>
        <a href="about">About Us</a>
        <a href="contact">Contact</a>
    </nav>
    <div class="bromo-book-btn"><a href="booking">
                Book now
            <span class="bromo-arrow"><i class="fa-regular fa-arrow-up-right"></i></span>
        </a>
    </div>
    <div class="bromo-mobile-menu" id="menu-btn" aria-label="Open menu">
        <span class="bromo-burger"><i></i><i></i><i></i></span>
    </div>
</header>

<!-- ===== MOBILE NAVIGATION ===== -->
<div class="bromo-mobile-nav-overlay" id="mobileNavOverlay"></div>
<div class="bromo-mobile-nav" id="mobileNav">
    <div class="bromo-mobile-nav-top">
        <a href="." class="bromo-mobile-nav-logo">
            <img src="assets/newlogo.png" alt="VMS Go Vista">
        </a>
        <button class="bromo-mobile-nav-close" id="mobileNavClose" aria-label="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <nav class="bromo-mobile-nav-links">
        <a href="."><i class="fa-solid fa-house"></i><span>Home</span></a>
        <a href="package" class="active"><i class="fa-solid fa-suitcase"></i><span>Packages</span></a>
        <a href="<?= SITE_URL ?>/service"><i class="fa-solid fa-concierge-bell"></i><span>Services</span></a>
        <a href="about"><i class="fa-solid fa-circle-info"></i><span>About Us</span></a>
        <a href="contact"><i class="fa-solid fa-envelope"></i><span>Contact</span></a>
    </nav>
    <div class="bromo-mobile-nav-foot">
        <a href="booking" class="bromo-mobile-nav-cta">
            <span>Book Now</span>
            <span class="bromo-arrow"><i class="fa-regular fa-arrow-up-right"></i></span>
        </a>
        <div class="bromo-mobile-nav-contact">
            <a href="tel:+919870182425"><i class="fa-solid fa-phone"></i> +91 98701 82425</a>
            <a href="mailto:info@vmsgovista.com"><i class="fa-solid fa-envelope"></i> info@vmsgovista.com</a>
        </div>
    </div>
</div>

<!-- breadcrumb -->
<div class="rts-breadcrumb-area two" data-bg-src="assets/images/package.webp">
    <div class="container h-100">
        <div class="breadcrumb-area-wrapper">
            <div class="nav-bread-crumb">
                <a href=".">Home</a>
                <span><i class="fa-regular fa-chevron-right"></i></span>
                <a href="#" class="current">Packages</a>
            </div>
            <h1 class="title">Adventure &amp; Nature</h1>
        </div>
    </div>
</div>
<center>
<!-- search bar -->
<div class="rts__section advance__search__section is__home__one home-hiking inner">
<div class="container">
        <form method="GET" action="package" class="form-area wow fadeInUp" data-wow-delay="0.6s">
            <div class="custom-select">
                <p class="tag">Destination</p>
                <span class="dropdown-icon"><svg width="12" height="8" viewBox="0 0 12 8" fill="none"><path d="M12 0L6 7.5L0 0H12Z" fill="currentColor"/></svg></span>
                <div class="custom-select-trigger"><?= $destination ? e($destination) : 'All Location' ?></div>
                <ul class="custom-options">
                    <li class="option <?= !$destination?'selected':'' ?>" data-value="">All Location</li>
                    <?php foreach($destinations as $d): ?>
                    <li class="option <?= $destination===$d['value']?'selected':'' ?>" data-value="<?= e($d['value']) ?>"><?= e($d['value']) ?></li>
                    <?php endforeach; ?>
                </ul>
                <input type="hidden" name="destination" value="<?= e($destination) ?>">
            </div>
            <div class="custom-select">
                <p class="tag">Trip Types</p>
                <span class="dropdown-icon"><svg width="12" height="8" viewBox="0 0 12 8" fill="none"><path d="M12 0L6 7.5L0 0H12Z" fill="currentColor"/></svg></span>
                <div class="custom-select-trigger"><?= $tour_type ? e($tour_type) : 'All Types' ?></div>
                <ul class="custom-options">
                    <li class="option <?= !$tour_type?'selected':'' ?>" data-value="">All Types</li>
                    <?php foreach($tourTypes as $tt): ?>
                    <li class="option <?= $tour_type===$tt['value']?'selected':'' ?>" data-value="<?= e($tt['value']) ?>"><?= e($tt['value']) ?></li>
                    <?php endforeach; ?>
                </ul>
                <input type="hidden" name="tour_type" value="<?= e($tour_type) ?>">
            </div>
            <div class="custom-select">
                <p class="tag">Duration</p>
                <span class="dropdown-icon"><svg width="12" height="8" viewBox="0 0 12 8" fill="none"><path d="M12 0L6 7.5L0 0H12Z" fill="currentColor"/></svg></span>
                <div class="custom-select-trigger"><?= $days ? e($days).' Days' : 'Any Duration' ?></div>
                <ul class="custom-options">
                    <li class="option <?= !$days?'selected':'' ?>" data-value="">Any Duration</li>
                    <li class="option <?= $days==='3'?'selected':'' ?>" data-value="3">Up to 3 Days</li>
                    <li class="option <?= $days==='5'?'selected':'' ?>" data-value="5">Up to 5 Days</li>
                    <li class="option <?= $days==='7'?'selected':'' ?>" data-value="7">Up to 7 Days</li>
                    <li class="option <?= $days==='10'?'selected':'' ?>" data-value="10">Up to 10 Days</li>
                    <li class="option <?= $days==='14'?'selected':'' ?>" data-value="14">Up to 14 Days</li>
                </ul>
                <input type="hidden" name="days" value="<?= e($days) ?>">
            </div>
            <div class="custom-select">
                <p class="tag">Show</p>
                <span class="dropdown-icon"><svg width="12" height="8" viewBox="0 0 12 8" fill="none"><path d="M12 0L6 7.5L0 0H12Z" fill="currentColor"/></svg></span>
                <div class="custom-select-trigger"><?= $perPage ?> / Page</div>
                <ul class="custom-options">
                    <li class="option <?= $perPage===9?'selected':'' ?>" data-value="9">9 / Page</li>
                    <li class="option <?= $perPage===10?'selected':'' ?>" data-value="10">10 / Page</li>
                    <li class="option <?= $perPage===12?'selected':'' ?>" data-value="12">12 / Page</li>
                    <li class="option <?= $perPage===18?'selected':'' ?>" data-value="18">18 / Page</li>
                    <li class="option <?= $perPage===24?'selected':'' ?>" data-value="24">24 / Page</li>
                    <li class="option <?= $perPage===48?'selected':'' ?>" data-value="48">48 / Page</li>
                    <li class="option <?= $perPage===50?'selected':'' ?>" data-value="50">50 / Page</li>
                    <li class="option <?= $perPage===100?'selected':'' ?>" data-value="100">100 / Page</li>
                </ul>
                <input type="hidden" name="per_page" value="<?= $perPage ?>">
                <input type="hidden" name="old_per_page" value="<?= $perPage ?>">
                <input type="hidden" name="min_price" value="<?= e($min_price) ?>">
                <input type="hidden" name="max_price" value="<?= e($max_price) ?>">
            </div>
            <div class="button-area">
                <button type="submit" class="vms-glass-btn">
                    Search
                    <span class="vms-glass-arrow"><i class="fa-solid fa-magnifying-glass"></i></span>
                </button>
            </div>
        </form>
    </div>
   
</div> </center>  


<!-- ===== TOUR LISTING ===== -->
<div class="rts-tour-area inner rts-section-gap">
    <div class="container">
        <div class="section-inner">
            <div class="row g-5">
                <!-- Left sidebar -->
                <div class="col-xl-3 col-lg-4">
                    <div class="sticky-top">
                        <div class="left-sidebar-area radius-10">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                                <h2 class="title" style="margin: 0;">All Tours</h2>
                                <button type="button" class="bromo-filter-btn" id="headerFilterBtn" aria-label="Toggle filters" style="margin: 0;">
                                    <i class="fa-solid fa-sliders"></i>
                                </button>
                            </div>
                            <?php if(!empty($destinations)): ?>
                            <div class="destination side-box">
                                <h6>Destination</h6>
                                <ul class="checkbox-list">
                                    <?php foreach(array_slice($destinations,0,6) as $d): ?>
                                    <li class="checkbox-item">
                                        <label class="checkbox-label">
                                            <input type="checkbox" <?= $destination===$d['value']?'checked':'' ?>
                                                onclick="vmsToggleFilter('destination', <?= e(json_encode($d['value'])) ?>)">
                                            <span><?= e($d['value']) ?></span>
                                        </label>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="package" class="btn">Show All</a>
                                <span class="cross"><i class="fa-regular fa-chevron-up"></i></span>
                            </div>
                            <?php endif; ?>
                            <div class="duration side-box">
                                <h6>Duration</h6>
                                <ul class="checkbox-list">
                                    <?php foreach([3,5,7,10,14] as $d): ?>
                                    <li class="checkbox-item">
                                        <label class="checkbox-label">
                                            <input type="checkbox" <?= $days==(string)$d?'checked':'' ?>
                                                onclick="vmsToggleFilter('days', '<?= $d ?>')">
                                            <span>Up to <?= $d ?> days</span>
                                        </label>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <span class="cross"><i class="fa-regular fa-chevron-up"></i></span>
                            </div>
                            <?php if(!empty($tourTypes)): ?>
                            <div class="trip-type side-box">
                                <h6>Trip Types</h6>
                                <ul class="checkbox-list">
                                    <?php foreach(array_slice($tourTypes,0,6) as $tt): ?>
                                    <li class="checkbox-item">
                                        <label class="checkbox-label">
                                            <input type="checkbox" <?= $tour_type===$tt['value']?'checked':'' ?>
                                                onclick="vmsToggleFilter('tour_type', <?= e(json_encode($tt['value'])) ?>)">
                                            <span><?= e($tt['value']) ?></span>
                                        </label>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <span class="cross"><i class="fa-regular fa-chevron-up"></i></span>
                            </div>
                            <?php endif; ?>
                            <?php if($destination || $tour_type || $days || $min_price || $max_price): ?>
                            <div style="padding:12px;">
                                <a href="package" class="vms-glass-btn w-100">Clear Filters <span class="vms-glass-arrow"><i class="fa-solid fa-rotate-left"></i></span></a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right content -->
                <div class="col-xl-9 col-lg-8">
                    <!-- filter top -->
                    <div class="filter-small-top-full mb--30">
                        <div class="left-filter">
                            <span><?= $total ?> tour<?= $total!=1?'s':'' ?> found</span>
                        </div>
                        <div class="right-filter">
                            <?php if($destination || $tour_type || $days): ?>
                            <a href="package" class="filter" style="cursor:pointer;">Clear Filter</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if(!empty($packages)): ?>
                    <div class="row g-5">
                        <?php foreach($packages as $pkg):
                            $imgUrl = packageImageUrl($pkg['main_image'] ?? null);
                            $slug   = e($pkg['slug'] ?? '');
                            $rating = (float)($pkg['rating'] ?? 5);
                        ?>
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="tour-wrapper radius-10 image-transform border">
                                <div class="image-area radius-6">
                                    <a href="package-details/<?= $slug ?>">
                                        <img class="hover-image" src="<?= e($imgUrl) ?>" alt="<?= e($pkg['title']) ?>">
                                        <span class="tag"><?= formatPrice((float)($pkg['price_discounted'] ?? $pkg['price'] ?? 0), $pkg['currency'] ?? 'INR') ?>/person</span>
                                    </a>
                                </div>
                                <div class="content">
                                    <h6 class="title"><a href="package-details/<?= $slug ?>"><?= e($pkg['title']) ?></a></h6>
                                    <ul class="meta-content">
                                        <li><i class="fa-light fa-location-dot"></i> <?= e($pkg['destination'] ?? '') ?></li>
                                        <li><i class="fa-light fa-clock"></i> <?= (int)($pkg['days'] ?? 0) ?> Days/<?= max(0,(int)($pkg['days']??0)-1) ?> Nights</li>
                                    </ul>
                                    <div class="star-rating-area">
                                        <ul class="stars">
                                            <li><?= starRatingHtml($rating) ?></li>
                                        </ul>
                                        <p class="desc">(<?= number_format($rating,1) ?>/5<?= !empty($pkg['review_count']) ? ' from '.(int)$pkg['review_count'].' reviews' : '' ?>)</p>
                                    </div>
                                    <div class="button-area">
                                        <a href="package-details/<?= $slug ?>" class="vms-glass-btn">View Details <span class="vms-glass-arrow"><i class="fa-solid fa-arrow-right"></i></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <!-- no packages found -->
                    <div class="text-center py-5">
                        <div style="font-size: 60px; color: #ccc; margin-bottom: 20px;">
                            <i class="fa-regular fa-face-frown"></i>
                        </div>
                        <h3 style="color: #333; margin-bottom: 10px;">No packages found</h3>
                        <p style="color: #666; margin-bottom: 20px;">We couldn't find any packages matching your search criteria.</p>
                        <a href="package" class="vms-glass-btn">View All Packages <span class="vms-glass-arrow"><i class="fa-solid fa-arrow-right"></i></span></a>
                    </div>
                    <?php endif; ?>

                    <!-- ===== PAGINATION ===== -->
                    <?php if($lastPage > 1): ?>
                    <div class="rts-course-pagination-area mt--50">
                        <ul>
                            <?php if($page > 1): ?>
                            <li><a href="package?page=<?= $page-1 ?>&destination=<?= urlencode($destination) ?>&tour_type=<?= urlencode($tour_type) ?>&days=<?= urlencode($days) ?>&min_price=<?= urlencode($min_price) ?>&max_price=<?= urlencode($max_price) ?>&per_page=<?= $perPage ?>"><button><i class="fa-regular fa-chevron-left"></i></button></a></li>
                            <?php endif; ?>
                            <?php for($p=max(1,$page-2); $p<=min($lastPage,$page+2); $p++): ?>
                            <li><a href="package?page=<?= $p ?>&destination=<?= urlencode($destination) ?>&tour_type=<?= urlencode($tour_type) ?>&days=<?= urlencode($days) ?>&min_price=<?= urlencode($min_price) ?>&max_price=<?= urlencode($max_price) ?>&per_page=<?= $perPage ?>"><button class="<?= $p==$page?'active':'' ?>"><?= str_pad($p,2,'0',STR_PAD_LEFT) ?></button></a></li>
                            <?php endfor; ?>
                            <?php if($page < $lastPage): ?>
                            <li><a href="package?page=<?= $page+1 ?>&destination=<?= urlencode($destination) ?>&tour_type=<?= urlencode($tour_type) ?>&days=<?= urlencode($days) ?>&min_price=<?= urlencode($min_price) ?>&max_price=<?= urlencode($max_price) ?>&per_page=<?= $perPage ?>"><button><i class="fa-regular fa-chevron-right"></i></button></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== CTA BANNER ===== -->
    <section class="bromo-cta-section">
        <div class="bromo-cta-wrap">
            <div class="bromo-cta-bg" style="background-image:url('assets/images/banner/bg-08.webp');"></div>
            <div class="bromo-cta-content">
                <span class="cta-eyebrow"><i class="fa-solid fa-paper-plane"></i> Let's Travel Together</span>
                <h2>Ready for Your<br>Next Adventure?</h2>
                <p class="cta-desc">Let VMS GO VISTA PVT LTD turn your travel dreams into unforgettable experiences — from weekend getaways to international holidays, honeymoons, and corporate trips.</p>
                <div class="cta-buttons">
                    <a href="contact" class="cta-btn-primary">
                        Get a Free Quote
                        <i class="fa-regular fa-arrow-up-right"></i>
                    </a>
                    <a href="tel:+919870182425" class="cta-btn-secondary">
                        <i class="fa-solid fa-phone"></i> Call Now
                    </a>
                </div>
            </div>
        </div>
    </section>

<!-- ===== FOOTER — COOLDOCK STYLE ===== -->
<footer class="vms-footer">

    <!-- Top floating pill (like Cooldock header on top) -->
    <div class="vms-footer-toppill">
        <div class="tp-logo">
     
        </div>
       </div>

    <!-- Content grid -->
    <div class="vms-footer-content">
        <!-- Brand Column -->
        <div class="vms-brand">
            <div class="vms-logo">
                <img src="assets/newlogo.png" alt="VMS Go Vista" style="height:42px;width:auto;border-radius:8px;">
                <span class="vms-logo-text" style="font-family: Sunsive;">VMS Go Vista Pvt Ltd</span>
            </div>
            <h3 class="vms-tagline">Your smart travel companion</h3>
            <p class="vms-desc">VMS Go Vista brings tours, destinations, deals, weather, quick bookings and more useful travel tools into one beautiful platform beside your dream journey.</p>
            <a href="package" class="vms-cta-btn">
                Explore Packages
                <span class="vms-btn-arrow"><i class="fa-regular fa-arrow-up-right"></i></span>
            </a>
        </div>

        <!-- Menu Column -->
        <div class="vms-footer-col">
            <h5>Menu</h5>
            <ul class="vms-footer-links">
                <li><a href=".">Home</a></li>
                <li><a href="package">Packages</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#">Pricing</a></li>
                <li><a href="#">Updates</a></li>
            </ul>
        </div>

        <!-- Navigation Column -->
        <div class="vms-footer-col">
            <h5>Navigation</h5>
            <ul class="vms-footer-links">
                <li><a href="contact">Contact</a></li>
                <li><a href="#">Share feedback</a></li>
                <li><a href="#">Roadmap</a></li>
                <li><a href="#">Privacy policy</a></li>
                <li><a href="#">Terms of service</a></li>
                <li><a href="#">Customer portal</a></li>
            </ul>
        </div>

        <!-- More products Column -->
        <div class="vms-footer-col">
            <h5>Our Services</h5>
            <ul class="vms-footer-links">
                <li><a href="package?destination=Kashmir">Kashmir Tours</a></li>
                <li><a href="package?destination=Kerala">Kerala Packages</a></li>
                <li><a href="package?destination=Goa">Goa Adventures</a></li>
                <li><a href="package?destination=Jaipur">Rajasthan Heritage</a></li>
                <li><a href="package?destination=Manali">Manali Trips</a></li>
                <li><a href="package?destination=Agra">Agra Tours</a></li>
            </ul>
        </div>
    </div>

    <!-- Bottom bar with copyright -->
    <div class="vms-footer-bottom">
        <p class="vms-credit" style="font-weight: 900; color: black;">&copy; <?= date('Y') ?> VMS Go Vista &middot; All rights reserved</p>
        <div class="vms-credit">
            <span style="font-weight: 900; color: black;">Crafted with dedication by</span>
            <span class="vms-author" style="font-weight: 900; color: black;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#e91e63" style="margin-right: 6px;"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                VMS Go Vista Team
            </span>
        </div>
    </div>

    <!-- Video Section with travel footage -->
    <div class="vms-video-section">
        <video src="assets/videofotte.mp4" class="vms-video-bg" autoplay muted loop playsinline></video>
        <div class="vms-video-gradient"></div>
        <div class="vms-big-text" style="font-family:Sunsive">VMS Go Vista Pvt Ltd</div>
    </div>
</footer>

<!-- mobile sidebar -->
<div id="side-bar" class="side-bar header-two header-eight">
    <button class="close-icon-menu"><i class="fa-sharp fa-thin fa-xmark"></i></button>
    <a class="logo" href="."><img src="assets/images/logo/02.svg" alt=""></a>
    <div class="mobile-menu-main">
        <nav class="nav-main mainmenu-nav mt--30">
            <ul class="mainmenu metismenu" id="mobile-menu-active">
                <li><a href="." class="main">Home</a></li>
                <li><a href="package" class="main">Tours</a></li>
                <li><a href="about" class="main">About</a></li>
                <li><a href="contact" class="main">Contact Us</a></li>
            </ul>
        </nav>
    </div>
</div>
<div id="anywhere-home"></div>
<div class="progress-wrap">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition:stroke-dashoffset 10ms linear 0s;stroke-dasharray:307.919,307.919;stroke-dashoffset:307.919;"></path>
    </svg>
</div>

<script defer src="assets/js/plugins/jquery.min.js"></script>
<script defer src="assets/js/plugins/bootstrap.min.js"></script>
<script defer src="assets/js/plugins/metismenu.js"></script>
<script defer src="assets/js/vendor/jqueryui.js"></script>
<script defer src="assets/js/vendor/waypoint.js"></script>
<script defer src="assets/js/plugins/swiper.js"></script>
<script defer src="assets/js/plugins/smoothscroll.js"></script>
<script defer src="assets/js/vendor/wow.js"></script>
<script defer src="assets/js/plugins/odometer.js"></script>
<script defer src="assets/js/plugins/magnific-popup.js"></script>
<script defer src="assets/js/plugins/isotop.js"></script>
<script defer src="assets/js/main.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.min.js"></script>
<script>
document.addEventListener('turbo:load',function(){
    document.body.classList.add('loaded');
    if(typeof WOW!=='undefined'){new WOW().init();}
});
window.addEventListener('pageshow',function(){ document.body.classList.add('loaded'); });

    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Menu
        const menuBtn = document.getElementById('menu-btn');
        const mobileNav = document.getElementById('mobileNav');
        const mobileNavOverlay = document.getElementById('mobileNavOverlay');
        const mobileNavClose = document.getElementById('mobileNavClose');
        if (menuBtn && mobileNav) {
            function openMobileNav() {
                mobileNav.classList.add('active');
                mobileNavOverlay.classList.add('active');
                menuBtn.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
            function closeMobileNav() {
                mobileNav.classList.remove('active');
                mobileNavOverlay.classList.remove('active');
                menuBtn.classList.remove('open');
                document.body.style.overflow = '';
            }
            menuBtn.addEventListener('click', function() { mobileNav.classList.contains('active') ? closeMobileNav() : openMobileNav(); });
            mobileNavClose.addEventListener('click', closeMobileNav);
            mobileNavOverlay.addEventListener('click', closeMobileNav);
            mobileNav.querySelectorAll('.bromo-mobile-nav-links a').forEach(link => {
                link.addEventListener('click', closeMobileNav);
            });
        }
        // Per-page selector: auto-submit the search form on selection
        document.querySelectorAll('.custom-select').forEach((sel) => {
            const hidden = sel.querySelector('input[name="per_page"]');
            if (!hidden) return;
            sel.querySelectorAll('.option').forEach((opt) => {
                opt.addEventListener('click', function () {
                    setTimeout(() => { sel.closest('form') && sel.closest('form').submit(); }, 80);
                });
            });
        });

        // Header Filter Button (desktop toggle)
        const headerFilterBtn = document.getElementById('headerFilterBtn');
        const filterSection = document.querySelector('.advance__search__section.is__home__one.home-hiking.inner');

        function toggleFilterSection() {
            if (!filterSection) return;
            filterSection.classList.toggle('vms-filter-visible');
            if (headerFilterBtn) headerFilterBtn.classList.toggle('active');
        }

        if (headerFilterBtn) {
            headerFilterBtn.addEventListener('click', toggleFilterSection);
        }
    });

    // ── Corporate Loader (fast — hides ~250ms after DOM ready, never blocks) ──
    (function () {
        var overlay = document.getElementById('vmsPreloader');
        var done = false;
        function removeLoader() {
            if (done || !overlay) return; done = true;
            overlay.classList.add('hidden');
            setTimeout(function () { if (overlay.parentNode) overlay.remove(); }, 500);
        }
        function fastHide() { setTimeout(removeLoader, 250); }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fastHide);
        } else { fastHide(); }
        window.addEventListener('pageshow', removeLoader);  // back/forward cache restore
        window.addEventListener('load', removeLoader);
        setTimeout(removeLoader, 1200);  // absolute max
    })();

    // ── Filter toggle: clicking an active filter REMOVES it, clicking an
    //    inactive one ADDS it (fixes 'remove filter not working') ──
    window.vmsToggleFilter = function (param, value) {
        var params = new URLSearchParams(window.location.search);
        if (params.get(param) === value) {
            params.delete(param);
        } else {
            params.set(param, value);
        }
        params.delete('page');
        var qs = params.toString();
        window.location.href = 'package' + (qs ? '?' + qs : '');
    };
</script>
<script src="assets/js/page-transition.js"></script>
</body>
</html>
