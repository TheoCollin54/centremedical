let current = 0;
const images = document.querySelectorAll(".slide-image");

function showSlide(index) {
    images.forEach((img, i) => {
        img.classList.remove("active");
        if (i === index) {
            img.classList.add("active");
        }
    });
}

function startSlideshow() {
    showSlide(current);
    setInterval(() => {
        current = (current + 1) % images.length;
        showSlide(current);
    }, 10000); // toutes les 10 secondes
}

window.onload = () => {
    startSlideshow();

    // Redirection au clic n'importe où sur la page
    document.body.addEventListener('click', () => {
        window.location.href = 'index.php';
    });
};