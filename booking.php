<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch packages for dropdown
$packages = getAllPackages();

// Preselect package from ?package=slug
$preselectedSlug = trim($_GET['package'] ?? '');
$preselectedId   = 0;
$preselectedPkg  = null;
if ($preselectedSlug !== '') {
    $preselectedPkg = getPackageBySlug($preselectedSlug);
    if ($preselectedPkg) {
        $preselectedId = (int)$preselectedPkg['id'];
    }
}

// ── Batch-load package details for the wizard (single queries) ──
$pkgIds = array_column($packages, 'id');
$inclMap = $exclMap = $highMap = $itinMap = $imgMap = $faqMap = $infoMap = [];
if ($pkgIds) {
    $in = implode(',', array_fill(0, count($pkgIds), '?'));
    foreach (fetchAll("SELECT package_id, item FROM package_inclusions WHERE package_id IN ($in) ORDER BY package_id, sort_order", $pkgIds) as $r) {
        $inclMap[$r['package_id']][] = $r['item'];
    }
    foreach (fetchAll("SELECT package_id, item FROM package_exclusions WHERE package_id IN ($in) ORDER BY package_id, sort_order", $pkgIds) as $r) {
        $exclMap[$r['package_id']][] = $r['item'];
    }
    foreach (fetchAll("SELECT package_id, item FROM package_highlights WHERE package_id IN ($in) ORDER BY package_id, sort_order", $pkgIds) as $r) {
        $highMap[$r['package_id']][] = $r['item'];
    }
    foreach (fetchAll("SELECT package_id, day_number, title, description, meals, accommodation FROM package_itinerary WHERE package_id IN ($in) ORDER BY package_id, day_number, sort_order", $pkgIds) as $r) {
        $itinMap[$r['package_id']][] = [
            'day'   => (int)$r['day_number'],
            'title' => $r['title'],
            'desc'  => $r['description'] ?? '',
            'meals' => $r['meals'] ?? '',
            'stay'  => $r['accommodation'] ?? '',
        ];
    }
    foreach (fetchAll("SELECT package_id, image_path FROM package_images WHERE package_id IN ($in) ORDER BY package_id, sort_order, id", $pkgIds) as $r) {
        $imgMap[$r['package_id']][] = $r['image_path'];
    }
    foreach (fetchAll("SELECT package_id, question, answer FROM package_faqs WHERE package_id IN ($in) ORDER BY package_id, sort_order, id", $pkgIds) as $r) {
        $faqMap[$r['package_id']][] = ['q' => $r['question'], 'a' => $r['answer']];
    }
    foreach (fetchAll("SELECT package_id, info_type, title, content FROM package_info WHERE package_id IN ($in) ORDER BY package_id, sort_order, id", $pkgIds) as $r) {
        $infoMap[$r['package_id']][] = [
            'type'    => $r['info_type'],
            'title'   => $r['title'] ?: ucwords(str_replace('_', ' ', $r['info_type'])),
            'content' => $r['content'],
        ];
    }
}
$pkgDetails = [];
foreach ($packages as $pkg) {
    $pid = (int)$pkg['id'];
    $pkgDetails[$pid] = [
        'id'             => $pid,
        'title'          => $pkg['title'],
        'days'           => (int)($pkg['days'] ?? 0),
        'nights'         => (int)($pkg['nights'] ?? 0),
        'price'          => (float)($pkg['price_discounted'] ?? $pkg['price_original']),
        'currency'       => $pkg['currency'] ?? 'INR',
        'dest'           => $pkg['destination'] ?: ($pkg['country'] ?? ''),
        'img'            => packageCoverImageUrl($pkg),
        'overview'       => trim((string)($pkg['overview'] ?: ($pkg['short_desc'] ?? ''))),
        'inclusions'     => $inclMap[$pid] ?? [],
        'exclusions'     => $exclMap[$pid] ?? [],
        'highlights'     => $highMap[$pid] ?? [],
        'itinerary'      => $itinMap[$pid] ?? [],
        'gallery'        => array_map(function ($p) { return packageImageUrl($p); }, $imgMap[$pid] ?? []),
        'faqs'           => $faqMap[$pid] ?? [],
        'info'           => $infoMap[$pid] ?? [],
        'rating'         => (float)($pkg['rating'] ?? 0),
        'review_count'   => (int)($pkg['review_count'] ?? 0),
        'price_original' => (float)($pkg['price_original'] ?? 0),
        'discount_pct'   => (int)($pkg['discount_pct'] ?? 0),
        'price_per_child'=> (float)($pkg['price_per_child'] ?? 0),
        'transportation' => $pkg['transportation'] ?? '',
        'accommodation'  => $pkg['accommodation'] ?? '',
        'meals'          => $pkg['meals'] ?? '',
        'tour_type'      => $pkg['tour_type'] ?? '',
        'language'       => $pkg['language'] ?? '',
        'group_size_min' => (int)($pkg['group_size_min'] ?? 0),
        'group_size_max' => (int)($pkg['group_size_max'] ?? 0),
        'min_age'        => $pkg['min_age'] ?? '',
        'max_age'        => $pkg['max_age'] ?? '',
        'max_altitude'   => $pkg['max_altitude'] ?? '',
        'best_season'    => $pkg['best_season'] ?? '',
        'fitness_level'  => $pkg['fitness_level'] ?? '',
        'departure_from' => $pkg['departure_from'] ?? '',
    ];
}
$pkgDetailsJson = json_encode($pkgDetails, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

// Get flash message
$flash = getFlash();
$bookingSuccess = isset($_GET['success']) && (int)$_GET['success'] === 1;

// Short description helper for cards
function bkShortDesc(?string $desc): string {
    $d = trim((string)$desc);
    if ($d === '') return 'Discover a handcrafted itinerary with stays, experiences and local insights.';
    $d = strip_tags($d);
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($d, 0, 96, '…');
    }
    return (strlen($d) > 96 ? substr($d, 0, 96) . '…' : $d);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book your dream holiday with VMS Go Vista — domestic &amp; international tour packages with expert planning, best prices and 24/7 support.">
    <link rel="canonical" href="https://vmsgovista.com/booking">
    <title>Book Your Trip – VMS Go Vista</title>
    <meta property="og:type" content="website">
    <meta property="og:title" content="Book Your Trip – VMS Go Vista">
    <meta property="og:description" content="Book your dream holiday with VMS Go Vista.">
    <meta property="og:url" content="https://vmsgovista.com/booking">
    <meta property="og:site_name" content="VMS Go Vista">
    <!-- PERFORMANCE: Preconnect & DNS-prefetch -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">

    <!-- PERFORMANCE: Critical CSS (render-blocking) -->
    <link rel="stylesheet" href="assets/css/plugins/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bromo-theme.css">
    <link rel="stylesheet" href="assets/css/plugins/swiper.min.css">
    <link rel="stylesheet" href="assets/fonts/custom-font.css">
    <link rel="stylesheet" href="assets/css/plugins/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/plugins/metismenu.css">
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/vendor/animate.css">
    <link rel="stylesheet" href="assets/css/plugins/odometer.css">
    <link rel="stylesheet" href="assets/css/plugins/nice-select.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== HEADER (matches index-three.php) ===== */
        .bromo-header .vms-logo-img { height: 80px; width: auto; }
        .bromo-logo span { white-space: nowrap; font-size: 16px; }
        @media (min-width: 768px) and (max-width: 1400px) {
            .bromo-logo span { display: none !important; }
        }
        @media (max-width: 767px) {
            .bromo-logo span { display: block !important; font-size: 12px !important; white-space: nowrap !important; }
            .bromo-header .bromo-logo { gap: 6px; }
            .bromo-header .vms-logo-img { height: 50px; }
            .bromo-nav { display: none; }
            .bromo-book-btn { display: none; }
            .bromo-mobile-menu { display: flex; }
        }
        @media (max-width: 575px) {
            .bromo-header .vms-logo-img { height: 42px; }
            .bromo-logo span { font-size: 11px !important; }
        }
        .bromo-book-btn a:hover .bromo-arrow{background:#003A59;color:#fff;}
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
        .bromo-header.header--sticky.sticky .bromo-logo span{color:#003A59;}
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
        @media(max-width:991px){
            .bromo-header.header--sticky.sticky{display:flex;justify-content:space-between;grid-template-columns:none;}
        }
        @keyframes bromoStickyIn{0%{transform:translateY(-15px)}100%{transform:translateY(0)}}

        /* ===== BOOKING HERO ===== */
        .booking-hero {
            position: relative;
            padding: 150px 0 110px;
            background: linear-gradient(135deg, #003A59 0%, #0a557c 55%, #0f6a94 100%);
            color: white;
            text-align: center;
            overflow: hidden;
        }
        .booking-hero::before {
            content: '';
            position: absolute;
            top: -40%; left: -20%; width: 70%; height: 140%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.10), transparent 60%);
            animation: heroFloat 14s ease-in-out infinite;
        }
        .booking-hero::after {
            content: '';
            position: absolute;
            bottom: -50%; right: -15%; width: 60%; height: 120%;
            background: radial-gradient(circle at 70% 70%, rgba(255,255,255,0.07), transparent 60%);
            animation: heroFloat 18s ease-in-out infinite reverse;
        }
        @keyframes heroFloat {
            0%,100% { transform: translate(0,0) scale(1); }
            50% { transform: translate(4%, 3%) scale(1.05); }
        }
        /* Hero background video + shade */
        .booking-hero-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        .booking-hero-shade {
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(180deg, rgba(0,34,52,0.78) 0%, rgba(0,58,89,0.6) 45%, rgba(2,18,30,0.88) 100%);
        }
        .booking-hero-content { position: relative; z-index: 2; max-width: 800px; margin: 0 auto; padding: 0 24px; }
        .booking-hero-badge {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 9px 22px;
            background: rgba(255,255,255,0.14);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.24);
            border-radius: 999px;
            font-size: 14px; font-weight: 600; letter-spacing: .02em;
            margin-bottom: 26px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            animation: fadeInDown .7s ease both;
        }
        .booking-hero-badge i { color: #8CC7E8; }
        .booking-hero h1 {
            font-size: clamp(38px, 5.5vw, 60px);
            font-weight: 800;
            margin: 0 0 18px;
            color: #fff;
            line-height: 1.08;
            letter-spacing: -0.5px;
            animation: fadeInUp .8s ease .15s both;
        }
        .booking-hero h1 em { font-style: normal; color: #8CC7E8; }
        .booking-hero p {
            font-size: 17px;
            opacity: .94;
            max-width: 620px;
            margin: 0 auto 36px;
            line-height: 1.65;
            animation: fadeInUp .8s ease .3s both;
        }
        .booking-hero-stats {
            display: flex; justify-content: center; gap: 12px;
            flex-wrap: wrap;
            animation: fadeInUp .8s ease .45s both;
        }
        .booking-hero-stat {
            min-width: 150px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 16px 22px;
            text-align: center;
            transition: transform .25s ease, background .25s ease;
        }
        .booking-hero-stat:hover { transform: translateY(-4px); background: rgba(255,255,255,0.16); }
        .booking-hero-stat-number { font-size: 30px; font-weight: 800; color: #8CC7E8; line-height: 1.1; }
        .booking-hero-stat-label { font-size: 12.5px; opacity: .85; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== BOOKING SECTION ===== */
        .booking-section {
            padding: 90px 0 110px;
            background: linear-gradient(180deg, #f6f9fb 0%, #ffffff 100%);
            position: relative;
        }
        .booking-container { max-width: 1240px; margin: 0 auto; padding: 0 24px; }

        .booking-form-header { margin-bottom: 30px; }
        .booking-form-header .bk-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 12.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase;
            color: #0f6a94; margin-bottom: 10px;
        }
        .booking-form-header h2 { font-size: 30px; font-weight: 800; color: #003A59; margin: 0 0 10px; letter-spacing: -0.4px; }
        .booking-form-header p { color: #667085; font-size: 15px; line-height: 1.65; margin: 0; }

        .booking-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .booking-form-group { margin-bottom: 22px; }
        .booking-form-group.full-width { grid-column: span 2; }
        .booking-form-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 13.5px; font-weight: 700; color: #003A59;
            margin-bottom: 9px;
        }
        .booking-form-label i { color: #0f6a94; font-size: 15px; }
        .booking-form-label .req { color: #0f6a94; }
        .booking-form-input, .booking-form-select, .booking-form-textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e6ecf2;
            border-radius: 14px;
            font-size: 15px;
            color: #1d2939;
            transition: all .25s ease;
            background: #fbfdff;
        }
        .booking-form-input:focus, .booking-form-select:focus, .booking-form-textarea:focus {
            outline: none;
            border-color: #0f6a94;
            box-shadow: 0 0 0 4px rgba(15,106,148,0.10);
            background: #fff;
        }
        .booking-form-input::placeholder, .booking-form-textarea::placeholder { color: #98a2b3; }
        .booking-form-textarea { resize: vertical; min-height: 100px; }

        .booking-trust {
            display: flex; justify-content: center; gap: 26px; flex-wrap: wrap;
            margin-top: 16px; font-size: 12.5px; color: #98a2b3;
        }
        .booking-trust span { display: inline-flex; align-items: center; gap: 6px; }
        .booking-trust i { color: #12b76a; }

        /* ===== STEPPER ===== */
        .bk-stepper {
            display: flex; align-items: center; justify-content: center;
            gap: 10px; margin-bottom: 40px;
        }
        .bk-step { display: flex; align-items: center; gap: 9px; }
        .bk-step-num {
            width: 38px; height: 38px; border-radius: 50%;
            background: #eef2f6; color: #98a2b3;
            font-weight: 800; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #e0e7ee;
            transition: all .3s ease;
            flex-shrink: 0;
        }
        .bk-step-label { font-size: 12.5px; font-weight: 700; color: #98a2b3; transition: color .3s ease; white-space: nowrap; }
        .bk-step.active .bk-step-num { background: #003A59; border-color: #003A59; color: #fff; box-shadow: 0 6px 18px -4px rgba(0,58,89,0.5); }
        .bk-step.active .bk-step-label { color: #003A59; }
        .bk-step.done .bk-step-num { background: #12b76a; border-color: #12b76a; color: #fff; }
        .bk-step.done .bk-step-label { color: #12b76a; }
        .bk-step-line { width: 44px; height: 2px; background: #e0e7ee; border-radius: 2px; position: relative; overflow: hidden; flex-shrink: 0; }
        .bk-step-line::after {
            content: ''; position: absolute; inset: 0;
            background: #12b76a; transform: scaleX(0); transform-origin: left;
            transition: transform .4s ease;
        }
        .bk-step-line.done::after { transform: scaleX(1); }

        /* ===== PACKAGE SEARCH FILTER (premium) ===== */
        .bk-search-wrapper {
            margin-bottom: 28px;
            position: relative;
            background: #fff;
            border: 1.5px solid #e2ecf3;
            border-radius: 14px;
            padding: 0;
            box-shadow: 0 4px 20px -8px rgba(0,58,89,0.12);
            transition: border-color .3s ease, box-shadow .3s ease;
            overflow: hidden;
        }
        .bk-search-wrapper:focus-within {
            border-color: rgba(15,106,148,0.5);
            box-shadow: 0 6px 28px -8px rgba(0,58,89,0.18);
        }
        .bk-search-field {
            display: flex;
            align-items: center;
        }
        .bk-search-icon {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #003A59, #0f6a94);
            color: #fff;
            font-size: 16px;
            border-radius: 0;
            pointer-events: none;
            transition: background .3s ease;
        }
        .bk-search-wrapper:focus-within .bk-search-icon {
            background: linear-gradient(135deg, #0f6a94, #1a8bc4);
        }
        .bk-search-input {
            width: 100%;
            padding: 15px 50px 15px 16px;
            border: none;
            border-radius: 0;
            font-size: 15px;
            font-weight: 500;
            color: #1d2939;
            background: transparent;
            transition: all .3s ease;
        }
        .bk-search-input::placeholder {
            color: #98a2b3;
            font-weight: 500;
        }
        .bk-search-input:focus {
            outline: none;
        }
        .bk-search-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #f0f4f8;
            border: 1px solid #dbe4ec;
            color: #667085;
            cursor: pointer;
            font-size: 12px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all .25s ease;
            z-index: 1;
        }
        .bk-search-clear:hover {
            background: #003A59;
            color: #fff;
            border-color: #003A59;
            transform: translateY(-50%) scale(1.12);
        }
        .bk-search-wrapper.has-value .bk-search-clear {
            display: flex;
        }
        .bk-no-results {
            text-align: center;
            padding: 50px 24px;
            color: #667085;
            font-size: 15px;
            display: none;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 16px;
            border: 1px dashed #d5e6ef;
        }
        .bk-no-results i {
            font-size: 52px;
            color: #cbd5e1;
            margin-bottom: 16px;
            display: block;
        }
        .bk-no-results p {
            margin: 0;
            font-weight: 500;
        }

        /* ===== WIZARD CARD ===== */
        .bk-wizard {
            max-width: 940px; margin: 0 auto;
            background: #fff;
            border-radius: 26px;
            box-shadow: 0 24px 70px -20px rgba(0,58,89,0.16);
            border: 1px solid rgba(0,58,89,0.08);
            position: relative;
            overflow: hidden;
            scroll-margin-top: 120px;
        }
        .bk-wizard::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 5px;
            background: linear-gradient(90deg, #003A59, #0f6a94, #8CC7E8);
        }
        .bk-panel { display: none; padding: 44px; }
        .bk-panel.active { display: block; animation: bkPanelIn .4s ease both; }
        @keyframes bkPanelIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== STEP 1 — PACKAGE CARDS ===== */
        .bk-pkg-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
        .bk-pkg-card {
            position: relative;
            background: #fff;
            border: 2px solid #e6ecf2;
            border-radius: 20px;
            overflow: hidden;
            cursor: pointer;
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .bk-pkg-card:hover { transform: translateY(-5px); box-shadow: 0 16px 40px -14px rgba(0,58,89,0.25); border-color: #0f6a94; }
        .bk-pkg-card.selected {
            border-color: #003A59;
            box-shadow: 0 0 0 4px rgba(0,58,89,0.12), 0 16px 40px -14px rgba(0,58,89,0.25);
            transform: translateY(-3px);
        }
        .bk-pkg-card img { width: 100%; height: 148px; object-fit: cover; display: block; }
        .bk-pkg-badge {
            position: absolute; top: 12px; left: 12px;
            background: rgba(0,58,89,0.88); color: #fff;
            font-size: 11.5px; font-weight: 700;
            padding: 5px 11px; border-radius: 999px;
            display: inline-flex; align-items: center; gap: 5px;
            backdrop-filter: blur(6px);
        }
        .bk-pkg-badge i { color: #8CC7E8; }
        .bk-pkg-check {
            position: absolute; top: 12px; right: 12px;
            width: 28px; height: 28px; border-radius: 50%;
            background: #12b76a; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
            opacity: 0; transform: scale(.5);
            transition: all .25s ease;
            box-shadow: 0 4px 12px -2px rgba(18,183,106,0.6);
        }
        .bk-pkg-card.selected .bk-pkg-check { opacity: 1; transform: scale(1); }
        .bk-pkg-body { padding: 16px 18px 18px; }
        .bk-pkg-title { font-size: 16px; font-weight: 800; color: #003A59; line-height: 1.3; margin: 0 0 7px; }
        .bk-pkg-dest { font-size: 12.5px; color: #0f6a94; font-weight: 600; display: flex; align-items: center; gap: 6px; margin-bottom: 9px; }
        .bk-pkg-desc {
            font-size: 13px; color: #667085; line-height: 1.55; margin: 0 0 14px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .bk-pkg-price { display: flex; align-items: baseline; gap: 7px; }
        .bk-pkg-price .amount { font-size: 22px; font-weight: 800; color: #003A59; letter-spacing: -0.3px; }
        .bk-pkg-price .per { font-size: 12px; color: #98a2b3; }

        /* ===== STEP 2 — PACKAGE DETAILS (TABBED) ===== */
        .bk-details-hero { position: relative; border-radius: 20px; overflow: hidden; margin-bottom: 26px; }
        .bk-details-hero img { width: 100%; height: 280px; object-fit: cover; display: block; }
        .bk-details-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.05) 0%, rgba(0,58,89,0.85) 100%);
        }
        .bk-details-info { position: absolute; left: 0; right: 0; bottom: 0; padding: 24px; color: #fff; }
        .bk-details-info .bk-dt-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
        .bk-details-info .bk-dt-badge {
            background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(8px); border-radius: 999px;
            padding: 5px 12px; font-size: 12px; font-weight: 700;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .bk-details-info h3 { font-size: 26px; font-weight: 800; margin: 0 0 8px; line-height: 1.2; letter-spacing: -0.3px; }
        .bk-details-info .bk-dt-price { display: flex; align-items: baseline; gap: 8px; }
        .bk-details-info .bk-dt-price .amount { font-size: 30px; font-weight: 800; color: #8CC7E8; }
        .bk-details-info .bk-dt-price .per { font-size: 13px; opacity: .85; }

        /* Package Info Grid (Accommodation, Duration, etc.) */
        .bk-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 26px;
        }
        .bk-info-item {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fbfdff;
            border: 1px solid #e6ecf2;
            border-radius: 14px;
            padding: 16px 18px;
            transition: all .25s ease;
        }
        .bk-info-item:hover {
            border-color: #0f6a94;
            background: #fff;
            box-shadow: 0 8px 24px -12px rgba(0,58,89,0.15);
        }
        .bk-info-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(0,58,89,0.08), rgba(15,106,148,0.08));
            color: #0f6a94;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .bk-info-text { flex: 1; min-width: 0; }
        .bk-info-label { font-size: 11.5px; font-weight: 700; color: #98a2b3; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 3px; }
        .bk-info-value { font-size: 14px; font-weight: 700; color: #003A59; line-height: 1.3; }

        /* Tabs Navigation */
        .bk-tabs-nav {
            display: flex;
            gap: 6px;
            border-bottom: 2px solid #e6ecf2;
            margin-bottom: 24px;
            overflow-x: auto;
            padding-bottom: 2px;
        }
        .bk-tab-btn {
            padding: 12px 20px;
            background: transparent;
            border: none;
            border-radius: 10px 10px 0 0;
            font-size: 14px;
            font-weight: 600;
            color: #667085;
            cursor: pointer;
            transition: all .25s ease;
            white-space: nowrap;
            position: relative;
        }
        .bk-tab-btn:hover { color: #003A59; background: #f0f6fa; }
        .bk-tab-btn.active {
            color: #003A59;
            background: #fff;
            font-weight: 700;
        }
        .bk-tab-btn.active::after {
            content: '';
            position: absolute; bottom: -4px; left: 0; right: 0;
            height: 3px; background: #003A59;
            border-radius: 3px 3px 0 0;
        }

        /* Tab Content */
        .bk-tab-content { display: none; animation: bkFadeIn .3s ease both; }
        .bk-tab-content.active { display: block; }
        @keyframes bkFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        .bk-dt-section { margin-bottom: 24px; }
        .bk-dt-section:last-child { margin-bottom: 0; }
        .bk-dt-section h4 {
            font-size: 16px; font-weight: 800; color: #003A59;
            display: flex; align-items: center; gap: 10px; margin: 0 0 16px;
        }
        .bk-dt-section h4 .ic {
            width: 32px; height: 32px; border-radius: 10px;
            background: linear-gradient(135deg, rgba(0,58,89,0.1), rgba(15,106,148,0.1));
            color: #0f6a94;
            display: inline-flex; align-items: center; justify-content: center; font-size: 14px;
        }
        .bk-dt-overview { font-size: 14.5px; line-height: 1.75; color: #475467; margin: 0; }
        .bk-dt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .bk-dt-list { list-style: none; padding: 0; margin: 0; }
        .bk-dt-list li {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 8px 0; font-size: 14px; color: #475467; line-height: 1.5;
        }
        .bk-dt-list li i { margin-top: 2px; font-size: 13px; flex-shrink: 0; }
        .bk-dt-list.incl li i { color: #12b76a; }
        .bk-dt-list.excl li i { color: #f97066; }
        .bk-highlight-chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .bk-highlight-chips span {
            background: linear-gradient(135deg, #f0f6fa, #e8f4f9);
            border: 1px solid #d5e6ef;
            color: #0f6a94;
            font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 999px;
        }
        .bk-itinerary { list-style: none; padding: 0; margin: 0; position: relative; }
        .bk-itinerary::before {
            content: ''; position: absolute; left: 20px; top: 8px; bottom: 8px;
            width: 2px; background: #e0e7ee; border-radius: 2px;
        }
        .bk-itinerary li { position: relative; padding: 0 0 20px 52px; }
        .bk-itinerary li:last-child { padding-bottom: 0; }
        .bk-itin-day {
            position: absolute; left: 0; top: 0;
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #003A59, #0f6a94); color: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            line-height: 1; gap: 2px;
            box-shadow: 0 4px 12px -2px rgba(0,58,89,0.4);
            z-index: 1;
        }
        .bk-day-lbl {
            font-size: 8px; font-weight: 700;
            letter-spacing: 1.2px; text-transform: uppercase;
            opacity: .85;
        }
        .bk-day-num {
            font-size: 15px; font-weight: 800; line-height: 1;
        }
        .bk-itin-title { font-size: 15px; font-weight: 700; color: #1d2939; margin-bottom: 4px; }
        .bk-itin-desc { font-size: 13.5px; color: #667085; line-height: 1.6; margin: 0; }
        .bk-itin-tags { display: flex; flex-wrap: wrap; gap: 8px; margin: 5px 0 8px; }
        .bk-itin-tag {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600; color: #0f6a94;
            background: #eef5f9; border: 1px solid #d5e6ef;
            border-radius: 999px; padding: 4px 11px;
        }
        .bk-itin-tag i { font-size: 11px; }

        /* Gallery strip */
        .bk-gallery-strip {
            display: flex; gap: 10px; flex-wrap: wrap;
            margin: -6px 0 26px;
        }
        .bk-gallery-thumb {
            width: 74px; height: 58px;
            border-radius: 12px; overflow: hidden;
            cursor: pointer; opacity: .65;
            border: 2px solid transparent;
            transition: all .25s ease;
        }
        .bk-gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .bk-gallery-thumb:hover { opacity: .9; transform: translateY(-2px); }
        .bk-gallery-thumb.active {
            opacity: 1;
            border-color: #003A59;
            box-shadow: 0 6px 16px -6px rgba(0,58,89,0.45);
        }

        /* Rating + discount badges on details hero */
        .bk-dt-rate i { color: #8CC7E8; margin-right: 2px; }
        .bk-dt-off {
            background: #8CC7E8; color: #003A59;
            font-size: 12px; font-weight: 800;
            padding: 4px 10px; border-radius: 999px;
            margin-left: 4px;
        }

        /* FAQ / Important-info accordion */
        .bk-faq { display: flex; flex-direction: column; gap: 10px; }
        .bk-faq-item {
            border: 1px solid #e6ecf2;
            border-radius: 14px;
            background: #fbfdff;
            overflow: hidden;
            transition: border-color .25s ease, box-shadow .25s ease;
        }
        .bk-faq-item.open { border-color: #0f6a94; box-shadow: 0 8px 22px -12px rgba(0,58,89,0.2); }
        .bk-faq-q {
            width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 18px;
            background: none; border: none; cursor: pointer;
            font-size: 14px; font-weight: 700; color: #1d2939; text-align: left;
            transition: color .2s ease;
        }
        .bk-faq-q:hover { color: #003A59; }
        .bk-faq-q i {
            flex-shrink: 0; color: #0f6a94;
            transition: transform .3s ease;
        }
        .bk-faq-item.open .bk-faq-q i { transform: rotate(180deg); }
        .bk-faq-a {
            max-height: 0; overflow: hidden;
            transition: max-height .35s ease;
            padding: 0 18px;
        }
        .bk-faq-item.open .bk-faq-a { max-height: 600px; padding-bottom: 16px; }
        .bk-faq-a p { font-size: 13.5px; color: #667085; line-height: 1.7; margin: 0; }

        /* ===== NAV BUTTONS ===== */
        .bk-nav { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-top: 34px; }
        .bk-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 15px; font-weight: 700;
            border: none; cursor: pointer;
            transition: all .25s ease;
            text-decoration: none;
        }
        .bk-btn-primary {
            background: linear-gradient(135deg, #003A59 0%, #0f6a94 100%);
            color: #fff;
            box-shadow: 0 10px 26px -8px rgba(0,58,89,0.5);
        }
        .bk-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 36px -8px rgba(0,58,89,0.55); }
        .bk-btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }
        .bk-btn-ghost {
            background: #f0f4f8; color: #003A59;
            border: 1px solid #dbe4ec;
        }
        .bk-btn-ghost:hover { background: #e2ecf3; }
        .bk-btn-submit { width: 100%; padding: 17px 32px; font-size: 16.5px; }
        .bk-btn .spinner {
            width: 20px; height: 20px;
            border: 2.5px solid rgba(255,255,255,0.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: bkSpin .7s linear infinite;
            display: none;
        }
        .bk-btn.loading .spinner { display: inline-block; }
        .bk-btn.loading .btn-label { display: none; }
        @keyframes bkSpin { to { transform: rotate(360deg); } }

        /* ===== SELECTION CHIP (step 3) ===== */
        .bk-chip {
            display: flex; align-items: center; gap: 14px;
            background: linear-gradient(135deg, #f2f8fb, #eef5f9);
            border: 1px solid #d5e6ef;
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 26px;
        }
        .bk-chip img { width: 54px; height: 54px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
        .bk-chip .bk-chip-info { flex: 1; min-width: 0; }
        .bk-chip .bk-chip-name { font-weight: 800; color: #003A59; font-size: 14.5px; line-height: 1.3; }
        .bk-chip .bk-chip-meta { font-size: 12px; color: #667085; margin-top: 3px; }
        .bk-chip .bk-chip-price { font-size: 16px; font-weight: 800; color: #0f6a94; white-space: nowrap; }

        /* ===== STEP 4 — REVIEW ===== */
        .bk-review { background: #fbfdff; border: 1px solid #e6ecf2; border-radius: 16px; padding: 8px 22px; }
        .bk-review-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 13px 0; border-bottom: 1px dashed #e6ecf2;
            font-size: 14.5px; gap: 20px;
        }
        .bk-review-row:last-child { border-bottom: none; }
        .bk-review-row .k { color: #667085; font-weight: 600; flex-shrink: 0; }
        .bk-review-row .v { font-weight: 800; color: #1d2939; text-align: right; }
        .bk-total {
            display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(135deg, #003A59, #0f6a94);
            color: #fff; border-radius: 16px; padding: 20px 24px; margin-top: 18px;
        }
        .bk-total .lbl { font-size: 14px; font-weight: 700; opacity: .9; }
        .bk-total .val { font-size: 28px; font-weight: 800; color: #8CC7E8; letter-spacing: -0.3px; }
        
        /* Price Breakdown */
        .bk-price-breakdown {
            background: #f0f6fa;
            border: 1px solid #d5e6ef;
            border-radius: 14px;
            padding: 16px 20px;
            margin-top: 14px;
        }
        .bk-breakdown-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        .bk-breakdown-label {
            font-size: 13px;
            color: #667085;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .bk-breakdown-label i {
            color: #0f6a94;
            font-size: 12px;
        }
        .bk-breakdown-value {
            font-size: 14px;
            font-weight: 700;
            color: #003A59;
        }
        .bk-breakdown-divider {
            height: 1px;
            background: #d5e6ef;
            margin: 8px 0;
        }
        .bk-breakdown-total {
            padding-top: 4px;
        }
        .bk-breakdown-total .bk-breakdown-label {
            font-size: 14px;
            color: #003A59;
        }
        .bk-breakdown-total .bk-breakdown-value {
            font-size: 18px;
            font-weight: 800;
            color: #8CC7E8;
        }
        .bk-hint { font-size: 12.5px; color: #98a2b3; text-align: center; margin-top: 16px; line-height: 1.6; }
        .bk-hint a { color: #0f6a94; font-weight: 700; text-decoration: none; }
        .bk-hint a:hover { text-decoration: underline; }

        /* ===== SUCCESS PARTY POPUP ===== */
        .bk-party-overlay {
            position: fixed; inset: 0; z-index: 99999;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
            background: rgba(2, 18, 30, 0.62);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            opacity: 0; visibility: hidden;
            transition: opacity .45s ease, visibility .45s ease;
        }
        .bk-party-overlay.show { opacity: 1; visibility: visible; }
        .bk-party-card {
            position: relative;
            width: 100%; max-width: 470px;
            background: #ffffff;
            border-radius: 30px;
            text-align: center;
            padding: 52px 40px 38px;
            box-shadow: 0 40px 90px -24px rgba(0,58,89,0.45), 0 0 0 1px rgba(0,58,89,0.06);
            transform: translateY(34px) scale(.92);
            opacity: 0;
            transition: transform .55s cubic-bezier(.22,.9,.3,1.2), opacity .4s ease;
        }
        .bk-party-overlay.show .bk-party-card {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .bk-party-close {
            position: absolute; top: 16px; right: 16px;
            width: 38px; height: 38px; border-radius: 50%;
            background: #f0f4f8; border: 1px solid #e0e7ee;
            color: #003A59; font-size: 15px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .25s ease;
        }
        .bk-party-close:hover { background: #003A59; color: #fff; border-color: #003A59; transform: rotate(90deg); }

        /* Animated tick icon */
        .bk-party-icon { position: relative; width: 120px; height: 120px; margin: 0 auto 22px; }
        .bk-party-ring {
            position: absolute; inset: 0; border-radius: 50%;
            border: 2px solid rgba(15,106,148,0.35);
            animation: bkRingPulse 2.2s cubic-bezier(.2,.6,.3,1) infinite;
        }
        .bk-party-ring.r2 { animation-delay: .5s; border-color: rgba(0,58,89,0.28); }
        @keyframes bkRingPulse {
            0% { transform: scale(.72); opacity: .9; }
            70% { transform: scale(1.25); opacity: 0; }
            100% { transform: scale(1.25); opacity: 0; }
        }
        .bk-party-circle {
            position: absolute; inset: 8px;
            background: linear-gradient(145deg, #003A59 0%, #0f6a94 100%);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 16px 36px -10px rgba(0,58,89,0.55);
            transform: scale(0);
            animation: bkCircleIn .5s cubic-bezier(.3,1.4,.4,1) .15s forwards;
        }
        @keyframes bkCircleIn { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        .bk-party-circle svg { width: 58px; height: 58px; }
        .bk-tick-path {
            stroke-dasharray: 46;
            stroke-dashoffset: 46;
            animation: bkTickDraw .5s cubic-bezier(.5,1.4,.4,1) .65s forwards;
        }
        @keyframes bkTickDraw { 100% { stroke-dashoffset: 0; } }
        .bk-party-spark {
            position: absolute; width: 7px; height: 7px; border-radius: 50%;
            background: #8CC7E8; opacity: 0;
            animation: bkSpark .9s ease-out infinite;
        }
        @keyframes bkSpark {
            0% { transform: scale(0); opacity: 1; }
            70% { transform: scale(1.4); opacity: 0; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        /* Confetti */
        .bk-confetti { position: absolute; inset: 0; overflow: hidden; border-radius: 30px; pointer-events: none; }
        .bk-confetti-piece {
            position: absolute; top: -14px;
            width: 9px; height: 15px; border-radius: 2px;
            opacity: 0;
            animation: bkConfettiFall linear infinite;
        }
        @keyframes bkConfettiFall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(560px) rotate(720deg); opacity: .0; }
        }

        /* Popup content */
        .bk-party-eyebrow {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 11.5px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase;
            color: #0f6a94; margin-bottom: 10px;
        }
        .bk-party-card h3 {
            font-size: 26px; font-weight: 800; color: #003A59;
            margin: 0 0 10px; letter-spacing: -0.4px; line-height: 1.2;
        }
        .bk-party-card .bk-party-sub {
            font-size: 14.5px; color: #667085; line-height: 1.65;
            margin: 0 0 24px;
        }
        .bk-party-note {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: #f0f6fa; border: 1px solid #d5e6ef;
            border-radius: 14px; padding: 13px 16px;
            font-size: 13px; color: #0f6a94; font-weight: 600;
            margin-bottom: 22px;
        }
        .bk-party-note i { color: #003A59; }
        .bk-party-actions { display: flex; gap: 12px; }
        .bk-party-btn {
            flex: 1;
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            padding: 15px 20px; border-radius: 14px;
            font-size: 14.5px; font-weight: 700;
            text-decoration: none; cursor: pointer; border: none;
            transition: all .25s ease;
        }
        .bk-party-btn-primary {
            background: linear-gradient(135deg, #003A59 0%, #0f6a94 100%);
            color: #fff; box-shadow: 0 10px 26px -8px rgba(0,58,89,0.5);
        }
        .bk-party-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 34px -8px rgba(0,58,89,0.55); }
        .bk-party-btn-ghost {
            background: #f0f4f8; color: #003A59; border: 1px solid #dbe4ec;
        }
        .bk-party-btn-ghost:hover { background: #e2ecf3; }

        .bk-alert-error {
            max-width: 940px; margin: 34px auto 0; padding: 18px 24px;
            background: #fef3f2; border: 1px solid #fecdca; border-radius: 14px;
            color: #b42318; font-size: 15px; font-weight: 600;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .bk-pkg-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 767px) {
            /* Hero Section */
            .booking-hero { padding: 100px 0 80px; }
            .booking-hero h1 { font-size: 32px; }
            .booking-hero p { font-size: 15px; margin-bottom: 24px; }
            .booking-hero-stats { gap: 8px; }
            .booking-hero-stat { min-width: 120px; padding: 12px 16px; }
            .booking-hero-stat-number { font-size: 24px; }
            .booking-hero-stat-label { font-size: 11px; }

            /* Booking Section */
            .booking-section { padding: 50px 0 70px; }
            .booking-container { padding: 0 16px; }
            .booking-form-header h2 { font-size: 24px; }
            .booking-form-header p { font-size: 14px; }

            /* Wizard */
            .bk-wizard { border-radius: 20px; }
            .bk-panel { padding: 24px 18px; }
            .bk-stepper { gap: 4px; margin-bottom: 24px; }
            .bk-step-line { width: 16px; }
            .bk-step-label { display: none; }
            .bk-step-num { width: 32px; height: 32px; font-size: 13px; }

            /* Package Cards */
            .bk-pkg-grid { grid-template-columns: 1fr; gap: 16px; }
            .bk-pkg-card { border-radius: 16px; }
            .bk-pkg-card img { height: 160px; }
            .bk-pkg-body { padding: 14px 16px 16px; }
            .bk-pkg-title { font-size: 15px; }
            .bk-pkg-price .amount { font-size: 20px; }

            /* Form */
            .booking-form-row { grid-template-columns: 1fr; gap: 16px; }
            .booking-form-group { margin-bottom: 18px; }
            .booking-form-group.full-width { grid-column: span 1; }
            .booking-form-input, .booking-form-select, .booking-form-textarea {
                padding: 12px 16px;
                font-size: 14px;
                border-radius: 12px;
            }
            .booking-form-label { font-size: 13px; margin-bottom: 8px; }

            /* Details */
            .bk-dt-grid { grid-template-columns: 1fr; gap: 14px; }
            .bk-details-hero { border-radius: 16px; margin-bottom: 20px; }
            .bk-details-hero img { height: 200px; }
            .bk-details-info { padding: 16px; }
            .bk-details-info h3 { font-size: 20px; }
            .bk-details-info .bk-dt-price .amount { font-size: 24px; }
            .bk-info-grid { grid-template-columns: 1fr; gap: 12px; }
            .bk-info-item { padding: 12px 14px; }
            .bk-info-icon { width: 38px; height: 38px; font-size: 16px; }
            .bk-info-value { font-size: 13px; }

            /* Tabs */
            .bk-tabs-nav { gap: 4px; margin-bottom: 16px; }
            .bk-tab-btn { padding: 10px 14px; font-size: 13px; border-radius: 8px 8px 0 0; }
            .bk-dt-section { margin-bottom: 18px; }
            .bk-dt-section h4 { font-size: 14px; }

            /* Chip */
            .bk-chip { flex-wrap: wrap; padding: 10px 14px; border-radius: 14px; }
            .bk-chip img { width: 48px; height: 48px; }
            .bk-chip .bk-chip-name { font-size: 14px; }
            .bk-chip .bk-chip-price { margin-left: auto; font-size: 15px; }

            /* Navigation */
            .bk-nav { flex-direction: row; gap: 12px; margin-top: 24px; }
            .bk-nav .bk-btn { flex: 1; padding: 14px 20px; font-size: 14px; border-radius: 12px; }

            /* Review */
            .bk-review { padding: 16px 18px; border-radius: 14px; }
            .bk-review-row { font-size: 13px; padding: 10px 0; gap: 12px; }
            .bk-total { padding: 16px 18px; border-radius: 14px; }
            .bk-total .val { font-size: 24px; }

            /* Search */
            .bk-search-wrapper { border-radius: 12px; }
            .bk-search-icon { width: 44px; height: 44px; font-size: 15px; }
            .bk-search-input { padding: 12px 44px 12px 14px; font-size: 14px; }

            /* Trust badges */
            .booking-trust { font-size: 11px; gap: 16px; margin-top: 12px; }

            /* Gallery */
            .bk-gallery-strip { gap: 8px; }
            .bk-gallery-thumb { width: 64px; height: 50px; border-radius: 10px; }

            /* FAQ */
            .bk-faq-q { padding: 12px 16px; font-size: 13px; }
            .bk-faq-a p { font-size: 13px; }

            /* Itinerary */
            .bk-itinerary::before { left: 16px; }
            .bk-itinerary li { padding-left: 44px; }
            .bk-itin-day { width: 34px; height: 34px; }
            .bk-day-lbl { font-size: 7px; }
            .bk-day-num { font-size: 13px; }
            .bk-itin-title { font-size: 14px; }
            .bk-itin-desc { font-size: 12.5px; }
        }
        @media (max-width: 575px) {
            /* Hero */
            .booking-hero { padding: 80px 0 60px; }
            .booking-hero h1 { font-size: 28px; }
            .booking-hero p { font-size: 14px; max-width: 100%; }
            .booking-hero-badge { padding: 7px 16px; font-size: 12px; margin-bottom: 18px; }
            .booking-hero-stats { flex-direction: column; align-items: stretch; }
            .booking-hero-stat { min-width: auto; padding: 12px; }

            /* Section */
            .booking-section { padding: 40px 0 60px; }
            .booking-form-header h2 { font-size: 22px; }
            .booking-form-header p { font-size: 13px; }

            /* Wizard */
            .bk-wizard { border-radius: 16px; }
            .bk-panel { padding: 20px 16px; }
            .bk-stepper { margin-bottom: 20px; }

            /* Package Cards */
            .bk-pkg-grid { gap: 14px; }
            .bk-pkg-card img { height: 140px; }
            .bk-pkg-body { padding: 12px 14px 14px; }
            .bk-pkg-title { font-size: 14px; }
            .bk-pkg-price .amount { font-size: 18px; }
            .bk-pkg-badge { font-size: 10.5px; padding: 4px 9px; }

            /* Form */
            .booking-form-row { gap: 14px; }
            .booking-form-group { margin-bottom: 16px; }
            .booking-form-input, .booking-form-select, .booking-form-textarea {
                padding: 11px 14px;
                font-size: 14px;
            }
            .booking-form-textarea { min-height: 80px; }

            /* Details */
            .bk-details-hero img { height: 180px; }
            .bk-details-info { padding: 14px; }
            .bk-details-info h3 { font-size: 18px; }
            .bk-details-info .bk-dt-price .amount { font-size: 22px; }
            .bk-info-item { padding: 12px; }
            .bk-info-icon { width: 36px; height: 36px; font-size: 15px; }
            .bk-info-label { font-size: 10.5px; }
            .bk-info-value { font-size: 12.5px; }

            /* Tabs */
            .bk-tabs-nav { gap: 3px; }
            .bk-tab-btn { padding: 9px 12px; font-size: 12px; }
            .bk-dt-section h4 { font-size: 13px; }
            .bk-dt-overview { font-size: 13.5px; }
            .bk-dt-list li { font-size: 13px; }

            /* Chip */
            .bk-chip { padding: 10px 12px; }
            .bk-chip img { width: 44px; height: 44px; }
            .bk-chip .bk-chip-name { font-size: 13px; }
            .bk-chip .bk-chip-price { font-size: 14px; }

            /* Navigation */
            .bk-nav { flex-direction: row; gap: 10px; }
            .bk-nav .bk-btn { padding: 13px 18px; font-size: 13px; flex: 1; }

            /* Review */
            .bk-review { padding: 14px 16px; }
            .bk-total { padding: 14px 16px; }
            .bk-total .val { font-size: 22px; }
            .bk-price-breakdown { padding: 14px 16px; }
            .bk-breakdown-label { font-size: 12px; }
            .bk-breakdown-value { font-size: 13px; }

            /* Search */
            .bk-search-icon { width: 40px; height: 40px; }
            .bk-search-input { padding: 11px 40px 11px 12px; font-size: 13px; }

            /* Gallery */
            .bk-gallery-thumb { width: 56px; height: 44px; }

            /* Popup */
            .bk-party-card { padding: 40px 28px 32px; border-radius: 24px; }
            .bk-party-card h3 { font-size: 22px; }
            .bk-party-sub { font-size: 13.5px; }
            .bk-party-icon { width: 100px; height: 100px; }
            .bk-party-circle svg { width: 48px; height: 48px; }

            /* Itinerary */
            .bk-itin-tag { font-size: 11px; padding: 3px 9px; }
        }

        /* Extra small devices (iPhone SE, etc.) */
        @media (max-width: 375px) {
            .booking-hero h1 { font-size: 24px; }
            .bk-pkg-card img { height: 120px; }
            .bk-panel { padding: 18px 14px; }
            .booking-form-input, .booking-form-select, .booking-form-textarea {
                padding: 10px 12px;
                font-size: 13px;
            }
            .bk-nav .bk-btn { padding: 12px 16px; font-size: 12.5px; }
        }

        /* Landscape mobile */
        @media (max-width: 767px) and (orientation: landscape) {
            .booking-hero { padding: 60px 0 40px; }
            .booking-hero h1 { font-size: 24px; }
            .booking-section { padding: 30px 0 40px; }
            .bk-panel { padding: 16px 18px; }
        }

        /* ===== VMS FOOTER (matches index-three.php) ===== */
        .vms-footer{position:relative;background:whitesmoke;padding:0;overflow:hidden;font-family:var(--font-body);}
        html,body{overflow-x:hidden !important;max-width:100%;}
        .container,.container-fluid,.wrapper{max-width:100% !important;overflow-x:hidden !important;}
        .bromo-header,.bromo-nav,.bromo-mobile-menu,.mobile-menu-wrapper{max-width:100% !important;overflow-x:hidden !important;}
        .vms-footer-content{position:relative;z-index:3;max-width:1100px;margin:0 auto;padding-top:80px;padding-bottom:40px;display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:60px;}
        .vms-brand{display:flex;flex-direction:column;gap:12px;}
        .vms-logo{display:flex;align-items:center;gap:10px;}
        .vms-logo-icon{width:38px;height:38px;border-radius:10px;background:#003A59;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;}
        .vms-logo-icon svg{width:20px;height:20px;}
        .vms-logo-text{font-size:19px;font-weight:700;color:#003A59;letter-spacing:-0.3px;}
        .vms-tagline{font-size:22px;font-weight:700;color:#003A59;margin:6px 0 4px;letter-spacing:-0.4px;line-height:1.3;font-family:var(--font-body)!important;}
        .vms-desc{font-size:13.5px;color:#555;line-height:1.65;margin:0 0 8px;max-width:340px;}
        .vms-footer-bottom{position:relative;z-index:3;max-width:1100px;margin:0 auto;padding:24px 0;border-top:1px solid rgba(0,58,89,0.06);display:flex;align-items:center;justify-content:space-between;gap:20px;}
        .vms-credit{display:flex;align-items:center;gap:6px;font-size:12.5px;color:#666;}
        .vms-footer-col h5{font-size:14px;font-weight:700;color:#003A59;margin:0 0 18px;}
        .vms-footer-links{list-style:none;padding:0;margin:0;}
        .vms-footer-links li{margin-bottom:11px;}
        .vms-footer-links a{font-size:13.5px;color:#555;text-decoration:none;transition:color .2s;}
        .vms-footer-links a:hover{color:#003A59;}
        .vms-video-section{position:relative;z-index:1;width:100%;height:520px;overflow:visible;margin-top:-210px;background:linear-gradient(135deg,#667eea 0%,#764ba2 50%,#f093fb 100%);background-size:400% 400%;animation:vmsBgShift 15s ease infinite;}
        .vms-video-section .vms-video-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;}
        .vms-video-gradient{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,whitesmoke 0%,rgba(245,245,245,0.92) 12%,rgba(245,245,245,0.35) 22%,rgba(245,245,245,0.08) 32%,rgba(0,0,0,0.2) 48%,rgba(0,0,0,0.5) 78%,#000 100%);}
        .vms-big-text{position:absolute;bottom:80px;left:0;right:0;z-index:2;text-align:center;font-size:clamp(80px,12vw,140px);font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:0.05em;text-transform:uppercase;text-shadow:0 2px 20px rgba(0,0,0,0.3);pointer-events:none;font-family:var(--font-heading);margin:0;padding:0 30px;white-space:nowrap;}
        .vms-big-text{font-family:'Sunsive',sans-serif !important;font-size:clamp(40px,7vw,90px) !important;font-weight:700 !important;bottom:60px !important;text-align:center !important;width:100%;}
        @keyframes vmsBgShift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
        @media(max-width:1199px){
            .vms-footer-content{padding:60px 32px 32px;gap:40px;}
        }
        @media(max-width:991px){
            .vms-footer-content{grid-template-columns:1fr 1fr;gap:32px;padding:40px 28px 80px;}
            .vms-brand{grid-column:span 2;}
            .vms-video-section{height:360px;margin-top:-120px;}
            .vms-big-text{font-size:clamp(40px,8vw,70px);bottom:45px;}
        }
        @media(max-width:767px){
            .vms-video-section{height:320px;margin-top:-105px;overflow:hidden !important;}
            .vms-big-text{font-size:clamp(30px,6vw,50px);bottom:35px;white-space:normal !important;padding:0 15px !important;}
            body{overflow-x:hidden !important;}
        }
        @media(max-width:575px){
            .vms-footer-content{grid-template-columns:1fr;gap:26px;padding:28px 22px 60px;}
            .vms-brand{grid-column:span 1;}
            .vms-video-section{height:280px;margin-top:-90px;overflow:hidden !important;}
            .vms-big-text{font-size:clamp(18px,4vw,28px);bottom:25px;white-space:normal !important;padding:0 15px !important;}
            body{overflow-x:hidden !important;}
        }
        .vms-footer-nominee{display:none;}
    </style>
</head>
<body class="home-yacht-bg with-sidebar" data-turbo-cache="false">

<!-- ===== BROMORISE HEADER ===== -->
<header class="bromo-header header--sticky" id="bromoHeader">
    <a href="index-three.php" class="bromo-logo">
        <img src="assets/newlogo.png" alt="VMS Go Vista" class="vms-logo-img">
        <span style="font-family: Sunsive;">VMS Go Vista Pvt Ltd</span>
    </a>
    <nav class="bromo-nav">
        <a href=".">Home</a>
        <a href="package">Packages</a>
        <a href="service">Services</a>
        <a href="about">About Us</a>
        <a href="contact">Contact</a>
    </nav>
    <div class="bromo-book-btn">
        <a href="booking">
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
        <a href="index-three.php" class="bromo-mobile-nav-logo">
            <img src="assets/newlogo.png" alt="VMS Go Vista">
        </a>
        <button class="bromo-mobile-nav-close" id="mobileNavClose" aria-label="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <nav class="bromo-mobile-nav-links">
        <a href="."><i class="fa-solid fa-house"></i><span>Home</span></a>
        <a href="package"><i class="fa-solid fa-suitcase"></i><span>Packages</span></a>
        <a href="service"><i class="fa-solid fa-concierge-bell"></i><span>Services</span></a>
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

<!-- ===== BOOKING HERO ===== -->
<section class="booking-hero">
    <video class="booking-hero-video" autoplay muted loop playsinline preload="auto" poster="assets/hero4.webp">
        <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4" type="video/mp4">
        <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" type="video/mp4">
    </video>
    <div class="booking-hero-shade"></div>
    <div class="container booking-hero-content">
        <div class="booking-hero-badge">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Easy &amp; Secure Booking</span>
        </div>
        <h1>Book Your <em>Dream</em> Trip</h1>
        <p>Four simple steps — pick your package, review every detail, share your info, and confirm. Our travel experts handle the rest within 24 hours.</p>

    </div>
</section>

<?php if ($bookingSuccess): ?>
<!-- ===== SUCCESS PARTY POPUP ===== -->
<div class="bk-party-overlay" id="partyOverlay" role="dialog" aria-modal="true" aria-labelledby="partyTitle">
    <div class="bk-confetti" id="confettiWrap"></div>
    <div class="bk-party-card">
        <button type="button" class="bk-party-close" id="partyClose" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="bk-party-icon">
            <span class="bk-party-ring r1"></span>
            <span class="bk-party-ring r2"></span>
            <span class="bk-party-spark" style="top:8%;left:12%;animation-delay:.2s;"></span>
            <span class="bk-party-spark" style="top:4%;right:18%;animation-delay:.5s;"></span>
            <span class="bk-party-spark" style="bottom:16%;left:4%;animation-delay:.35s;"></span>
            <span class="bk-party-spark" style="bottom:6%;right:8%;animation-delay:.65s;"></span>
            <div class="bk-party-circle">
                <svg viewBox="0 0 52 52" fill="none">
                    <path class="bk-tick-path" d="M15 27.5 L23 35.5 L38 18" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        <div class="bk-party-eyebrow"><i class="fa-solid fa-sparkles"></i> Booking Confirmed</div>
        <h3 id="partyTitle">Booking Request Received!</h3>
        <p class="bk-party-sub">Thank you for choosing VMS Go Vista. Our travel expert will call/email you within 24 hours to confirm your trip.</p>
        <div class="bk-party-note">
            <i class="fa-solid fa-envelope-circle-check"></i>
            A confirmation email is on its way to your inbox.
        </div>
        <div class="bk-party-actions">
            <a href="booking" class="bk-party-btn bk-party-btn-ghost"><i class="fa-regular fa-rotate-left"></i> New Booking</a>
            <a href="package.php" class="bk-party-btn bk-party-btn-primary">Explore More <i class="fa-regular fa-arrow-right"></i></a>
        </div>
    </div>
</div>
<?php elseif ($flash && $flash['type'] === 'error'): ?>
<div class="bk-alert-error"><?= e($flash['message']) ?></div>
<?php endif; ?>

<!-- ===== BOOKING WIZARD ===== -->
<section class="booking-section">
    <div class="booking-container">

        <!-- Stepper -->
        <div class="bk-stepper">
            <div class="bk-step active" data-step="1">
                <div class="bk-step-num">1</div>
                <div class="bk-step-label">Choose Package</div>
            </div>
            <div class="bk-step-line" data-line="1"></div>
            <div class="bk-step" data-step="2">
                <div class="bk-step-num">2</div>
                <div class="bk-step-label">Package Details</div>
            </div>
            <div class="bk-step-line" data-line="2"></div>
            <div class="bk-step" data-step="3">
                <div class="bk-step-num">3</div>
                <div class="bk-step-label">Your Details</div>
            </div>
            <div class="bk-step-line" data-line="3"></div>
            <div class="bk-step" data-step="4">
                <div class="bk-step-num">4</div>
                <div class="bk-step-label">Confirm</div>
            </div>
        </div>

        <div class="bk-wizard">
            <form action="booking-submit.php" method="POST" id="bookingForm">
                <input type="hidden" name="package_id" id="hiddenPkgId" value="<?= (int)$preselectedId ?>">

                <!-- STEP 1 — CHOOSE PACKAGE -->
                <div class="bk-panel active" data-panel="1">
                    <div class="booking-form-header">
                        <div class="bk-eyebrow"><i class="fa-solid fa-suitcase-rolling"></i> Step 1 of 4</div>
                        <h2>Choose Your Package</h2>
                        <p>Tap a package — we'll show you every detail in the next step.</p>
                    </div>


                    <div class="bk-search-wrapper" id="pkgSearchWrap">
                        <div class="bk-search-field">
                            <i class="fa-solid fa-magnifying-glass bk-search-icon"></i>
                            <input type="text" class="bk-search-input" id="pkgSearchInput" placeholder="Search packages by destination or name..." autocomplete="off">
                            <button type="button" class="bk-search-clear" id="pkgSearchClear" aria-label="Clear search"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                  

                    <div class="bk-pkg-grid" id="pkgGrid">
                        <?php foreach ($packages as $pkg): $pid = (int)$pkg['id']; ?>
                        <div class="bk-pkg-card <?= $preselectedId === $pid ? 'selected' : '' ?>"
                             data-id="<?= $pid ?>"
                             data-title="<?= e($pkg['title']) ?>"
                             data-days="<?= (int)($pkg['days'] ?? 0) ?>"
                             data-nights="<?= (int)($pkg['nights'] ?? 0) ?>"
                             data-price="<?= (float)($pkg['price_discounted'] ?? $pkg['price_original']) ?>"
                             data-currency="<?= e($pkg['currency'] ?? 'INR') ?>"
                             data-dest="<?= e($pkg['destination'] ?? '') ?>"
                             data-img="<?= e(packageCoverImageUrl($pkg)) ?>">
                            <img src="<?= e(packageCoverImageUrl($pkg)) ?>" alt="<?= e($pkg['title']) ?>" loading="lazy">
                            <span class="bk-pkg-badge"><i class="fa-regular fa-clock"></i> <?= (int)$pkg['days'] ?>D / <?= (int)$pkg['nights'] ?>N</span>
                            <span class="bk-pkg-check"><i class="fa-solid fa-check"></i></span>
                            <div class="bk-pkg-body">
                                <h4 class="bk-pkg-title"><?= e($pkg['title']) ?></h4>
                                <div class="bk-pkg-dest"><i class="fa-solid fa-location-dot"></i> <?= e($pkg['destination'] ?: ($pkg['country'] ?? 'Travel')) ?></div>
                                <p class="bk-pkg-desc"><?= e(bkShortDesc($pkg['short_desc'])) ?></p>
                                <div class="bk-pkg-price">
                                    <span class="amount"><?= formatPrice((float)($pkg['price_discounted'] ?? $pkg['price_original']), $pkg['currency'] ?? 'INR') ?></span>
                                    <span class="per">/ person</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bk-no-results" id="pkgNoResults">
                        <i class="fa-solid fa-search"></i>
                        <p>No packages found matching your search. Try a different location.</p>
                    </div>
                    <p class="bk-hint" style="margin-top:20px;">👆 Tap a package card to see its full details.</p>
                </div>

                <!-- STEP 2 — PACKAGE DETAILS -->
                <div class="bk-panel" data-panel="2">
                    <div class="booking-form-header">
                        <div class="bk-eyebrow"><i class="fa-solid fa-circle-info"></i> Step 2 of 4</div>
                        <h2>Package Details</h2>
                        <p>Review everything your package includes — itinerary, inclusions, exclusions and highlights.</p>
                    </div>
                    <div id="pkgDetailsWrap">
                        <!-- JS renders full package details here -->
                    </div>
                    <div class="bk-nav">
                        <button type="button" class="bk-btn bk-btn-ghost" data-goto="1">
                            <i class="fa-regular fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="bk-btn bk-btn-primary" id="step2Next">
                            Continue Details <i class="fa-regular fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 3 — TRAVELLER DETAILS -->
                <div class="bk-panel" data-panel="3">
                    <div class="booking-form-header">
                        <div class="bk-eyebrow"><i class="fa-solid fa-user-pen"></i> Step 3 of 4</div>
                        <h2>Traveller Details</h2>
                        <p>Tell us who's travelling and when — we'll handle the rest.</p>
                    </div>

                    <div class="bk-chip">
                        <img src="" alt="" id="chipImg">
                        <div class="bk-chip-info">
                            <div class="bk-chip-name" id="chipName">—</div>
                            <div class="bk-chip-meta" id="chipMeta">—</div>
                        </div>
                        <div class="bk-chip-price" id="chipPrice">—</div>
                    </div>

                    <div class="booking-form-row">
                        <div class="booking-form-group">
                            <label class="booking-form-label">
                                <i class="fa-solid fa-user"></i>
                                Full Name <span class="req">*</span>
                            </label>
                            <input type="text" name="name" id="bkName" class="booking-form-input" required placeholder="Enter your full name">
                        </div>
                        <div class="booking-form-group">
                            <label class="booking-form-label">
                                <i class="fa-solid fa-envelope"></i>
                                Email Address <span class="req">*</span>
                            </label>
                            <input type="email" name="email" id="bkEmail" class="booking-form-input" required placeholder="Enter your email">
                        </div>
                    </div>
                    <div class="booking-form-row">
                        <div class="booking-form-group">
                            <label class="booking-form-label">
                                <i class="fa-solid fa-phone"></i>
                                Phone Number <span class="req">*</span>
                            </label>
                            <input type="tel" name="phone" id="bkPhone" class="booking-form-input" required placeholder="Enter Your Number">
                        </div>
                        <div class="booking-form-group">
                            <label class="booking-form-label">
                                <i class="fa-solid fa-calendar-days"></i>
                                Travelling Date <span class="req">*</span>
                            </label>
                            <input type="date" name="travel_date" id="bkDate" class="booking-form-input" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                    </div>
                    <div class="booking-form-row">
                        <div class="booking-form-group">
                            <label class="booking-form-label">
                                <i class="fa-solid fa-user-group"></i>
                                Adults <span class="req">*</span>
                            </label>
                            <input type="number" name="adults" id="bkAdults" class="booking-form-input" min="1" max="30" value="2" required>
                        </div>
                        <div class="booking-form-group">
                            <label class="booking-form-label">
                                <i class="fa-solid fa-child-reaching"></i>
                                Children
                            </label>
                            <input type="number" name="children" id="bkChildren" class="booking-form-input" min="0" max="20" value="0">
                        </div>
                    </div>
                    <div class="booking-form-group full-width">
                        <label class="booking-form-label">
                            <i class="fa-solid fa-message"></i>
                            Special Requests
                        </label>
                        <textarea name="special_requests" id="bkRequests" class="booking-form-textarea" placeholder="Dietary needs, room preferences, flight timing, celebration plans..."></textarea>
                    </div>

                    <div class="bk-nav">
                        <button type="button" class="bk-btn bk-btn-ghost" data-goto="2">
                            <i class="fa-regular fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="bk-btn bk-btn-primary" id="step3Next">
                            Review Booking <i class="fa-regular fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 4 — REVIEW & CONFIRM -->
                <div class="bk-panel" data-panel="4">
                    <div class="booking-form-header">
                        <div class="bk-eyebrow"><i class="fa-solid fa-clipboard-check"></i> Step 4 of 4</div>
                        <h2>Review &amp; Confirm</h2>
                        <p>Please check your booking summary below — everything correct? Confirm to send your request.</p>
                    </div>

                    <div class="bk-review">
                        <div class="bk-review-row">
                            <span class="k"><i class="fa-solid fa-suitcase-rolling" style="color:#0f6a94;margin-right:8px;"></i>Package</span>
                            <span class="v" id="revPkg">—</span>
                        </div>
                        <div class="bk-review-row">
                            <span class="k"><i class="fa-solid fa-user" style="color:#0f6a94;margin-right:8px;"></i>Full Name</span>
                            <span class="v" id="revName">—</span>
                        </div>
                        <div class="bk-review-row">
                            <span class="k"><i class="fa-solid fa-envelope" style="color:#0f6a94;margin-right:8px;"></i>Email</span>
                            <span class="v" id="revEmail">—</span>
                        </div>
                        <div class="bk-review-row">
                            <span class="k"><i class="fa-solid fa-phone" style="color:#0f6a94;margin-right:8px;"></i>Phone</span>
                            <span class="v" id="revPhone">—</span>
                        </div>
                        <div class="bk-review-row">
                            <span class="k"><i class="fa-solid fa-calendar-days" style="color:#0f6a94;margin-right:8px;"></i>Travelling Date</span>
                            <span class="v" id="revDate">—</span>
                        </div>
                        <div class="bk-review-row">
                            <span class="k"><i class="fa-solid fa-user-group" style="color:#0f6a94;margin-right:8px;"></i>Travellers</span>
                            <span class="v" id="revTravellers">—</span>
                        </div>
                    </div>

                    <div class="bk-total">
                        <span class="lbl"><i class="fa-solid fa-calculator" style="margin-right:8px;"></i>Estimated Total</span>
                        <span class="val" id="revTotal">—</span>
                    </div>
                    <div class="bk-price-breakdown" id="priceBreakdown">
                        <div class="bk-breakdown-row">
                            <span class="bk-breakdown-label"><i class="fa-solid fa-user"></i> Adults (<span id="breakdownAdults">0</span>)</span>
                            <span class="bk-breakdown-value" id="breakdownAdultPrice">—</span>
                        </div>
                        <div class="bk-breakdown-row" id="breakdownChildRow">
                            <span class="bk-breakdown-label"><i class="fa-solid fa-child-reaching"></i> Children (<span id="breakdownChildren">0</span>)</span>
                            <span class="bk-breakdown-value" id="breakdownChildPrice">—</span>
                        </div>
                        <div class="bk-breakdown-divider"></div>
                        <div class="bk-breakdown-row bk-breakdown-total">
                            <span class="bk-breakdown-label"><strong>Final Total</strong></span>
                            <span class="bk-breakdown-value" id="breakdownFinalTotal">—</span>
                        </div>
                    </div>
                    <p class="bk-hint">No payment is needed now. Our team will confirm availability and pricing within <strong>24 hours</strong>. Questions? Call <a href="tel:+919870182425">+91 98701 82425</a></p>

                    <div class="bk-nav">
                        <button type="button" class="bk-btn bk-btn-ghost" data-goto="3">
                            <i class="fa-regular fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="bk-btn bk-btn-primary bk-btn-submit" id="submitBtn">
                            <span class="spinner"></span>
                            <span class="btn-label"><i class="fa-solid fa-paper-plane"></i> Confirm Booking</span>
                        </button>
                    </div>
                    <div class="booking-trust">
                        <span><i class="fa-solid fa-shield-halved"></i> 100% Secure</span>
                        <span><i class="fa-solid fa-lock"></i> No Payment Required</span>
                        <span><i class="fa-solid fa-rotate-left"></i> Free Cancellation</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ===== VMS FOOTER ===== -->
<footer class="vms-footer">
    <div class="vms-footer-content">
        <div class="vms-brand">
            <div class="vms-logo">
                <img src="assets/newlogo.png" alt="VMS Go Vista" style="height:42px;width:auto;border-radius:8px;">
                <span class="vms-logo-text" style="font-family: Sunsive;">VMS Go Vista Pvt Ltd</span>
            </div>
            <h3 class="vms-tagline">Your smart travel companion</h3>
            <p class="vms-desc">VMS Go Vista brings tours, destinations, deals, weather, quick bookings and more useful travel tools into one beautiful platform beside your dream journey.</p>
        </div>
        <div class="vms-footer-col">
            <h5>Menu</h5>
            <ul class="vms-footer-links">
                <li><a href="index-three.php">Home</a></li>
                <li><a href="package.php">Packages</a></li>
                <li><a href="service.html">Services</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </div>
        <div class="vms-footer-col">
            <h5>Destinations</h5>
            <ul class="vms-footer-links">
                <li><a href="package.php?destination=Kashmir">Kashmir Tours</a></li>
                <li><a href="package.php?destination=Kerala">Kerala Packages</a></li>
                <li><a href="package.php?destination=Goa">Goa Adventures</a></li>
                <li><a href="package.php?destination=Rajasthan">Rajasthan Heritage</a></li>
                <li><a href="package.php?destination=Manali">Manali Trips</a></li>
            </ul>
        </div>
        <div class="vms-footer-col">
            <h5>Contact</h5>
            <ul class="vms-footer-links">
                <li><a href="tel:+919870182425">+91 98701 82425</a></li>
                <li><a href="mailto:info@vmsgovista.com">info@vmsgovista.com</a></li>
            </ul>
        </div>
    </div>

    <!-- Bottom bar with copyright -->
    <div class="vms-footer-bottom">
        <p class="vms-credit" style="font-weight: 900; color: black;">&copy; <?= date('Y') ?> VMS Go Vista &middot; All rights reserved</p>
        <div class="vms-credit">
            <span style="font-weight: 900; color: black;">Crafted with dedication by</span>
            <span style="font-weight: 900; color: black;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#e91e63" style="margin-right: 6px; vertical-align: middle;"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                Vipin Kumar (Owner)
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

<!-- ===== MOBILE SIDEBAR ===== -->
<div id="side-bar" class="side-bar header-two header-eight">
    <button class="close-icon-menu"><i class="fa-sharp fa-thin fa-xmark"></i></button>
    <a class="logo" href="index-three.php"><img src="assets/images/logo/05.svg" alt=""></a>
    <div class="mobile-menu-main">
        <nav class="nav-main mainmenu-nav mt--30">
            <ul class="mainmenu metismenu" id="mobile-menu-active">
                <li><a href="index-three.php" class="main">Home</a></li>
                <li><a href="package.php" class="main">Packages</a></li>
                <li><a href="about.html" class="main">About</a></li>
                <li><a href="contact.html" class="main">Contact Us</a></li>
                <li><a href="booking" class="main">Book Now</a></li>
            </ul>
        </nav>
    </div>
</div>

<script>
// ── Package details dataset (built server-side, one batch per table) ──
var BKG_DETAILS = <?= $pkgDetailsJson ?>;
</script>
<!-- Scripts -->
<script src="assets/js/plugins/jquery.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/plugins/metismenu.js"></script>
<script src="assets/js/vendor/waypoint.js"></script>
<script src="assets/js/vendor/wow.js"></script>
<script src="assets/js/main.js"></script>
<script>
(function () {
    // Sticky header
    var header = document.getElementById('bromoHeader');
    if (header) {
        var onScroll = function () {
            header.classList.toggle('sticky', window.scrollY > 60);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Mobile nav toggle
    var menuBtn = document.getElementById('menu-btn');
    var mobileNav = document.getElementById('mobileNav');
    var mobileNavOverlay = document.getElementById('mobileNavOverlay');
    var mobileNavClose = document.getElementById('mobileNavClose');
    if (menuBtn && mobileNav && mobileNavOverlay && mobileNavClose) {
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
        menuBtn.addEventListener('click', function () {
            mobileNav.classList.contains('active') ? closeMobileNav() : openMobileNav();
        });
        mobileNavClose.addEventListener('click', closeMobileNav);
        mobileNavOverlay.addEventListener('click', closeMobileNav);
        mobileNav.querySelectorAll('.bromo-mobile-nav-links a').forEach(function (link) {
            link.addEventListener('click', closeMobileNav);
        });
    }

    // ===== BOOKING WIZARD =====
    var hiddenPkgId = document.getElementById('hiddenPkgId');
    var pkgCards = document.querySelectorAll('.bk-pkg-card');
    var form = document.getElementById('bookingForm');
    var submitBtn = document.getElementById('submitBtn');

    var symbolFor = function (cur) {
        return { 'USD': '$', 'EUR': '€', 'GBP': '£', 'AED': 'AED ', 'INR': '₹', 'AUD': 'A$' }[cur] || (cur + ' ');
    };
    var fmt = function (n) { return Number(n).toLocaleString('en-IN'); };

    function getSelectedId() {
        return hiddenPkgId && hiddenPkgId.value ? hiddenPkgId.value : null;
    }
    function getSelectedCard() {
        var id = getSelectedId();
        if (!id) return null;
        var found = null;
        pkgCards.forEach(function (c) { if (c.dataset.id === id) found = c; });
        return found;
    }

    // ── Step 2: render full package details from BKG_DETAILS ──
    function renderPackageDetails(id) {
        var d = BKG_DETAILS[id];
        var wrap = document.getElementById('pkgDetailsWrap');
        if (!d || !wrap) return;

        var esc = function (s) {
            return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        };

        var inclusions = (d.inclusions || []).map(function (i) {
            return '<li><i class="fa-solid fa-circle-check"></i><span>' + esc(i) + '</span></li>';
        }).join('');
        var exclusions = (d.exclusions || []).map(function (i) {
            return '<li><i class="fa-solid fa-circle-xmark"></i><span>' + esc(i) + '</span></li>';
        }).join('');
        var highlights = (d.highlights || []).map(function (i) {
            return '<span>' + esc(i) + '</span>';
        }).join('');

        // Itinerary with meals / stay badges
        var itinerary = (d.itinerary || []).map(function (t) {
            var tags = '';
            if (t.meals) tags += '<span class="bk-itin-tag"><i class="fa-solid fa-utensils"></i> ' + esc(t.meals) + '</span>';
            if (t.stay)  tags += '<span class="bk-itin-tag"><i class="fa-solid fa-hotel"></i> ' + esc(t.stay) + '</span>';
            return '<li>'
                 + '<div class="bk-itin-day"><span class="bk-day-lbl">DAY</span><span class="bk-day-num">' + t.day + '</span></div>'
                 + '<div class="bk-itin-title">' + esc(t.title) + '</div>'
                 + (tags ? '<div class="bk-itin-tags">' + tags + '</div>' : '')
                 + (t.desc ? '<p class="bk-itin-desc">' + esc(t.desc) + '</p>' : '')
                 + '</li>';
        }).join('');

        // Gallery (fall back to cover image)
        var gallery = (d.gallery && d.gallery.length) ? d.gallery.map(esc) : [d.img];

        // Rating stars
        var stars = '';
        var rating = Number(d.rating || 0);
        if (rating > 0) {
            var full = Math.floor(rating);
            var half = (rating - full) >= 0.5;
            for (var si = 0; si < full; si++) stars += '<i class="fa-solid fa-star"></i>';
            if (half) stars += '<i class="fa-solid fa-star-half-stroke"></i>';
            for (var si2 = full + (half ? 1 : 0); si2 < 5; si2++) stars += '<i class="fa-regular fa-star"></i>';
        }

        // Child-price note (shown in Cost tab when set)
        var childNote = '';
        if (d.price_per_child > 0) {
            childNote = '<div class="bk-dt-section"><h4><span class="ic"><i class="fa-solid fa-child-reaching"></i></span> Child Pricing</h4>'
                      + '<p class="bk-dt-overview">Children (per child): <strong>' + symbolFor(d.currency) + fmt(d.price_per_child) + '</strong> — children pricing applies based on age at time of travel.</p>'
                      + '</div>';
        }

        // Build info grid items (A–Z tour facts)
        var infoItems = '';
        function infoItem(icon, label, value) {
            if (!value) return;
            infoItems += '<div class="bk-info-item">'
                      +   '<div class="bk-info-icon"><i class="' + icon + '"></i></div>'
                      +   '<div class="bk-info-text">'
                      +     '<div class="bk-info-label">' + label + '</div>'
                      +     '<div class="bk-info-value">' + esc(value) + '</div>'
                      +   '</div>'
                      + '</div>';
        }
        if (d.accommodation) infoItem('fa-solid fa-hotel', 'Accommodation', d.accommodation);
        infoItem('fa-regular fa-clock', 'Duration', d.days + ' Days / ' + d.nights + ' Nights');
        if (d.transportation) infoItem('fa-solid fa-bus', 'Transportation', d.transportation);
        if (d.meals) infoItem('fa-solid fa-utensils', 'Meals', d.meals);
        if (d.tour_type) infoItem('fa-solid fa-person-hiking', 'Tour Type', d.tour_type);
        if (d.language) infoItem('fa-solid fa-language', 'Language', d.language);
        if (d.group_size_min || d.group_size_max) {
            var groupSize = (d.group_size_min && d.group_size_max) ? d.group_size_min + ' - ' + d.group_size_max : (d.group_size_min || d.group_size_max);
            infoItem('fa-solid fa-users', 'Group Size', groupSize);
        }
        if (d.min_age) infoItem('fa-solid fa-cake-candles', 'Minimum Age', d.min_age);
        if (d.max_age) infoItem('fa-solid fa-cake-candles', 'Maximum Age', d.max_age);
        if (d.max_altitude) infoItem('fa-solid fa-mountain-sun', 'Max Altitude', d.max_altitude);
        if (d.best_season) infoItem('fa-solid fa-sun', 'Best Season', d.best_season);
        if (d.fitness_level) infoItem('fa-solid fa-heart-pulse', 'Fitness Level', d.fitness_level);
        if (d.departure_from) infoItem('fa-solid fa-plane-departure', 'Departure From', d.departure_from);

        // Price (with discount badge)
        var priceBadge = '';
        if (d.discount_pct && d.price_original > d.price) {
            priceBadge = '<span class="bk-dt-off">' + d.discount_pct + '% OFF</span>';
        }

        var html = '';
        // Hero with gallery + rating
        html += '<div class="bk-details-hero">'
              +   '<img src="' + d.img + '" alt="' + esc(d.title) + '" id="bkHeroImg">'
              +   '<div class="bk-details-overlay"></div>'
              +   '<div class="bk-details-info">'
              +     '<div class="bk-dt-badges">'
              +       '<span class="bk-dt-badge"><i class="fa-regular fa-clock"></i> ' + d.days + 'D / ' + d.nights + 'N</span>'
              +       (d.dest ? '<span class="bk-dt-badge"><i class="fa-solid fa-location-dot"></i> ' + esc(d.dest) + '</span>' : '')
              +       (rating > 0 ? '<span class="bk-dt-badge bk-dt-rate">' + stars + ' ' + rating.toFixed(1) + (d.review_count ? ' (' + d.review_count + ')' : '') + '</span>' : '')
              +     '</div>'
              +     '<h3 style="color: #ffffff;">' + esc(d.title) + '</h3>'
              +     '<div class="bk-dt-price"><span class="amount">' + symbolFor(d.currency) + fmt(d.price) + '</span><span class="per">/ person</span>' + priceBadge + '</div>'
              +   '</div>'
              + '</div>';

        // Gallery strip
        if (gallery.length > 1) {
            html += '<div class="bk-gallery-strip">';
            gallery.forEach(function (g, gi) {
                html += '<div class="bk-gallery-thumb' + (gi === 0 ? ' active' : '') + '" data-src="' + g + '">'
                      +   '<img src="' + g + '" alt="' + esc(d.title) + ' photo">'
                      + '</div>';
            });
            html += '</div>';
        }

        // Info Grid
        html += '<div class="bk-info-grid">' + infoItems + '</div>';

        // Tabs Navigation
        var tabs = [];
        if (d.overview || highlights) tabs.push(['overview', 'Overview']);
        if (d.itinerary && d.itinerary.length) tabs.push(['itinerary', 'Itinerary']);
        if ((d.inclusions && d.inclusions.length) || (d.exclusions && d.exclusions.length)) tabs.push(['cost', 'Inclusions & Exclusions']);
        if (d.faqs && d.faqs.length) tabs.push(['faq', 'FAQs']);
        if (d.info && d.info.length) tabs.push(['info', 'Important Info']);

        if (tabs.length > 0) {
            html += '<div class="bk-tabs-nav">';
            tabs.forEach(function (t, i) {
                html += '<button class="bk-tab-btn' + (i === 0 ? ' active' : '') + '" data-tab="' + t[0] + '">' + t[1] + '</button>';
            });
            html += '</div>';

            // Tab Contents
            tabs.forEach(function (t, i) {
                var isActive = i === 0 ? ' active' : '';
                html += '<div class="bk-tab-content' + isActive + '" data-content="' + t[0] + '">';

                if (t[0] === 'overview') {
                    if (d.overview) {
                        html += '<div class="bk-dt-section">'
                              +   '<h4><span class="ic"><i class="fa-solid fa-book-open"></i></span> Overview</h4>'
                              +   '<p class="bk-dt-overview">' + esc(d.overview) + '</p>'
                              + '</div>';
                    }
                    if (highlights) {
                        html += '<div class="bk-dt-section">'
                              +   '<h4><span class="ic"><i class="fa-solid fa-star"></i></span> Highlights</h4>'
                              +   '<div class="bk-highlight-chips">' + highlights + '</div>'
                              + '</div>';
                    }
                }
                if (t[0] === 'itinerary') {
                    html += '<div class="bk-dt-section"><h4><span class="ic"><i class="fa-solid fa-route"></i></span> Day-wise Itinerary</h4>'
                          + '<ul class="bk-itinerary">' + itinerary + '</ul></div>';
                }
                if (t[0] === 'cost') {
                    if (d.inclusions && d.inclusions.length) {
                        html += '<div class="bk-dt-section"><h4><span class="ic"><i class="fa-solid fa-gift"></i></span> What\u2019s Included</h4>'
                              + '<ul class="bk-dt-list incl">' + inclusions + '</ul></div>';
                    }
                    if (d.exclusions && d.exclusions.length) {
                        html += '<div class="bk-dt-section"><h4><span class="ic"><i class="fa-solid fa-ban"></i></span> Not Included</h4>'
                              + '<ul class="bk-dt-list excl">' + exclusions + '</ul></div>';
                    }
                    if (childNote) html += childNote;
                }
                if (t[0] === 'faq') {
                    html += '<div class="bk-dt-section"><h4><span class="ic"><i class="fa-solid fa-circle-question"></i></span> Frequently Asked Questions</h4>'
                          + '<div class="bk-faq">';
                    (d.faqs || []).forEach(function (f, fi) {
                        html += '<div class="bk-faq-item' + (fi === 0 ? ' open' : '') + '">'
                              +   '<button type="button" class="bk-faq-q"><span>' + esc(f.q) + '</span><i class="fa-solid fa-chevron-down"></i></button>'
                              +   '<div class="bk-faq-a"><p>' + esc(f.a) + '</p></div>'
                              + '</div>';
                    });
                    html += '</div></div>';
                }
                if (t[0] === 'info') {
                    html += '<div class="bk-dt-section"><h4><span class="ic"><i class="fa-solid fa-circle-info"></i></span> Good to Know</h4>'
                          + '<div class="bk-faq">';
                    (d.info || []).forEach(function (inf, ii) {
                        html += '<div class="bk-faq-item' + (ii === 0 ? ' open' : '') + '">'
                              +   '<button type="button" class="bk-faq-q"><span>' + esc(inf.title || inf.type) + '</span><i class="fa-solid fa-chevron-down"></i></button>'
                              +   '<div class="bk-faq-a"><p>' + esc(inf.content) + '</p></div>'
                              + '</div>';
                    });
                    html += '</div></div>';
                }
                html += '</div>';
            });
        }

        wrap.innerHTML = html;

        // Gallery thumbnail click → swap hero image
        wrap.querySelectorAll('.bk-gallery-thumb').forEach(function (th) {
            th.addEventListener('click', function () {
                wrap.querySelectorAll('.bk-gallery-thumb').forEach(function (x) { x.classList.remove('active'); });
                th.classList.add('active');
                var heroImg = wrap.querySelector('#bkHeroImg');
                if (heroImg) heroImg.src = th.dataset.src;
            });
        });

        // Tab click handlers
        wrap.querySelectorAll('.bk-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tabName = this.dataset.tab;
                wrap.querySelectorAll('.bk-tab-btn').forEach(function (b) { b.classList.remove('active'); });
                wrap.querySelectorAll('.bk-tab-content').forEach(function (c) { c.classList.remove('active'); });
                this.classList.add('active');
                var content = wrap.querySelector('.bk-tab-content[data-content="' + tabName + '"]');
                if (content) content.classList.add('active');
            });
        });

        // FAQ accordion toggles
        wrap.querySelectorAll('.bk-faq-q').forEach(function (q) {
            q.addEventListener('click', function () {
                var item = q.parentNode;
                var wasOpen = item.classList.contains('open');
                var list = item.parentNode;
                list.querySelectorAll('.bk-faq-item').forEach(function (x) { x.classList.remove('open'); });
                if (!wasOpen) item.classList.add('open');
            });
        });
    }

    // ── Chip (step 3) ──
    function fillChip() {
        var card = getSelectedCard();
        var chipImg = document.getElementById('chipImg');
        var chipName = document.getElementById('chipName');
        var chipMeta = document.getElementById('chipMeta');
        var chipPrice = document.getElementById('chipPrice');
        if (!card) return;
        if (chipImg) chipImg.src = card.dataset.img;
        if (chipName) chipName.textContent = card.dataset.title;
        if (chipMeta) chipMeta.textContent = card.dataset.days + 'D / ' + card.dataset.nights + 'N' + (card.dataset.dest ? '  •  ' + card.dataset.dest : '');
        if (chipPrice) chipPrice.textContent = symbolFor(card.dataset.currency || 'INR') + fmt(parseFloat(card.dataset.price || 0));
    }

    // ── Review (step 4) ──
    function fillReview() {
        var card = getSelectedCard();
        var name = document.getElementById('bkName');
        var email = document.getElementById('bkEmail');
        var phone = document.getElementById('bkPhone');
        var date = document.getElementById('bkDate');
        var adults = document.getElementById('bkAdults');
        var children = document.getElementById('bkChildren');
        var fmtDate = function (d) {
            if (!d) return '—';
            var p = d.split('-');
            if (p.length !== 3) return d;
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return p[2] + ' ' + months[parseInt(p[1],10)-1] + ' ' + p[0];
        };
        document.getElementById('revPkg').textContent = card ? card.dataset.title : '—';
        document.getElementById('revName').textContent = name ? name.value.trim() || '—' : '—';
        document.getElementById('revEmail').textContent = email ? email.value.trim() || '—' : '—';
        document.getElementById('revPhone').textContent = phone ? phone.value.trim() || '—' : '—';
        document.getElementById('revDate').textContent = date ? fmtDate(date.value) : '—';
        var a = parseInt(adults ? adults.value : 1, 10) || 1;
        var c = parseInt(children ? children.value : 0, 10) || 0;
        document.getElementById('revTravellers').textContent = a + ' Adult' + (a > 1 ? 's' : '') + (c > 0 ? ' + ' + c + ' Child' + (c > 1 ? 'ren' : '') : '');
        
        // Price breakdown
        var adultPrice = 0;
        var childPrice = 0;
        var finalTotal = 0;
        
        if (card) {
            var pkgId = card.dataset.id;
            var pkgData = window.packageDetails && window.packageDetails[pkgId];
            var adultRate = parseFloat(card.dataset.price || 0);
            var childRate = pkgData ? (parseFloat(pkgData.price_per_child) || 0) : 0;
            
            adultPrice = adultRate * a;
            childPrice = childRate * c;
            finalTotal = adultPrice + childPrice;
            
            // Update breakdown
            document.getElementById('breakdownAdults').textContent = a;
            document.getElementById('breakdownAdultPrice').textContent = symbolFor(card.dataset.currency || 'INR') + fmt(adultPrice);
            
            var childRow = document.getElementById('breakdownChildRow');
            if (c > 0 && childRate > 0) {
                childRow.style.display = 'flex';
                document.getElementById('breakdownChildren').textContent = c;
                document.getElementById('breakdownChildPrice').textContent = symbolFor(card.dataset.currency || 'INR') + fmt(childPrice);
            } else {
                childRow.style.display = 'none';
            }
            
            document.getElementById('breakdownFinalTotal').textContent = symbolFor(card.dataset.currency || 'INR') + fmt(finalTotal);
            document.getElementById('revTotal').textContent = symbolFor(card.dataset.currency || 'INR') + fmt(finalTotal);
        } else {
            document.getElementById('revTotal').textContent = '—';
            document.getElementById('breakdownAdults').textContent = '0';
            document.getElementById('breakdownAdultPrice').textContent = '—';
            document.getElementById('breakdownChildRow').style.display = 'none';
            document.getElementById('breakdownFinalTotal').textContent = '—';
        }
    }

    // ── Step navigation ──
    var panels = document.querySelectorAll('.bk-panel');
    var steps = document.querySelectorAll('.bk-step');
    var lines = document.querySelectorAll('.bk-step-line');

    function setStep(n) {
        panels.forEach(function (p) { p.classList.toggle('active', parseInt(p.dataset.panel, 10) === n); });
        steps.forEach(function (s) {
            var sNum = parseInt(s.dataset.step, 10);
            s.classList.toggle('active', sNum === n);
            s.classList.toggle('done', sNum < n);
        });
        lines.forEach(function (l) {
            l.classList.toggle('done', parseInt(l.dataset.line, 10) < n);
        });
        var wizard = document.querySelector('.bk-wizard');
        if (wizard) wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ── Package Search Filter ──
    var searchInput = document.getElementById('pkgSearchInput');
    var searchClear = document.getElementById('pkgSearchClear');
    var searchWrapper = document.querySelector('.bk-search-wrapper');
    var pkgGrid = document.getElementById('pkgGrid');
    var noResults = document.getElementById('pkgNoResults');

    function filterPackages(searchTerm) {
        var term = searchTerm.toLowerCase().trim();
        var visibleCount = 0;

        pkgCards.forEach(function (card) {
            var dest = (card.dataset.dest || '').toLowerCase();
            var title = (card.dataset.title || '').toLowerCase();
            var matches = dest.includes(term) || title.includes(term);

            if (matches || term === '') {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (noResults) {
            noResults.style.display = (visibleCount === 0 && term !== '') ? 'block' : 'none';
        }

        // Update clear button visibility
        if (searchWrapper) {
            searchWrapper.classList.toggle('has-value', term !== '');
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterPackages(this.value);
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                filterPackages('');
                searchInput.focus();
            }
        });
    }

    // ── Card click → select + AUTO-ADVANCE to step 2 ──
    pkgCards.forEach(function (card) {
        card.addEventListener('click', function () {
            pkgCards.forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            if (hiddenPkgId) hiddenPkgId.value = card.dataset.id;
            renderPackageDetails(card.dataset.id);
            fillChip();
            // brief pause so the selection animation is visible, then auto-switch
            setTimeout(function () { setStep(2); }, 350);
        });
    });

    // Step 2 → 3
    var step2Next = document.getElementById('step2Next');
    if (step2Next) {
        step2Next.addEventListener('click', function () {
            if (!getSelectedId()) { setStep(1); return; }
            fillChip();
            setStep(3);
        });
    }

    // Step 3 → 4
    var step3Next = document.getElementById('step3Next');
    if (step3Next) {
        step3Next.addEventListener('click', function () {
            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }
            fillReview();
            setStep(4);
        });
    }

    // Back buttons
    document.querySelectorAll('[data-goto]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setStep(parseInt(btn.dataset.goto, 10));
        });
    });

    // Submit loading state (prevents double-click + shows progress)
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            if (form.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
            }
        });
    }

    // Auto-advance when arriving with a preselected package (?package=slug)
    if (hiddenPkgId && hiddenPkgId.value && BKG_DETAILS[hiddenPkgId.value]) {
        renderPackageDetails(hiddenPkgId.value);
        fillChip();
        setTimeout(function () { setStep(2); }, 400);
    }

    // Smooth-scroll to wizard on load (when preselected)
    if (window.location.search.indexOf('package=') !== -1) {
        var wizard = document.querySelector('.bk-wizard');
        if (wizard) setTimeout(function () { wizard.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 350);
    }
})();

// ===== SUCCESS PARTY POPUP =====
(function () {
    var overlay = document.getElementById('partyOverlay');
    if (!overlay) return;

    // Confetti generator (fresh burst each open)
    var confettiWrap = document.getElementById('confettiWrap');
    var colors = ['#003A59', '#0f6a94', '#8CC7E8', '#12b76a', '#FFD9A0', '#5fa8c9'];
    var pieces = 46;
    function spawnConfetti() {
        if (!confettiWrap) return;
        var html = '';
        for (var i = 0; i < pieces; i++) {
            var left = (Math.random() * 100).toFixed(1);
            var delay = (Math.random() * 2.4).toFixed(2);
            var dur = (2.4 + Math.random() * 2.2).toFixed(2);
            var color = colors[i % colors.length];
            var w = 6 + Math.random() * 6;
            var h = 9 + Math.random() * 9;
            var round = Math.random() > 0.5 ? 'border-radius:50%;' : '';
            html += '<div class="bk-confetti-piece" style="left:' + left + '%;width:' + w.toFixed(1) + 'px;height:' + h.toFixed(1) + 'px;background:' + color + ';' + round + 'animation-duration:' + dur + 's;animation-delay:' + delay + 's;"></div>';
        }
        confettiWrap.innerHTML = html;
    }

    var lastFocus = null;
    function openParty() {
        lastFocus = document.activeElement;
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        spawnConfetti();
        var closeBtn = document.getElementById('partyClose');
        if (closeBtn) setTimeout(function () { closeBtn.focus(); }, 600);
    }
    function closeParty() {
        overlay.classList.remove('show');
        document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    var closeBtn = document.getElementById('partyClose');
    if (closeBtn) closeBtn.addEventListener('click', closeParty);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeParty();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeParty();
    });

    // Show after a beat so the page paints first
    setTimeout(openParty, 350);
})();
</script>
</body>
</html>
