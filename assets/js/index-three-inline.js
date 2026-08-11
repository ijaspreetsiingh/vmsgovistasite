/* ===== index-three.php inline scripts (externalized) ===== */
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

/* ===== Turbo load handler ===== */
document.addEventListener('turbo:load',function(){
    document.body.classList.add('loaded');
    if(typeof WOW!=='undefined'){new WOW().init();}
});

/* ===== Hero carousel + loader ===== */
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

                // Mobile: right image cards are hidden (display:none) so a clone
                // animation would fly in from the top-left corner. Use a clean
                // crossfade between slides instead (CSS handles the fade).
                if (window.innerWidth <= 991 || getComputedStyle(cardRow).display === 'none') {
                    var mSelected = cards[index];
                    var mImage = mSelected.querySelector('img');
                    var mSlide = slides[Number(mSelected.dataset.slideIndex)];
                    cards = cards.slice(index).concat(cards.slice(0, index));
                    cards.forEach(function (card) { cardRow.appendChild(card); });
                    renderActive();
                    mSlide.querySelector('.bromo-hero-slide-bg').style.backgroundImage = 'url("' + (mImage.currentSrc || mImage.src) + '")';
                    isTransitioning = false;
                    scheduleAutoplay();
                    return;
                }

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
                }, 3000);
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

            // ── Wait for the corporate loader to disappear before starting ──
            // The loader takes ~1400ms + 500ms fade, while autoplay would have
            // already rotated to slide 2 by then — making Kerala look like the
            // first image. Boot the carousel (fresh from slide 0 = Dubai) only
            // once the preloader element is actually removed from the DOM.
            function bootCarousel() {
                renderActive();
                scheduleAutoplay();
                console.log('Autoplay scheduled (after loader)');
            }
            var preloader = document.getElementById('vmsPreloader');
            if (preloader && !preloader.classList.contains('hidden')) {
                var bootTimer = window.setInterval(function () {
                    if (!document.getElementById('vmsPreloader')) {
                        window.clearInterval(bootTimer);
                        bootCarousel();
                    }
                }, 60);
                // Safety fallback: if the loader somehow never gets removed
                // (JS error etc.), force-boot the carousel after 5s.
                window.setTimeout(function () {
                    if (document.getElementById('vmsPreloader')) {
                        window.clearInterval(bootTimer);
                        bootCarousel();
                    }
                }, 5000);
            } else {
                bootCarousel();
            }
        })();

        // Mobile Menu Toggle
        const menuBtn = document.getElementById('menu-btn');
        const mobileNav = document.getElementById('mobileNav');
        const mobileNavOverlay = document.getElementById('mobileNavOverlay');
        const mobileNavClose = document.getElementById('mobileNavClose');

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

            menuBtn.addEventListener('click', function() {
                mobileNav.classList.contains('active') ? closeMobileNav() : openMobileNav();
            });

            mobileNavClose.addEventListener('click', closeMobileNav);
            mobileNavOverlay.addEventListener('click', closeMobileNav);

            const mobileNavLinks = mobileNav.querySelectorAll('.bromo-mobile-nav-links a');
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', closeMobileNav);
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
