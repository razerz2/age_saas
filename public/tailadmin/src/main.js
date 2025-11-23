import Alpine from "alpinejs";

window.Alpine = Alpine;

document.addEventListener("alpine:init", () => {
    Alpine.data("mainState", () => ({
        page: "ecommerce",
        loaded: true,
        darkMode: JSON.parse(localStorage.getItem("darkMode")) ?? false,
        stickyMenu: false,
        sidebarToggle: false,
        scrollTop: false,
        selected: "",

        init() {
            this.$watch("darkMode", (val) =>
                localStorage.setItem("darkMode", JSON.stringify(val))
            );
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
        },

        toggleSidebar() {
            this.sidebarToggle = !this.sidebarToggle;
        },

        toggleDropdown(key) {
            this.selected = this.selected === key ? "" : key;
        },
    }));
});

Alpine.start();
