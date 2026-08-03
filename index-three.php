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
    
    <!-- PERFORMANCE: Preconnect & DNS-prefetch -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    
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
    <link rel="stylesheet" href="assets/css/bromo-theme.css">
    <link rel="stylesheet" href="assets/css/index-three-inline.css">
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
        /* VMS Big Text footer watermark - restore original positioning (matches index-three-inline.css) */
        .vms-big-text {
            font-family: 'Sunsive', sans-serif !important;
            font-size: clamp(120px, 18vw, 180px) !important;
            font-weight: 700 !important;
            bottom: 80px !important;
            text-align: center !important;
        }
        @media (max-width: 991px) {
            .vms-big-text { font-size: clamp(16px, 4vw, 24px) !important; bottom: 40px !important; }
        }
        @media (max-width: 575px) {
            .vms-big-text { font-size: clamp(14px, 5vw, 20px) !important; bottom: 24px !important; }
        }
    </style>
</head>
<body class="home-yacht-bg with-sidebar">



<!-- ===== BROMORISE HEADER ===== -->
<header class="bromo-header header--sticky" id="bromoHeader">
    <a href="." class="bromo-logo">
        <img src="assets/3d.png" alt="VMS Go Vista" class="vms-logo-img">
        <span class="vms-logo-name">VMS Go Vista</span>
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
    <div class="bromo-mobile-menu" id="menu-btn" aria-label="Open menu">
        <span class="bromo-burger"><i></i><i></i><i></i></span>
    </div>
</header>

<!-- ===== MOBILE NAVIGATION ===== -->
<div class="bromo-mobile-nav-overlay" id="mobileNavOverlay"></div>
<div class="bromo-mobile-nav" id="mobileNav">
    <div class="bromo-mobile-nav-top">
        <a href="." class="bromo-mobile-nav-logo">
            <img src="assets/3d logo.png" alt="VMS Go Vista">
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
        <a href="contact" class="bromo-mobile-nav-cta">
            <span>Book Now</span>
            <span class="bromo-arrow"><i class="fa-regular fa-arrow-up-right"></i></span>
        </a>
        <div class="bromo-mobile-nav-contact">
            <a href="tel:+919876543210"><i class="fa-solid fa-phone"></i> +91 98765 43210</a>
            <a href="mailto:hello@vmsgovista.com"><i class="fa-solid fa-envelope"></i> hello@vmsgovista.com</a>
        </div>
    </div>
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

<!-- Premium Loader -->
<link rel="preload" href="assets/loader-logo.png" as="image">
<script src="assets/js/loader.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var loader = new VMSUltraLoader({
        logoSrc: 'assets/loader-logo.png',
        holdTime: 1500,
        loop: false,
        onReady: function() {
            if (typeof WOW !== 'undefined') new WOW().init();
        }
    });
    loader.play();
    // Hide after animation completes (~7.5s) + 1s buffer
    setTimeout(function() { loader.hide(); }, 8500);
});
</script>

<!-- BromoRise Hero FIFO Carousel -->
<script src="assets/js/index-three-inline.js"></script>
</body>
</html>