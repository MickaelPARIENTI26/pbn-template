$(document).ready(function() {

    // 1. Navbar scroll shadow
    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
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
    $(document).on('click', '.toc-link', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        if ($(target).length) {
            $('html, body').animate({
                scrollTop: $(target).offset().top - 100
            }, 400);
        }
    });

    // 4. Active state sur les liens du sommaire
    var tocLinks = $('.toc-link');
    var sections = [];

    tocLinks.each(function() {
        var target = $($(this).attr('href'));
        if (target.length) {
            sections.push({
                link: $(this),
                target: target
            });
        }
    });

    if (sections.length) {
        $(window).on('scroll', function() {
            var scrollPos = $(window).scrollTop() + 150;

            sections.forEach(function(section, index) {
                var sectionTop = section.target.offset().top;
                var sectionBottom = (index < sections.length - 1)
                    ? sections[index + 1].target.offset().top
                    : $(document).height();

                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    tocLinks.removeClass('active');
                    section.link.addClass('active');
                }
            });
        });
    }

    // 5. Image placeholder si erreur de chargement
    $('img').on('error', function() {
        var placeholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect fill='%232C5F2E' width='400' height='300'/%3E%3Ctext fill='%23fff' font-family='sans-serif' font-size='18' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3EImage%3C/text%3E%3C/svg%3E";
        $(this).attr('src', placeholder);
    });

});
