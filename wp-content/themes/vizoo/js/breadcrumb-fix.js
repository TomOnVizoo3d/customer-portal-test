document.addEventListener("DOMContentLoaded", () => {
    const breadcrumbList = document.querySelector(
        ".breadcrumb-container",
    )?.firstElementChild;
    if (breadcrumbList === undefined || breadcrumbList.tagName !== "OL") {
        console.warn("Breadcrumb list element couldn't be found.");
        return;
    }
    for (const child of breadcrumbList.children) {
        const breadcrumbLink = child.firstElementChild;
        if (
            breadcrumbLink === undefined ||
            breadcrumbLink.title !== "category"
        ) {
            continue;
        }
        child.style = "display: none;";
    }
});
