document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    const menuButton = document.getElementById("mobileMenuBtn");
    const closeButton = document.getElementById("sidebarCloseBtn");
    const backdrop = document.getElementById("sidebarBackdrop");
    const submenuToggles = document.querySelectorAll(".submenu-toggle");

    function saveOpenState(open) {
        try {
            localStorage.setItem("admin_sidebar_open", open ? "1" : "0");
        } catch (error) {
            // The menu remains usable when browser storage is unavailable.
        }
    }

    function savedOpenState() {
        try {
            return localStorage.getItem("admin_sidebar_open") === "1";
        } catch (error) {
            return false;
        }
    }

    function updateMenuButton() {
        if (!menuButton) return;

        const open = body.classList.contains("sidebar-open");
        menuButton.setAttribute("aria-expanded", String(open));
        menuButton.setAttribute("aria-label", open ? "Đóng menu" : "Mở menu");
    }

    function openSidebar() {
        body.classList.add("sidebar-open");
        saveOpenState(true);
        updateMenuButton();
    }

    function closeSidebar() {
        body.classList.remove("sidebar-open");
        saveOpenState(false);
        updateMenuButton();
    }

    function toggleSidebar() {
        if (body.classList.contains("sidebar-open")) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    menuButton?.addEventListener("click", function (event) {
        event.stopPropagation();
        toggleSidebar();
    });

    closeButton?.addEventListener("click", closeSidebar);
    backdrop?.addEventListener("click", closeSidebar);

    submenuToggles.forEach(function (toggle) {
        toggle.addEventListener("click", function () {
            const menuItem = toggle.closest(".menu-item");
            if (!menuItem) return;

            const open = menuItem.classList.toggle("open");
            toggle.setAttribute("aria-expanded", String(open));
        });
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") closeSidebar();
    });

    if (savedOpenState() && window.matchMedia("(min-width: 992px)").matches) {
        openSidebar();
    } else {
        updateMenuButton();
    }
});
