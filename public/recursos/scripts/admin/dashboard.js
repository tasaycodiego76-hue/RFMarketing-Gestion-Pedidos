
//  dashboard.js — Gestión de Dashboard con Chart.js

document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Navegación por flechas para tarjetas de empresas ──
    const empScroll = document.getElementById('empScroll');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');

    if (empScroll && btnPrev && btnNext) {
        const scrollAmount = 375;

        btnNext.addEventListener('click', () => {
            empScroll.scrollLeft += scrollAmount;
        });

        btnPrev.addEventListener('click', () => {
            empScroll.scrollLeft -= scrollAmount;
        });
    }

    // ── 2. Animación de números (Métricas) ──
    const counters = document.querySelectorAll('[data-count]');
    counters.forEach(function (el) {
        const target = parseInt(el.getAttribute('data-count'), 10);
        if (isNaN(target) || target === 0) {
            el.textContent = '0';
            return;
        }

        let start = 0;
        const duration = 1500;
        const startTime = performance.now();

        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const value = Math.floor(progress * target);
            el.textContent = value;
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        requestAnimationFrame(update);
    });

    // ── 3. Gráficos con Chart.js ──
    if (typeof Chart !== 'undefined') {

        // Estilos base para modo oscuro
        Chart.defaults.color = '#888';
        Chart.defaults.font.family = "'DM Sans', sans-serif";

        // --- Gráfico de Barras (Carga de Trabajo) ---
        const ctxBar = document.getElementById('chartEmpresas');
        if (ctxBar) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: dataEmpresas.labels,
                    datasets: [{
                        label: 'Pedidos',
                        data: dataEmpresas.datasets[0].data,
                        backgroundColor: dataEmpresas.datasets[0].backgroundColor,
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 30,
                        maxBarThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#161616',
                            titleColor: '#fff',
                            bodyColor: '#aaa',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#888',
                                font: { size: 10, weight: '600' }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false },
                            ticks: { stepSize: 1, color: '#555' }
                        }
                    }
                }
            });
        }

        // --- Gráfico de Dona (Estado de Pedidos) ---
        const ctxDona = document.getElementById('chartEstados');
        if (ctxDona) {
            const totalPedidos = dataEstados.datasets[0].data.reduce((a, b) => a + b, 0);

            new Chart(ctxDona, {
                type: 'doughnut',
                data: dataEstados,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '80%',
                    borderWidth: 2,
                    borderColor: '#0f0f0f',
                    hoverOffset: 12,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: { size: 11, weight: '600' },
                                color: '#999'
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'centerText',
                    afterDraw: function (chart) {
                        const { ctx, chartArea: { left, top, right, bottom } } = chart;
                        const centerX = (left + right) / 2;
                        const centerY = (top + bottom) / 2;

                        ctx.save();

                        // Número Central (Blanco puro para máxima claridad)
                        ctx.font = 'bold 42px "Bebas Neue"';
                        ctx.fillStyle = '#ffffff';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(totalPedidos, centerX, centerY - 6);

                        // Etiqueta (Gris suave)
                        ctx.font = 'bold 9px "DM Sans"';
                        ctx.fillStyle = '#777';
                        ctx.letterSpacing = '1px';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('PEDIDOS TOTALES', centerX, centerY + 24);

                        ctx.restore();
                    }
                }]
            });
        }
    }
});
