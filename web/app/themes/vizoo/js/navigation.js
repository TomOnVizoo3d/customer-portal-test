/**
 * File navigation.js.
 *
 * Handles toggling the navigation menu for small screens and enables TAB key
 * navigation support for dropdown menus.
 */
(function navigation() {
    /**
     * Sets or removes .focus class on an element.
     */
    function toggleFocus(event) {
        let self = event.currentTarget;

        // Move up through the ancestors of the current link until we hit .nav-menu.
        while (self.className.indexOf("nav-menu") === -1) {
            // On li elements toggle the class .focus.
            if (self.tagName.toLowerCase() === "li") {
                if (self.className.indexOf("focus") === -1) {
                    self.className += " focus";
                } else {
                    self.className = self.className.replace(" focus", "");
                }
            }

            self = self.parentElement;
        }
    }

    const container = document.getElementById("site-navigation");
    if (!container) {
        return;
    }

    const button = container.getElementsByTagName("button")[0];
    if (typeof button === "undefined") {
        return;
    }

    const menu = container.getElementsByTagName("ul")[0];

    // Hide menu toggle button if menu is empty and return early.
    if (typeof menu === "undefined") {
        button.style.display = "none";
        return;
    }

    menu.setAttribute("aria-expanded", "false");
    if (menu.className.indexOf("nav-menu") === -1) {
        menu.className += " nav-menu";
    }

    button.onclick = function clickHandler() {
        if (container.className.indexOf("toggled") === -1) {
            container.className += " toggled";
            button.setAttribute("aria-expanded", "true");
            menu.setAttribute("aria-expanded", "true");
        } else {
            container.className = container.className.replace(" toggled", "");
            button.setAttribute("aria-expanded", "false");
            menu.setAttribute("aria-expanded", "false");
        }
    };

    // Get all the link elements within the menu.
    const links = menu.getElementsByTagName("a");

    // Each time a menu link is focused or blurred, toggle focus.
    for (let i = 0; i < links.length; i++) {
        links[i].addEventListener("focus", toggleFocus, true);
        links[i].addEventListener("blur", toggleFocus, true);
    }

    /**
     * Toggles `focus` class to allow submenu access on tablets.
     */
    (function toggleFocus_iief(container_) {
        const parentLink = container_.querySelectorAll(
            ".menu-item-has-children > a, .page_item_has_children > a",
        );

        if ("ontouchstart" in window) {
            const touchStartFn = function (e) {
                const menuItem = e.currentTarget.parentNode;

                if (menuItem.classList.contains("focus")) {
                    menuItem.classList.remove("focus");
                } else {
                    e.preventDefault();
                    for (
                        let i = 0;
                        i < menuItem.parentNode.children.length;
                        ++i
                    ) {
                        if (menuItem === menuItem.parentNode.children[i]) {
                            continue;
                        }
                        menuItem.parentNode.children[i].classList.remove(
                            "focus",
                        );
                    }
                    menuItem.classList.add("focus");
                }
            };

            for (let i = 0; i < parentLink.length; ++i) {
                parentLink[i].addEventListener(
                    "touchstart",
                    touchStartFn,
                    false,
                );
            }
        }
    })(container);
})();
