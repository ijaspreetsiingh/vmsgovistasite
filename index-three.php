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
    <meta name="google-site-verification" content="q04Fea95irY7FaPfYtP6EPUb-i7h7W0CVsklNRdT590" />
    <meta name="description" content="VMS Go Vista Pvt Ltd — domestic &amp; international tour packages, honeymoons, family holidays, group tours, visa assistance and 24/7 travel support. Book your dream journey today.">
    <link rel="canonical" href="https://vmsgovista.com/">
    <title>VMS Go Vista – Travel &amp; Tour Booking</title>
    <meta property="og:type" content="website">
    <meta property="og:title" content="VMS Go Vista – Travel &amp; Tour Booking">
    <meta property="og:description" content="Domestic &amp; international tour packages, honeymoons, family holidays and group tours with 24/7 support.">
    <meta property="og:url" content="https://vmsgovista.com/">
    <meta property="og:site_name" content="VMS Go Vista">
    <meta property="og:image" content="https://vmsgovista.com/assets/newlogo.png">
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "TravelAgency",
      "name": "VMS Go Vista Pvt Ltd",
      "alternateName": "VMS Go Vista",
      "url": "https://vmsgovista.com",
      "logo": "https://vmsgovista.com/assets/newlogo.png",
      "description": "Domestic and international tour packages, honeymoons, family holidays, group tours, visa assistance and travel insurance.",
      "founder": {
        "@type": "Person",
        "name": "Vipin Kumar"
      },
      "owner": {
        "@type": "Person",
        "name": "Vipin Kumar"
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Unit no. 04, Ground floor, D-MALL, Netaji Subhash Place, Shakurpur",
        "addressLocality": "New Delhi",
        "addressRegion": "Delhi",
        "postalCode": "110034",
        "addressCountry": "IN"
      },
      "telephone": "+91 98701 82425",
      "email": "info@vmsgovista.com",
      "sameAs": [
        "https://vmsgovista.com"
      ]
    }
    </script>
    
    <!-- PERFORMANCE: Preconnect & DNS-prefetch -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    
    <!-- PERFORMANCE: Preload hero images so they are ready before the loader fades -->
    <link rel="preload" as="image" href="assets/hero/dub1.webp">
    <link rel="preload" as="image" href="assets/hero/k.webp">
    <link rel="preload" as="image" href="assets/hero/thailand.webp">
    <link rel="preload" as="image" href="assets/hero/kas.webp">
    
    <!-- CSS: EXACT MATCH to index-three.html load order -->
    <link rel="stylesheet preload" href="assets/css/plugins/swiper.min.css" as="style">
    <link rel="stylesheet preload" href="assets/fonts/custom-font.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet preload" href="assets/css/plugins/magnific-popup.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/metismenu.css" as="style">
    <link rel="stylesheet preload" href="assets/css/vendor/bootstrap.min.css" as="style">
    <link rel="stylesheet preload" href="assets/css/vendor/animate.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/odometer.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/fontawesome.min.css" as="style">
    <link rel="stylesheet preload" href="assets/css/plugins/nice-select.css" as="style">
    <link rel="stylesheet preload" href="assets/css/style.css" as="style">
    <link rel="stylesheet" href="assets/css/bromo-theme.css?v=3.0">
    <link rel="stylesheet" href="assets/css/index-three-inline.css?v=3.0">
    <link rel="stylesheet" href="assets/css/loader.css">
    
    <!-- RESTORE index-three.html styling: Override index-three-inline.css font-heading changes -->
    <style>
        /* Restore Sunsive font for all headings (index-three-inline.css changes it to Playfair Display) */
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6,
        .section-title, .section-title-area h2,
        .bromo-center-text h1, .gallery-content-wrapper .title,
        .vms-footer-col h5, .package-title, .counter-title,
        .holiday-package-title,
        .holiday-packages-sidebar .heading-title {
            font-family: var(--font-primary) !important;
        }
        /* Section title sizes - match index-three.html */
        .section-title-area h2,
        .section-title {
            font-size: var(--h2) !important;
            font-weight: var(--s-semi-bold) !important;
            line-height: 1.1 !important;
        }
        /* Package card titles */
        .package-wrapper .content .title {
            font-size: 18px !important;
            font-weight: 600 !important;
        }
        /* Responsive section titles */
        @media (max-width: 991px) {
            .section-title-area h2, .section-title {
                font-size: 36px !important;
            }
            .package-wrapper .content .title {
                font-size: 17px !important;
            }
        }
        @media (max-width: 767px) {
            .section-title-area h2, .section-title {
                font-size: 32px !important;
            }
            .package-wrapper .content .title {
                font-size: 16px !important;
            }
        }
        @media (max-width: 575px) {
            .section-title-area h2, .section-title {
                font-size: 26px !important;
            }
            .package-wrapper .content .title {
                font-size: 15px !important;
            }
        }
        /* Match package.php header logo size (80px) */
        .bromo-header .vms-logo-img {
            height: 80px;
            width: auto;
        }
        /* Header logo text — hide when zoomed 110%+ (1920px@110%=1745px) */
        .bromo-logo span {
            white-space: nowrap;
            font-size: 16px;
            transition: opacity 0.3s ease;
        }
        @media (min-width: 768px) and (max-width: 1400px) {
            .bromo-logo span {
                display: none !important;
            }
        }
        /* Always show on phone — hide Pvt Ltd, reduce font */
        @media (max-width: 767px) {
            .bromo-logo span { display: block !important; font-size: 12px !important; white-space: nowrap !important; }
            .header-pvt { display: none !important; }
            .bromo-header .bromo-logo { gap: 6px; }
        }
        /* Fix: video section overflow clips watermark — allow it */
        .vms-video-section { overflow: visible !important; }
        /* VMS Big Text footer watermark */
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
        /* Newsletter Section Responsive Styles */
        .bromo-newsletter-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .bromo-newsletter-form input {
            flex: 1;
            min-width: 200px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 50px;
            font-size: 14px;
        }
        .bromo-newsletter-form button {
            padding: 12px 20px;
            border: none;
            border-radius: 50px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        @media (max-width: 767px) {
            .bromo-newsletter-inner {
                padding: 30px 20px;
            }
            .bromo-newsletter-title {
                font-size: 24px !important;
                line-height: 1.3 !important;
            }
            .bromo-newsletter-desc {
                font-size: 14px !important;
                margin-bottom: 20px !important;
            }
            .bromo-newsletter-form {
                flex-direction: column;
                gap: 10px;
            }
            .bromo-newsletter-form input {
                width: 100% !important;
                min-width: auto;
                padding: 14px 15px;
                font-size: 16px;
            }
            .bromo-newsletter-form button {
                width: 100% !important;
                padding: 14px 20px;
                font-size: 16px;
            }
        }
        @media (max-width: 575px) {
            .bromo-newsletter-inner {
                padding: 25px 15px;
            }
            .bromo-newsletter-title {
                font-size: 20px !important;
            }
            .bromo-newsletter-desc {
                font-size: 13px !important;
            }
        }
    </style>
</head>
<body class="home-yacht-bg with-sidebar" data-turbo-cache="false">

<!-- ===== CORPORATE LOADER (static — renders instantly) ===== -->
<div class="vms-preloader" id="vmsPreloader">
    <div class="vms-preloader-logo">
        <img src="assets/newlogo.png" alt="VMS Go Vista">
    </div>
    <div class="vms-preloader-brand" style="font-family: Sunsive;">VMS Go Vista Pvt Ltd</span></span>
    </a> </div>
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
        <a href="." class="active">Home</a>
        <a href="package">Packages</a>
        <a href="service">Services</a>
        <a href="about">About Us</a>
        <a href="contact">Contact</a>
    </nav>
    <div class="bromo-book-btn">
        <a href="booking" class="active">
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
        <a href="." class="active"><i class="fa-solid fa-house"></i><span>Home</span></a>
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

<!-- ===== BROMORISE HERO ===== -->
<div class="bromo-hero">
    <div class="bromo-hero-swiper">
        <div class="swiper bromo-bg-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/hero/dub1.webp');"></div></div>
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/hero/k.webp');"></div></div>
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/hero/thailand.webp');"></div></div>
                <div class="swiper-slide"><div class="bromo-hero-slide-bg" style="background-image:url('assets/hero/kas.webp');"></div></div>
            </div>
        </div>
    </div>
    <div class="bromo-hero-content">
        <div class="bromo-center-text">
            <span class="bromo-eyebrow">Travel Beyond Expectations</span>
            <h1>
                <span class="bromo-line">Unforgettable Travel Journeys</span>
                <span class="bromo-line">Begin With VMS Go Vista</span>
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
            <a href="package" class="bromo-hero-btn">
                Explore Packages
                <span class="bromo-arrow"><i class="fa-regular fa-arrow-up-right"></i></span>
            </a>
        </div>
        <div class="bromo-right-cards">
            <div class="bromo-img-card is-active" data-slide-index="0">
                <img src="assets/hero/dub1.webp" alt="Dubai Luxury Adventure and Skyline" fetchpriority="high">
                <div class="bromo-card-label"><strong>Dubai – Luxury, Adventure &amp; Skyline</strong><span>Experience world-class luxury, iconic skylines, thrilling adventures, and unforgettable desert experiences.</span></div>
            </div>
            <div class="bromo-img-card" data-slide-index="1">
                <img src="assets/hero/k.webp" alt="Kerala God's Own Country">
                <div class="bromo-card-label"><strong>Kerala – God's Own Country</strong><span>Experience serene backwaters, lush greenery, pristine beaches, and unforgettable cultural escapes.</span></div>
            </div>
            <div class="bromo-img-card" data-slide-index="2">
                <img src="assets/hero/thailand.webp" alt="Thailand Beaches Culture and Adventure">
                <div class="bromo-card-label"><strong>Thailand – Beaches, Culture &amp; Adventure</strong><span>Discover stunning beaches, vibrant culture, delicious cuisine, and thrilling adventures in a tropical paradise.</span></div>
            </div>
            <div class="bromo-img-card" data-slide-index="3">
                <img src="assets/hero/kas.webp" alt="Kashmir Paradise in the Himalayas">
                <div class="bromo-card-label"><strong>Kashmir – Paradise in the Himalayas</strong><span>Discover breathtaking valleys, snow-capped mountains, serene lakes, and timeless natural beauty.</span></div>
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
                    About Us
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
                                <img class="hover-image" src="<?= e($imgUrl) ?>" alt="<?= e($pkg['title']) ?>" loading="lazy">
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
                                <img class="hover-image" src="<?= e($imgUrl) ?>" alt="<?= e($pkg['title']) ?>" loading="lazy">
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
                                        <img src="assets/imh2.webp" alt="Corporate Travel" loading="lazy">
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
                                        src="assets/video4.mp4"
                                        type="video/mp4">
                                </video>
                                <div class="video-gradient-overlay"></div>
                                <button class="video-play-btn" id="videoPlayBtn" aria-label="Pause video">
                                    <i class="fa-solid fa-pause"></i>
                                </button>
                            </div>
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
                                        <div class="button-area" style="text-align: left;">
                                            <div class="bromo-book-btn" style="justify-self: start;">
                                                <a href="contact" class="why-choose-btn" style="background: rgba(0,58,89,0.06); border-color: rgba(0,58,89,0.12); color: #003A59;">
                                                    Talk to Our Team
                                                    <span class="bromo-arrow" style="background: #003A59; color: #fff;"><i class="fa-regular fa-arrow-up-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <style>
                                        .why-choose-btn:hover {
                                            background: #003A59 !important;
                                            color: #fff !important;
                                        }
                                        .why-choose-btn:hover .bromo-arrow {
                                            background: #fff !important;
                                            color: #003A59 !important;
                                        }
                                    </style>
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
                <div class="image-area"><img src="assets/indexpageimg/img1.webp" width="630" alt="Rainbow Bridge Tokyo"></div>
                <div class="content-area"><p style="color: white;"><i class="fa-light fa-location-dot"></i> Tokyo, Japan</p><h4 class="title" style="color: white;">Rainbow Bridge</h4></div>
            </div>
            <div class="project-block-four">
                <div class="image-area"><img src="assets/indexpageimg/img3.webp" width="630" alt="Bangkok Temple"></div>
                <div class="content-area"><p style="color: white;"><i class="fa-light fa-location-dot"></i> Bangkok, Thailand</p><h4 class="title" style="color: white;">Wat Arun Temple</h4></div>
            </div>
            <div class="project-block-four">
                <div class="image-area"><img src="assets/indexpageimg/imglast.webp" width="630" alt="Udaipur Lake Palace"></div>
                <div class="content-area"><p style="color: white;"><i class="fa-light fa-location-dot"></i> Udaipur, India</p><h4 class="title" style="color: white;">Lake Palace</h4></div>
            </div>
            <div class="project-block-four">
                <div class="image-area"><img src="assets/indexpageimg/new (1).webp" width="630" alt="Bangkok City"></div>
                <div class="content-area"><p style="color: white;"><i class="fa-light fa-location-dot"></i> Bangkok, Thailand</p><h4 class="title" style="color: white;">Grand Palace</h4></div>
            </div>
            <div class="project-block-four active">
                <div class="image-area"><img src="assets/indexpageimg/new (2).webp" width="630" alt="Kerala Backwaters"></div>
                <div class="content-area"><p style="color: white;"><i class="fa-light fa-location-dot"></i> Kerala, India</p><h4 class="title" style="color: white;">Kerala Backwaters</h4></div>
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
                <a href="assets/indexpageimg/indexgallery/img (1).webp" class="gallery-image magnific-zoom">
                    <img src="assets/indexpageimg/indexgallery/img (1).webp" alt="Travel Gallery 1">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Travel Moment 1</span>
                            <span class="bento-location">Beautiful Destination</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-2">
                <a href="assets/indexpageimg/indexgallery/img (2).webp" class="gallery-image magnific-zoom">
                    <img src="assets/indexpageimg/indexgallery/img (2).webp" alt="Travel Gallery 2">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Travel Moment 2</span>
                            <span class="bento-location">Beautiful Destination</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-3">
                <a href="assets/indexpageimg/indexgallery/img (3).webp" class="gallery-image magnific-zoom">
                    <img src="assets/indexpageimg/indexgallery/img (3).webp" alt="Travel Gallery 3">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Travel Moment 3</span>
                            <span class="bento-location">Beautiful Destination</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-4">
                <a href="assets/indexpageimg/indexgallery/img (4).webp" class="gallery-image magnific-zoom">
                    <img src="assets/indexpageimg/indexgallery/img (4).webp" alt="Travel Gallery 4">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Travel Moment 4</span>
                            <span class="bento-location">Beautiful Destination</span>
                        </div>
                        <span class="bento-zoom"><i class="fa-regular fa-plus"></i></span>
                    </div>
                </a>
            </div>
            <div class="bento-item bento-item-5">
                <a href="assets/indexpageimg/indexgallery/img (5).webp" class="gallery-image magnific-zoom">
                    <img src="assets/indexpageimg/indexgallery/img (5).webp" alt="Travel Gallery 5">
                    <div class="bento-overlay"></div>
                    <div class="bento-caption">
                        <div class="bento-label">
                            <span class="bento-name">Travel Moment 5</span>
                            <span class="bento-location">Beautiful Destination</span>
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
            <h2 class="section-title wow fadeInUp" data-wow-delay="0.2s">What Our Guests Say</h2>
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
                        <img src="assets/imgr.webp" alt="Happy travelers enjoying their journey" class="testi-fixed-image">
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
                Vipin Kumar (Owner)
            </span>
        </div>
    </div>

    <!-- Video Section with travel footage -->
    <div class="vms-video-section">
        <video src="assets/videofotte.mp4" class="vms-video-bg" autoplay muted loop playsinline></video>
        <div class="vms-video-gradient"></div>
        <div class="vms-big-text" style="font-family:Sunsive">VMS Go Vista PVT LTD</div>
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

<!-- Corporate Loader (inline — no external file needed) -->
<link rel="preload" href="assets/newlogo.png" as="image">
<script>
(function(){
    var duration = 1400;
    var overlay = document.getElementById('vmsPreloader');
    var fill = document.getElementById('vmsLoaderFill');
    function removeLoader(){
        if(overlay){overlay.classList.add('hidden');setTimeout(function(){if(overlay.parentNode)overlay.remove();},500);}
    }
    function initWOW(){if(typeof WOW!=='undefined')new WOW().init();}
    if(!overlay||!fill){removeLoader();initWOW();return;}
    var start=performance.now();
    function tick(now){
        var elapsed=now-start;
        var progress=Math.min(elapsed/duration,1);
        var eased=1-Math.pow(1-progress,3);
        fill.style.width=(eased*100)+'%';
        if(progress<1){requestAnimationFrame(tick);}
        else{setTimeout(function(){removeLoader();initWOW();},250);}
    }
    requestAnimationFrame(tick);
})();
</script>

<!-- BromoRise Hero FIFO Carousel -->
<script src="assets/js/index-three-inline.js"></script>
</body>
</html>