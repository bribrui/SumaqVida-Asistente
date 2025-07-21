function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  menu.classList.toggle('open');
}
function toggleDropdown() {
  const menu = document.getElementById('dropdown-menu');
  menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Cerrar si se hace clic fuera del dropdown
window.addEventListener('click', function (e) {
  const menu = document.getElementById('dropdown-menu');
  if (!e.target.matches('.dropdown-toggle')) {
    menu.style.display = 'none';
  }
});

fetch('/views/partials/header.html')
    .then(res => res.text())
    .then(html => {
      document.getElementById('include-header').innerHTML = html;

      // Esperar a que el contenido se haya insertado
      setTimeout(() => {
        const header = document.querySelector('header');

        // Scroll: color de fondo
        function actualizarColorHeader() {
          if (window.scrollY > 20) {
            header.classList.add('scrolled');
          } else {
            header.classList.remove('scrolled');
          }
        }

        window.addEventListener('scroll', actualizarColorHeader);
        actualizarColorHeader();

      }, 50); // Pequeña espera para que se inserte el header correctamente
    });