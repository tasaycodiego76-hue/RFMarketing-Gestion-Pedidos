
//  dashboard.js — Gestión de Dashboard con Chart.js

document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Navegación por flechas para tarjetas de empresas ──
    const empScroll = document.getElementById('empScroll');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');

    if (empScroll && btnPrev && btnNext) {
        const getScrollAmount = () => {
            const card = empScroll.querySelector('.emp-card');
            return card ? card.offsetWidth + 24 : 375;
        };

        btnNext.addEventListener('click', () => {
            empScroll.scrollLeft += getScrollAmount();
        });

        btnPrev.addEventListener('click', () => {
            empScroll.scrollLeft -= getScrollAmount();
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
        const duration = 1000;
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

        Chart.defaults.font.family = "'DM Sans', sans-serif";

        let chartEmpInstance = null;
        let chartEstInstance = null;

        const getThemeColors = () => {
            const isLight = document.documentElement.getAttribute('data-theme') === 'light';
            return {
                grid: isLight ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.03)',
                ticks: isLight ? '#666' : '#888',
                mainText: isLight ? '#000000' : '#ffffff',
                subText: isLight ? '#888' : '#666'
            };
        };

        const truncateLabel = (str, n = 15) => {
            return (str.length > n) ? str.substr(0, n - 1) + '...' : str;
        };

        // --- Gráfico de Barras ---
        const ctxBar = document.getElementById('chartEmpresas');
        if (ctxBar) {
            const colors = getThemeColors();
            const shortLabels = dataEmpresas.labels.map(label => truncateLabel(label));

            chartEmpInstance = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: shortLabels,
                    datasets: [{
                        label: 'Pedidos',
                        data: dataEmpresas.datasets[0].data,
                        backgroundColor: dataEmpresas.datasets[0].backgroundColor,
                        borderRadius: 6,
                        barThickness: window.innerWidth < 480 ? 18 : 25,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => dataEmpresas.labels[items[0].dataIndex]
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: colors.ticks,
                                font: { size: 9, weight: '600' },
                                maxRotation: 0,
                                autoSkip: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: colors.grid, drawBorder: false },
                            ticks: { color: colors.ticks, font: { size: 10 }, stepSize: 1 }
                        }
                    }
                }
            });
        }

        const isMobile = window.innerWidth <= 480;

        // --- Gráfico de Dona ---
        const ctxDona = document.getElementById('chartEstados');
        if (ctxDona) {
            const totalPedidos = dataEstados.datasets[0].data.reduce((a, b) => a + b, 0);

            chartEstInstance = new Chart(ctxDona, {
                type: 'doughnut',
                data: dataEstados,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: 0 // Sin padding extra para que use el espacio máximo posible
                    },
                    cutout: isMobile ? '68%' : '65%', // Anillo más grueso y estético
                    borderWidth: 0,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: isMobile ? 12 : 20,
                                usePointStyle: true,
                                font: { size: isMobile ? 10 : 11, weight: '600' },
                                color: getThemeColors().ticks
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
                        const colors = getThemeColors();

                        ctx.save();
                        
                        // Número adaptado
                        ctx.font = isMobile ? 'bold 30px "Bebas Neue"' : 'bold 32px "Bebas Neue"';
                        ctx.fillStyle = '#ffffff'; 
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(totalPedidos, centerX, centerY - (isMobile ? 10 : 6));

                        // Letras adaptadas (dos líneas en móvil para mejor ajuste horizontal)
                        ctx.font = isMobile ? 'bold 9px "DM Sans"' : 'bold 9px "DM Sans"';
                        ctx.fillStyle = '#ffffff'; 
                        ctx.letterSpacing = '1px';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        
                        if (isMobile) {
                            ctx.fillText('PEDIDOS', centerX, centerY + 8);
                            ctx.fillText('TOTALES', centerX, centerY + 20);
                        } else {
                            ctx.fillText('PEDIDOS TOTALES', centerX, centerY + 22);
                        }
                        
                        ctx.restore();
                    }
                }]
            });
        }

        // --- Observador de Tema ---
        const observer = new MutationObserver(() => {
            const colors = getThemeColors();
            if (chartEmpInstance) {
                chartEmpInstance.options.scales.x.ticks.color = colors.ticks;
                chartEmpInstance.options.scales.y.grid.color = colors.grid;
                chartEmpInstance.options.scales.y.ticks.color = colors.ticks;
                chartEmpInstance.update('none');
            }
            if (chartEstInstance) {
                chartEstInstance.options.plugins.legend.labels.color = colors.ticks;
                chartEstInstance.update('none');
            }
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    }
});
