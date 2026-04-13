//Scroll horizontal en el menú de empresas
const empScroll = document.getElementById('empScroll');
if (empScroll) {
    empScroll.addEventListener('wheel', function (e) {
        e.preventDefault();
        empScroll.scrollLeft += e.deltaY;
    }, { passive: false });
}
