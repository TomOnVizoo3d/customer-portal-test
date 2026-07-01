const vizooModalContainer = document.querySelector(".video-modal-container");
const vizooModalContent = document.querySelector(".video-modal-content");
const vizooModalIFrame = vizooModalContent.querySelector("iframe");

function vizooCloseModal() {
    vizooModalContainer.style.display = "none";
    if (
        vizooModalIFrame.getAttributeNames().includes("consent-original-src-_")
    ) {
        vizooModalIFrame.setAttribute("consent-original-src-_", ``);
    } else {
        vizooModalIFrame.src = ``;
    }
    vizooModalContainer.removeEventListener("click", vizooCloseModal);
}

function vizooEscCloseModal(event) {
    if (event.key === "Escape") {
        vizooCloseModal();
        vizooModalContainer.removeEventListener("keydown", vizooEscCloseModal);
    }
}

// tiggered in php
// eslint-disable-next-line @typescript-eslint/no-unused-vars
function vizooOpenModal(videoId) {
    vizooModalContainer.addEventListener("keydown", vizooEscCloseModal);
    vizooModalContainer.addEventListener("click", vizooCloseModal);
    if (
        vizooModalIFrame.getAttributeNames().includes("consent-original-src-_")
    ) {
        vizooModalIFrame.setAttribute(
            "consent-original-src-_",
            `https://www.youtube.com/embed/${videoId}`,
        );
    } else {
        vizooModalIFrame.src = `https://www.youtube.com/embed/${videoId}`;
    }
    vizooModalContainer.style.display = "flex";
    setTimeout(() => {
        vizooModalContainer.focus();
    }, 0);
}
