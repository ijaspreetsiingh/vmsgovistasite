<?php // Shared public footer ?>
    <!-- ===== FOOTER ===== -->
    <footer class="bromo-footer" id="bromo-footer">
        <!-- Subtle glow orbs -->
        <div class="footer-glow footer-glow-1" aria-hidden="true"></div>
        <div class="footer-glow footer-glow-2" aria-hidden="true"></div>

        <div class="bromo-footer-wave">
            <svg viewBox="0 0 1440 100" preserveAspectRatio="none" class="footer-wave-svg">
                <path d="M0,50 C240,100 480,0 720,50 C960,100 1200,0 1440,50 L1440,100 L0,100 Z" fill="currentColor" opacity="0.04"/>
            </svg>
        </div>
        <div class="bromo-footer-inner">
            <!-- Brand Column -->
            <div class="bromo-footer-col bromo-footer-brand footer-reveal" style="--reveal-delay: 0.1s;">
                <div class="footer-brand-top">
                    <div class="footer-brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="22" height="22">
                            <path d="M4 18L8.5 10L12 14L15.5 8L20 18H4Z" stroke-linejoin="round"/>
                            <circle cx="17" cy="7" r="2"/>
                        </svg>
                    </div>
                    <span class="footer-brand-name">VMS Go Vista</span>
                </div>
                <p class="footer-desc">We provide curated travel experiences with expert local guides, safe transportation, and unforgettable moments across Indonesia and beyond.</p>
                <div class="footer-awards">
                    <span class="award-badge"><i class="fa-solid fa-medal"></i> Top Rated 2024</span>
                    <span class="award-badge"><i class="fa-solid fa-shield-halved"></i> Licensed</span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bromo-footer-col footer-reveal" style="--reveal-delay: 0.2s;">
                <h6><i class="fa-solid fa-link"></i> Quick Links</h6>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/index-three.php"><i class="fa-regular fa-chevron-right"></i> Home</a></li>
                    <li><a href="<?= SITE_URL ?>/package.php"><i class="fa-regular fa-chevron-right"></i> Packages</a></li>
                    <li><a href="<?= SITE_URL ?>/about.html"><i class="fa-regular fa-chevron-right"></i> About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.html"><i class="fa-regular fa-chevron-right"></i> Contact</a></li>
                    <li><a href="<?= SITE_URL ?>/sign-in.php"><i class="fa-regular fa-chevron-right"></i> Sign In</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="bromo-footer-col footer-reveal" style="--reveal-delay: 0.3s;">
                <h6><i class="fa-solid fa-phone"></i> Contact</h6>
                <div class="footer-contact-list">
                    <div class="contact-item">
                        <span class="contact-icon"><i class="fa-solid fa-envelope"></i></span>
                        <div>
                            <span class="contact-label">Email</span>
                            <a href="mailto:hello@vmsgovista.com" class="contact-value">hello@vmsgovista.com</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon"><i class="fa-solid fa-phone-volume"></i></span>
                        <div>
                            <span class="contact-label">Phone</span>
                            <a href="tel:+628XXXXXXX" class="contact-value">+62 8XX XXXX XXX</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <div>
                            <span class="contact-label">Address</span>
                            <span class="contact-value">East Java, Indonesia</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon"><i class="fa-regular fa-clock"></i></span>
                        <div>
                            <span class="contact-label">Working Hours</span>
                            <span class="contact-value">Mon-Sat, 9AM-6PM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscribe -->
            <div class="bromo-footer-col bromo-footer-subscribe footer-reveal" style="--reveal-delay: 0.4s;">
                <h6><i class="fa-solid fa-newspaper"></i> Newsletter</h6>
                <p class="subscribe-text">Subscribe to receive exclusive tour deals and travel inspiration.</p>
                <form class="bromo-footer-form footer-newsletter-form" action="#" novalidate>
                    <div class="form-icon"><i class="fa-regular fa-envelope"></i></div>
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" aria-label="Subscribe"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
                <div class="newsletter-feedback" style="display:none;"></div>
                <div class="bromo-footer-socials">
                    <a href="#" aria-label="Instagram" class="social-link social-ig"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook" class="social-link social-fb"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="YouTube" class="social-link social-yt"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" aria-label="Twitter" class="social-link social-tw"><i class="fa-brands fa-x-twitter"></i></a>
                </div>
            </div>
        </div>

        <div class="bromo-footer-bottom">
            <div class="footer-bottom-inner">
                <div class="footer-bottom-left">
                    <p>&copy; <?= date('Y') ?> VMS Go Vista. All rights reserved.</p>
                </div>
                <div class="footer-bottom-center">
                    <span class="payment-label">We Accept</span>
                    <div class="payment-icons">
                        <span class="payment-icon" title="Visa"><i class="fa-brands fa-cc-visa"></i></span>
                        <span class="payment-icon" title="Mastercard"><i class="fa-brands fa-cc-mastercard"></i></span>
                        <span class="payment-icon" title="PayPal"><i class="fa-brands fa-cc-paypal"></i></span>
                        <span class="payment-icon" title="American Express"><i class="fa-brands fa-cc-amex"></i></span>
                        <span class="payment-icon" title="Discover"><i class="fa-brands fa-cc-discover"></i></span>
                    </div>
                </div>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <span class="sep">·</span>
                    <a href="#">Terms of Service</a>
                    <span class="sep">·</span>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <div id="side-bar" class="side-bar header-two header-eight">
        <button class="close-icon-menu"><i class="fa-sharp fa-thin fa-xmark"></i></button>
        <a class="logo" href="<?= SITE_URL ?>/index-three.php"><img src="<?= SITE_URL ?>/assets/images/logo/05.svg" alt=""></a>
        <div class="mobile-menu-main">
            <nav class="nav-main mainmenu-nav mt--30">
                <ul class="mainmenu metismenu" id="mobile-menu-active">
                    <li><a href="<?= SITE_URL ?>/index-three.php" class="main">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/package.php" class="main">Packages</a></li>
                    <li><a href="<?= SITE_URL ?>/about.html" class="main">About</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.html" class="main">Contact Us</a></li>
                    <li><a href="<?= SITE_URL ?>/sign-in.php" class="main">Log In</a></li>
                </ul>
            </nav>
            <div class="follow-us">
                <ul>
                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
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
    <!-- PERFORMANCE: Critical scripts loaded with defer (non-blocking) -->
    <script src="<?= SITE_URL ?>/assets/js/plugins/jquery.min.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/plugins/bootstrap.min.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/plugins/metismenu.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/plugins/swiper.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/vendor/waypoint.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/vendor/wow.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/plugins/odometer.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/plugins/magnific-popup.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/plugins/isotop.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/plugins/smoothscroll.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/vendor/jqueryui.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
    <?php if (isset($extraFooter)) echo $extraFooter; ?>
</body>
</html>
