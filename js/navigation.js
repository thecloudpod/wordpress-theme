/**
 * The Cloud Pod Navigation
 * Handles mobile menu toggle
 */

(function() {
    'use strict';

    const menuToggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.main-navigation');

    if (!menuToggle || !navigation) return;

    // Toggle menu on button click
    menuToggle.addEventListener('click', function() {
        const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
        
        menuToggle.setAttribute('aria-expanded', !expanded);
        navigation.classList.toggle('toggled');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navigation.contains(e.target) && navigation.classList.contains('toggled')) {
            menuToggle.setAttribute('aria-expanded', 'false');
            navigation.classList.remove('toggled');
        }
    });

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navigation.classList.contains('toggled')) {
            menuToggle.setAttribute('aria-expanded', 'false');
            navigation.classList.remove('toggled');
            menuToggle.focus();
        }
    });

})();
