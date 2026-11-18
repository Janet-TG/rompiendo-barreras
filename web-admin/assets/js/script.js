// menu responsive (boton hamburguesa)
document.addEventListener("DOMContentLoaded", () => {
    const toggler = document.querySelector(".navbar-toggler");
    const menu = document.querySelector("#navbarNav");

    if (toggler && menu) {
        toggler.addEventListener("click", () => {
            menu.classList.toggle("show");
        });
    }
});


// scroll reveal, para que se vea el contenido al hacer scroll
// Hacer scroll se refiere a desplazarse hacia abajo o hacia arriba para ver más contenido.
function revealOnScroll() {
    const elements = document.querySelectorAll(".reveal");

    if (!elements.length) return;

    const windowHeight = window.innerHeight;

    elements.forEach(el => {
        const position = el.getBoundingClientRect().top;

        if (position < windowHeight - 100) {
            el.classList.add("active");
        }
    });
}

window.addEventListener("scroll", revealOnScroll);
revealOnScroll();


//navbar con sombra al bajar
// Cuando te desplazas hacia abajo, la barra de navegación adquiere una sombra para mejorar la visibilidad y estetica
window.addEventListener("scroll", () => {
    const nav = document.querySelector("nav");

    if (!nav) return;

    if (window.scrollY > 50) {
        nav.classList.add("nav-scrolled");
    } else {
        nav.classList.remove("nav-scrolled");
    }
});


// efecto hover en las cajas de servicios
// hace que cambie el estilo al pasar el raton por encima de ellas
document.querySelectorAll(".service-box").forEach(box => {
    box.addEventListener("mouseenter", () => box.classList.add("hovered"));
    box.addEventListener("mouseleave", () => box.classList.remove("hovered"));
});

// Mensaje emergente al dar clic en el boton de registro
document.getElementById("btn-register").addEventListener("click", function(e) {
    e.preventDefault(); // evita que se recargue la página o navegue a "#"
    alert("Registrándote en nuestra página podrás recibir más novedades y actualizaciones");
});

document.querySelectorAll(".social-icon").forEach(icon => {
    icon.addEventListener("click", function(e) {
        e.preventDefault();
        alert("Gracias por seguirnos en nuestras redes sociales. ¡Pronto tendrás más novedades!");
    });
});
