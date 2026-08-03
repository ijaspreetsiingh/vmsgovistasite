<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch packages from DB
$homepagePackages = getHomepagePackages(4);   // "Popular Packages" grid
$popularPackages  = getPopularPackages(8);    // "Yacht Deals" slider
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/fav.svg">
    <title>VMS Go Vista – Travel &amp; Tour Booking</title>
    <link rel="stylesheet preload" href="assets/css/plugins/swiper.min.css" as="style">
    <link rel="stylesheet preload" href="assets/fonts/custom-font.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet preload" href="assets/css/plugins/magnific-popup.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/metismenu.css" as="style">
    <link rel="stylesheet preload" href="assets/css/vendor/bootstrap.min.css" as="style">
    <link rel="stylesheet preload" href="assets/css/vendor/animate.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/odometer.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/fontawesome.min.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/nice-select.css" as="style">
    <link rel="stylesheet preload" href="assets/css/style.css" as="style">
    <link rel="stylesheet preload" href="assets/css/bromo-theme.css" as="style">
    <style>
        /* ============================================
           FONT THEME — Premium Travel Typography
           ============================================ */
        :root {
            --font-heading: 'Playfair Display', 'Georgia', serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-ui: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        body { font-family: var(--font-body); }

        /* ===== VIDEO LOADER ===== */
        #vms-video-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 999999;
            overflow: hidden;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        #vms-video-loader img {
            display: block;
            max-width: 50vw;
            max-height: 70vh;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        #vms-video-loader .vms-loader-overlay {
            display: none;
        }
        #vms-video-loader .vms-loader-content {
            position: absolute;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }
        #vms-video-loader .vms-loader-bar-track {
            width: 200px;
            height: 3px;
            background: rgba(0,0,0,0.10);
            border-radius: 999px;
            overflow: hidden;
        }
        #vms-video-loader .vms-loader-bar-fill {
            height: 100%;
            width: 0%;
            background: #003A59;
            border-radius: 999px;
            transition: width 0.3s ease;
        }
        #vms-video-loader .vms-loader-label {
            font-size: 13px;
            color: rgba(0,0,0,0.45);
            font-weight: 400;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        #vms-video-loader.vms-loader-hidden {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease 0.05s;
        }
        /* h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6,
        .title, .section-title, .bromo-center-text h1,
        .gallery-content-wrapper .title,
        .offer-title, .package-title,
        .footer-widget-heading .title,
        .bromo-footer .title,
        .counter-title {
            font-family: var(--font-heading) !important;
        } */
        .bromo-eyebrow, .gallery-eyebrow, .bromo-card-label,
        .package-eyebrow, .section-eyebrow,
        .bromo-nav a, .bromo-book-btn a,
        .gallery-cta, .bromo-offer-btn, .bromo-hero-btn,
        .gallery-fraction, .gallery-nav-prev span, .gallery-nav-next span,
        .bromo-offer-badge, .offer-badge,
        .footer-widget, .footer-bottom,
        input, button, select, textarea,
        .badge, .label, .btn, .rts-btn, .nav-link {
            font-family: var(--font-ui);
        }
        /* ===== BROMORISE HEADER (GRID LAYOUT) ===== */
        .bromo-header{position:absolute;top:0;left:0;right:0;z-index:100;padding:26px 72px;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;transition:background .35s,box-shadow .35s,border-color .35s;}
        .bromo-header .bromo-logo{justify-self:start;display:flex;align-items:center;gap:12px;text-decoration:none;}
        .bromo-header .vms-logo-img{height: 70px; width: auto; object-fit: contain; transition: height .3s ease;}
        .bromo-header .bromo-logo-icon{width:38px;height:38px;border-radius:50%;background:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}
        .bromo-header .bromo-logo-icon svg{width:22px;height:22px;display:block;}
        .bromo-header .bromo-logo span{font-size:21px;font-weight:600;color:#fff;letter-spacing:-0.2px;}
        .bromo-nav{justify-self:center;background:rgba(255,255,255,0.16);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.22);border-radius:999px;padding:6px 8px;display:flex;align-items:center;gap:4px;}
        .bromo-nav a{color:rgba(255,255,255,0.95);font-size:14px;font-weight:500;padding:9px 22px;border-radius:999px;text-decoration:none;transition:background .22s,color .22s;white-space:nowrap;}
        .bromo-nav a:hover,.bromo-nav a.active{background:#fff;color:#111;}
        .bromo-book-btn{justify-self:end;display:flex;align-items:center;gap:10px;}
        .bromo-header .dark-mode-toggle{display:none;}
        .bromo-book-btn a{background:rgba(255,255,255,0.14);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.28);color:#fff;font-size:14px;font-weight:500;padding:10px 10px 10px 24px;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:14px;transition:background .22s,color .22s,border-color .22s;}
        .bromo-book-btn a:hover{background:rgba(255,255,255,0.9);color:#1a1a1a;}
        .bromo-book-btn a:hover .bromo-arrow{background:#003A59;color:#fff;}
        .bromo-book-btn a .bromo-arrow{width:32px;height:32px;background:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#111;font-size:13px;flex-shrink:0;transition:background .22s,color .22s;}
        .bromo-mobile-menu{display:none;}

        /* ===== STICKY HEADER — MINIMAL ===== */
        .bromo-header.header--sticky.sticky{
            position:fixed!important;top:0;left:0;right:0;
            z-index:1000!important;
            display:grid;grid-template-columns:1fr auto 1fr;
            background:rgb(255 255 255 / 36%);
            backdrop-filter:blur(14px);
            -webkit-backdrop-filter:blur(14px);
            box-shadow:0 2px 20px rgba(0,0,0,0.08);
            border-bottom:1px solid rgba(0,0,0,0.06);
            animation:bromoStickyIn .3s ease;
        }
        .bromo-header.header--sticky.sticky .vms-logo-img{height: 50px;}
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

        @keyframes bromoStickyIn{0%{transform:translateY(-15px)}100%{transform:none}}

        @media(max-width:991px){
            .bromo-header{padding:18px 20px;display:flex;justify-content:space-between;}
            .bromo-header .dark-mode-toggle{display:inline-flex;}
            .bromo-nav{display:none;}
            .bromo-mobile-menu{display:flex;align-items:center;justify-content:center;width:40px;height:40px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.3);border-radius:50%;cursor:pointer;}
            .bromo-mobile-menu span{color:#fff;font-size:18px;}
        }
        /* ===== HERO ===== */
        .bromo-hero{position:relative;width:100%;height:100vh;min-height:680px;overflow:hidden;isolation:isolate;}
        .bromo-hero .bromo-hero-swiper{position:absolute;inset:0;width:100%;height:100%;}
        .bromo-hero .bromo-hero-swiper .swiper,.bromo-hero .bromo-hero-swiper .swiper-slide{width:100%;height:100%;}
        .bromo-hero-slide-bg{position:absolute;inset:0;width:100%;height:100%;background-size:cover;background-position:center center;background-repeat:no-repeat;}
        .bromo-shared-image{position:absolute;z-index:1;display:block;object-fit:cover;object-position:center center;pointer-events:none;will-change:left,top,width,height,border-radius,opacity;transform-origin:center center;backface-visibility:hidden;}
        .bromo-hero::after{content:'';position:absolute;inset:0;background:linear-gradient(160deg,rgba(0,0,0,0.28) 0%,rgba(0,0,0,0.18) 40%,rgba(0,0,0,0.52) 100%);z-index:1;pointer-events:none;}
        .bromo-hero-content{position:absolute;inset:0;z-index:2;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:0 72px;margin-top:-108px;}
        .bromo-center-text{text-align:center;max-width:1100px;}
        .bromo-center-text .bromo-eyebrow{display:inline-block;padding:9px 18px;border:1px solid rgba(255,255,255,0.14);border-radius:999px;background:rgb(18 24 28/.21);backdrop-filter:blur(10px);font-size:13px;font-weight:400;color:rgba(255,255,255,0.92);margin-bottom:28px;}
        .bromo-center-text h1{font-size:clamp(40px,5.1vw,74px);font-weight:400!important;color:#fff;line-height:1.16!important;margin-bottom:0!important;text-shadow:0 2px 20px rgba(0,0,0,0.18);letter-spacing:-0.015em;}
        .bromo-center-text h1 .bromo-line{display:block;font-weight:300!important;}
        .bromo-center-text h1 .bromo-line:first-child{white-space:nowrap;}
        .bromo-hero-bottom{position:absolute;bottom:40px;left:48px;right:48px;z-index:3;display:flex;align-items:flex-end;justify-content:space-between;gap:24px;}
        .bromo-left-card{display:block;background:rgb(0 0 0/.31);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,0.18);border-radius:20px;padding:22px 26px;max-width:290px;flex-shrink:0;}
        .bromo-avatars{display:flex;align-items:center;margin-bottom:10px;}
        .bromo-avatars img{width:34px;height:34px;border-radius:50%;border:2px solid rgba(255,255,255,0.7);object-fit:cover;margin-right:-10px;}
        .bromo-avatars .bromo-count{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.25);border:2px solid rgba(255,255,255,0.6);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff;margin-right:10px;}
        .bromo-avatars .bromo-joined{font-size:13px;font-weight:600;color:#fff;margin-left:6px;}
        .bromo-left-card p{font-size:13px;color:rgba(255,255,255,0.82);line-height:1.6;margin-bottom:18px;}
        .bromo-hero-btn{display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);color:#fff;font-size:13px;font-weight:600;padding:9px 20px;border-radius:50px;text-decoration:none;transition:background .22s;}
        .bromo-hero-btn:hover{background:rgba(255,255,255,0.3);color:#fff;}
        .bromo-hero-btn .bromo-arrow{width:26px;height:26px;background:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#1a1a1a;font-size:11px;flex-shrink:0;}
        .bromo-right-cards{display:flex;align-items:flex-end;gap:10px;flex-shrink:0;}
        .bromo-img-card{position:relative;border-radius:18px;overflow:hidden;flex-shrink:0;box-shadow:0 8px 32px rgba(0,0,0,0.38);cursor:pointer;border:2px solid transparent;transition:border-color .35s,transform .35s,box-shadow .35s;}
        .bromo-img-card img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s ease;}
        .bromo-img-card:hover img{transform:scale(1.06);}
        .bromo-right-cards .bromo-img-card{width:128px;height:198px;transition:transform .85s cubic-bezier(.22,.8,.2,1),border-color .35s,box-shadow .35s;}
        .bromo-right-cards .bromo-img-card.is-active{width:242px;height:250px;box-shadow:0 14px 40px rgba(0,0,0,0.42);}
        .bromo-right-cards .bromo-img-card:not(.is-active) .bromo-card-label{opacity:0;transform:translateY(10px);}
        .bromo-right-cards .bromo-img-card.is-active .bromo-card-label{opacity:1;transform:translateY(0);}
        .bromo-right-cards .bromo-card-label{transition:opacity .28s,transform .38s;}
        .bromo-right-cards .bromo-img-card.is-active .bromo-card-label strong{animation:bromoTextIn .45s ease .22s both;}
        .bromo-right-cards .bromo-img-card.is-active .bromo-card-label span{animation:bromoTextIn .45s ease .33s both;}
        .bromo-right-cards .bromo-img-card.is-active .bromo-card-label{max-height:48%;padding:14px 15px 16px;overflow:hidden;}
        .bromo-right-cards .bromo-img-card.is-active .bromo-card-label strong{font-size:14px;line-height:1.3;}
        .bromo-right-cards .bromo-img-card.is-active .bromo-card-label span{font-size:11px;line-height:1.4;margin-top:4px;}
        @keyframes bromoTextIn{from{opacity:0;transform:translateY(9px)}to{opacity:1;transform:translateY(0)}}
        .bromo-bg-slider .swiper-wrapper{position:relative;height:100%;transform:none!important;}
        .bromo-bg-slider .swiper-slide{position:absolute;inset:0;opacity:0;pointer-events:none;transition:opacity .9s ease;}
        .bromo-bg-slider .swiper-slide:first-child{opacity:1;}
        .bromo-bg-slider .swiper-slide.is-main-active{opacity:1;z-index:1;}
        .bromo-card-label{position:absolute;bottom:0;left:0;right:0;padding:10px 12px 12px;background:linear-gradient(0deg,rgba(0,0,0,0.72) 0%,transparent 100%);}
        .bromo-card-label strong{display:block;font-size:12px;font-weight:700;color:#fff;line-height:1.3;}
        .bromo-card-label span{font-size:10px;color:rgba(255,255,255,0.75);line-height:1.4;display:block;margin-top:2px;}
        .bromo-hero-dots{display:none;}
        @media(max-width:991px){.bromo-hero-bottom{left:20px;right:20px;bottom:24px;}.bromo-right-cards{display:none;}.bromo-left-card{max-width:100%;}}
        @media(max-width:575px){.bromo-hero{min-height:610px;}.bromo-hero-content{padding:0 16px;margin-top:-36px;}.bromo-center-text h1{font-size:34px;}.bromo-center-text h1 .bromo-line:first-child{white-space:normal;}.bromo-left-card{padding:16px 18px;}}
        /* dark mode toggle */
        .dark-mode-toggle{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.12);border:1.5px solid rgba(255,255,255,0.3);cursor:pointer;position:relative;margin-left:8px;flex-shrink:0;}
        .dark-mode-toggle .icon-sun,.dark-mode-toggle .icon-moon{font-size:16px;position:absolute;transition:opacity .3s,transform .3s;}
        .dark-mode-toggle .icon-sun{color:#FFD700;opacity:1;transform:scale(1) rotate(0deg);}
        .dark-mode-toggle .icon-moon{color:#a0c4ff;opacity:0;transform:scale(0.5) rotate(90deg);}
        body.dark-mode .dark-mode-toggle .icon-sun{opacity:0;transform:scale(0.5) rotate(-90deg);}
        body.dark-mode .dark-mode-toggle .icon-moon{opacity:1;transform:scale(1) rotate(0deg);}
        body.dark-mode{background-color:#0d1117!important;color:#c9d1d9!important;}
        body.dark-mode .rts-popular-package-area,body.dark-mode .rts-destination-area,body.dark-mode .rts-counter-area,body.dark-mode .rts-offer-area,body.dark-mode .rts-testimonials-area,body.dark-mode .rts-brand-area,body.dark-mode .rts-newsletter-area,body.dark-mode .rts-gallery-area,body.dark-mode section{background-color:#0d1117!important;}
        body.dark-mode .package-wrapper::after{background:linear-gradient(180deg,rgba(0,0,0,.05) 30%,rgba(0,0,0,.85) 100%)!important;}
        body.dark-mode .package-wrapper .content{background:transparent!important;}
        body.dark-mode .package-wrapper .content .title a{color:#fff!important;}
        body.dark-mode .section-title-area h2,body.dark-mode .section-title-area .section-title{color:#e6edf3!important;}
        body.dark-mode .counter-wrapper{background:#161b22!important;border:1px solid #30363d!important;}
        body.dark-mode .rts-footer-area-one,body.dark-mode .bromo-footer{background:rgba(8,14,26,0.96)!important;}body.dark-mode .bromo-footer-col{background:rgba(255,255,255,0.04)!important;border-color:rgba(255,255,255,0.06)!important;}body.dark-mode .bromo-footer-col:hover{background:rgba(255,255,255,0.06)!important;}body.dark-mode .bromo-footer-form input{color:#e6edf3!important;}body.dark-mode .social-link{background:rgba(255,255,255,0.03)!important;}
        body.dark-mode #side-bar{background:#161b22!important;}
        body.dark-mode .testimonials-wrapper-five{background:rgba(255,255,255,0.04)!important;border-color:rgba(255,255,255,0.06)!important;}body.dark-mode .testimonials-wrapper-five .text{color:#c9d1d9!important;}body.dark-mode .testimonials-wrapper-five h6{color:#e6edf3!important;}body.dark-mode .testimonials-wrapper-five p{color:#8b949e!important;}
        /* recent gallery */
        /* ===== RECENT GALLERY — BENTO GRID ===== */
        .rts-recent-gallery-area{background:linear-gradient(180deg,#f8fafc 0%,#f0f4f8 100%);padding:100px 0;}
        .bento-gallery{display:grid;grid-template-columns:1fr 0.9fr 1.1fr;grid-template-rows:repeat(3,200px);gap:16px;max-width:1200px;margin:0 auto;padding:0 24px;}
        .bento-item{position:relative;overflow:hidden;border-radius:16px;cursor:pointer;background:#e2e8f0;box-shadow:0 2px 12px rgba(0,0,0,0.04);transition:all .5s cubic-bezier(.22,.8,.2,1);}
        .bento-item:hover{box-shadow:0 12px 40px rgba(0,58,89,0.12);transform:translateY(-3px);}
        .bento-item img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .7s cubic-bezier(.22,.8,.2,1);}
        .bento-item:hover img{transform:scale(1.08);}
        .bento-item .bento-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,58,89,0.85) 100%);opacity:0;transition:opacity .5s ease;}
        .bento-item:hover .bento-overlay{opacity:1;}
        .bento-item .bento-caption{position:absolute;bottom:0;left:0;right:0;padding:20px 22px;transform:translateY(20px);opacity:0;transition:all .4s cubic-bezier(.22,.8,.2,1);z-index:2;display:flex;align-items:center;justify-content:space-between;gap:12px;}
        .bento-item:hover .bento-caption{transform:translateY(0);opacity:1;}
        .bento-caption .bento-label{display:flex;flex-direction:column;gap:2px;}
        .bento-caption .bento-name{font-size:16px;font-weight:700;color:#fff;line-height:1.2;font-family:var(--font-heading);}
        .bento-caption .bento-location{font-size:11px;font-weight:500;color:rgba(255,255,255,0.6);letter-spacing:0.5px;}
        .bento-caption .bento-zoom{width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0;transition:all .3s ease;}
        .bento-item:hover .bento-zoom{background:rgba(255,255,255,0.25);transform:scale(1.1);}
        /* specific grid placements */
        .bento-item-1{grid-column:1;grid-row:1/4;border-radius:20px;}
        .bento-item-2{grid-column:2;grid-row:1/3;}
        .bento-item-3{grid-column:3;grid-row:1/2;}
        .bento-item-4{grid-column:3;grid-row:2/4;}
        .bento-item-5{grid-column:2;grid-row:3/4;}

        /* cta banner */
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
        /* ===== COOLDOCK-STYLE FOOTER ===== */
/* ===== FOOTER — COOLDOCK STYLE (EXACT MATCH) ===== */
.vms-footer{position:relative;background:whitesmoke;padding:0;overflow:hidden;font-family:var(--font-body);}

/* Top rounded dark pill (like Cooldock header floating on top) */
.vms-footer-toppill{position:absolute;top:-28px;left:50%;transform:translateX(-50%);z-index:5;background:#003A59;border-radius:999px;padding:8px 8px 8px 20px;display:flex;align-items:center;gap:22px;box-shadow:0 8px 30px rgba(0,58,89,0.25);}
.vms-footer-toppill .tp-logo{display:flex;align-items:center;gap:8px;color:#fff;font-weight:600;font-size:14px;}
.vms-footer-toppill .tp-logo-box{width:22px;height:22px;background:#fff;border-radius:6px;display:flex;align-items:center;justify-content:center;}
.vms-footer-toppill .tp-logo-box svg{width:14px;height:14px;}
.vms-footer-toppill .tp-nav{display:flex;align-items:center;gap:18px;}
.vms-footer-toppill .tp-nav a{color:rgba(255,255,255,0.85);font-size:13px;font-weight:500;text-decoration:none;transition:color .2s;}
.vms-footer-toppill .tp-nav a:hover{color:#fff;}
.vms-footer-toppill .tp-download{background:rgba(255,255,255,0.15);color:#fff;font-size:13px;font-weight:600;padding:8px 16px;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,0.2);}
.vms-footer-toppill .tp-download:hover{background:rgba(255,255,255,0.25);}

/* Right vertical "Nominee" badge */
.vms-footer-nominee{position:absolute;top:120px;right:0;z-index:4;background:#003A59;color:#fff;padding:20px 8px;border-radius:8px 0 0 8px;writing-mode:vertical-rl;transform:rotate(180deg);font-size:12px;font-weight:600;letter-spacing:2px;}

/* Content grid — 4 columns */
.vms-footer-content{position:relative;z-index:3;max-width:1100px;margin:0 auto;padding-top:80px;padding-bottom:40px;display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:60px;}

/* Brand column */
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

/* Bottom bar for copyright */
.vms-footer-bottom{position:relative;z-index:3;max-width:1100px;margin:0 auto;padding:24px 0;border-top:1px solid rgba(0,58,89,0.06);display:flex;align-items:center;justify-content:space-between;gap:20px;}
.vms-credit{display:flex;align-items:center;gap:6px;font-size:12.5px;color:#666;}
.vms-author{color:#555;font-weight:400;display:inline-flex;align-items:center;gap:6px;}
.vms-author-avatar{width:20px;height:20px;border-radius:50%;background:#003A59;display:inline-block;}

/* Column links */
.vms-footer-col h5{font-size:14px;font-weight:700;color:#003A59;margin:0 0 18px;}
.vms-footer-links{list-style:none;padding:0;margin:0;}
.vms-footer-links li{margin-bottom:11px;}
.vms-footer-links a{font-size:13.5px;color:#555;text-decoration:none;transition:color .2s;}
.vms-footer-links a:hover{color:#003A59;}

/* ===== VIDEO SECTION WITH TRAVEL VIDEO ===== */
@keyframes vmsBgShift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.vms-video-section{position:relative;z-index:1;width:100%;height:520px;overflow:hidden;margin-top:-210px;background:linear-gradient(135deg,#667eea 0%,#764ba2 50%,#f093fb 100%);background-size:400% 400%;animation:vmsBgShift 15s ease infinite;}
.vms-video-section video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;}
.vms-video-gradient{position:absolute;inset:0;z-index:1;background:linear-gradient(180deg,whitesmoke 0%,rgba(245,245,245,0.92) 12%,rgba(245,245,245,0.35) 22%,rgba(245,245,245,0.08) 32%,rgba(0,0,0,0.2) 48%,rgba(0,0,0,0.5) 78%,#000 100%);}
.vms-big-text{position:absolute;bottom:80px;left:0;right:0;z-index:2;text-align:center;font-size:clamp(120px,18vw,180px);font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:0.05em;text-transform:uppercase;text-shadow:0 2px 20px rgba(0,0,0,0.3);pointer-events:none;font-family:var(--font-heading);margin:0;padding:0 20px;white-space:nowrap;}

/* Dark mode */
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

/* Responsive */
@media(max-width:1199px){
    .vms-footer-toppill{display:none;}
    .vms-footer-nominee{display:none;}
    .vms-footer-content{padding:60px 32px 32px;gap:40px;}
}
@media(max-width:991px){
    .vms-footer-content{grid-template-columns:1fr 1fr;gap:32px;padding:40px 28px 80px;}
    .vms-brand{grid-column:span 2;}
    .vms-video-section{height:360px;margin-top:-120px;}
    .vms-big-text{font-size:clamp(16px,4vw,24px);bottom:40px;}
}
@media(max-width:575px){
    .vms-footer-content{grid-template-columns:1fr;gap:26px;padding:28px 22px 60px;}
    .vms-brand{grid-column:span 1;}
    .vms-video-section{height:280px;margin-top:-90px;}
    .vms-big-text{font-size:clamp(14px,5vw,20px);bottom:24px;}
}

        /* Uniform image sizing */
        /* .package-wrapper .image-area { position: relative !important; height: 320px !important; overflow: hidden !important; }
        .package-wrapper .image-area img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block !important; }
        @media (max-width: 767px) { .package-wrapper .image-area { height: 250px !important; } } */

        /* ===== NEWSLETTER — FULL BG IMAGE + 10px INSET GLASS ===== */
        .bromo-newsletter-section{padding:80px 20px;}
        .bromo-newsletter-wrap{position:relative;width:100%;max-width:1400px;min-height:560px;margin:0 auto;border-radius:32px;overflow:hidden;background-size:cover;background-position:center;background-repeat:no-repeat;}
        .bromo-newsletter-wrap::before{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,0.25) 0%,rgba(0,0,0,0.05) 55%,rgba(0,0,0,0.15) 100%);z-index:1;pointer-events:none;}
        /* glass panel — full height, no gaps, no border */
        .bromo-newsletter-content{position:absolute;z-index:2;top:0;left:0;bottom:0;width:42%;display:flex;align-items:center;padding:48px 46px;border-radius:24px 0 0 24px;background:rgba(255,255,255,0.07);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:none;}
        .bromo-newsletter-inner{width:100%;}
        .bromo-newsletter-badge{display:inline-block;margin-bottom:20px;padding:6px 14px;border-radius:999px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;backdrop-filter:blur(4px);}
        .bromo-newsletter-title{margin:0 0 18px;color:#fff;font-size:clamp(30px,4.2vw,56px);line-height:1.06;font-weight:600;letter-spacing:-0.03em;text-shadow:0 2px 20px rgba(0,0,0,0.15);}
        .bromo-newsletter-desc{max-width:440px;margin:0 0 28px;color:rgba(255,255,255,0.65);font-size:15px;line-height:1.7;font-weight:400;text-shadow:0 1px 10px rgba(0,0,0,0.08);}
        /* clean white form with brand button */
        .bromo-newsletter-form{display:flex;align-items:center;gap:0;width:100%;max-width:480px;padding:5px;border-radius:100px;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,0.08);}
        .bromo-newsletter-form:focus-within{box-shadow:0 4px 20px rgba(0,58,89,0.15);}
        .bromo-newsletter-form input{flex:1;min-width:0;height:48px;padding:0 16px;border:0;outline:none;background:transparent;color:#222;font-size:14px;font-weight:400;}
        .bromo-newsletter-form input::placeholder{color:#999;}
        .bromo-newsletter-btn{display:inline-flex;align-items:center;gap:8px;height:48px;padding:0 24px;border:1px solid rgba(255,255,255,0.15);border-radius:999px;background:rgba(255,255,255,0.1);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);color:#fff;font-weight:600;cursor:pointer;white-space:nowrap;transition:all .3s ease;font-size:13px;flex-shrink:0;}
        .bromo-newsletter-btn i{font-size:12px;transition:transform .3s;}
        .bromo-newsletter-btn:hover{background:rgba(255,255,255,0.2);gap:10px;border-color:rgba(255,255,255,0.25);transform:translateY(-2px);box-shadow:0 8px 25px rgba(0,58,89,0.2);}
        .bromo-newsletter-btn:hover i{transform:translate(3px,-2px);}
        .bromo-newsletter-btn:active{transform:scale(0.97);}
        @media(max-width:991px){
            .bromo-newsletter-wrap{min-height:auto;border-radius:24px;}
            .bromo-newsletter-content{position:relative;top:auto;left:auto;bottom:auto;width:100%;padding:56px 36px;border-radius:0;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);}
        }
        @media(max-width:575px){
            .bromo-newsletter-content{padding:36px 20px;border-radius:0;}
            .bromo-newsletter-title{font-size:28px;}
            .bromo-newsletter-desc{font-size:14px;margin-bottom:22px;}
            .bromo-newsletter-form{flex-direction:column;border-radius:14px;padding:8px;gap:8px;background:#fff;}
            .bromo-newsletter-form input{width:100%;border-radius:100px;padding:0 14px;height:44px;}
            .bromo-newsletter-btn{width:100%;justify-content:center;height:44px;}
        }
        body.dark-mode .bromo-newsletter-section{background:transparent!important;}
        body.dark-mode .bromo-newsletter-wrap{background:transparent!important;}
        body.dark-mode .bromo-newsletter-content{background:rgba(255,255,255,0.07)!important;}

        /* ===== BENTO GALLERY - RESPONSIVE ===== */
        @media(max-width:991px){
            .bento-gallery{grid-template-columns:1fr 1fr;grid-template-rows:repeat(4,180px);gap:12px;padding:0 20px;}
            .bento-item-1{grid-column:1/2;grid-row:1/3;}
            .bento-item-2{grid-column:2/3;grid-row:1/2;border-radius:16px;}
            .bento-item-3{grid-column:2/3;grid-row:2/3;}
            .bento-item-4{grid-column:1/2;grid-row:3/5;}
            .bento-item-5{grid-column:2/3;grid-row:3/5;}
            .bromo-newsletter-wrap{min-height:auto;border-radius:24px;}
            .bromo-newsletter-content{position:relative;top:auto;left:auto;bottom:auto;width:100%;padding:56px 36px;border-radius:0;backdrop-filter:blur(12px);}
            .rts-testimonials-area .row g-5{align-items:center;}
            .testi-image-wrap{height:300px;border-radius:16px;}
            .testimonials-wrapper-five{padding:32px 28px;}
            .rts-popular-package-area{padding:60px 0!important;}
            .rts-destination-area{padding:40px 0!important;}
        }
        @media(max-width:575px){
            .bento-gallery{grid-template-columns:1fr 1fr;grid-template-rows:repeat(4,130px);gap:8px;padding:0 16px;}
            .bento-item{border-radius:12px;}
            .bento-item-1{border-radius:14px;}
            .bromo-newsletter-content{padding:36px 20px;}
            .bromo-newsletter-title{font-size:28px!important;}
            .bromo-newsletter-desc{font-size:14px!important;margin-bottom:22px!important;}
            .bromo-newsletter-form{flex-direction:column;border-radius:14px;padding:8px;gap:8px;background:#fff;}
            .bromo-newsletter-form input{width:100%;border-radius:100px;padding:0 14px;height:44px;}
            .bromo-newsletter-btn{width:100%;justify-content:center;height:44px;border-radius:100px;}
            .testi-image-wrap{height:220px;border-radius:14px;}
            .testimonials-wrapper-five{padding:24px 20px;border-radius:16px;}
            .testimonials-wrapper-five .text{font-size:14px!important;}
            .bromo-right-cards{display:none!important;}
            .bromo-left-card{max-width:100%!important;}
        }
        body.dark-mode .rts-recent-gallery-area{background:#0d1117!important;}

        /* ===== DESTINATION WRAPPER-4 — FIXED HEIGHTS + OBJECT-FIT ===== */
        .destination-wrapper-4{position:relative;z-index:1;overflow:hidden;transition:all .4s;border-radius:10px;}
        .destination-wrapper-4:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,0.12);}
        .destination-wrapper-4 .image-area{position:relative;overflow:hidden;width:100%;height:380px;}
        .destination-wrapper-4 .image-area a{display:block;position:relative;z-index:1;width:100%;height:100%;}
        .destination-wrapper-4 .image-area a::after{content:'';position:absolute;z-index:1;background:linear-gradient(180deg,rgba(0,0,0,0) 35%,rgba(0,0,0,0.8) 100%);height:100%;width:100%;top:0;left:0;pointer-events:none;}
        .destination-wrapper-4 .image-area img{width:100%;height:100%;display:block;object-fit:cover;transition:transform .5s ease;}
        .destination-wrapper-4:hover .image-area img{transform:scale(1.07);}
        .destination-wrapper-4 .content-area{position:absolute;bottom:30px;left:50%;transform:translateX(-50%);z-index:2;text-align:center;transition:all .4s;pointer-events:none;width:90%;max-width:280px;}
        .destination-wrapper-4:hover .content-area{bottom:40px;}
        .destination-wrapper-4 .content-area p{color:#fff;margin-bottom:8px;font-weight:500;font-size:14px;}
        .destination-wrapper-4 .content-area .title{margin-bottom:8px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .destination-wrapper-4 .content-area .title a{color:#fff;text-decoration:none;font-size:22px;}
        .destination-wrapper-4 .content-area .rts-btn{margin:auto;color:rgba(255,255,255,0.9);opacity:0;transition:all .4s;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:14px;pointer-events:auto;}
        .destination-wrapper-4:hover .content-area .rts-btn{opacity:1;}
        .destination-wrapper-4 .content-area .rts-btn i{font-size:12px;transition:transform .3s;}
        .destination-wrapper-4:hover .content-area .rts-btn i{transform:translate(3px,-3px);}
        @media(max-width:991px){
            .destination-wrapper-4 .image-area{height:280px;}
            .destination-wrapper-4 .content-area{bottom:24px;}
            .destination-wrapper-4:hover .content-area{bottom:30px;}
            .destination-wrapper-4 .content-area .rts-btn{opacity:1;}
            .destination-wrapper-4 .content-area .title a{font-size:19px;}
        }
        @media(max-width:575px){
            .destination-wrapper-4 .image-area{height:220px;}
            .destination-wrapper-4{border-radius:10px;}
            .destination-wrapper-4:hover{transform:translateY(-3px);}
            .destination-wrapper-4 .content-area{bottom:18px;max-width:220px;}
            .destination-wrapper-4:hover .content-area{bottom:22px;}
            .destination-wrapper-4 .content-area .title a{font-size:17px;}
            .destination-wrapper-4 .content-area p{font-size:12px;margin-bottom:4px;}
        }
        body.dark-mode .destination-wrapper-4:hover{box-shadow:0 16px 40px rgba(0,0,0,0.5);}
        body.dark-mode .destination-wrapper-4 .image-area a::after{background:linear-gradient(180deg,rgba(0,0,0,0) 30%,rgba(0,0,0,0.85) 100%)!important;}

        /* ===== ABOUT US — GLASS HEADER STYLE ===== */
        .about-glass {
            position: relative;
            padding: 100px 0;
            overflow: hidden;
            background: linear-gradient(160deg, #f0f6fb 0%, #e8f0f6 50%, #f5f0eb 100%);
            isolation: isolate;
        }

        body.dark-mode .about-glass {
            background: linear-gradient(160deg, #0d1117 0%, #111820 50%, #151c24 100%) !important;
        }

        .about-glass::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,165,103,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .about-glass::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -15%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,58,89,0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .about-glass-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 60px;
            padding: 0;
            border-radius: 0;
            background: transparent;
            border: none;
            box-shadow: none;
        }

        .about-glass-image {
            flex-shrink: 0;
            width: 420px;
            height: 520px;
            position: relative;
        }

        .about-glass-image .img-wrap {
            width: 100%;
            height: 100%;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            background: rgba(0,58,89,0.04);
            border: 1px solid rgba(0,58,89,0.08);
            box-shadow: 0 20px 60px rgba(0,58,89,0.10), 0 4px 16px rgba(0,0,0,0.04);
        }

        body.dark-mode .about-glass-image .img-wrap {
            border-color: rgba(255,255,255,0.06);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .about-glass-image .img-wrap::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background: linear-gradient(180deg, transparent 60%, rgba(0,58,89,0.15) 100%);
            pointer-events: none;
        }

        .about-glass-image .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .7s cubic-bezier(.22,.8,.2,1);
        }

        .about-glass-image .img-wrap:hover img {
            transform: scale(1.05);
        }

        .about-glass-image .glass-badge {
            position: absolute;
            bottom: 24px;
            left: -16px;
            z-index: 5;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px 16px 20px;
            border-radius: 16px;
            background: #003A59;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(201,165,103,0.3);
            box-shadow: 0 12px 32px rgba(0,58,89,0.25), 0 0 0 1px rgba(255,255,255,0.05) inset;
            transition: transform .3s ease;
        }

        .about-glass-image .glass-badge:hover {
            transform: translateY(-3px);
        }

        body.dark-mode .about-glass-image .glass-badge {
            background: rgba(201,165,103,0.15);
            border-color: rgba(201,165,103,0.3);
        }

        .about-glass-image .glass-badge .gb-num {
            font-size: 32px;
            font-weight: 800;
            color: #8e8d8b;
            line-height: 1;
            font-family: var(--font-heading);
        }

        body.dark-mode .about-glass-image .glass-badge .gb-num {
            color: #0a557c;
        }

        .about-glass-image .glass-badge .gb-label {
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            line-height: 1.4;
        }

        body.dark-mode .about-glass-image .glass-badge .gb-label {
            color: rgba(255,255,255,0.7);
        }

        .about-glass-content {
            flex: 1;
            min-width: 0;
        }

        .about-glass-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 20px 8px 14px;
            border-radius: 999px;
            background: #003A59;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0,58,89,0.15);
        }

        body.dark-mode .about-glass-eyebrow {
            background: rgba(201,165,103,0.15);
            border: 1px solid rgba(201,165,103,0.3);
            color: #0a557c;
        }

        .about-glass-eyebrow .ep-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #0a557c;
            flex-shrink: 0;
            animation: epPulse 2s ease-in-out infinite;
        }

        @keyframes epPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.2); }
        }

        .about-glass-content h2 {
            font-size: clamp(34px, 4vw, 52px);
            font-weight: 800;
            color: #003A59;
            line-height: 1.1;
            margin: 0 0 20px;
            letter-spacing: -0.5px;
        }

        body.dark-mode .about-glass-content h2 {
            color: #e6edf3;
        }

        .about-glass-content h2 .hl-gold {
            color: #0a557c;
            font-style: italic;
            font-weight: 800;
            text-shadow: 0 2px 16px rgba(201,165,103,0.2);
            position: relative;
        }

        body.dark-mode .about-glass-content h2 .hl-gold {
            color: #d4b06a;
        }

        .about-glass-content .glass-desc {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.85;
            margin-bottom: 16px;
            max-width: 520px;
        }

        body.dark-mode .about-glass-content .glass-desc {
            color: #8b949e;
        }

        .about-glass-content .glass-desc:last-of-type {
            margin-bottom: 32px;
        }

        .about-glass-stats {
            display: flex;
            gap: 0;
            margin-bottom: 32px;
            padding: 0;
            border-top: none;
            border-bottom: none;
            background: rgba(0,58,89,0.03);
            border-radius: 16px;
            padding: 20px 8px;
            border: 1px solid rgba(0,58,89,0.06);
        }

        body.dark-mode .about-glass-stats {
            background: rgba(255,255,255,0.03);
            border-color: rgba(255,255,255,0.06);
        }

        .about-glass-stat {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            text-align: center;
            padding: 8px 16px;
        }

        .about-glass-stat .stat-num {
            font-size: 30px;
            font-weight: 800;
            color: #003A59;
            line-height: 1;
            font-family: var(--font-heading);
        }

        body.dark-mode .about-glass-stat .stat-num {
            color: #e6edf3;
        }

        .about-glass-stat .stat-num .stat-gold {
            color: #0a557c;
        }

        body.dark-mode .about-glass-stat .stat-num .stat-gold {
            color: #d4b06a;
        }

        .about-glass-stat .stat-label {
            font-size: 12px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.dark-mode .about-glass-stat .stat-label {
            color: #8b949e;
        }

        .about-glass-stat:not(:last-child) {
            border-right: 1px solid rgba(0,58,89,0.08);
        }

        body.dark-mode .about-glass-stat:not(:last-child) {
            border-right-color: rgba(255,255,255,0.06);
        }

        .about-glass-btn {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 10px 10px 10px 24px;
            border-radius: 999px;
            background: rgba(0,58,89,0.14);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(0,58,89,0.28);
            color: #003A59;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all .22s;
            width: fit-content;
        }

        .about-glass-btn:hover {
            background: #003A59;
            color: #fff;
            border-color: #003A59;
        }

        .about-glass-btn:hover .btn-glass-arrow {
            background: #fff;
            color: #003A59;
        }

        .about-glass-btn .btn-glass-arrow {
            width: 32px;
            height: 32px;
            background: #003A59;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            flex-shrink: 0;
            transition: all .22s;
        }

        body.dark-mode .about-glass-btn {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.14);
            color: #e6edf3;
        }

        body.dark-mode .about-glass-btn:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        body.dark-mode .about-glass-btn .btn-glass-arrow {
            background: #e6edf3;
            color: #0d1117;
        }

        body.dark-mode .about-glass-btn:hover .btn-glass-arrow {
            background: #fff;
            color: #003A59;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1199px) {
            .about-glass-card {
                gap: 40px;
            }
            .about-glass-image {
                width: 360px;
                height: 460px;
            }
        }

        @media (max-width: 991px) {
            .about-glass {
                padding: 80px 0;
            }
            .about-glass-card {
                flex-direction: column-reverse;
                gap: 40px;
                text-align: center;
            }
            .about-glass-image {
                width: 100%;
                max-width: 420px;
                height: 360px;
            }
            .about-glass-image .glass-badge {
                left: 12px;
            }
            .about-glass-content {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .about-glass-content .glass-desc {
                max-width: 100%;
            }
            .about-glass-stats {
                width: 100%;
                max-width: 420px;
            }
        }

        @media (max-width: 575px) {
            .about-glass {
                padding: 60px 0;
            }
            .about-glass-image {
                height: 280px;
                max-width: 100%;
            }
            .about-glass-image .glass-badge {
                bottom: 12px;
                left: 8px;
                padding: 12px 16px;
                gap: 8px;
            }
            .about-glass-image .glass-badge .gb-num {
                font-size: 24px;
            }
            .about-glass-image .glass-badge .gb-label {
                font-size: 10px;
            }
            .about-glass-content h2 {
                font-size: 28px;
            }
            .about-glass-stats {
                gap: 0;
                padding: 16px 4px;
            }
            .about-glass-stat {
                padding: 8px 8px;
            }
            .about-glass-stat .stat-num {
                font-size: 24px;
            }
            .about-glass-stat .stat-label {
                font-size: 10px;
            }
            .about-glass-btn {
                width: 100%;
                justify-content: center;
                padding: 14px 14px 14px 30px;
                font-size: 14px;
            }
        }

        /* ===== HOLIDAY PACKAGES SECTION ===== */
        .holiday-packages-section {
            padding-top: 80px;
            padding-bottom: 80px;
            background: var(--color-bg-1);
        }

        body.dark-mode .holiday-packages-section {
            background: #0d1117;
        }

        .holiday-packages-sidebar {
            position: -webkit-sticky;
            position: sticky;
            top: 110px;
            align-self: flex-start;
            z-index: 10;
        }

        .holiday-packages-hint {
            color: #666;
            font-size: 15px;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        body.dark-mode .holiday-packages-hint {
            color: #8b949e;
        }

        .holiday-package-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .holiday-package-nav li {
            margin-bottom: 4px;
        }

        .holiday-package-nav-link {
            display: block;
            padding: 10px 0 10px 16px;
            border-left: 2px solid rgba(0, 58, 89, 0.1);
            font-size: 14px;
            font-weight: 600;
            color: #666;
            text-decoration: none;
            transition: color 0.25s, border-color 0.25s;
        }

        .holiday-package-nav-link:hover,
        .holiday-package-nav-link.is-active {
            color: #003A59;
            border-left-color: #003A59;
        }

        body.dark-mode .holiday-package-nav-link {
            color: #8b949e;
            border-left-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .holiday-package-nav-link:hover,
        body.dark-mode .holiday-package-nav-link.is-active {
            color: #58a6ff;
            border-left-color: #58a6ff;
        }

        .holiday-packages-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .holiday-package-card {
            position: relative;
            padding: 32px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid rgba(0, 58, 89, 0.06);
            box-shadow: 0 8px 32px rgba(0, 58, 89, 0.08);
            scroll-margin-top: 120px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        body.dark-mode .holiday-package-card {
            background: #161b22;
            border-color: #30363d;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .holiday-package-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 58, 89, 0.12);
        }

        body.dark-mode .holiday-package-card:hover {
            box-shadow: 0 12px 40px rgba(88, 166, 255, 0.2);
        }

        .holiday-package-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .holiday-package-title {
            font-size: clamp(22px, 3vw, 32px);
            font-weight: 700;
            color: #003A59;
            margin: 0;
            line-height: 1.2;
        }

        body.dark-mode .holiday-package-title {
            color: #e6edf3;
        }

        .holiday-package-num {
            font-size: 14px;
            color: #999;
            font-weight: 600;
            white-space: nowrap;
        }

        body.dark-mode .holiday-package-num {
            color: #8b949e;
        }

        .holiday-package-desc {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        body.dark-mode .holiday-package-desc {
            color: #8b949e;
        }

        .holiday-package-thumb {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(0, 58, 89, 0.06);
            box-shadow: 0 8px 24px rgba(0, 58, 89, 0.1);
        }

        body.dark-mode .holiday-package-thumb {
            border-color: #30363d;
        }

        .holiday-package-thumb img {
            width: 100%;
            height: auto;
            aspect-ratio: 16/10;
            object-fit: cover;
            display: block;
        }

        .holiday-package-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .holiday-package-tag {
            padding: 6px 14px;
            background: rgba(0, 58, 89, 0.06);
            color: #003A59;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        body.dark-mode .holiday-package-tag {
            background: rgba(88, 166, 255, 0.1);
            color: #58a6ff;
        }

        .heading-section {
            margin-bottom: 40px;
        }

        .heading-sub {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #003A59;
            margin-bottom: 12px;
            display: block;
        }

        body.dark-mode .heading-sub {
            color: #58a6ff;
        }

        .heading-title {
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 700;
            color: #003A59;
            line-height: 1.2;
            margin: 0;
        }

        body.dark-mode .heading-title {
            color: #e6edf3;
        }

        .holiday-package-nav-link:hover,
        .holiday-package-nav-link.is-active {
            color: #003A59;
            border-left-color: #003A59;
        }

        body.dark-mode .holiday-package-nav-link {
            color: #8b949e;
            border-left-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .holiday-package-nav-link:hover,
        body.dark-mode .holiday-package-nav-link.is-active {
            color: #58a6ff;
            border-left-color: #58a6ff;
        }

        .holiday-packages-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .holiday-package-card {
            position: relative;
            padding: 32px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid rgba(0, 58, 89, 0.06);
            box-shadow: 0 8px 32px rgba(0, 58, 89, 0.08);
            scroll-margin-top: 120px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        body.dark-mode .holiday-package-card {
            background: #161b22;
            border-color: #30363d;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .holiday-package-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 58, 89, 0.12);
        }

        body.dark-mode .holiday-package-card:hover {
            box-shadow: 0 12px 40px rgba(88, 166, 255, 0.2);
        }

        .holiday-package-card-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .holiday-package-title {
            font-size: clamp(22px, 3vw, 32px);
            font-weight: 700;
            color: #003A59;
            margin: 0;
            line-height: 1.2;
        }

        body.dark-mode .holiday-package-title {
            color: #e6edf3;
        }

        .holiday-package-num {
            font-size: 14px;
            color: #999;
            font-weight: 600;
            white-space: nowrap;
        }

        body.dark-mode .holiday-package-num {
            color: #8b949e;
        }

        .holiday-package-desc {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        body.dark-mode .holiday-package-desc {
            color: #8b949e;
        }

        .holiday-package-thumb {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(0, 58, 89, 0.06);
            box-shadow: 0 8px 24px rgba(0, 58, 89, 0.1);
        }

        body.dark-mode .holiday-package-thumb {
            border-color: #30363d;
        }

        .holiday-package-thumb img {
            width: 100%;
            height: auto;
            aspect-ratio: 16/10;
            object-fit: cover;
            display: block;
        }

        .holiday-package-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .holiday-package-tag {
            padding: 6px 14px;
            background: rgba(0, 58, 89, 0.06);
            color: #003A59;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        body.dark-mode .holiday-package-tag {
            background: rgba(88, 166, 255, 0.1);
            color: #58a6ff;
        }

        .heading-section {
            margin-bottom: 40px;
        }

        .heading-sub {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #003A59;
            margin-bottom: 12px;
            display: block;
        }

        body.dark-mode .heading-sub {
            color: #58a6ff;
        }

        .heading-title {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 800;
            color: #003A59;
            line-height: 1.2;
            letter-spacing: -1px;
        }

        body.dark-mode .heading-title {
            color: #e6edf3;
        }

        @media (max-width: 991px) {
            .holiday-packages-section {
                padding-top: 60px;
                padding-bottom: 60px;
            }

            .holiday-packages-sidebar {
                position: static;
                margin-bottom: 32px;
            }

            .holiday-package-card {
                padding: 24px;
            }
        }

        @media (max-width: 767px) {
            .holiday-package-card {
                padding: 20px;
            }

            .holiday-package-thumb img {
                aspect-ratio: 4/3;
            }
        }

        /* ===== WHY CHOOSE US ===== */
        .why-choose-wrapper-list li .icon i {
            font-size: 28px;
            color: #003A59;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 58, 89, 0.08);
            border-radius: 12px;
            flex-shrink: 0;
        }

        body.dark-mode .why-choose-wrapper-list li .icon i {
            background: rgba(255, 255, 255, 0.08);
            color: #58a6ff;
        }

        .why-choose-wrapper-list li .content .title {
            color: #003A59;
            margin-bottom: 5px;
            font-size: 16px;
            font-weight: 700;
        }

        body.dark-mode .why-choose-wrapper-list li .content .title {
            color: #e6edf3;
        }

        .why-choose-wrapper-list li .content p {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
            line-height: 1.5;
        }

        body.dark-mode .why-choose-wrapper-list li .content p {
            color: #8b949e;
        }

        .why-choose-left-image-area {
            position: sticky !important;
            top: 140px;
            align-self: start;
        }

        .why-choose-right-side-content {
            position: relative;
            padding-left: 40px;
        }

        .rts-why-choose-us-area {
            margin-top: -60px;
        }

        @media (max-width: 991px) {
            .rts-why-choose-us-area {
                margin-top: 0;
            }
        }

        .why-choose-clip-section {
            position: relative;
            isolation: isolate;
        }

        .why-choose-clip-section .section-title-area {
            position: -webkit-sticky;
            position: sticky !important;
            top: 100px;
            z-index: 5;
            background: var(--color-bg-1);
            padding: 20px 30px 20px 0;
            margin-bottom: 10px;
        }

        .why-choose-clip-body {
            position: relative;
            z-index: 1;
        }

        body.dark-mode .why-choose-clip-section .section-title-area {
            background: #0d1117;
        }

        @media (max-width: 991px) {
            .why-choose-left-image-area {
                position: static;
            }

            .why-choose-right-side-content {
                position: static;
                padding: 0;
            }

            .why-choose-clip-section .section-title-area {
                position: static;
                padding: 0;
                box-shadow: none;
            }

            .why-choose-fixed-bottom {
                position: static;
            }

            .why-choose-wrapper-list {
                margin-top: 30px;
            }

            .why-choose-fixed-bottom {
                margin-top: 30px;
            }

            .why-choose-video-wrap {
                min-height: 420px;
            }

            .why-choose-video-wrap video {
                height: 420px;
            }
        }

        .why-choose-right-side-content {
            padding-left: 40px;
            padding-bottom: 400px;
        }

        .why-choose-wrapper-list {
            margin-top: 25px;
            margin-bottom: 25px;
        }

        .why-choose-wrapper-list li {
            display: flex;
            align-items: center;
            gap: 20px;
            border: none;
            padding: 18px 22px;
            background: var(--color-bg-1);
            transition: transform 0.3s ease;
            margin-bottom: 16px;
        }

        body.dark-mode .why-choose-wrapper-list li {
            background: var(--color-bg-1);
            border-color: transparent;
        }

        .why-choose-wrapper-list li:hover {
            transform: translateX(6px);
        }

        .why-choose-video-wrap {
            position: relative;
            width: 100%;
            min-height: 520px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 16px 48px rgba(0, 58, 89, 0.18);
            background: linear-gradient(135deg, #003A59 0%, #1a5a7a 100%);
        }

        .why-choose-video-wrap video {
            display: block;
            width: 100%;
            height: 520px;
            object-fit: cover;
            background: linear-gradient(135deg, #003A59 0%, #1a5a7a 100%);
        }

        .why-choose-video-wrap .video-gradient-overlay {
            position: absolute;
            inset: 0;
            z-index: 2;
            background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, transparent 20%, transparent 70%, rgba(0,58,89,0.15) 100%);
            pointer-events: none;
        }

        body.dark-mode .why-choose-video-wrap .video-gradient-overlay {
            background: linear-gradient(180deg, rgba(0,0,0,0.4) 0%, transparent 18%, transparent 72%, rgba(0,0,0,0.3) 100%);
        }

        .why-choose-video-wrap .video-play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 3;
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(12px);
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
            font-size: 28px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }

        .why-choose-video-wrap .video-play-btn:hover {
            background: rgba(255,255,255,0.4);
            border-color: rgba(255,255,255,0.8);
            transform: translate(-50%, -50%) scale(1.08);
            box-shadow: 0 12px 40px rgba(0,0,0,0.3);
        }

        .why-choose-video-wrap .video-play-btn i {
            margin-left: 3px;
        }

        .why-choose-fixed-bottom {
            position: relative;
            z-index: 3;
            background: var(--color-bg-1);
            padding-top: 24px;
            margin-top: 30px;
            border-top: 1px solid rgba(0, 58, 89, 0.08);
        }

        body.dark-mode .why-choose-fixed-bottom {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        body.dark-mode .why-choose-fixed-bottom {
            background: transparent;
        }

        .why-choose-fixed-bottom .button-area {
            margin-top: 18px;
        }

        .why-choose-fixed-bottom .button-area .rts-btn {
            width: 100%;
            text-align: center;
            padding: 14px 20px;
        }

        /* ===== BOOK TODAY — OFFER CARDS ===== */
        /* ===== BOOK TODAY — OFFER CARDS (compact) ===== */
        .bromo-offers-section{padding:30px 0 60px;}
        .bromo-offer-card{position:relative;z-index:1;overflow:hidden;border-radius:12px;cursor:pointer;transition:transform .4s cubic-bezier(.22,.8,.2,1),box-shadow .4s ease;}
        .bromo-offer-card:hover{transform:translateY(-4px);box-shadow:0 14px 35px rgba(0,0,0,0.13);}
        .bromo-offer-card .bromo-offer-bg{position:absolute;inset:0;width:100%;height:100%;background-size:cover;background-position:center;background-repeat:no-repeat;transition:transform .5s ease;}
        .bromo-offer-card:hover .bromo-offer-bg{transform:scale(1.06);}
        .bromo-offer-card::after{content:'';z-index:1;top:0;left:0;width:100%;height:100%;position:absolute;background:linear-gradient(270deg,rgba(0,0,0,0) 0%,rgba(0,0,0,0.75) 100%);pointer-events:none;transition:opacity .35s;}
        .bromo-offer-card:hover::after{opacity:0.85;}
        .bromo-offer-content{position:relative;z-index:2;padding:40px 40px 40px 22px;width:100%;}
        .bromo-offer-badge{display:inline-block;padding:4px 12px;border-radius:999px;font-size:10px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:10px;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);color:#fff;}
        .bromo-offer-badge-discount{background:rgba(239,68,68,0.7)!important;border-color:rgba(239,68,68,0.5)!important;}
        .bromo-offer-badge-save{background:rgba(34,197,94,0.7)!important;border-color:rgba(34,197,94,0.5)!important;}
        .bromo-offer-title{color:#fff!important;font-size:clamp(17px,1.8vw,21px)!important;font-weight:700!important;line-height:1.15!important;margin:0 0 8px!important;letter-spacing:-0.03em!important;text-shadow:0 1px 10px rgba(0,0,0,0.2);}
        .bromo-offer-desc{color:rgba(255,255,255,0.78)!important;font-size:12px!important;line-height:1.5!important;margin:0 0 14px!important;font-weight:400!important;}
        .bromo-offer-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
        .bromo-offer-price{font-size:12px;color:rgba(255,255,255,0.65);font-weight:400;}
        .bromo-offer-price strong{font-size:17px;color:#fff;font-weight:700;}
        .bromo-offer-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:999px;background:rgba(255,255,255,0.12);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);color:#fff!important;font-size:12px;font-weight:600;text-decoration:none!important;transition:all .25s ease;white-space:nowrap;}
        .bromo-offer-btn i{font-size:10px;transition:transform .25s;}
        .bromo-offer-btn:hover{background:#fff!important;color:#111!important;gap:8px;}
        .bromo-offer-btn:hover i{transform:translate(2px,-2px);}
        @media(max-width:1199px){.bromo-offer-content{padding:32px 30px 32px 18px;}}
        @media(max-width:767px){
            .bromo-offer-content{padding:26px 24px 26px 16px;}
            .bromo-offer-card{border-radius:10px;}
        }
        @media(max-width:575px){
            .bromo-offers-section{padding:16px 0 40px;}
            .bromo-offer-content{padding:22px 16px 22px 14px;}
            .bromo-offer-title{font-size:16px!important;}
            .bromo-offer-desc{font-size:11px!important;margin-bottom:10px!important;}
            .bromo-offer-btn{padding:6px 12px;font-size:11px;}
            .bromo-offer-price strong{font-size:14px;}
            .bromo-offer-card:hover{transform:translateY(-3px);}
        }
        body.dark-mode .bromo-offers-section{background:transparent!important;}
        body.dark-mode .bromo-offer-card:hover{box-shadow:0 14px 35px rgba(0,0,0,0.35);}
        body.dark-mode .bromo-offer-card::after{background:linear-gradient(270deg,rgba(0,0,0,0) 0%,rgba(0,0,0,0.82) 100%);}



        /* ===== TESTIMONIALS — CLEAN BALANCED LAYOUT ===== */
        .rts-testimonials-area{padding:100px 0;background:#f8fafc;}
        .testi-image-wrap{overflow:hidden;border-radius:20px;height:450px;box-shadow:0 8px 30px rgba(0,0,0,0.06);}
        .testi-fixed-image{width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease;}
        .testi-image-wrap:hover .testi-fixed-image{transform:scale(1.04);}
        .testimonials-wrapper-five{padding:44px 50px 40px 40px;background:#fff;border-radius:20px;box-shadow:0 2px 20px rgba(0,0,0,0.04);border:1px solid #eef2f6;transition:all .4s cubic-bezier(.22,.8,.2,1);}
        .testimonials-wrapper-five:hover{box-shadow:0 8px 35px rgba(0,58,89,0.06);border-color:rgba(0,58,89,0.08);transform:translateY(-2px);}
        .testimonials-wrapper-five .quote-area{margin-bottom:28px;}
        .testimonials-wrapper-five .quote-area img{width:36px;height:auto;opacity:0.5;filter:brightness(0) saturate(0%) invert(18%) sepia(50%) hue-rotate(164deg) saturate(400%);}
        .testimonials-wrapper-five .text{font-size:20px;font-style:italic;color:#2c3e4e;line-height:1.55;margin-bottom:32px;font-weight:400;}
        .testimonials-wrapper-five .author-area{padding-top:18px;border-top:1px solid #eef2f6;}
        .testimonials-wrapper-five .author-area h6{font-family:var(--font-heading);color:#003A59;margin-bottom:3px;font-size:17px;font-weight:700;}
        .testimonials-wrapper-five .author-area p{font-family:var(--font-ui);color:#7a8e9e;font-size:13px;font-weight:500;margin:0;}
        .rts-testimonials-area.summer-travel .section-inner .slider-dots-2{max-width:max-content;right:0;left:auto;bottom:-40px;position:absolute;}
        .rts-testimonials-area.summer-travel .section-inner .slider-dots-2 .swiper-pagination-bullet{width:10px;height:10px;background:rgba(0,58,89,0.15);opacity:1;margin:0 5px!important;border-radius:50%;transition:all .3s;}
        .rts-testimonials-area.summer-travel .section-inner .slider-dots-2 .swiper-pagination-bullet.swiper-pagination-bullet-active{background:#003A59;width:24px;border-radius:10px;}
        body.dark-mode .rts-testimonials-area{background:#0d1117!important;}
        body.dark-mode .testimonials-wrapper-five{background:rgba(255,255,255,0.04)!important;border-color:rgba(255,255,255,0.06)!important;box-shadow:none!important;}
        body.dark-mode .testimonials-wrapper-five .text{color:#c9d1d9!important;}
        body.dark-mode .testimonials-wrapper-five h6{color:#e6edf3!important;}
        body.dark-mode .testimonials-wrapper-five p{color:#8b949e!important;}
        body.dark-mode .testimonials-wrapper-five .author-area{border-top-color:rgba(255,255,255,0.06)!important;}
        body.dark-mode .testimonials-wrapper-five:hover{border-color:rgba(255,255,255,0.12)!important;box-shadow:0 8px 35px rgba(0,0,0,0.15)!important;}
        body.dark-mode .rts-testimonials-area.summer-travel .section-inner .slider-dots-2 .swiper-pagination-bullet{background:rgba(255,255,255,0.15)!important;}
        body.dark-mode .rts-testimonials-area.summer-travel .section-inner .slider-dots-2 .swiper-pagination-bullet-active{background:#58a6ff!important;}
    </style>
</head>
<body class="home-yacht-bg with-sidebar">

<!-- ===== VIDEO LOADER ===== -->
<div id="vms-video-loader">
    <img src="assets/loader.gif" alt="Loading...">
    <div class="vms-loader-overlay"></div>
    <div class="vms-loader-content">
        <div class="vms-loader-bar-track">
            <div class="vms-loader-bar-fill"></div>
        </div>
        <span class="vms-loader-label">Loading your journey...</span>
    </div>
</div>

<!-- ===== BROMORISE HEADER ===== -->
<header class="bromo-header header--sticky" id="bromoHeader">
    <a href="." class="bromo-logo">
        <img src="assets/3d logo.png" alt="VMS Go Vista" class="vms-logo-img">
    </a>
    <nav class="bromo-nav">
        <a href="." class="active">Home</a>
        <a href="package">Packages</a>
        <a href="service">Services</a>
        <a href="about">About Us</a>
        <a href="contact">Contact</a>
    </nav>
    <div class="bromo-book-btn">
        <a href="contact">
            Book now
            <span class="bromo-arrow"><i class="fa-regular fa-arrow-up-right"></i></span>
        </a>
    </div>
    <div class="bromo-mobile-menu" id="menu-btn">
        <span><i class="fa-solid fa-bars"></i></span>
    </div>
</header>

<!-- ===== MOBILE NAVIGATION ===== -->
<div class="bromo-mobile-nav-overlay" id="mobileNavOverlay"></div>
<div class="bromo-mobile-nav" id="mobileNav">
    <button class="bromo-mobile-nav-close" id="mobileNavClose">
        <i class="fa-solid fa-xmark"></i>
    </button>
    <nav class="bromo-mobile-nav-links">
        <a href="." class="active">Home</a>
        <a href="package">Packages</a>
        <a href="service">Services</a>
        <a href="about">About Us</a>
        <a href="contact">Contact</a>
    </nav>
</div>

<!-- ===== BROMORISE HERO ===== -->
<div class="bromo-hero">
    <div class="bromo-hero-swiper">
        <div class="swiper bromo-bg-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/images/banner/bg-01.webp');"></div></div>
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/images/banner/bg-02.webp');"></div></div>
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/images/banner/bg-05.webp');"></div></div>
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/images/banner/bg-10.webp');"></div></div>
            </div>
        </div>
    </div>
    <div class="bromo-hero-content">
        <div class="bromo-center-text">
            <span class="bromo-eyebrow">East Java's Natural Wonder</span>
            <h1>
                <span class="bromo-line">Unforgettable Mount Bromo</span>
                <span class="bromo-line">Sunrise Experience</span>
            </h1>
        </div>
    </div>
    <div class="bromo-hero-bottom">
        <div class="bromo-left-card">
            <div class="bromo-avatars">
                <img src="assets/images/testimonials/01.webp" alt="user" onerror="this.src='assets/images/package/01.webp'">
                <img src="assets/images/testimonials/02.webp" alt="user" onerror="this.src='assets/images/package/02.webp'">
                <img src="assets/images/testimonials/03.webp" alt="user" onerror="this.src='assets/images/package/03.webp'">
                <span class="bromo-count">10K+</span>
                <span class="bromo-joined">Happy Travelers</span>
            </div>
            <p>From coastlines to mountain trails, discover handpicked journeys made for stories worth telling.</p>
            <a href="contact" class="bromo-hero-btn">
                Explore trips
                <span class="bromo-arrow"><i class="fa-regular fa-arrow-up-right"></i></span>
            </a>
        </div>
        <div class="bromo-right-cards">
            <div class="bromo-img-card is-active" data-slide-index="0">
                <img src="assets/images/banner/bg-01.webp" alt="Scenic road trips">
                <div class="bromo-card-label"><strong>Scenic Road Trips</strong><span>Follow unforgettable roads through dramatic landscapes.</span></div>
            </div>
            <div class="bromo-img-card" data-slide-index="1">
                <img src="assets/images/banner/bg-02.webp" alt="Coastal escapes">
                <div class="bromo-card-label"><strong>Coastal Escapes</strong><span>Chase clear blue waters, quiet coves, and sun-soaked days.</span></div>
            </div>
            <div class="bromo-img-card" data-slide-index="2">
                <img src="assets/images/banner/bg-05.webp" alt="Glacier adventures">
                <div class="bromo-card-label"><strong>Glacier Adventures</strong><span>Step into wild ice landscapes with expert-led experiences.</span></div>
            </div>
            <div class="bromo-img-card" data-slide-index="3">
                <img src="assets/images/banner/bg-10.webp" alt="Safari sunsets">
                <div class="bromo-card-label"><strong>Safari Sunsets</strong><span>Experience unforgettable wildlife beneath golden skies.</span></div>
            </div>
        </div>
    </div>
    <div class="bromo-hero-dots" id="bromoDots"></div>
</div>
<!-- ===== HERO END ===== -->

<!-- ===== ABOUT US — GLASS HEADER STYLE ===== -->
<section class="about-glass">
    <div class="container">
        <div class="about-glass-card wow fadeInUp" data-wow-delay="0.2s">
            <!-- LEFT: Image with glass frame -->
            <div class="about-glass-image wow fadeInLeft" data-wow-delay="0.3s">
                <div class="img-wrap">
                    <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=800&fit=crop&q=80" alt="Beautiful Travel Destination">
                </div>
            </div>

            <!-- RIGHT: Content -->
            <div class="about-glass-content wow fadeInRight" data-wow-delay="0.4s">

                <!-- Eyebrow -->
                <div class="about-glass-eyebrow">
                    <span class="ep-dot"></span>
                    Who We Are
                </div>

                <h2>
                    Your Trusted<br>
                    <span class="hl-gold">Travel Partner</span>
                </h2>
                <p class="glass-desc">At VMS GO VISTA PVT LTD, we craft personalized travel experiences that turn your dream journey into reality — from weekend getaways to international adventures.</p>
                <p class="glass-desc">Our expert team handles every detail with care, so you can focus on creating unforgettable memories.</p>

                <!-- Mini stats -->
                <div class="about-glass-stats">
                    <div class="about-glass-stat">
                        <span class="stat-num">1K<span class="stat-gold">+</span></span>
                        <span class="stat-label">Happy Travelers</span>
                    </div>
                    <div class="about-glass-stat">
                        <span class="stat-num">50<span class="stat-gold">+</span></span>
                        <span class="stat-label">Destinations</span>
                    </div>
                    <div class="about-glass-stat">
                        <span class="stat-num">24<span class="stat-gold">/7</span></span>
                        <span class="stat-label">Travel Support</span>
                    </div>
                </div>

                <a href="about.html" class="about-glass-btn wow fadeInUp" data-wow-delay="0.5s">
                    Learn More About Us
                    <span class="btn-glass-arrow"><i class="fa-solid fa-arrow-right"></i></span>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- ===== ABOUT US END ===== -->

<!-- ===== POPULAR PACKAGES (from DB) ===== -->
<section class="rts-popular-package-area rts-section-gap">
    <div class="container">
        <div class="section-top-area d-flex align-items-end justify-content-between">
            <div class="section-title-area wow fadeInLeft" data-wow-delay="0.2s">
                <p class="sub-title d-flex align-items-center gap-2"><img src="assets/images/banner/icon/02.svg" alt="">Our Packages</p>
                <h2 class="section-title mb-0 text-uppercase">Popular Packages</h2>
            </div>
            <div class="button-area mb--15 wow fadeInRight" data-wow-delay="0.2s">
                <a href="package" class="rts-btn text-btn with-arrow">Explore More <i class="fa-regular fa-arrow-up up-right"></i></a>
            </div>
        </div>
        <div class="section-inner mt--60">
            <div class="row g-5">
                <?php if (!empty($homepagePackages)): ?>
                    <?php foreach ($homepagePackages as $i => $pkg):
                        $imgUrl = packageImageUrl($pkg['main_image'] ?? null);
                        $slug   = e($pkg['slug'] ?? '');
                        $delay  = 0.2 + ($i * 0.2);
                    ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="package-wrapper radius-10 image-transform wow fadeInRight" data-wow-delay="<?= $delay ?>s">
                            <div class="image-area">
                                <img class="hover-image" src="<?= e($imgUrl) ?>" alt="<?= e($pkg['title']) ?>">
                            </div>
                            <a href="package-details/<?= $slug ?>" class="wishlist"><i class="fa-light fa-heart"></i></a>
                            <?php if (!empty($pkg['discount_percent'])): ?>
                                <span class="tag"><?= (int)$pkg['discount_percent'] ?>% OFF</span>
                            <?php endif; ?>
                            <div class="content">
                                <ul class="meta-content">
                                    <li><i class="fa-light fa-location-dot"></i> <?= e($pkg['destination'] ?? '') ?></li>
                                    <li><i class="fa-light fa-clock"></i> <?= (int)($pkg['days'] ?? 0) ?> Days</li>
                                </ul>
                                <h5 class="title"><a href="package-details/<?= $slug ?>"><?= e($pkg['title']) ?></a></h5>
                                <p class="price">From – <?= formatPrice((float)($pkg['price_discounted'] ?? $pkg['price_original'] ?? 0), $pkg['currency'] ?? 'USD') ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback static cards if no packages in DB -->
                    <?php
                    $fallback = [
                        ['img'=>'package/01.webp','loc'=>'Iceland','days'=>3,'title'=>'Maldives Luxury Cruise','price'=>'$1,200'],
                        ['img'=>'package/02.webp','loc'=>'Indonesia','days'=>3,'title'=>'Bali Private Charter','price'=>'$1,100'],
                        ['img'=>'package/03.webp','loc'=>'UAE','days'=>3,'title'=>'Dubai Skyline Cruise','price'=>'$1,800'],
                        ['img'=>'package/04.webp','loc'=>'Greece','days'=>3,'title'=>'Greek Island Hopping','price'=>'$1,200'],
                    ];
                    foreach ($fallback as $i => $f):
                        $delay = 0.2 + ($i * 0.2);
                    ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="package-wrapper radius-10 image-transform wow fadeInRight" data-wow-delay="<?= $delay ?>s">
                            <div class="image-area">
                                <img class="hover-image" src="assets/images/<?= $f['img'] ?>" alt="<?= $f['title'] ?>">
                            </div>
                            <a href="package" class="wishlist"><i class="fa-light fa-heart"></i></a>
                            <div class="content">
                                <ul class="meta-content">
                                    <li><i class="fa-light fa-location-dot"></i> <?= $f['loc'] ?></li>
                                    <li><i class="fa-light fa-clock"></i> <?= $f['days'] ?> Days</li>
                                </ul>
                                <h5 class="title"><a href="package"><?= $f['title'] ?></a></h5>
                                <p class="price">From – <?= $f['price'] ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<!-- ===== DESTINATIONS SECTION — PREMIUM INDIA GRID ===== -->
<section class="rts-destination-area india-dest-section" style="padding-top:30px;">
    <div class="container">
        <div class="section-top-area d-flex align-items-end justify-content-between">
            <div class="section-title-area wow fadeInLeft" data-wow-delay="0.2s">
                <p class="sub-title d-flex align-items-center gap-2"><img src="assets/images/banner/icon/02.svg" alt="">Explore India</p>
                <h2 class="section-title mb-0 text-uppercase">Where in India Will You Go Next?</h2>
            </div>
            <div class="button-area mb--15 wow fadeInRight" data-wow-delay="0.2s">
                <a href="package" class="rts-btn text-btn with-arrow">Explore More <i class="fa-regular fa-arrow-up up-right"></i></a>
            </div>
        </div>
        <div class="section-inner rts-section-gapBottom mt--60">
            <div class="row g-5">
                <div class="col-lg-6 col-md-12">
                    <div class="destination-wrapper-4 radius-10 image-transform wow fadeInRight" data-wow-delay="0.2s">
                        <div class="image-area image-transform">
                            <a href="package?destination=Agra"><img class="hover-image" src="assets/images/destination/india-taj.webp" alt="Taj Mahal" loading="lazy"></a>
                        </div>
                        <div class="content-area">
                            <p>120+ Tours</p>
                            <h4 class="title"><a href="package?destination=Agra">Taj Mahal, Agra</a></h4>
                            <a href="package?destination=Agra" class="rts-btn text-btn with-arrow">Explore Now <i class="fa-regular fa-arrow-up up-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="destination-wrapper-4 radius-10 image-transform wow fadeInRight" data-wow-delay="0.4s">
                        <div class="image-area image-transform">
                            <a href="package?destination=Jaipur"><img class="hover-image" src="assets/images/destination/india-jaipur.webp" alt="Jaipur" loading="lazy"></a>
                        </div>
                        <div class="content-area">
                            <p>85+ Tours</p>
                            <h4 class="title"><a href="package?destination=Jaipur">Jaipur, Rajasthan</a></h4>
                            <a href="package?destination=Jaipur" class="rts-btn text-btn with-arrow">Explore Now <i class="fa-regular fa-arrow-up up-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="destination-wrapper-4 radius-10 image-transform wow fadeInRight" data-wow-delay="0.6s">
                        <div class="image-area image-transform">
                            <a href="package?destination=Kerala"><img class="hover-image" src="assets/images/destination/india-kerala.webp" alt="Kerala" loading="lazy"></a>
                        </div>
                        <div class="content-area">
                            <p>95+ Tours</p>
                            <h4 class="title"><a href="package?destination=Kerala">Kerala Backwaters</a></h4>
                            <a href="package?destination=Kerala" class="rts-btn text-btn with-arrow">Explore Now <i class="fa-regular fa-arrow-up up-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="destination-wrapper-4 radius-10 image-transform wow fadeInLeft" data-wow-delay="1.2s">
                        <div class="image-area image-transform">
                            <a href="package?destination=Goa"><img class="hover-image" src="assets/images/destination/india-goa.webp" alt="Goa" loading="lazy"></a>
                        </div>
                        <div class="content-area">
                            <p>110+ Tours</p>
                            <h4 class="title"><a href="package?destination=Goa">Goa Beaches</a></h4>
                            <a href="package?destination=Goa" class="rts-btn text-btn with-arrow">Explore Now <i class="fa-regular fa-arrow-up up-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 order-change">
                    <div class="destination-wrapper-4 radius-10 image-transform wow fadeInLeft" data-wow-delay="1s">
                        <div class="image-area image-transform">
                            <a href="package?destination=Kashmir"><img class="hover-image" src="assets/images/destination/india-kashmir.webp" alt="Kashmir" loading="lazy"></a>
                        </div>
                        <div class="content-area">
                            <p>130+ Tours</p>
                            <h4 class="title"><a href="package?destination=Kashmir">Kashmir Valley</a></h4>
                            <a href="package?destination=Kashmir" class="rts-btn text-btn with-arrow">Explore Now <i class="fa-regular fa-arrow-up up-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="destination-wrapper-4 radius-10 image-transform wow fadeInLeft" data-wow-delay=".8s">
                        <div class="image-area image-transform">
                            <a href="package?destination=Manali"><img class="hover-image" src="assets/images/destination/india-manali.webp" alt="Manali" loading="lazy"></a>
                        </div>
                        <div class="content-area">
                            <p>75+ Tours</p>
                            <h4 class="title"><a href="package?destination=Manali">Manali, Himachal</a></h4>
                            <a href="package?destination=Manali" class="rts-btn text-btn with-arrow">Explore Now <i class="fa-regular fa-arrow-up up-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- ===== YACHT DEALS SLIDER (from DB – popular packages) ===== -->
<section class="rts-popular-package-area rts-section-gap body-bg-three">
    <div class="container">
        <div class="section-top-area d-flex align-items-end justify-content-between">
            <div class="section-title-area wow fadeInLeft" data-wow-delay="0.2s">
                <p class="sub-title d-flex align-items-center gap-2"><img src="assets/images/banner/icon/02.svg" alt="">Our Deals</p>
                <h2 class="section-title mb-0 text-uppercase">Explore Our Exclusive <br>Tour Deals</h2>
            </div>
            <div class="button-area mb--15 wow fadeInRight" data-wow-delay="0.2s">
                <a href="package" class="rts-btn text-btn with-arrow">Explore More <i class="fa-regular fa-arrow-up up-right"></i></a>
            </div>
        </div>
        <div class="section-inner position-relative mt--60 pb--80 wow fadeInUp" data-wow-delay="0.2s">
            <div class="swiper trip-slider2">
                <div class="swiper-wrapper">
                    <?php
                    // Use popular packages from DB, fall back to static data if empty
                    $sliderPackages = !empty($popularPackages) ? $popularPackages : [];
                    $discounts = ['30% OFF','20% OFF','40% OFF','25% OFF','35% OFF','15% OFF','50% OFF','10% OFF'];
                    if (!empty($sliderPackages)):
                        foreach ($sliderPackages as $i => $pkg):
                            $imgUrl = packageImageUrl($pkg['main_image'] ?? null);
                            $slug   = e($pkg['slug'] ?? '');
                            $disc   = !empty($pkg['discount_percent']) ? (int)$pkg['discount_percent'].'% OFF' : $discounts[$i % count($discounts)];
                    ?>
                    <div class="swiper-slide">
                        <div class="package-wrapper image-transform radius-10">
                            <div class="image-area">
                                <img class="hover-image" src="<?= e($imgUrl) ?>" alt="<?= e($pkg['title']) ?>">
                            </div>
                            <a href="package-details/<?= $slug ?>" class="wishlist"><i class="fa-light fa-heart"></i></a>
                            <span class="tag"><?= $disc ?></span>
                            <div class="content">
                                <ul class="meta-content">
                                    <li><i class="fa-light fa-location-dot"></i> <?= e($pkg['destination'] ?? '') ?></li>
                                    <li><i class="fa-light fa-clock"></i> <?= (int)($pkg['days'] ?? 0) ?> Days</li>
                                </ul>
                                <h5 class="title"><a href="package-details/<?= $slug ?>"><?= e($pkg['title']) ?></a></h5>
                                <p class="price">From – <?= formatPrice((float)($pkg['price_discounted'] ?? $pkg['price_original'] ?? 0), $pkg['currency'] ?? 'USD') ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else:
                    // Static fallback slides
                    $staticSlides = [
                        ['img'=>'package/01.webp','loc'=>'Iceland','days'=>3,'title'=>'Maldives Luxury Cruise','price'=>'$1,200','disc'=>'30% OFF'],
                        ['img'=>'package/03.webp','loc'=>'UAE','days'=>3,'title'=>'Dubai Skyline Cruise','price'=>'$1,800','disc'=>'20% OFF'],
                        ['img'=>'package/02.webp','loc'=>'Indonesia','days'=>3,'title'=>'Bali Private Charter','price'=>'$1,100','disc'=>'40% OFF'],
                        ['img'=>'package/04.webp','loc'=>'Greece','days'=>3,'title'=>'Greek Island Hopping','price'=>'$1,200','disc'=>'30% OFF'],
                    ];
                    foreach ($staticSlides as $s): ?>
                    <div class="swiper-slide">
                        <div class="package-wrapper image-transform radius-10">
                            <div class="image-area"><img class="hover-image" src="assets/images/<?= $s['img'] ?>" alt="<?= $s['title'] ?>"></div>
                            <a href="package" class="wishlist"><i class="fa-light fa-heart"></i></a>
                            <span class="tag"><?= $s['disc'] ?></span>
                            <div class="content">
                                <ul class="meta-content">
                                    <li><i class="fa-light fa-location-dot"></i> <?= $s['loc'] ?></li>
                                    <li><i class="fa-light fa-clock"></i> <?= $s['days'] ?> Days</li>
                                </ul>
                                <h5 class="title"><a href="package"><?= $s['title'] ?></a></h5>
                                <p class="price">From – <?= $s['price'] ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="swiper-nav-pag-area">
                <div class="swiper-pagination body-bg-four"></div>
                <div class="swiper-navigation">
                    <div class="swiper-btn swiper-btn-prev"><i class="fa-regular fa-chevron-left"></i></div>
                    <div class="swiper-btn swiper-btn-next"><i class="fa-regular fa-chevron-right"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== HOLIDAY PACKAGES SECTION (from about.html) ===== -->
    <section class="holiday-packages-section" id="holiday-packages">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="holiday-packages-sidebar">
                        <div class="heading-section">
                            <span class="heading-sub">Our Holiday Packages</span>
                            <h2 class="heading-title">Where Dreams<br>Meet Destinations</h2>
                        </div>
                        <ul class="holiday-package-nav">
                            <li><a href="#package-01" class="holiday-package-nav-link">Domestic Holidays</a></li>
                            <li><a href="#package-02" class="holiday-package-nav-link">International Tours</a></li>
                            <li><a href="#package-03" class="holiday-package-nav-link">Honeymoon Packages</a></li>
                            <li><a href="#package-04" class="holiday-package-nav-link">Family Vacations</a></li>
                            <li><a href="#package-05" class="holiday-package-nav-link">Group Tours</a></li>
                            <li><a href="#package-06" class="holiday-package-nav-link">Corporate Travel</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="holiday-packages-list">
                        <article id="package-01" class="holiday-package-card">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="holiday-package-card-head">
                                        <h3 class="holiday-package-title">Domestic Holiday Packages</h3>
                                        <span class="holiday-package-num">(01)</span>
                                    </div>
                                    <p class="holiday-package-desc">Experience India's breathtaking mountains, beaches, heritage cities, and wildlife with customized travel packages designed for every explorer.</p>
                                    <div class="holiday-package-tags">
                                        <span class="holiday-package-tag">Mountains</span>
                                        <span class="holiday-package-tag">Beaches</span>
                                        <span class="holiday-package-tag">Heritage</span>
                                        <span class="holiday-package-tag">Wildlife</span>
                                    </div>
                                </div>
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="holiday-package-thumb">
                                        <img src="https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?auto=format&fit=crop&w=1200&q=80" alt="Domestic Holiday Packages" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </article>
                        <article id="package-02" class="holiday-package-card">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="holiday-package-card-head">
                                        <h3 class="holiday-package-title">International Tours</h3>
                                        <span class="holiday-package-num">(02)</span>
                                    </div>
                                    <p class="holiday-package-desc">Explore world-famous destinations with carefully planned itineraries and premium travel services for unforgettable global adventures.</p>
                                    <div class="holiday-package-tags">
                                        <span class="holiday-package-tag">Europe</span>
                                        <span class="holiday-package-tag">Asia</span>
                                        <span class="holiday-package-tag">Dubai</span>
                                        <span class="holiday-package-tag">Maldives</span>
                                    </div>
                                </div>
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="holiday-package-thumb">
                                        <img src="https://images.unsplash.com/photo-1467269204594-9661b134dd2b?auto=format&fit=crop&w=1200&q=80" alt="International Tours" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </article>
                        <article id="package-03" class="holiday-package-card">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="holiday-package-card-head">
                                        <h3 class="holiday-package-title">Honeymoon Packages</h3>
                                        <span class="holiday-package-num">(03)</span>
                                    </div>
                                    <p class="holiday-package-desc">Celebrate love with romantic escapes to the world's most beautiful destinations, creating memories that last a lifetime.</p>
                                    <div class="holiday-package-tags">
                                        <span class="holiday-package-tag">Romantic</span>
                                        <span class="holiday-package-tag">Beach</span>
                                        <span class="holiday-package-tag">Hill Station</span>
                                        <span class="holiday-package-tag">Cruise</span>
                                    </div>
                                </div>
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="holiday-package-thumb">
                                        <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&w=1200&q=80" alt="Honeymoon Packages" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </article>
                        <article id="package-04" class="holiday-package-card">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="holiday-package-card-head">
                                        <h3 class="holiday-package-title">Family Vacations</h3>
                                        <span class="holiday-package-num">(04)</span>
                                    </div>
                                    <p class="holiday-package-desc">Create unforgettable memories with family-friendly holidays designed for all age groups, ensuring fun for everyone.</p>
                                    <div class="holiday-package-tags">
                                        <span class="holiday-package-tag">Kid-Friendly</span>
                                        <span class="holiday-package-tag">Resorts</span>
                                        <span class="holiday-package-tag">Parks</span>
                                        <span class="holiday-package-tag">Activities</span>
                                    </div>
                                </div>
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="holiday-package-thumb">
                                        <img src="assets/img1.jpg" alt="Family Vacations" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </article>
                        <article id="package-05" class="holiday-package-card">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="holiday-package-card-head">
                                        <h3 class="holiday-package-title">Group Tours</h3>
                                        <span class="holiday-package-num">(05)</span>
                                    </div>
                                    <p class="holiday-package-desc">Travel together with expertly planned group tours for friends, schools, organizations, and special events with shared experiences.</p>
                                    <div class="holiday-package-tags">
                                        <span class="holiday-package-tag">Friends</span>
                                        <span class="holiday-package-tag">School</span>
                                        <span class="holiday-package-tag">Events</span>
                                        <span class="holiday-package-tag">Adventure</span>
                                    </div>
                                </div>
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="holiday-package-thumb">
                                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80" alt="Group Tours" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </article>
                        <article id="package-06" class="holiday-package-card">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 order-md-1 order-2">
                                    <div class="holiday-package-card-head">
                                        <h3 class="holiday-package-title">Corporate Travel</h3>
                                        <span class="holiday-package-num">(06)</span>
                                    </div>
                                    <p class="holiday-package-desc">Professional travel management solutions for meetings, conferences, incentive trips, and business travel with seamless coordination.</p>
                                    <div class="holiday-package-tags">
                                        <span class="holiday-package-tag">Meetings</span>
                                        <span class="holiday-package-tag">Conferences</span>
                                        <span class="holiday-package-tag">Incentives</span>
                                        <span class="holiday-package-tag">MICE</span>
                                    </div>
                                </div>
                                <div class="col-md-6 order-md-2 order-1">
                                    <div class="holiday-package-thumb">
                                        <img src="assets/imh2.png" alt="Corporate Travel" loading="lazy">
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- ===== WHY CHOOSE US SECTION (from about.html) ===== -->
    <section class="rts-why-choose-us-area rts-section-gap">
        <div class="container">
            <div class="section-inner">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="why-choose-left-image-area">
                            <div class="why-choose-video-wrap radius-10 wow fadeInLeft" data-wow-delay="0.1s">
                                <video id="whyChooseVideo" autoplay muted loop playsinline>
                                    <source
                                        src="assets/video3.mp4"
                                        type="video/mp4">
                                </video>
                                <div class="video-gradient-overlay"></div>
                                <button class="video-play-btn" id="videoPlayBtn" aria-label="Pause video">
                                    <i class="fa-solid fa-pause"></i>
                                </button>
                            </div>
                            <script>
                                (function() {
                                    var video = document.getElementById('whyChooseVideo');
                                    var btn = document.getElementById('videoPlayBtn');
                                    if (video && btn) {
                                        btn.addEventListener('click', function() {
                                            if (video.paused) {
                                                video.play();
                                                btn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                                                btn.setAttribute('aria-label', 'Pause video');
                                            } else {
                                                video.pause();
                                                btn.innerHTML = '<i class="fa-solid fa-play"></i>';
                                                btn.setAttribute('aria-label', 'Play video');
                                            }
                                        });
                                    }
                                })();
                            </script>
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.2s">
                        <div class="why-choose-right-side-content">
                            <div class="why-choose-clip-section">
                                <div class="section-title-area">
                                    <p class="sub-title">Why Choose Us</p>
                                    <h2 class="section-title mb-0">We Are Committed to Your Travel Happiness</h2>
                                    <p class="desc">We go the extra mile to make your travel experience smooth, safe, and unforgettable. Here's why thousands of travelers trust us for their journeys around the world.</p>
                                </div>
                                <div class="why-choose-clip-body">
                                    <ul class="why-choose-wrapper-list">
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-map"></i></div>
                                            <div class="content">
                                                <h5 class="title">Personalized Travel Planning</h5>
                                                <p>Every journey is carefully designed around your preferences, budget, and travel goals to create a truly unique experience.</p>
                                            </div>
                                        </li>
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-badge-check"></i></div>
                                            <div class="content">
                                                <h5 class="title">Best Price Assurance</h5>
                                                <p>Enjoy competitive pricing and exceptional value without compromising on quality or comfort.</p>
                                            </div>
                                        </li>
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-user-tie"></i></div>
                                            <div class="content">
                                                <h5 class="title">Experienced Travel Experts</h5>
                                                <p>Our dedicated travel specialists provide expert guidance and professional assistance at every stage of your journey.</p>
                                            </div>
                                        </li>
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-suitcase"></i></div>
                                            <div class="content">
                                                <h5 class="title">Customized Holiday Packages</h5>
                                                <p>From romantic honeymoons to family vacations and corporate travel, every itinerary is tailored to your needs.</p>
                                            </div>
                                        </li>
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-passport"></i></div>
                                            <div class="content">
                                                <h5 class="title">Visa & Travel Assistance</h5>
                                                <p>We simplify the travel process with complete visa guidance, documentation support, and travel essentials.</p>
                                            </div>
                                        </li>
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-handshake"></i></div>
                                            <div class="content">
                                                <h5 class="title">Trusted Hotel & Flight Partners</h5>
                                                <p>We collaborate with reliable airlines and premium hotels to ensure comfort, convenience, and peace of mind.</p>
                                            </div>
                                        </li>
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-handshake"></i></div>
                                            <div class="content">
                                                <h5 class="title">24/7 Customer Support</h5>
                                                <p>Our support team is available before, during, and after your trip to ensure a smooth travel experience.</p>
                                            </div>
                                        </li>
                                        <li class="radius-10 wow fadeInRight">
                                            <div class="icon"><i class="fa-solid fa-handshake"></i></div>
                                            <div class="content">
                                                <h5 class="title">Safe & Hassle-Free Booking</h5>
                                                <p>With transparent pricing, secure bookings, and reliable service, you can travel with complete confidence.</p>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="why-choose-fixed-bottom">
                                        <div class="button-area">
                                            <a href="contact" class="rts-btn btn-primary">Talk to Our Team</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


<!-- ===== TRAVEL MEMORIES SECTION ===== -->
<section class="rts-destination-area"><br><br>
    <div class="container">
        <div class="section-top-area d-flex align-items-end justify-content-between">
            <div class="section-title-area wow fadeInLeft" data-wow-delay="0.2s">
                <p class="sub-title d-flex align-items-center gap-2"><img src="assets/images/banner/icon/02.svg" alt="">Travel Stories</p>
                <h2 class="section-title mb-0 text-uppercase">Memorable Journeys</h2>
            </div>
            <div class="button-area mb--15 wow fadeInRight" data-wow-delay="0.2s">
                <a href="package" class="rts-btn text-btn with-arrow">View All <i class="fa-regular fa-arrow-up up-right"></i></a>
            </div>
        </div>
        <div class="container"><div class=""><div class="outer-box overflow-hidden radius-10 wow fadeInUp" data-wow-delay="0.4s">
            <br><br>
            <div class="project-block-four">
                <div class="image-area"><img src="assets/images/destination/india-kashmir.webp" width="630" alt="Kashmir Valley"></div>
                <div class="content-area"><p><i class="fa-light fa-location-dot"></i> Jammu & Kashmir</p><h4 class="title"><a href="vms-tour-details">Kashmir Valley Escape</a></h4></div>
            </div>
            <div class="project-block-four">
                <div class="image-area"><img src="assets/images/destination/india-taj.webp" width="630" alt="Taj Mahal"></div>
                <div class="content-area"><p><i class="fa-light fa-location-dot"></i> Uttar Pradesh</p><h4 class="title"><a href="vms-tour-details">Taj Mahal Sunrise Tour</a></h4></div>
            </div>
            <div class="project-block-four">
                <div class="image-area"><img src="assets/images/destination/india-jaipur.webp" width="630" alt="Jaipur Palace"></div>
                <div class="content-area"><p><i class="fa-light fa-location-dot"></i> Rajasthan</p><h4 class="title"><a href="vms-tour-details">Jaipur Heritage Walk</a></h4></div>
            </div>
            <div class="project-block-four">
                <div class="image-area"><img src="assets/images/destination/india-goa.webp" width="630" alt="Goa Beach"></div>
                <div class="content-area"><p><i class="fa-light fa-location-dot"></i> Goa</p><h4 class="title"><a href="vms-tour-details">Goa Beach Retreat</a></h4></div>
            </div>
            <div class="project-block-four active">
                <div class="image-area"><img src="assets/images/destination/india-manali.webp" width="630" alt="Manali Mountains"></div>
                <div class="content-area"><p><i class="fa-light fa-location-dot"></i> Himachal Pradesh</p><h4 class="title"><a href="vms-tour-details">Manali Himalayan Trek</a></h4></div>
            </div>
        </div></div></div>
    </div>
</section>
<br><br><br>

</section>




<!-- ===== RECENT GALLERY — BENTO GRID ===== -->
<section class="rts-recent-gallery-area" id="gallery">
    <div class="container">
        <div class="section-title-area text-center mb--50 wow fadeInUp" data-wow-delay="0.1s">
            <p class="fst-italic mb--5" style="font-size:16px;color:var(--color-body-1);">Captured Moments, Lasting Memories</p>
            <h2 class="mb-0" style="font-size:36px;font-weight:700;color:var(--color-title);">Recent Gallery</h2>
        </div>
        <div class="bento-gallery wow fadeInUp" data-wow-delay="0.2s">
            <div class="bento-item bento-item-1">
                <a href="assets/images/gallery/01.webp" class="gallery-image magnific-zoom">
                    <img src="assets/images/gallery/01.webp" alt="Kerala Backwaters">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Kerala Backwaters</span>
                            <span class="bento-location">God's Own Country</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-2">
                <a href="assets/images/gallery/02.webp" class="gallery-image magnific-zoom">
                    <img src="assets/images/gallery/02.webp" alt="Taj Mahal">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Taj Mahal</span>
                            <span class="bento-location">Agra, Uttar Pradesh</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-3">
                <a href="assets/images/gallery/03.webp" class="gallery-image magnific-zoom">
                    <img src="assets/images/gallery/03.webp" alt="Jaipur">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Pink City</span>
                            <span class="bento-location">Jaipur, Rajasthan</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-4">
                <a href="assets/images/gallery/04.webp" class="gallery-image magnific-zoom">
                    <img src="assets/images/gallery/04.webp" alt="Himalayas">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Himalayan Range</span>
                            <span class="bento-location">Northern India</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-5">
                <a href="assets/images/gallery/05.webp" class="gallery-image magnific-zoom">
                    <img src="assets/images/gallery/05.webp" alt="Goa Beach">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Goa Beaches</span>
                            <span class="bento-location">Sun, Sand & Sea</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- ===== TESTIMONIALS ===== -->
<section class="rts-testimonials-area summer-travel rts-section-gapBottom">
    <div class="container">
        <div class="section-title-area center-style">
            <p class="fst-italic mb--5 wow fadeInUp" data-wow-delay="0.1s" style="font-size:16px;color:var(--color-body-1);">Trusted by travelers worldwide</p>
            <h2 class="section-title text-uppercase wow fadeInUp" data-wow-delay="0.2s">What Our Guests Say</h2>
        </div>
        <div class="section-inner position-relative mt--60 wow fadeInUp" data-wow-delay="0.2s">
            <?php
            $testimonials = [
                ['text'=>'It was the best vacation of my life! The beach resort in Bali was stunning and everything was perfectly arranged. Highly recommended!','name'=>'Jessica R., USA','role'=>'Solo Traveler'],
                ['text'=>'Absolutely breathtaking! The team handled everything seamlessly. We felt safe, comfortable, and inspired throughout the journey.','name'=>'Mark T., UK','role'=>'Travel Blogger'],
                ['text'=>'Incredible experience from start to finish. The local guides were knowledgeable and friendly. Will definitely book again!','name'=>'Aisha M., UAE','role'=>'Photographer'],
                ['text'=>'Beyond expectations! Every detail was thoughtfully planned. The sunrise over the mountains was a moment I will never forget.','name'=>'Carlos P., Spain','role'=>'Adventure Enthusiast'],
            ];
            ?>
            <div class="row g-5 align-items-center">
                <div class="col-xl-5 col-lg-6">
                    <div class="testi-image-wrap">
                        <img src="assets/images/destination/india-kerala.webp" alt="Beautiful Kerala Backwaters" class="testi-fixed-image">
                    </div>
                </div>
                <div class="col-xl-7 col-lg-6">
                    <div class="testimonials-main-slider">
                        <div class="swiper testimonials-slider3">
                            <div class="swiper-wrapper">
                                <?php foreach ($testimonials as $t): ?>
                                <div class="swiper-slide">
                                    <div class="testimonials-wrapper-five">
                                        <div class="quote-area">
                                            <img src="assets/images/testimonials/quote-06.svg" alt="quote">
                                        </div>
                                        <h5 class="text">
                                            <?= e($t['text']) ?>
                                        </h5>
                                        <div class="author-area">
                                            <h6><?= e($t['name']) ?></h6>
                                            <p><?= e($t['role']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Pagination dots -->
                        <div class="slider-dots-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== BRAND LOGOS ===== -->
<!-- <div class="rts-brand-area rts-section-gapBottom wow fadeInUp" data-wow-delay="0.2s">
    <div class="container">
        <div class="swiper brand-slider">
            <div class="swiper-wrapper">
             
                <div class="swiper-slide"><div class="image-area"><a href="#"><img src="assets/images/brand/0<?= $b ?>.svg" alt=""></a></div></div>
              
            </div>
        </div>
    </div>
</div> -->





<!-- ===== NEWSLETTER / STAY CONNECTED ===== -->
<section class="rts-newsletter-area bromo-newsletter-section">
    <div class="bromo-newsletter-wrap" style="background-image:url('assets/images/destination/india-kerala.webp');">
        <div class="bromo-newsletter-content">
            <div class="bromo-newsletter-inner">
                <span class="bromo-newsletter-badge">Stay Connected</span>
                <h2 class="bromo-newsletter-title">Exclusive Tour<br>Deals &amp; Updates</h2>
                <p class="bromo-newsletter-desc">Get handpicked travel offers, destination inspiration, and early-bird discounts delivered straight to your inbox.</p>
                <form class="bromo-newsletter-form" action="#">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit" style="width: 28%;background:#003A59;" class="bromo-newsletter-btn">Subscribe <i class="fa-regular fa-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>

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
                    <a href="tel:+919876543210" class="cta-btn-secondary">
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
                <div class="vms-logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 18L8.5 10L12 14L15.5 8L20 18H4Z" stroke-linejoin="round"/>
                        <circle cx="17" cy="7" r="2"/>
                    </svg>
                </div>
                <span class="vms-logo-text">VMS Go Vista</span>
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
        <p class="vms-credit">&copy; <?= date('Y') ?> VMS Go Vista &middot; All rights reserved</p>
        <div class="vms-credit">
            <span>Crafted with dedication by</span>
            <span class="vms-author">
                <span class="vms-author-avatar"></span>
                VMS Go Vista Team
            </span>
        </div>
    </div>

    <!-- Video Section with travel footage -->
    <div class="vms-video-section">
        <video autoplay muted loop playsinline poster="">
            <source src="assets/video6.mp4" type="video/mp4">
        </video>
        <div class="vms-video-gradient"></div>
        <div class="vms-big-text" style="font-family:Sunsive">VMS Go Vista</div>
    </div>
</footer>


<!-- ===== MOBILE SIDEBAR ===== -->
<div id="side-bar" class="side-bar header-two header-eight">
    <button class="close-icon-menu"><i class="fa-sharp fa-thin fa-xmark"></i></button>
    <a class="logo" href="."><img src="assets/images/logo/05.svg" alt=""></a>
    <div class="mobile-menu-main">
        <nav class="nav-main mainmenu-nav mt--30">
            <ul class="mainmenu metismenu" id="mobile-menu-active">
                <li><a href="." class="main">Home</a></li>
                <li class="has-droupdown">
                    <a href="#" class="main">Tour</a>
                    <ul class="submenu mm-collapse">
                        <li><a class="mobile-menu-link" href="package">All Tours</a></li>
                        <li><a class="mobile-menu-link" href="package-details.php">Tour Details</a></li>
                    </ul>
                </li>
                <li><a href="about" class="main">About</a></li>
                <li><a href="contact" class="main">Contact Us</a></li>
            </ul>
        </nav>
        <div class="follow-us">
            <ul>
                <li><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="#" aria-label="instagram"><i class="fab fa-instagram"></i></a></li>
                <li><a href="#" aria-label="youtube"><i class="fab fa-youtube"></i></a></li>
            </ul>
        </div>
    </div>
</div>
<div id="anywhere-home"></div>
<div class="progress-wrap">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition:stroke-dashoffset 10ms linear 0s;stroke-dasharray:307.919,307.919;stroke-dashoffset:307.919;"></path>
    </svg>
</div>

<!-- Scripts -->
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
<script defer src="assets/js/plugins/contact-form.js"></script>
<script defer src="assets/js/main.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.min.js"></script>
<script>
document.addEventListener('turbo:load',function(){
    document.body.classList.add('loaded');
    if(typeof WOW!=='undefined'){new WOW().init();}
});
</script>

<!-- BromoRise Hero FIFO Carousel -->
    <script>
        (function initHeroCarousel() {
            var hero = document.querySelector('.bromo-hero');
            var cardRow = document.querySelector('.bromo-right-cards');
            var cards = Array.prototype.slice.call(document.querySelectorAll('.bromo-img-card'));
            var slides = Array.prototype.slice.call(document.querySelectorAll('.bromo-bg-slider .swiper-slide'));
            var dots = document.getElementById('bromoDots');
            var isTransitioning = false;
            var autoplayId;
            var transitionMs = 850;

            if (!hero || !cardRow || !cards.length || cards.length !== slides.length) {
                console.log('Hero carousel init failed:', {hero: !!hero, cardRow: !!cardRow, cards: cards.length, slides: slides.length});
                return;
            }

            console.log('Hero carousel initialized with', cards.length, 'slides');

            cards.forEach(function (card) {
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.setAttribute('aria-label', 'Show ' + card.querySelector('img').alt + ' in hero');
            });

            function renderActive() {
                var activeSlide = Number(cards[0].dataset.slideIndex);
                cards.forEach(function (card, cardIndex) {
                    var active = cardIndex === 0;
                    card.classList.toggle('is-active', active);
                    card.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                slides.forEach(function (slide, slideIndex) {
                    var active = slideIndex === activeSlide;
                    slide.classList.toggle('is-main-active', active);
                    slide.style.opacity = active ? '1' : '0';
                    slide.style.zIndex = active ? '1' : '0';
                });
                if (dots) {
                    dots.innerHTML = cards.map(function (_, dotIndex) {
                        return '<button type="button" class="' + (dotIndex === 0 ? 'is-active' : '') + '" aria-label="Show image ' + (dotIndex + 1) + '"></button>';
                    }).join('');
                    Array.prototype.forEach.call(dots.querySelectorAll('button'), function (dot, dotIndex) {
                        dot.addEventListener('click', function () { transitionTo(dotIndex); });
                    });
                }
            }

            function transitionTo(index) {
                if (isTransitioning || index === 0) return;
                isTransitioning = true;
                window.clearInterval(autoplayId);

                var previousRects = new Map();
                cards.forEach(function (card) { previousRects.set(card, card.getBoundingClientRect()); });
                var selected = cards[index];
                var selectedImage = selected.querySelector('img');
                var sourceRect = selectedImage.getBoundingClientRect();
                var heroRect = hero.getBoundingClientRect();
                var clone = selectedImage.cloneNode(true);
                var startLeft = sourceRect.left - heroRect.left;
                var startTop = sourceRect.top - heroRect.top;
                var selectedSlide = slides[Number(selected.dataset.slideIndex)];

                clone.className = 'bromo-shared-image';
                clone.style.left = startLeft + 'px';
                clone.style.top = startTop + 'px';
                clone.style.width = sourceRect.width + 'px';
                clone.style.height = sourceRect.height + 'px';
                clone.style.borderRadius = getComputedStyle(selected).borderRadius;
                hero.appendChild(clone);
                // Keep the cards in a circular queue. With 0, 1, 2, 3 this makes
                // autoplay visit every image instead of bouncing between 0 and 1.
                cards = cards.slice(index).concat(cards.slice(0, index));

                cards.forEach(function (card) { cardRow.appendChild(card); });
                renderActive();
                selectedSlide.querySelector('.bromo-hero-slide-bg').style.backgroundImage = 'url("' + (selectedImage.currentSrc || selectedImage.src) + '")';

                cards.forEach(function (card) {
                    var before = previousRects.get(card);
                    var after = card.getBoundingClientRect();
                    var translateX = before.left - after.left;
                    var translateY = before.top - after.top;
                    var cardScaleX = before.width / after.width;
                    var cardScaleY = before.height / after.height;
                    card.style.transition = 'none';
                    card.style.transform = 'translate(' + translateX + 'px, ' + translateY + 'px) scale(' + cardScaleX + ', ' + cardScaleY + ')';
                });

                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        // Animate the image box dimensions rather than scaling it.
                        // object-fit: cover keeps the photo proportional throughout.
                        clone.style.transition = 'left 1050ms cubic-bezier(.22,.8,.2,1), top 1050ms cubic-bezier(.22,.8,.2,1), width 1050ms cubic-bezier(.22,.8,.2,1), height 1050ms cubic-bezier(.22,.8,.2,1), border-radius 1050ms cubic-bezier(.22,.8,.2,1), opacity 180ms ease 870ms';
                        clone.style.left = '0px';
                        clone.style.top = '0px';
                        clone.style.width = heroRect.width + 'px';
                        clone.style.height = heroRect.height + 'px';
                        clone.style.borderRadius = '0px';
                        cards.forEach(function (card) {
                            card.style.transition = '';
                            card.style.transform = 'translate(0, 0) scale(1)';
                        });
                    });
                });

                window.setTimeout(function () {
                    cards.forEach(function (card) { card.style.transform = ''; });
                    clone.style.opacity = '0';
                    window.setTimeout(function () { clone.remove(); }, 220);
                    isTransitioning = false;
                    scheduleAutoplay();
                }, 1090);
            }

            function scheduleAutoplay() {
                window.clearInterval(autoplayId);
                autoplayId = window.setInterval(function () {
                    transitionTo(1);
                }, 2200);
            }

            cards.forEach(function (card) {
                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        transitionTo(cards.indexOf(card));
                    }
                });
                card.addEventListener('click', function () { transitionTo(cards.indexOf(card)); });
            });

            hero.addEventListener('mouseenter', function () { window.clearInterval(autoplayId); });
            hero.addEventListener('mouseleave', scheduleAutoplay);
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) window.clearInterval(autoplayId);
                else scheduleAutoplay();
            });

            renderActive();
            scheduleAutoplay();
            console.log('Autoplay scheduled');
        })();

        // Mobile Menu Toggle
        const menuBtn = document.getElementById('menu-btn');
        const mobileNav = document.getElementById('mobileNav');
        const mobileNavOverlay = document.getElementById('mobileNavOverlay');
        const mobileNavClose = document.getElementById('mobileNavClose');

        if (menuBtn && mobileNav && mobileNavOverlay && mobileNavClose) {
            menuBtn.addEventListener('click', function() {
                mobileNav.classList.add('active');
                mobileNavOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            mobileNavClose.addEventListener('click', function() {
                mobileNav.classList.remove('active');
                mobileNavOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });

            mobileNavOverlay.addEventListener('click', function() {
                mobileNav.classList.remove('active');
                mobileNavOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });

            const mobileNavLinks = mobileNav.querySelectorAll('.bromo-mobile-nav-links a');
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileNav.classList.remove('active');
                    mobileNavOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            });
        }

        // ===== HOLIDAY PACKAGES: Smooth scroll nav + scroll spy =====
        function initHolidayPackages() {
            var packageNav = document.querySelector('.holiday-package-nav');
            if (!packageNav || packageNav.dataset.initialized === 'true') return;
            packageNav.dataset.initialized = 'true';

            document.querySelectorAll('.holiday-package-nav-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetId = this.getAttribute('href');
                    var targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        var header = document.getElementById('bromoHeader');
                        var headerHeight = header ? header.offsetHeight : 90;
                        var elementPosition = targetElement.getBoundingClientRect().top;
                        var offsetPosition = elementPosition + window.pageYOffset - headerHeight - 30;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Update active nav link on scroll
            window.addEventListener('scroll', function() {
                var cards = document.querySelectorAll('.holiday-package-card');
                var navLinks = document.querySelectorAll('.holiday-package-nav-link');
                var header = document.getElementById('bromoHeader');
                var headerHeight = header ? header.offsetHeight : 90;
                var current = '';
                
                cards.forEach(function(card) {
                    var cardTop = card.getBoundingClientRect().top + window.pageYOffset;
                    if (window.pageYOffset >= cardTop - headerHeight - 50) {
                        current = card.getAttribute('id');
                    }
                });
                
                navLinks.forEach(function(link) {
                    link.classList.remove('is-active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('is-active');
                    }
                });
            });
        }

        // ===== WHY CHOOSE US: Video auto-play/pause on scroll =====
        function initWhyChooseVideo() {
            var whyVideo = document.getElementById('whyChooseVideo');
            if (!whyVideo || whyVideo.dataset.initialized === 'true') return;
            whyVideo.dataset.initialized = 'true';

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        whyVideo.play().catch(function(e) { console.log('Video play error:', e); });
                    } else {
                        whyVideo.pause();
                    }
                });
            }, { threshold: 0.3 });
            observer.observe(whyVideo);
        }

        // ===== LOADER — PROGRESS BAR + HIDE =====
        var loaderBarInterval = null;

        function startLoaderBar() {
            var bar = document.querySelector('#vms-video-loader .vms-loader-bar-fill');
            if (!bar) return;
            var progress = 0;
            var direction = 1;
            loaderBarInterval = setInterval(function() {
                progress += direction * 2.5;
                if (progress >= 82) {
                    progress = 82;
                    direction = -1;
                } else if (progress <= 15) {
                    progress = 15;
                    direction = 1;
                }
                bar.style.width = progress + '%';
            }, 60);
        }

        function hideLoader() {
            var loader = document.getElementById('vms-video-loader');
            var bar = document.querySelector('#vms-video-loader .vms-loader-bar-fill');
            if (!loader) return;

            // Stop the interval progress
            if (loaderBarInterval) {
                clearInterval(loaderBarInterval);
                loaderBarInterval = null;
            }

            // Complete the bar to 100% (CSS transition makes it smooth)
            if (bar) {
                bar.style.width = '100%';
            }

            // Wait for bar fill, then fade out
            setTimeout(function() {
                loader.classList.add('vms-loader-hidden');
                setTimeout(function() {
                    loader.style.display = 'none';
                }, 350);
            }, 350);
        }

        // Start the progress bar animation
        startLoaderBar();

        // Record page load start so we can enforce minimum display time
        var loaderStartTime = Date.now();
        var minDisplayTime = 4000; // 4 seconds — enough for GIF to play through

        function hideLoaderWhenReady() {
            var elapsed = Date.now() - loaderStartTime;
            if (elapsed >= minDisplayTime) {
                hideLoader();
            } else {
                // Wait until minimum time is reached
                setTimeout(hideLoader, minDisplayTime - elapsed);
            }
        }

        // Try to hide on window load, but enforce minimum display time
        if (document.readyState === 'complete') {
            hideLoaderWhenReady();
        } else {
            window.addEventListener('load', hideLoaderWhenReady);
        }

        // Max safety net: always hide after 8 seconds no matter what
        setTimeout(function() {
            var loader = document.getElementById('vms-video-loader');
            if (loader && loader.style.display !== 'none') {
                hideLoader();
            }
        }, 8000);

        // Auto-detect GIF background color and apply to loader
        (function() {
            var loaderImg = document.querySelector('#vms-video-loader img');
            var loaderEl = document.getElementById('vms-video-loader');
            if (loaderImg && loaderEl) {
                function detectGifBg(img) {
                    try {
                        var canvas = document.createElement('canvas');
                        var ctx = canvas.getContext('2d');
                        canvas.width = 1;
                        canvas.height = 1;
                        ctx.drawImage(img, 0, 0, 1, 1);
                        var pixel = ctx.getImageData(0, 0, 1, 1).data;
                        if (pixel[3] > 10) {
                            var color = 'rgb(' + pixel[0] + ',' + pixel[1] + ',' + pixel[2] + ')';
                            loaderEl.style.background = color;
                        }
                    } catch(e) { /* CORS or load error */ }
                }
                if (loaderImg.complete && loaderImg.naturalWidth > 0) {
                    detectGifBg(loaderImg);
                } else {
                    loaderImg.addEventListener('load', function() { detectGifBg(this); });
                }
            }
        })();

        // Initialize on DOMContentLoaded and Turbo load events
        document.addEventListener('DOMContentLoaded', function() {
            initHolidayPackages();
            initWhyChooseVideo();
        });
        document.addEventListener('turbo:load', function() {
            initHolidayPackages();
            initWhyChooseVideo();
        });
    </script>
</body>
</html>