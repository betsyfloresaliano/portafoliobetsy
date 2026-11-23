document.getElementById('anio').textContent = new Date().getFullYear();

// Cerrar el menú móvil al tocar un enlace
document.querySelectorAll('.navbar-nav .nav-link').forEach(function (enlace) {
  enlace.addEventListener('click', function () {
    var menu = document.getElementById('menu');
    if (menu.classList.contains('show')) {
      bootstrap.Collapse.getOrCreateInstance(menu).hide();
    }
  });
});

// Resaltar la sección visible en el menú
var secciones = document.querySelectorAll('main section[id]');
var enlaces = document.querySelectorAll('.navbar-nav .nav-link');
var observador = new IntersectionObserver(function (entradas) {
  entradas.forEach(function (e) {
    if (e.isIntersecting) {
      enlaces.forEach(function (a) {
        a.classList.toggle('activo', a.getAttribute('href') === '#' + e.target.id);
      });
    }
  });
}, { rootMargin: '-45% 0px -50% 0px' });
secciones.forEach(function (s) { observador.observe(s); });

// Formulario de contacto
var form = document.getElementById('form-contacto');
var respuesta = document.getElementById('respuesta');
var boton = document.getElementById('btn-enviar');

function mostrar(texto, clase) {
  respuesta.className = 'mt-3 ' + clase;
  respuesta.textContent = texto;
}

form.addEventListener('submit', function (ev) {
  ev.preventDefault();
  var nombre = form.nombre.value.trim();
  var correo = form.correo.value.trim();
  var telefono = form.telefono.value.trim();
  var mensaje = form.mensaje.value.trim();

  if (!nombre || !correo || !telefono || !mensaje) {
    mostrar('Completa nombre, correo, teléfono y mensaje.', 'error');
    return;
  }
  if (!/^[0-9+() -]{6,15}$/.test(telefono)) {
    mostrar('Escribe un teléfono válido, solo números (por ejemplo 987654321).', 'error');
    return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
    mostrar('Escribe un correo válido, por ejemplo nombre@correo.com.', 'error');
    return;
  }

  boton.disabled = true;
  boton.textContent = 'Enviando...';

  fetch('contacto.php', { method: 'POST', body: new FormData(form) })
    .then(function (r) { return r.json(); })
    .then(function (datos) {
      if (datos.ok) {
        mostrar('Mensaje enviado. Gracias por escribir.', 'ok');
        form.reset();
      } else {
        mostrar(datos.error || 'No se pudo enviar. Intenta de nuevo.', 'error');
      }
    })
    .catch(function () {
      mostrar('No se pudo enviar. Revisa tu conexión e intenta de nuevo.', 'error');
    })
    .finally(function () {
      boton.disabled = false;
      boton.textContent = 'Enviar mensaje';
    });
});
