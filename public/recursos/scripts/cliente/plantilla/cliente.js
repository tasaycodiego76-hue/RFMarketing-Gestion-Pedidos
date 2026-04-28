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
