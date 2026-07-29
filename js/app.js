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

// Cargar header después de que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  // Detectar la ruta base
  const baseURL = window.location.pathname.includes('/') ? '/Sumaqvida' : '';
  const headerPath = baseURL + '/views/partials/header.html';
  
  console.log('Intentando cargar header desde:', headerPath);
  
  fetch(headerPath)
    .then(res => {
      console.log('Fetch response status:', res.status);
      if (!res.ok) {
        throw new Error('Error loading header: HTTP ' + res.status);
      }
      return res.text();
    })
    .then(html => {
      console.log('HTML recibido, inyectando...');
      const container = document.getElementById('include-header');
      if (container) {
        container.innerHTML = html;
        console.log('Header inyectado exitosamente');
      } else {
        console.error('No se encontró el contenedor include-header');
      }
    })
    .catch(err => {
      console.error('Error al cargar header:', err);
    });
});

// Scroll effect para header
window.addEventListener('load', function() {
  setTimeout(function() {
    const header = document.querySelector('header');
    if (header) {
      function actualizarColorHeader() {
        if (window.scrollY > 20) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      }
      window.addEventListener('scroll', actualizarColorHeader);
      actualizarColorHeader();
    }
  }, 500);
});