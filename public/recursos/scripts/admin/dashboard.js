// ══════════════════════════════════════════════
//  dashboard.js — Animaciones y efectos del Dashboard
// ══════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {

    // ── Scroll horizontal en el menú de empresas ──
    const empScroll = document.getElementById('empScroll');
    if (empScroll) {
        empScroll.addEventListener('wheel', function (e) {
            e.preventDefault();
            empScroll.scrollLeft += e.deltaY;
        }, { passive: false });
    }

    // ── Animación de contadores numéricos ──
    const counters = document.querySelectorAll('[data-count]');
    counters.forEach(function (el) {
        const target = parseInt(el.getAttribute('data-count'), 10);
        if (isNaN(target) || target === 0) {
            el.textContent = '0';
            return;
        }

        const duration = 800;     // ms
        const startTime = performance.now();

        function animate(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic
            const ease = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(ease * target);
            el.textContent = current;

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }

        // Delay basado en posición del card
        const card = el.closest('.met-card');
        const siblings = document.querySelectorAll('.met-card');
        let idx = 0;
        siblings.forEach(function (s, i) { if (s === card) idx = i; });

        setTimeout(function () {
            requestAnimationFrame(animate);
        }, 200 + idx * 120);
    });

    // ── Hover glow en tarjetas de métricas ──
    document.querySelectorAll('.met-card').forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 8px 30px rgba(0, 0, 0, .3)';
        });
        card.addEventListener('mouseleave', function () {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });

});
