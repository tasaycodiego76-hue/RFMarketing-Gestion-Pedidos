// Obtenemos los componentes del HTML mediante su ID
const sidebar = document.getElementById("sidebar"); // El contenedor del menú lateral
const overlay = document.getElementById("sidebarOverlay"); // El fondo oscuro que bloquea el resto de la pantalla
const hamburgerBtn = document.getElementById("hamburgerBtn"); // El botón de tres líneas (hamburguesa)
const sidebarCloseBtn = document.getElementById("sidebarCloseBtn"); // El botón X para cerrar

//Muestra el menú y bloquea el scroll de la página
function abrirSidebar() {
  // Añade la clase CSS 'is-open'
  sidebar.classList.add("is-open");
  // Muestra el fondo oscuro (overlay)
  overlay.classList.add("is-open");
  // Cambia la apariencia del botón
  hamburgerBtn.classList.add("is-active");
  // 'hidden' evita que el usuario pueda hacer scroll en el contenido de fondo mientras el menú está abierto
  document.body.style.overflow = "hidden";
}

function cerrarSidebar() {
  sidebar.classList.remove("is-open");
  overlay.classList.remove("is-open");
  hamburgerBtn.classList.remove("is-active");
  document.body.style.overflow = "";
}

// Si el menú ya está abierto, lo cierra; si está cerrado, lo abre.
hamburgerBtn.addEventListener("click", function () {
  // .contains verifica si la clase 'is-open' existe en el elemento en ese momento
  if (sidebar.classList.contains("is-open")) {
    cerrarSidebar();
  } else {
    abrirSidebar();
  }
});

//Permite al usuario cerrar el menú simplemente tocando cualquier parte oscura de la pantalla
overlay.addEventListener("click", cerrarSidebar);

// Agregar evento al botón X para cerrar el sidebar
if (sidebarCloseBtn) {
  sidebarCloseBtn.addEventListener("click", cerrarSidebar);
}

// TOGGLE TEMA CLARO / OSCURO

(function () {
  const STORAGE_KEY = "rf-cliente-theme";
  const html = document.documentElement; // <html>
  const toggleBtn = document.getElementById("themeToggleBtn");

  // Al cargar la página, aplicar el tema guardado (si existe)
  const temaGuardado = localStorage.getItem(STORAGE_KEY);
  if (temaGuardado) {
    html.setAttribute("data-theme", temaGuardado);
  }
  // Si no hay tema guardado, se mantiene el oscuro (por defecto, sin atributo data-theme)

  // Click en el botón toggle
  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      const temaActual = html.getAttribute("data-theme");

      if (temaActual === "light") {
        // Cambiar a oscuro
        html.removeAttribute("data-theme");
        localStorage.setItem(STORAGE_KEY, "dark");
      } else {
        // Cambiar a claro
        html.setAttribute("data-theme", "light");
        localStorage.setItem(STORAGE_KEY, "light");
      }
    });
  }
})();

// Confirmación de cierre de sesión
document.addEventListener("DOMContentLoaded", function() {
  const logoutBtn = document.querySelector(".logout-link");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();
      const url = this.getAttribute("href");

      // Detectar tema actual para SweetAlert
      const esClaro = document.documentElement.getAttribute("data-theme") === "light";

      Swal.fire({
        title: "¿Estás seguro?",
        text: "Se cerrará tu sesión actual.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#E8B800",
        cancelButtonColor: esClaro ? "#6a6a7a" : "#333",
        confirmButtonText: "Sí, salir",
        cancelButtonText: "Cancelar",
        background: esClaro ? "#faf7f2" : "#111",
        color: esClaro ? "#1a1a2e" : "#fff"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });
    });
  }
});
