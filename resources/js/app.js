import './bootstrap';
import Alpine from 'alpinejs';
import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/skins/ui/oxide/skin.css';

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', function () {
    // TinyMCE initialization
    tinymce.init({
        selector: 'textarea:not(.no-rich-text)',
        plugins: 'link image table lists',
        toolbar: 'undo redo | bold italic | bullist numlist | link image table',
        height: 400,
        menubar: false,
        base_url: '/tinymce',
        suffix: '.min',
        setup: (editor) => {
            editor.on('init', () => {
                console.log('TinyMCE initialized for:', editor.id);
            });
            editor.on('error', (err) => {
                console.error('TinyMCE error:', err);
            });
        },
    });

    // User menu toggle
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');
    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', function () {
            userMenu.classList.toggle('hidden');
        });
        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
    }

    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function () {
            console.log('Mobile menu button clicked');
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Settings dropdown toggle in mobile menu
    const settingsToggles = document.querySelectorAll('.settings-toggle');
    settingsToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const menu = this.nextElementSibling;
            const arrow = this.querySelector('.settings-arrow');
            
            menu.classList.toggle('hidden');
            arrow.classList.toggle('transform');
            arrow.classList.toggle('rotate-180');
        });
    });
});