document.addEventListener("DOMContentLoaded", function () {

  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const hamburgerBtn = document.getElementById("hamburgerBtn");
  const sidebarCloseBtn = document.getElementById("sidebarCloseBtn");
  const mainWrapper = document.getElementById("mainWrapper");

  // Sidebar Movil

  // Abrir sidebar
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", function () {
      sidebar.classList.add("active");
      sidebarOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
    });
  }

  // Cerrar sidebar
  function cerrarSidebar() {
    sidebar.classList.remove("active");
    sidebarOverlay.classList.remove("active");
    document.body.style.overflow = "";
  }

  if (sidebarCloseBtn) {
    sidebarCloseBtn.addEventListener("click", cerrarSidebar);
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", cerrarSidebar);
  }

  // Cerrar al hacer clic en un enlace (en móvil)
  const navLinks = document.querySelectorAll(".nav-link-item");
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      if (window.innerWidth < 992) {
        cerrarSidebar();
      }
    });
  });

  // Cerrar Sesion - (Confirmacion Sweet Alert)
  const logoutLink = document.querySelector(".logout-link");
  if (logoutLink) {
    logoutLink.addEventListener("click", function (e) {
      e.preventDefault();

      Swal.fire({
        title: "¿Cerrar sesión?",
        text: "¿Estás seguro de que deseas salir del sistema?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#F5C400",
        cancelButtonColor: "#71717a",
        confirmButtonText: "Sí, salir",
        cancelButtonText: "Cancelar",
        background: "#161616",
        color: "#ffffff",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = this.href;
        }
      });
    });
  }


  // ToolTips Boostrap
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]',
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
  );

  // Notificaciones
  window.mostrarToast = function (mensaje, tipo = "success") {
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      background: "#161616",
      color: "#ffffff",
    });

    Toast.fire({
      icon: tipo,
      title: mensaje,
    });
  };
});
