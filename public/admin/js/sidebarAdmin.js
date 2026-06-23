document.addEventListener("DOMContentLoaded", function() {
    const body = document.body;

    const sidebar = document.getElementById("sidebar");
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");
    const sidebarBackdrop = document.getElementById("sidebarBackdrop");

    const userDropdown = document.getElementById("userDropdown");
    const userButton = document.getElementById("userButton");

    const submenuToggles = document.querySelectorAll(".submenu-toggle");

    function openSidebar() {
        body.classList.add("sidebar-open");

        if (sidebar) {
            sidebar.classList.add("active");
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.classList.add("active");
        }
    }

    function closeSidebar() {
        body.classList.remove("sidebar-open");

        if (sidebar) {
            sidebar.classList.remove("active");
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.classList.remove("active");
        }
    }

    function toggleSidebar() {
        if (body.classList.contains("sidebar-open")) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener("click", function(event) {
            event.stopPropagation();
            toggleSidebar();
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener("click", function() {
            closeSidebar();
        });
    }

    submenuToggles.forEach(function(toggle) {
        toggle.addEventListener("click", function() {
            const menuItem = toggle.closest(".menu-item");

            if (!menuItem) {
                return;
            }

            menuItem.classList.toggle("open");
        });
    });

    if (userButton && userDropdown) {
        userButton.addEventListener("click", function(event) {
            event.stopPropagation();
            userDropdown.classList.toggle("open");
        });
    }

    document.addEventListener("click", function(event) {
        if (userDropdown && !userDropdown.contains(event.target)) {
            userDropdown.classList.remove("open");
        }
    });

    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            closeSidebar();

            if (userDropdown) {
                userDropdown.classList.remove("open");
            }
        }
    });

    window.addEventListener("resize", function() {
        if (window.innerWidth > 991) {
            closeSidebar();
        }
    });
});