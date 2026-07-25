document.documentElement.classList.add('js-ready');

document.addEventListener('DOMContentLoaded', function () {
    // Navbar transparente -> pleine au scroll
    const header = document.getElementById('siteHeader');
    function updateHeader() {
        if (!header) return;
        if (window.scrollY > 40) header.classList.add('scrolled');
        else header.classList.remove('scrolled');
    }
    updateHeader();
    window.addEventListener('scroll', updateHeader);

    // Menu mobile
    const toggle = document.getElementById('navToggle');
    const nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    // Galerie du détail projet : cliquer une miniature change l'image principale
    const mainImg = document.getElementById('projetMainImage');
    const thumbs = document.querySelectorAll('.projet-gallery-thumbs img');
    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (mainImg) mainImg.src = thumb.src;
        });
    });

    // Slideshow du hero (change d'image en fond)
    const heroSlider = document.getElementById('heroSlider');
    if (heroSlider) {
        const heroImages = heroSlider.querySelectorAll('img');
        let heroIndex = 0;
        if (heroImages.length > 1) {
            setInterval(function () {
                heroImages[heroIndex].classList.remove('active');
                heroIndex = (heroIndex + 1) % heroImages.length;
                heroImages[heroIndex].classList.add('active');
            }, 20000);
        }
    }

    // Animation d'entrée du texte du hero
    const heroInner = document.querySelector('.hero-inner');
    if (heroInner) {
        setTimeout(function () {
            heroInner.classList.add('revealed');
        }, 100);
    }

    // Apparition progressive des cartes projets au scroll
    const revealCards = document.querySelectorAll('.project-card');
    if (revealCards.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, index) {
                if (entry.isIntersecting) {
                    setTimeout(function () {
                        entry.target.classList.add('revealed');
                    }, index * 100);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        revealCards.forEach(function (card) {
            observer.observe(card);
        });
    } else {
        revealCards.forEach(function (card) { card.classList.add('revealed'); });
    }
});