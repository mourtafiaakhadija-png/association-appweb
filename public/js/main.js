// public/js/main.js

document.addEventListener('DOMContentLoaded', function () {
    // Navbar transparent -> pleine au scroll
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
});
