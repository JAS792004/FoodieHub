
document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll(".navbar-nav .nav-link");

    navLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            const navbar = document.querySelector(".navbar-collapse");

            if (navbar.classList.contains("show")) {
                bootstrap.Collapse.getOrCreateInstance(navbar).hide();
            }
        });
    });

    const foodCards = document.querySelectorAll(".food-card");

    foodCards.forEach(function (card) {
        card.addEventListener("mouseenter", function () {
            card.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
        });
    });
});
