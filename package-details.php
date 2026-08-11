<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// ── Load package ──────────────────────────────────────────────
$slug = trim($_GET['slug'] ?? '');
$pkg  = null;

if ($slug) {
    $pkg = getPackageBySlug($slug);
}

// If no slug or not found, try first published package as default
if (!$pkg) {
    $result = getAllPublishedPackages([], 1, 1);
    if (!empty($result['packages'])) {
        $pkg = getPackageBySlug($result['packages'][0]['slug']);
    }
}

// Still nothing? Show 404-style message
$notFound = ($pkg === null);

// Helper: package field with fallback
function pkgField(array $pkg, string $key, string $fallback = ''): string {
    return e((string)($pkg[$key] ?? $fallback));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pkg ? e($pkg['title']).' – VMS Go Vista' : 'Package Not Found – VMS Go Vista' ?></title>
    <?php $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
    <base href="<?= $basePath ?>/">
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
    <link rel="stylesheet preload" href="assets/css/plugins/aos.css" as="style">
    <link rel="preload" href="assets/hero4.webp" as="image">
    <style>
        /* Page-specific overrides */
        /* ===== GLASS PILL BUTTONS — index-three.php style ===== */
        .vms-logo-img{
            height: 70px;
            width: auto;
        }
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
            font-family: inherit;
            transition: background .22s ease, color .22s ease, border-color .22s ease, transform .22s ease, box-shadow .22s ease;
            width: fit-content;
        }
        .vms-glass-btn:hover {
            background: #003A59;
            color: #fff;
            border-color: #003A59;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px -10px rgba(0,58,89,.45);
        }
        /* Send-Enquiry loading state — instant feedback + double-click guard */
        .vms-glass-btn.loading {
            pointer-events: none;
            background: #003A59;
            color: #fff;
            border-color: #003A59;
            opacity: .96;
        }
        .vms-glass-btn .vms-glass-spinner {
            display: none;
            align-items: center;
            justify-content: center;
        }
        .vms-glass-btn.loading .vms-glass-spinner {
            display: inline-flex;
            animation: vmsGlassSpinIn .3s ease;
        }
        .vms-glass-btn.loading .vms-glass-spinner i {
            animation: vmsGlassSpin 1s linear infinite;
            font-size: 15px;
        }
        .vms-glass-btn.loading .vms-glass-arrow {
            display: none;
        }
        @keyframes vmsGlassSpin { to { transform: rotate(360deg); } }
        @keyframes vmsGlassSpinIn {
            0% { transform: scale(0) rotate(-90deg); }
            100% { transform: scale(1) rotate(0); }
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
        .vms-glass-btn .vms-glass-arrow svg {
            width: 15px;
            height: 15px;
            display: block;
        }
        .vms-glass-btn .vms-glass-arrow svg path { fill: currentColor; }
        .vms-glass-btn.w-100 { width: 100%; justify-content: center; }
        /* Enquiry form field labels */
        .enq-field-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #003A59;
            margin-bottom: 7px;
            letter-spacing: .01em;
        }
        .enq-field-label .req { color: #0f6a94; }
        .single-input input[type="date"],
        .single-input input[type="text"],
        .single-input input[type="number"],
        .single-input input[type="email"] { color: #1d2939; }
        .single-input input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: .6; }
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
        body.dark-mode .rts-tour-details-area,body.dark-mode section{background-color:#0d1117!important;}
        .rts-tour-details-area{background:var(--color-bg-1)!important;}
        body.dark-mode .content-box{background:#161b22!important;border-color:#30363d!important;}
        body.dark-mode .rts-breadcrumb-area{background:#0d1117!important;}

        /* ===== PREMIUM MODERN GALLERY ===== */
        .tour-gallery {
            margin-bottom: 60px;
            padding: 0;
        }
        
        /* Gallery Grid - Bento/Masonry Style */
        .tour-gallery-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: repeat(2, minmax(180px, 280px));
            gap: 16px;
            border-radius: 20px;
            overflow: hidden;
        }

        .tour-gallery-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            background: var(--color-bg-2);
        }

        .tour-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94), filter 0.4s ease;
        }

        .tour-gallery-item:hover img {
            transform: scale(1.08);
            filter: brightness(1.05);
        }

        /* Hero - Large Featured Image */
        .tour-gallery-item.hero {
            grid-column: span 8;
            grid-row: span 2;
            min-height: 380px;
        }

        /* Secondary Images */
        .tour-gallery-item.medium {
            grid-column: span 4;
            grid-row: span 1;
        }

        /* Small Images */
        .tour-gallery-item.small {
            grid-column: span 2;
            grid-row: span 1;
        }

        /* Overlay Effects */
        .tour-gallery-item .item-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.7) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            z-index: 2;
        }

        .tour-gallery-item:hover .item-overlay {
            opacity: 1;
        }

        .tour-gallery-item .item-overlay .zoom-icon {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #003A59;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            transform: scale(0.8) translateY(20px);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .tour-gallery-item:hover .item-overlay .zoom-icon {
            transform: scale(1) translateY(0);
        }

        .tour-gallery-item .item-overlay .view-text {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transform: translateY(15px);
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.05s;
        }

        .tour-gallery-item:hover .item-overlay .view-text {
            transform: translateY(0);
        }

        /* Photo Count Badge */
        .tour-gallery-item .photo-count {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 3;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .tour-gallery-item:hover .photo-count {
            background: rgba(0,58,89,0.85);
        }

        .tour-gallery-item .photo-count i {
            font-size: 14px;
        }

        /* More Photos Overlay */
        .tour-gallery-item .more-photos {
            position: absolute;
            inset: 0;
            background: rgba(0,58,89,0.85);
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            z-index: 3;
            transition: background 0.3s ease;
        }

        .tour-gallery-item:hover .more-photos {
            background: rgba(0,58,89,0.95);
        }

        .tour-gallery-item .more-photos .count-number {
            font-size: 36px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .tour-gallery-item .more-photos .count-label {
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        /* Gallery heading */
        .gallery-section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            padding: 0 4px;
        }
        .gallery-section-heading h3 {
            font-size: 28px;
            font-weight: 700;
            color: #003A59;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .gallery-section-heading h3 i {
            color: #C9A567;
            font-size: 24px;
        }
        .gallery-section-heading .gallery-nav-btns {
            display: flex;
            gap: 10px;
        }
        .gallery-section-heading .gallery-nav-btns button {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(0,58,89,0.15);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #003A59;
            font-size: 16px;
        }
        .gallery-section-heading .gallery-nav-btns button:hover {
            background: #003A59;
            border-color: #003A59;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,58,89,0.25);
        }

        /* Dark mode */
        body.dark-mode .gallery-section-heading h3 {
            color: #e6edf3;
        }
        body.dark-mode .gallery-section-heading h3 i {
            color: #C9A567;
        }
        body.dark-mode .gallery-section-heading .gallery-nav-btns button {
            background: #161b22;
            border-color: rgba(255,255,255,0.1);
            color: #e6edf3;
        }
        body.dark-mode .gallery-section-heading .gallery-nav-btns button:hover {
            background: #003A59;
            border-color: #003A59;
            color: #fff;
        }
        body.dark-mode .tour-gallery-item .photo-count {
            background: rgba(0,0,0,0.7);
        }
        body.dark-mode .tour-gallery-item:hover .photo-count {
            background: rgba(0,58,89,0.9);
        }

        /* Responsive Gallery */
        @media (max-width: 991px) {
            .tour-gallery-grid {
                grid-template-columns: repeat(6, 1fr);
                grid-template-rows: repeat(3, minmax(140px, 200px));
                gap: 12px;
            }
            .tour-gallery-item.hero {
                grid-column: span 6;
                grid-row: span 1;
                min-height: 240px;
            }
            .tour-gallery-item.medium {
                grid-column: span 3;
                grid-row: span 1;
            }
            .tour-gallery-item.small {
                grid-column: span 2;
                grid-row: span 1;
            }
            .gallery-section-heading h3 {
                font-size: 22px;
            }
            .gallery-section-heading .gallery-nav-btns button {
                width: 38px;
                height: 38px;
                font-size: 14px;
            }
        }

        @media (max-width: 575px) {
            .tour-gallery-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: auto;
                gap: 10px;
            }
            .tour-gallery-item.hero {
                grid-column: span 2;
                grid-row: span 1;
                min-height: 200px;
            }
            .tour-gallery-item.medium,
            .tour-gallery-item.small {
                grid-column: span 1;
                grid-row: span 1;
                min-height: 120px;
            }
            .tour-gallery-item .more-photos .count-number {
                font-size: 28px;
            }
            .tour-gallery-item .more-photos .count-label {
                font-size: 11px;
            }
            .tour-gallery-item .item-overlay .zoom-icon {
                width: 48px;
                height: 48px;
                font-size: 18px;
            }
            .tour-gallery-item .item-overlay .view-text {
                font-size: 12px;
            }
            .gallery-section-heading {
                margin-bottom: 20px;
            }
            .gallery-section-heading h3 {
                font-size: 20px;
            }
            .gallery-section-heading h3 i {
                font-size: 20px;
            }
        }

        /* Magnific Popup overrides */
        .mfp-fade.mfp-bg {
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .mfp-fade.mfp-bg.mfp-ready {
            opacity: 0.92;
        }
        .mfp-fade.mfp-bg.mfp-removing {
            opacity: 0;
        }
        .mfp-fade.mfp-wrap .mfp-content {
            opacity: 0;
            transform: scale(0.92);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .mfp-fade.mfp-wrap.mfp-ready .mfp-content {
            opacity: 1;
            transform: scale(1);
        }
        .mfp-fade.mfp-wrap.mfp-removing .mfp-content {
            opacity: 0;
            transform: scale(0.92);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .tour-gallery .gallery-hero {
                min-height: 240px;
                max-height: 320px;
            }
            .tour-gallery .gallery-thumb {
                min-height: 80px;
                max-height: 140px;
            }
        }
        @media (max-width: 575px) {
            .tour-gallery .gallery-hero {
                min-height: 200px;
                max-height: 260px;
            }
            .tour-gallery .gallery-thumb {
                min-height: 70px;
                max-height: 110px;
            }
            .gallery-section-heading h3 {
                font-size: 18px;
            }
        }
        .bromo-book-btn a:hover .bromo-arrow{background:#003A59;color:#fff;}
        /* ===== PREMIUM ITINERARY — timeline design (FAQ section untouched via :not(.faq)) ===== */
        .tour-details-tab-content .tab-content-inner .itinerary-area {
            position: relative;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-header {
            margin-bottom: 28px;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #003A59;
            letter-spacing: -0.3px;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-list {
            padding-left: 0;
            display: grid;
            gap: 16px;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-list::after {
            display: none;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-item {
            border: 1px solid #e6edf2;
            border-radius: 16px;
            background: #fff;
            overflow: hidden;
            transition: border-color .3s ease, box-shadow .3s ease, transform .3s ease;
            box-shadow: 0 2px 10px -4px rgba(0,58,89,.08);
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-item:hover {
            border-color: rgba(201,165,103,.55);
            box-shadow: 0 10px 30px -12px rgba(0,58,89,.18);
            transform: translateY(-2px);
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-item.active {
            border-color: rgba(201,165,103,.65);
            box-shadow: 0 12px 34px -14px rgba(0,58,89,.2);
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-title {
            display: flex;
            align-items: center;
            gap: 16px;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 20px 22px;
            line-height: 1.35;
            font-size: 16px;
            font-weight: 600;
            color: #003A59;
            cursor: pointer;
            position: relative;
            transition: color .25s ease;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-title::after {
            display: none;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-title:hover {
            color: #003A59;
        }
        .tour-details-tab-content .tab-content-inner .itin-day-badge {
            flex-shrink: 0;
            min-width: 58px;
            height: 44px;
            padding: 0 12px;
            border-radius: 12px;
            background: linear-gradient(135deg, #003A59 0%, #02507a 100%);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 16px -6px rgba(0,58,89,.5);
        }
        .tour-details-tab-content .tab-content-inner .itin-title-inner {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .tour-details-tab-content .tab-content-inner .itin-title-text {
            font-size: 16px;
            font-weight: 600;
            color: #003A59;
            line-height: 1.35;
        }
        .tour-details-tab-content .tab-content-inner .itin-sub {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .tour-details-tab-content .tab-content-inner .itin-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #003A59;
            background: rgba(0,58,89,.07);
            border: 1px solid rgba(0,58,89,.12);
            border-radius: 999px;
            padding: 5px 12px;
        }
        .tour-details-tab-content .tab-content-inner .itin-tag i {
            color: #C9A567;
            font-size: 11px;
        }
        .tour-details-tab-content .tab-content-inner .itin-tag.meals {
            background: rgba(201,165,103,.1);
            border-color: rgba(201,165,103,.28);
            color: #8a6a2f;
        }
        .tour-details-tab-content .tab-content-inner .itin-tag.meals i {
            color: #C9A567;
        }
        .tour-details-tab-content .tab-content-inner .itin-chevron {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(0,58,89,.06);
            border: 1px solid rgba(0,58,89,.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #003A59;
            font-size: 13px;
            transition: transform .35s cubic-bezier(.25,.46,.45,.94), background .3s ease, color .3s ease;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-item.active .itin-chevron {
            transform: rotate(180deg);
            background: #003A59;
            color: #fff;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height .45s cubic-bezier(.25,.46,.45,.94);
            padding: 0 22px;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-content p {
            font-size: 14.5px;
            color: #4a5a68;
            line-height: 1.75;
            padding: 0 0 22px 74px;
            margin: 0;
        }
        .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-item.active .itinerary-content {
            max-height: 2000px;
            padding-top: 4px;
        }
        body.dark-mode .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-item {
            background: #161b22;
            border-color: #30363d;
            box-shadow: none;
        }
        body.dark-mode .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-title {
            color: #e6edf3;
        }
        body.dark-mode .tour-details-tab-content .tab-content-inner .itin-title-text {
            color: #e6edf3;
        }
        body.dark-mode .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-content p {
            color: #9da7b3;
        }
        body.dark-mode .tour-details-tab-content .tab-content-inner .itin-tag {
            background: rgba(255,255,255,.07);
            border-color: rgba(255,255,255,.14);
            color: #e6edf3;
        }
        body.dark-mode .tour-details-tab-content .tab-content-inner .itin-chevron {
            background: rgba(255,255,255,.07);
            border-color: rgba(255,255,255,.14);
            color: #e6edf3;
        }
        body.dark-mode .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-item.active .itin-chevron {
            background: #003A59;
            color: #fff;
        }
        @media (max-width: 575px) {
            .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-title {
                flex-wrap: wrap;
                gap: 12px;
                padding: 16px;
            }
            .tour-details-tab-content .tab-content-inner .itinerary-area:not(.faq) .itinerary-content p {
                padding-left: 0;
            }
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

        @keyframes bromoStickyIn{0%{transform:translateY(-15px)}100%{transform:translateY(0)}}

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
        .vms-big-text {
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
<script>
// Suppress errors from third-party scripts
 (e.g. browser extensions, analytics)
// that can cause console errors like redeclared identifiers
window.addEventListener('error', function(e) {
    if (e.filename && !e.filename.includes(location.hostname)) {
        e.preventDefault();
    }
}, true);
</script>
<body class="home-bg with-sidebar onepage" data-turbo-cache="false">

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
    <div class="bromo-book-btn">
        <a href="booking.php?package=<?= e($pkg['slug'] ?? '') ?>">
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
        <a href="booking.php?package=<?= e($pkg['slug'] ?? '') ?>" class="bromo-mobile-nav-cta">
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
<div class="rts-breadcrumb-area four" data-bg-src="assets/hero4.webp">
    <div class="container">
        <div class="nav-bread-crumb">
            <a href=".">Home</a>
            <span><i class="fa-regular fa-chevron-right"></i></span>
            <a href="package">Packages</a>
            <span><i class="fa-regular fa-chevron-right"></i></span>
            <a href="#" class="current"><?= $pkg ? e($pkg['destination'] ?? 'Details') : 'Not Found' ?></a>
        </div>
        <h1 class="title"><?= $pkg ? e($pkg['title']) : 'Package Not Found' ?></h1>
    </div>
</div>

<?php if ($notFound): ?>
<div class="rts-tour-details-area pt--60 rts-section-gapBottom bg-white">
    <div class="container text-center py-5">
        <h3>Sorry, this tour could not be found.</h3>
        <p>The package you are looking for does not exist or has been removed.</p>
        <a href="package" class="vms-glass-btn">Browse All Packages <span class="vms-glass-arrow"><i class="fa-solid fa-arrow-right"></i></span></a>
    </div>
</div>
<?php else: ?>

<?php
// ── Prepare package data ──────────────────────────────────────
$galleryPaths = packageGalleryImages($pkg);
$mainImage    = packageCoverImageUrl($pkg);
$fallbackImg  = packageImageUrl(null);
$itinerary   = $pkg['itinerary']  ?? [];
$inclusions  = $pkg['inclusions'] ?? [];
$exclusions  = $pkg['exclusions'] ?? [];
$highlights  = $pkg['highlights'] ?? [];
$faqs        = $pkg['faqs']       ?? [];
$info        = $pkg['info']       ?? [];

// ── Section availability (only show tabs that actually have data) ──
$hasOverview   = !empty(trim((string)($pkg['overview'] ?? ''))) || !empty($highlights);
$hasItinerary  = !empty($itinerary);
$hasCost       = !empty($inclusions) || !empty($exclusions);
$hasFaqs       = !empty($faqs);
$hasMap        = !empty(trim((string)($pkg['map_embed'] ?? '')));

$availableTabs = [];
if ($hasOverview)   $availableTabs[] = ['overview',  'Overview'];
if ($hasItinerary)  $availableTabs[] = ['itinerary', 'Itinerary'];
if ($hasCost)       $availableTabs[] = ['cost',      'Cost'];
if ($hasFaqs)       $availableTabs[] = ['faq',       'FAQs'];
if ($hasMap)        $availableTabs[] = ['map',       'Location'];
$firstTab = $availableTabs[0][0] ?? '';

$childPrice  = (float)($pkg['price_per_child']  ?? 0);
$price       = (float)($pkg['price_original']   ?? 0);
$discounted  = (float)($pkg['price_discounted'] ?? $price);
$discPct     = $price > 0 && $discounted < $price ? round((1 - $discounted/$price)*100) : 0;
$currency    = $pkg['currency'] ?? 'INR';
$rating      = (float)($pkg['rating']           ?? 5);
$reviewCount = (int)($pkg['review_count']       ?? 0);
$days        = (int)($pkg['days']               ?? 0);
?>

<div class="rts-tour-details-area pt--60 rts-section-gapBottom bg-white">
    <div class="container">



        <!-- ===== BOTTOM CONTENT ===== -->
        <div class="bottom-content-area g-5 mt--50">

            <!-- LEFT COLUMN -->
            <div class="left">

                <!-- Feature meta box -->
                <div class="tour-details-wrapper content-box radius-10 border overflow-hidden">
                    <div class="tour-content">
                        <div class="left-content">
                            <h4 class="title"><?= pkgField($pkg,'title') ?></h4>
                            <ul class="meta-area">
                                <li>
                                    <?= starRatingHtml($rating) ?>
                                    <?= number_format($rating,1) ?>
                                    <?= $reviewCount ? "($reviewCount review".($reviewCount!=1?'s':'').")" : '' ?>
                                </li>
                                <li><i class="fa-regular fa-location-dot"></i> <?= pkgField($pkg,'destination') ?></li>
                            </ul>
                        </div>
                        <div class="right-content">
                            <div class="day-left">
                                <h4><?= str_pad($days,2,'0',STR_PAD_LEFT) ?></h4>
                                <p>days</p>
                            </div>
                        </div>
                    </div>

                    <!-- Package info grid -->
                    <ul class="feature-list-area">
                        <?php if(!empty($pkg['transportation'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/03.svg" alt=""></div><div class="text"><p>Transportation</p><h6><?= pkgField($pkg,'transportation') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['accommodation'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/04.svg" alt=""></div><div class="text"><p>Accommodation</p><h6><?= pkgField($pkg,'accommodation') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if($days > 0): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/10.svg" alt=""></div><div class="text"><p>Duration</p><h6><?= $days ?> Days / <?= max(0,$days-1) ?> Nights</h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['tour_type'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/12.svg" alt=""></div><div class="text"><p>Tour Type</p><h6><?= pkgField($pkg,'tour_type') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['meals'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/09.svg" alt=""></div><div class="text"><p>Meals</p><h6><?= pkgField($pkg,'meals') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['language'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/10.svg" alt=""></div><div class="text"><p>Language</p><h6><?= pkgField($pkg,'language') ?></h6></div></li>
                        <?php endif; ?>
                        <?php $gMin = (int)($pkg['group_size_min'] ?? 0); $gMax = (int)($pkg['group_size_max'] ?? 0); ?>
                        <?php if($gMin > 0 || $gMax > 0): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/12.svg" alt=""></div><div class="text"><p>Group Size</p><h6><?= $gMin > 0 && $gMax > 0 ? $gMin.' - '.$gMax : ($gMin > 0 ? $gMin : $gMax) ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['min_age'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/13.svg" alt=""></div><div class="text"><p>Minimum Age</p><h6><?= pkgField($pkg,'min_age') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['max_age'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/13.svg" alt=""></div><div class="text"><p>Maximum Age</p><h6><?= pkgField($pkg,'max_age') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['max_altitude'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/06.svg" alt=""></div><div class="text"><p>Maximum Altitude</p><h6><?= pkgField($pkg,'max_altitude') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['departure_from'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/06.svg" alt=""></div><div class="text"><p>Departure from</p><h6><?= pkgField($pkg,'departure_from') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['best_season'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/06.svg" alt=""></div><div class="text"><p>Best season</p><h6><?= pkgField($pkg,'best_season') ?></h6></div></li>
                        <?php endif; ?>
                        <?php if(!empty($pkg['fitness_level'])): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/06.svg" alt=""></div><div class="text"><p>Fitness level</p><h6><?= pkgField($pkg,'fitness_level') ?></h6></div></li>
                        <?php endif; ?>
                        <?php foreach(array_slice($info,0,3) as $inf): ?>
                        <li><div class="icon"><img src="assets/images/trip/icon/06.svg" alt=""></div><div class="text"><p><?= e($inf['title'] ?? '') ?></p><h6><?= e($inf['content'] ?? '') ?></h6></div></li>
                        <?php endforeach; ?>
                    </ul>
                </div>


                <!-- ===== TABS (only if any data) ===== -->
                <?php if (!empty($availableTabs)): ?>
                <div class="content-box tour-details-tab-area radius-10 border overflow-hidden">
                    <ul class="nav nav-tabs border-bottom" id="myTab" role="tablist">
                        <?php foreach ($availableTabs as $ti => $tab): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link<?= $ti === 0 ? ' active' : '' ?>" data-bs-toggle="tab" data-bs-target="#<?= $tab[0] ?>" type="button" role="tab"><?= $tab[1] ?></button>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tour-details-tab-content" id="myTabContent">

                        <!-- OVERVIEW -->
                        <?php if ($hasOverview): ?>
                        <div class="tab-pane fade<?= $firstTab === 'overview' ? ' show active' : '' ?>" id="overview" role="tabpanel">
                            <div class="tab-content-inner">
                                <?php if (!empty(trim((string)($pkg['overview'] ?? '')))): ?>
                                <div class="overview-area mb--60">
                                    <h5 class="title mb--15">Overview</h5>
                                    <p class="desc"><?= nl2br(e($pkg['overview'])) ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if(!empty($highlights)): ?>
                                <div class="highlight-area">
                                    <h5 class="title">Highlights:</h5>
                                    <ul>
                                        <?php foreach($highlights as $h): ?>
                                        <li><i class="fa-regular fa-check"></i> <?= e($h['item']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ITINERARY -->
                        <?php if ($hasItinerary): ?>
                        <div class="tab-pane fade<?= $firstTab === 'itinerary' ? ' show active' : '' ?>" id="itinerary" role="tabpanel">
                            <div class="tab-content-inner">
                                <div class="itinerary-area">
                                    <div class="itinerary-header">
                                        <h2>Itinerary</h2>
                                        <div class="expand-toggle">
                                            <span>Expand all</span>
                                            <label class="switch"><input type="checkbox" id="expandAll"><span class="slider"></span></label>
                                        </div>
                                    </div>
                                    <div class="itinerary-list">
                                        <?php
                                        $prevDay = 0;
                                        foreach ($itinerary as $it):
                                            $rawDay   = (int)($it['day_number'] ?? 0);
                                            $dayNum   = ($rawDay > $prevDay) ? $rawDay : ($prevDay + 1);
                                            $prevDay  = $dayNum;
                                            $dayTitle = trim((string)($it['title'] ?? ''));
                                            $dayDesc  = trim((string)($it['description'] ?? ''));
                                            $dayActs  = trim((string)($it['activities'] ?? ''));
                                            $dayMeals = trim((string)($it['meals'] ?? ''));
                                        ?>
                                        <div class="itinerary-item">
                                            <button class="itinerary-title" type="button">
                                                <span class="itin-day-badge">Day <?= $dayNum ?></span>
                                                <span class="itin-title-inner">
                                                    <span class="itin-title-text"><?= $dayTitle !== '' ? e($dayTitle) : 'Day ' . $dayNum ?></span>
                                                    <span class="itin-sub">
                                                        <?php if ($dayActs !== ''): ?>
                                                        <span class="itin-tag"><i class="fa-solid fa-map-location-dot"></i> <?= e($dayActs) ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($dayMeals !== '' && !in_array(strtolower($dayMeals), ['', 'kuch nahi', 'none', 'n/a', 'na', '-', 'not applicable'], true)): ?>
                                                        <span class="itin-tag meals"><i class="fa-solid fa-utensils"></i> <?= e($dayMeals) ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                </span>
                                                <span class="itin-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                                            </button>
                                            <div class="itinerary-content">
                                                <?php if ($dayDesc !== ''): ?>
                                                <p><?= nl2br(e($dayDesc)) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- COST -->
                        <?php if ($hasCost): ?>
                        <div class="tab-pane fade<?= $firstTab === 'cost' ? ' show active' : '' ?>" id="cost" role="tabpanel">
                            <div class="tab-content-inner">
                                <div class="highlight-area">
                                    <h5 class="title">Trip Cost Breakdown</h5>
                                    <?php if(!empty($inclusions)): ?>
                                    <ul class="mb--30">
                                        <li class="tag mb--10 c-p">Included:</li>
                                        <?php foreach($inclusions as $inc): ?>
                                        <li><i class="fa-regular fa-check"></i> <?= e($inc['item']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                    <?php if(!empty($exclusions)): ?>
                                    <ul>
                                        <li class="tag mb--10 c-p">Not Included:</li>
                                        <?php foreach($exclusions as $exc): ?>
                                        <li><i class="fa-regular fa-times"></i> <?= e($exc['item']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- FAQS -->
                        <?php if ($hasFaqs): ?>
                        <div class="tab-pane fade<?= $firstTab === 'faq' ? ' show active' : '' ?>" id="faq" role="tabpanel">
                            <div class="tab-content-inner">
                                <div class="itinerary-area faq">
                                    <div class="itinerary-header">
                                        <h2>Frequently Asked Questions</h2>
                                        <div class="expand-toggle">
                                            <span>Expand all</span>
                                            <label class="switch"><input type="checkbox" id="expandAll2"><span class="slider"></span></label>
                                        </div>
                                    </div>
                                    <div class="itinerary-list">
                                        <?php foreach($faqs as $faq): ?>
                                        <div class="itinerary-item">
                                            <button class="itinerary-title">
                                                <span class="icon"><img src="assets/images/trip/icon/14.svg" alt=""></span>
                                                <?= e($faq['question'] ?? '') ?>
                                            </button>
                                            <div class="itinerary-content">
                                                <p><?= nl2br(e($faq['answer'] ?? '')) ?></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- MAP -->
                        <?php if ($hasMap): ?>
                        <div class="tab-pane fade<?= $firstTab === 'map' ? ' show active' : '' ?>" id="map" role="tabpanel">
                            <div class="tab-content-inner">
                                <div class="highlight-area">
                                    <h5 class="title">Tour Map</h5>
                                    <div class="map-area overflow-hidden radius-10">
                                        <?= $pkg['map_embed'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- /tab-content -->
                </div>
                <?php endif; ?>

                <!-- ===== ENQUIRY FORM ===== -->
                <div class="contact-area content-box radius-10 border overflow-hidden" id="contact-form">
                    <h5 class="title mb--30">Send your enquiry via the form below.</h5>
                    <?php if (isset($_GET['enquiry']) && $_GET['enquiry'] === 'sent'): ?>
                    <div style="background:rgba(52,199,89,.12);border:1px solid rgba(52,199,89,.35);color:#248a3d;border-radius:12px;padding:14px 16px;margin-bottom:20px;font-weight:600;">
                        <i class="fa-solid fa-circle-check me-2"></i>Your enquiry has been sent! We will get back to you soon.
                    </div>
                    <?php elseif (isset($_GET['enquiry']) && $_GET['enquiry'] === 'error'): ?>
                    <div style="background:rgba(255,59,48,.1);border:1px solid rgba(255,59,48,.3);color:#d70015;border-radius:12px;padding:14px 16px;margin-bottom:20px;font-weight:600;">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <?php
                        $reason = $_GET['reason'] ?? '';
                        if ($reason === 'email') {
                            echo 'Please enter a valid email address.';
                        } elseif ($reason === 'missing') {
                            echo 'Please fill in all required fields (name, email, message).';
                        } else {
                            echo 'Something went wrong. Please try again or contact us directly.';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                    <form id="enquiry-form" action="<?= SITE_URL ?>/enquiry-submit.php" method="POST" class="contact-form border radius-10">
                        <?php if ($pkg): ?>
                        <input type="hidden" name="package_id" value="<?= (int)$pkg['id'] ?>">
                        <input type="hidden" name="package_title" value="<?= e($pkg['title']) ?>">
                        <input type="hidden" name="package_slug" value="<?= e($pkg['slug']) ?>">
                        <?php endif; ?>
                        <div class="input-div">
                            <div class="row g-24">
                                <div class="col-lg-6"><div class="single-input"><input type="text" name="first_name" placeholder="Enter your first name*" required></div></div>
                                <div class="col-lg-6"><div class="single-input"><input type="text" name="last_name" placeholder="Enter your last name*" required></div></div>
                                <div class="col-lg-12"><div class="single-input"><input type="email" name="email" placeholder="Enter your email address*" required></div></div>
                                <div class="col-lg-6"><div class="single-input"><input type="text" name="country" placeholder="Choose a country"></div></div>
                                <div class="col-lg-6"><div class="single-input"><input type="text" name="phone" placeholder="Enter your contact number"></div></div>
                                <div class="col-lg-6"><div class="single-input"><input type="number" name="adults" placeholder="Number of adults" min="0"></div></div>
                                <div class="col-lg-6"><div class="single-input"><input type="number" name="children" placeholder="Number of children" min="0"></div></div>
                                <div class="col-lg-12"><div class="single-input">
                                    <label class="enq-field-label" for="travelDateField">Travelling Date <span class="req">*</span></label>
                                    <input type="date" id="travelDateField" name="travel_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                </div></div>
                                <div class="col-lg-12"><div class="single-input"><textarea name="message" placeholder="Enter your message*" required></textarea></div></div>
                                <div class="col-lg-12"><button type="submit" class="vms-glass-btn">Send Enquiry <span class="vms-glass-arrow"><i class="fa-solid fa-paper-plane"></i></span></button></div>
                            </div>
                        </div>
                    </form>
                </div>

            </div><!-- /left column -->

            <!-- ===== RIGHT PRICING SIDEBAR ===== -->
            <div class="right">
                <div class="sticky-top">
                    <div class="content-box radius-10 border overflow-hidden pricing-box">
                        <?php if($discPct > 0): ?>
                        <p class="tag"><?= $discPct ?>% OFF</p>
                        <?php endif; ?>
                        <div class="price-area">
                            <!-- Adult Price -->
                            <div class="price">
                                <?php if($discPct > 0): ?>
                                <div class="prev"><h6>From <span><?= formatPrice($price, $currency) ?></span></h6></div>
                                <?php endif; ?>
                                <div class="current"><h5><?= formatPrice($discPct > 0 ? $discounted : ($price ?: $discounted), $currency) ?> <span>/ Adult</span></h5></div>
                            </div>
                            <!-- Child Price -->
                            <?php if($childPrice > 0): ?>
                            <div class="price">
                                <?php if($discPct > 0): ?>
                                <div class="prev"><h6>From <span><?= formatPrice($price, $currency) ?></span></h6></div>
                                <?php endif; ?>
                                <div class="current"><h5><?= formatPrice($childPrice, $currency) ?> <span>/ Child</span></h5></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="mid-area">
                            <ul>
                                <li><i class="fa-regular fa-check"></i> Best Price Guaranteed</li>
                                <li><i class="fa-regular fa-check"></i> Emergency Support</li>
                                <li><i class="fa-regular fa-check"></i> No Booking Fees</li>
                            </ul>
                        </div>
                        <a href="#contact-form" class="vms-glass-btn w-100">Check Availability <span class="vms-glass-arrow"><i class="fa-regular fa-calendar-check"></i></span></a>
                        <p class="desc">Need help with booking? <a href="#contact-form">Send Us A Message</a></p>
                    </div>
                </div>
            </div>

        </div><!-- /bottom-content-area -->

        <!-- ===== PROFESSIONAL GALLERY ===== -->
        <?php
        // Build full gallery image list: main + all gallery images
        $galleryImages = [];
        $galleryImages[] = ['url' => $mainImage, 'title' => $pkg['title'] ?? ''];
        foreach ($galleryPaths as $gi) {
            $galleryImages[] = ['url' => packageImageUrl($gi['image_path']), 'title' => $pkg['title'] ?? ''];
        }
        // Remove exact duplicates
        $seen = [];
        $uniqueImages = [];
        foreach ($galleryImages as $img) {
            $key = $img['url'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueImages[] = $img;
            }
        }
        $galleryImages = $uniqueImages;
        $totalImages = count($galleryImages);
        ?>

        <section class="tour-gallery" data-aos="fade-up" data-aos-duration="800">
            <div class="gallery-section-heading">
                <h3><i class="fa-regular fa-images"></i>Tour Gallery</h3>
                <div class="gallery-nav-btns">
                    <button type="button" onclick="var el=document.querySelector('.tour-gallery-item.hero a');if(el&&window.mfpReady)el.click();else if(el)window.location=el.href;" title="View all photos">
                        <i class="fa-regular fa-expand"></i>
                    </button>
                </div>
            </div>

            <div class="tour-gallery-grid">
                <?php if ($totalImages > 0): ?>
                    <!-- HERO IMAGE - Large Featured -->
                    <div class="tour-gallery-item hero" data-aos="fade-right" data-aos-duration="700" data-aos-delay="100">
                        <a href="<?= e($galleryImages[0]['url']) ?>" class="gallery-popup" title="<?= e($galleryImages[0]['title']) ?>">
                            <img src="<?= e($galleryImages[0]['url']) ?>" alt="<?= pkgField($pkg,'title') ?>">
                            <div class="photo-count">
                                <i class="fa-regular fa-camera"></i>
                                <?= $totalImages ?> <?= $totalImages === 1 ? 'Photo' : 'Photos' ?>
                            </div>
                            <div class="item-overlay">
                                <div class="zoom-icon">
                                    <i class="fa-regular fa-plus"></i>
                                </div>
                                <span class="view-text">View Gallery</span>
                            </div>
                        </a>
                    </div>

                    <!-- SECONDARY IMAGES -->
                    <?php if ($totalImages > 1): ?>
                        <?php for ($i = 1; $i < $totalImages; $i++): ?>
                            <?php
                                $itemClass = ($i === 1) ? 'medium' : 'small';
                                $delay = 150 + ($i * 80);
                            ?>
                            <div class="tour-gallery-item <?= $itemClass ?>"
                                 data-aos="fade-left"
                                 data-aos-duration="700"
                                 data-aos-delay="<?= $delay ?>">
                                <a href="<?= e($galleryImages[$i]['url']) ?>" class="gallery-popup" title="<?= e($galleryImages[$i]['title']) ?>">
                                    <img src="<?= e($galleryImages[$i]['url']) ?>" alt="Gallery <?= $i + 1 ?>">
                                    <div class="item-overlay">
                                        <div class="zoom-icon">
                                            <i class="fa-regular fa-plus"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>

                    <!-- Fill remaining grid slots if needed -->
                    <?php if ($totalImages <= 1): ?>
                        <div class="tour-gallery-item medium" data-aos="fade-left" data-aos-duration="700" data-aos-delay="200">
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#f5f7fa 0%,#e4e8eb 100%);display:flex;align-items:center;justify-content:center;color:#999;font-size:14px;">
                                <i class="fa-regular fa-image" style="font-size:32px;margin-right:10px;"></i>
                                No more photos
                            </div>
                        </div>
                        <div class="tour-gallery-item small" data-aos="fade-left" data-aos-duration="700" data-aos-delay="280">
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#f5f7fa 0%,#e4e8eb 100%);display:flex;align-items:center;justify-content:center;color:#999;">
                                <i class="fa-regular fa-image" style="font-size:24px;"></i>
                            </div>
                        </div>
                        <div class="tour-gallery-item small" data-aos="fade-left" data-aos-duration="700" data-aos-delay="360">
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#f5f7fa 0%,#e4e8eb 100%);display:flex;align-items:center;justify-content:center;color:#999;">
                                <i class="fa-regular fa-image" style="font-size:24px;"></i>
                            </div>
                        </div>
                    <?php elseif ($totalImages === 2): ?>
                        <div class="tour-gallery-item small" data-aos="fade-left" data-aos-duration="700" data-aos-delay="280">
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#f5f7fa 0%,#e4e8eb 100%);display:flex;align-items:center;justify-content:center;color:#999;">
                                <i class="fa-regular fa-image" style="font-size:24px;"></i>
                            </div>
                        </div>
                        <div class="tour-gallery-item small" data-aos="fade-left" data-aos-duration="700" data-aos-delay="360">
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#f5f7fa 0%,#e4e8eb 100%);display:flex;align-items:center;justify-content:center;color:#999;">
                                <i class="fa-regular fa-image" style="font-size:24px;"></i>
                            </div>
                        </div>
                    <?php elseif ($totalImages === 3): ?>
                        <div class="tour-gallery-item small" data-aos="fade-left" data-aos-duration="700" data-aos-delay="360">
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#f5f7fa 0%,#e4e8eb 100%);display:flex;align-items:center;justify-content:center;color:#999;">
                                <i class="fa-regular fa-image" style="font-size:24px;"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

    </div><!-- /container -->
</div><!-- /tour-details-area -->

<?php endif; // end not-found else ?>

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

<!-- ===== FOOTER ===== -->
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
        <img src="assets/videofotter.gif" alt="VMS Go Vista" class="vms-video-bg" loading="lazy">
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
<div class="slider-drag-cursor"><i class="fas fa-angle-left me-2"></i> DRAG <i class="fas fa-angle-right ms-2"></i></div>

<!-- Scripts loaded once with error handling -->
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
<script defer src="assets/js/plugins/aos.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.min.js"></script>
<script>
document.addEventListener('turbo:load',function(){
    document.body.classList.add('loaded');
    if(typeof WOW!=='undefined'){new WOW().init();}
});
window.addEventListener('pageshow',function(){ document.body.classList.add('loaded'); });
</script>

<script>
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

<script>
// Initialize gallery and animations once DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // --- Mobile Menu ---
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

    // --- AOS (Animate on Scroll) ---
    if (typeof AOS !== 'undefined') {
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic'
        });
    }

    // --- Magnific Popup Gallery ---
    if (typeof jQuery !== 'undefined' && $.fn.magnificPopup) {
        $('.gallery-popup').magnificPopup({
            type: 'image',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1]
            },
            mainClass: 'mfp-fade',
            removalDelay: 300,
            fixedContentPos: true,
            image: {
                verticalFit: true,
                titleSrc: function(item) {
                    return item.el.attr('title') || '';
                }
            },
            callbacks: {
                open: function() {
                    document.body.style.overflow = 'hidden';
                },
                close: function() {
                    document.body.style.overflow = '';
                }
            }
        });
        // Signal that Magnific Popup is ready (for gallery button)
        window.mfpReady = true;
    }
});

// ===== Send Enquiry — instant button feedback + double-click guard =====
(function () {
    var form = document.getElementById('enquiry-form');
    if (!form) return;
    var btn = form.querySelector('button[type="submit"]');
    if (!btn) return;

    form.addEventListener('submit', function (e) {
        // Guard: if already submitting, block repeat clicks
        if (btn.classList.contains('loading')) {
            e.preventDefault();
            return;
        }

        // Only engage the loading state when the form passes HTML5 validation
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            return; // browser will show validation bubbles — do nothing
        }

        // .loading sets pointer-events:none (blocks repeat clicks). Disabling via
        // .disabled inside the submit handler can cancel submission on Safari/WebKit,
        // so we only swap to the loading state + aria-busy here.
        btn.classList.add('loading');
        btn.setAttribute('aria-busy', 'true');
        btn.innerHTML =
            '<span class="vms-glass-spinner"><i class="fa-solid fa-circle-notch"></i></span>' +
            'Sending...';
    });
})();
</script>
<script src="assets/js/page-transition.js"></script>

</body>
</html>