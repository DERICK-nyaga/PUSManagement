import './bootstrap';
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const sidebar = document.querySelector('.sidebar');
    const menuToggler = document.querySelector('.navbar-toggler');

    if (menuToggler) {
        menuToggler.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Submenu toggle
    const hasSubmenu = document.querySelectorAll('.has-submenu > .nav-link');

    hasSubmenu.forEach(item => {
        item.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('show');
            }
        });
    });
});
