document.addEventListener("DOMContentLoaded", function () {
  const graficoProductividad = document.getElementById("graficoProductividad");
  const graficoDistribucion = document.getElementById("graficoDistribucion");
  const graficoTendencia = document.getElementById("graficoTendencia");
  const graficoTiempo = document.getElementById("graficoTiempo");

  // Variables globales de los gráficos para poder actualizarlos después
  let graficosInicializados = false;
  let graficoA, graficoB, graficoC, graficoD;

  fetch("/responsable/dashboard/metricas")
    .then((res) => res.json())
    .then(function (resp) {
      //Validacion de Datos
      if (!resp.success) {
        console.error("Error al obtener métricas:", resp.message);
        return;
      }

      // Desestructuración de datos para cada gráfico
      const productividad = resp.productividad;
      const distribucion = resp.distribucion_carga;
      const tendencia = resp.tendencias_Semanal;
      const tiempoPromedio = resp.tiempopromedio;

      // Detectar Tema
      const esClaro = document.documentElement.getAttribute("data-theme") === "light";
      const textColor = esClaro ? "#4a4a5a" : "#d1d1d6";
      const gridColor = esClaro ? "rgba(0,0,0,0.05)" : "rgba(255,255,255,0.05)";
      const centerTextColor = esClaro ? "#1a1a2e" : "#ffffff";

      // GRÁFICO A: Productividad por empleado
      const graficoA = new Chart(graficoProductividad, {
        type: "bar",
        data: {
          labels: productividad.map((e) => e.nombre),
          datasets: [
            {
              label: "En Proceso",
              data: productividad.map((e) => e.total_proceso),
              backgroundColor: "rgba(230, 201, 148, 0.8)",
            },
            {
              label: "Completadas",
              data: productividad.map((e) => e.total_completado),
              backgroundColor: "rgba(106, 176, 76, 0.8)",
            },
            {
              label: "Total Tareas",
              data: productividad.map((e) => e.total_tareas),
              backgroundColor: "rgba(118, 240, 209, 0.8)",
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "top",
              labels: { color: textColor },
            },
          },
          scales: {
            y: {
              grid: { color: gridColor },
              ticks: { color: textColor },
            },
            x: {
              ticks: { color: textColor },
            },
          },
        },
      });

      // GRAFICO B: Distribución de carga por empleado

      // Acumulador sobre el total de tareas para calcular el porcentaje de cada empleado
      const totalCarga = distribucion.reduce(
        (sum, e) => sum + parseInt(e.cantidad_tareas),
        0,
      );

      const GraficoB = new Chart(graficoDistribucion, {
        type: "doughnut",
        data: {
          labels: distribucion.map(
            (e) =>
              `${e.nombre_completo} (${totalCarga > 0 ? Math.round((e.cantidad_tareas / totalCarga) * 100) : 0}%)`,
          ),
          datasets: [
            {
              data: distribucion.map((e) => e.cantidad_tareas),
              backgroundColor: [
                "rgba(104,109,224,0.8)",
                "rgba(246,229,141,0.8)",
                "rgba(106,176,76,0.8)",
                "rgba(255,107,107,0.8)",
                "rgba(72,219,251,0.8)",
              ],
              borderWidth: 0,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "75%",
          plugins: {
            legend: {
              position: "bottom",
              align: "center",
              labels: {
                color: textColor,
                padding: 20,
                boxWidth: 12,
                font: { size: 12 },
              },
            },
          },
        },
        plugins: [
          {
            id: "centerText",
            afterDraw: function (chart) {
              if (chart.config.type === "doughnut" && chart.getDatasetMeta(0).data.length > 0) {
                var ctx = chart.ctx;
                var meta = chart.getDatasetMeta(0);
                var x = meta.data[0].x;
                var y = meta.data[0].y;
                ctx.save();
                ctx.font = "bold 3rem Bebas Neue, sans-serif";
                ctx.textBaseline = "middle";
                ctx.textAlign = "center";
                ctx.fillStyle = centerTextColor;
                ctx.fillText(totalCarga, x, y - 8);
                ctx.font = "700 0.85rem Inter, sans-serif";
                ctx.fillStyle = textColor;
                ctx.fillText("TAREAS", x, y + 25);
                ctx.restore();
              }
            },
          },
        ],
      });

      // GRÁFICO C: Tendencia semanal (Semana Actual)
      const diasSemanaStr = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
      const tendenciaFinal = [0, 0, 0, 0, 0];
      
      tendencia.forEach(d => {
        const numDia = parseInt(d.numero_dia);
        if (numDia >= 1 && numDia <= 5) {
          tendenciaFinal[numDia - 1] = parseInt(d.total_finalizados);
        }
      });

      const GraficoC = new Chart(graficoTendencia, {
        type: "line",
        data: {
          labels: diasSemanaStr,
          datasets: [
            {
              label: "Tareas Finalizadas",
              data: tendenciaFinal,
              borderColor: "rgba(104,109,224,1)",
              backgroundColor: "rgba(104,109,224,0.1)",
              tension: 0.4,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: "top", labels: { color: textColor } },
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: gridColor },
              ticks: { color: textColor },
            },
            x: {
              grid: { display: false },
              ticks: { color: textColor },
            },
          },
        },
      });

      // GRÁFICO D: Tiempo promedio de resolución
      const GraficoD = new Chart(graficoTiempo, {
        type: "bar",
        data: {
          labels: tiempoPromedio.map((e) => e.nombre),
          datasets: [
            {
              label: "Horas Promedio",
              data: tiempoPromedio.map((e) => e.promedio_horas),
              backgroundColor: "rgba(246, 229, 141, 0.8)",
              borderRadius: 5,
            },
          ],
        },
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function (context) {
                  return context.parsed.x + " horas";
                },
              },
            },
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: gridColor },
              ticks: { color: textColor },
              title: {
                display: true,
                text: "Horas",
                color: textColor,
              },
            },
            y: {
              grid: { display: false },
              ticks: { color: textColor },
            },
          },
        },
      });
    })
    .catch((err) => console.error("Error de red:", err));
});
