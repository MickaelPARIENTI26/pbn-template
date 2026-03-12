document.addEventListener('DOMContentLoaded', function() {

    // 1. Navbar scroll shadow
    window.addEventListener('scroll', function() {
        var navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });

    // 2. Animations fadeUp au scroll
    var fadeObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        rootMargin: '0px 0px -50px 0px'
    });

    document.querySelectorAll('.fade-up').forEach(function(el) {
        fadeObserver.observe(el);
    });

    // 3. Smooth scroll pour le sommaire
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('toc-link')) {
            e.preventDefault();
            var targetId = e.target.getAttribute('href');
            var target = document.querySelector(targetId);
            if (target) {
                var offsetTop = target.getBoundingClientRect().top + window.pageYOffset - 100;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        }
    });

    // 4. Active state sur les liens du sommaire
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
        window.addEventListener('scroll', function() {
            var scrollPos = window.scrollY + 150;

            sections.forEach(function(section, index) {
                var sectionTop = section.target.getBoundingClientRect().top + window.pageYOffset;
                var sectionBottom = (index < sections.length - 1)
                    ? sections[index + 1].target.getBoundingClientRect().top + window.pageYOffset
                    : document.body.scrollHeight;

                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    tocLinks.forEach(function(l) { l.classList.remove('active'); });
                    section.link.classList.add('active');
                }
            });
        });
    }

    // 5. Image placeholder si erreur de chargement
    document.querySelectorAll('img').forEach(function(img) {
        img.addEventListener('error', function() {
            var placeholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect fill='%232C5F2E' width='400' height='300'/%3E%3Ctext fill='%23fff' font-family='sans-serif' font-size='18' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3EImage%3C/text%3E%3C/svg%3E";
            this.src = placeholder;
        });
    });

});
