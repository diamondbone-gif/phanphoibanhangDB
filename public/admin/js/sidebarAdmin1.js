const sidebar = document.getElementById('sidebar');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const sidebarBackdrop = document.getElementById('sidebarBackdrop');

const menuLinks = document.querySelectorAll('.menu-link');
const submenuLinks = document.querySelectorAll('.submenu-link');
const submenuToggles = document.querySelectorAll('.submenu-toggle');

const userDropdown = document.getElementById('userDropdown');
const userButton = document.getElementById('userButton');

/* Mở menu mobile */
if (mobileMenuBtn && sidebar && sidebarBackdrop) {
    mobileMenuBtn.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        sidebarBackdrop.classList.toggle('show');
    });
}

/* Bấm nền mờ thì đóng menu mobile */
if (sidebarBackdrop && sidebar) {
    sidebarBackdrop.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarBackdrop.classList.remove('show');
    });
}

/* Mở / đóng menu con */
submenuToggles.forEach(function(toggle) {
    toggle.addEventListener('click', function() {
        const parentItem = this.closest('.menu-item');

        if (parentItem) {
            parentItem.classList.toggle('open');
        }
    });
});

/* Active menu chính */
menuLinks.forEach(function(link) {
    link.addEventListener('click', function() {
        menuLinks.forEach(function(item) {
            item.classList.remove('active');
        });

        submenuLinks.forEach(function(item) {
            item.classList.remove('active');
        });

        this.classList.add('active');

        if (window.innerWidth <= 991.98 && sidebar && sidebarBackdrop) {
            sidebar.classList.remove('show');
            sidebarBackdrop.classList.remove('show');
        }
    });
});

/* Active menu con */
submenuLinks.forEach(function(link) {
    link.addEventListener('click', function() {
        menuLinks.forEach(function(item) {
            item.classList.remove('active');
        });

        submenuLinks.forEach(function(item) {
            item.classList.remove('active');
        });

        this.classList.add('active');

        const parentItem = this.closest('.menu-item');

        if (parentItem) {
            parentItem.classList.add('open');
        }

        if (window.innerWidth <= 991.98 && sidebar && sidebarBackdrop) {
            sidebar.classList.remove('show');
            sidebarBackdrop.classList.remove('show');
        }
    });
});

/* Mở / đóng avatar dropdown */
if (userButton && userDropdown) {
    userButton.addEventListener('click', function(event) {
        event.stopPropagation();
        userDropdown.classList.toggle('show');
    });
}

/* Bấm ra ngoài thì đóng avatar dropdown */
document.addEventListener('click', function(event) {
    if (userDropdown && !userDropdown.contains(event.target)) {
        userDropdown.classList.remove('show');
    }
});

/* Khi resize về desktop thì reset trạng thái nền mờ mobile */
window.addEventListener('resize', function() {
    if (window.innerWidth > 991.98 && sidebar && sidebarBackdrop) {
        sidebar.classList.remove('show');
        sidebarBackdrop.classList.remove('show');
    }
});