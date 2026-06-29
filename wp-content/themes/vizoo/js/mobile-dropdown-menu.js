let isOpen = false;

// used in template
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function toggleDropdown() {
    const menuContainer = document.querySelector(".main-navigation");
    const openDropdownButton = document.querySelector(
        ".toggle-dropdown-mobile-icon",
    );
    if (isOpen) {
        menuContainer.style = "";
        openDropdownButton.classList.remove("fa-x");
        openDropdownButton.classList.add("fa-bars");
        isOpen = false;
    } else {
        menuContainer.style = "transform: translateY(0) !important";
        openDropdownButton.classList.remove("fa-bars");
        openDropdownButton.classList.add("fa-x");
        openDropdownButton.style =
            "position: absolute; top: 1.5rem; right: 1.5rem;";
        isOpen = true;
    }
}
