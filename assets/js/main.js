document.addEventListener('DOMContentLoaded', function() {

    // 1. Navbar scroll effect (organic glass effect on scroll)
    var mainNav = document.querySelector('.main-nav');
    var navbar = document.querySelector('.navbar');
    var lastScrollY = 0;
    var ticking = false;

    function updateNavbar() {
        var scrollY = window.scrollY;

        // Main nav (new style)
        if (mainNav) {
            if (scrollY > 60) {
                mainNav.classList.add('scrolled');
            } else {
                mainNav.classList.remove('scrolled');
            }
        }

        // Legacy navbar
        if (navbar) {
            if (scrollY > 60) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }

        ticking = false;
    }

    window.addEventListener('scroll', function() {
        lastScrollY = window.scrollY;
        if (!ticking) {
            window.requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    });

    // 2. Fade-up animations on scroll (Intersection Observer)
    var fadeObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        rootMargin: '0px 0px -80px 0px',
        threshold: 0.1
    });

    document.querySelectorAll('.fade-up').forEach(function(el) {
        fadeObserver.observe(el);
    });

    // 3. Staggered animation for grid items
    var gridObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var items = entry.target.querySelectorAll('.recent-card, .seo-col, .seo-chiffre');
                items.forEach(function(item, index) {
                    item.style.transitionDelay = (index * 100) + 'ms';
                    item.classList.add('visible');
                });
                gridObserver.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '0px 0px -50px 0px',
        threshold: 0.1
    });

    document.querySelectorAll('.recents-grid, .seo-block-1-cols, .seo-chiffres').forEach(function(grid) {
        gridObserver.observe(grid);
    });

    // 4. Smooth scroll for table of contents
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('toc-link')) {
            e.preventDefault();
            var targetId = e.target.getAttribute('href');
            var target = document.querySelector(targetId);
            if (target) {
                var offsetTop = target.getBoundingClientRect().top + window.pageYOffset - 120;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        }
    });

    // 5. Active state on TOC links
    var tocLinks = document.querySelectorAll('.toc-link');
    var sections = [];

    tocLinks.forEach(function(link) {
        var targetId = link.getAttribute('href');
        var target = document.querySelector(targetId);
        if (target) {
            sections.push({
                link: link,
                target: target
            });
        }
    });

    if (sections.length) {
        var tocObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    tocLinks.forEach(function(l) { l.classList.remove('active'); });
                    var activeSection = sections.find(function(s) {
                        return s.target === entry.target;
                    });
                    if (activeSection) {
                        activeSection.link.classList.add('active');
                    }
                }
            });
        }, {
            rootMargin: '-20% 0px -70% 0px'
        });

        sections.forEach(function(section) {
            tocObserver.observe(section.target);
        });
    }

    // 6. Image placeholder on error
    document.querySelectorAll('img').forEach(function(img) {
        img.addEventListener('error', function() {
            var placeholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect fill='%232C5F2E' width='400' height='300'/%3E%3Ctext fill='%23fff' font-family='Lora, serif' font-size='18' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3EImage%3C/text%3E%3C/svg%3E";
            this.src = placeholder;
        });
    });

    // 7. Parallax effect on hero image (subtle)
    var heroSection = document.querySelector('.hero-section');
    var heroImg = heroSection ? heroSection.querySelector('img') : null;

    if (heroImg && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    var scrollY = window.scrollY;
                    var heroHeight = heroSection.offsetHeight;
                    if (scrollY < heroHeight) {
                        var translateY = scrollY * 0.3;
                        heroImg.style.transform = 'translateY(' + translateY + 'px) scale(1.05)';
                    }
                });
            }
        });
    }

    // 8. Card hover effect enhancement
    document.querySelectorAll('.recent-card, .article-card').forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.willChange = 'transform, box-shadow';
        });
        card.addEventListener('mouseleave', function() {
            this.style.willChange = 'auto';
        });
    });

    // 9. Button ripple effect
    document.querySelectorAll('.hero-btn, .nav-link-cta').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var rect = this.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;

            var ripple = document.createElement('span');
            ripple.style.cssText = 'position:absolute;border-radius:50%;background:rgba(255,255,255,0.3);transform:scale(0);animation:ripple 0.6s linear;pointer-events:none;';
            ripple.style.width = ripple.style.height = Math.max(rect.width, rect.height) + 'px';
            ripple.style.left = x - (Math.max(rect.width, rect.height) / 2) + 'px';
            ripple.style.top = y - (Math.max(rect.width, rect.height) / 2) + 'px';

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(function() {
                ripple.remove();
            }, 600);
        });
    });

    // Add ripple animation keyframes
    if (!document.querySelector('#ripple-style')) {
        var style = document.createElement('style');
        style.id = 'ripple-style';
        style.textContent = '@keyframes ripple { to { transform: scale(4); opacity: 0; } }';
        document.head.appendChild(style);
    }

});
