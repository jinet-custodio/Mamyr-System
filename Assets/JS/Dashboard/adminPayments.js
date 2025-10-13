function filteredPayments(selectedFilter) {
  fetch(
    `../../Function/Admin/Graph/paymentsGraph.php?selectedFiltered=${encodeURIComponent(
      selectedFilter
    )}`
  )
    .then((res) => {
      if (!res.ok) throw new Error("Network");
      return res.json();
    })
    .then((data) => {
      const ctx = document.getElementById("paymentsBar").getContext("2d");

      if (!data.success || !data.payments || data.payments.length === 0) {
        if (paymentsChart) {
          paymentsChart.destroy();
        }

        paymentsChart = new Chart(ctx, {
          type: "bar",
          data: {
            labels: [],
            dataset: [],
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true,
              },
              x: {
                title: {
                  display: true,
                  text:
                    selectedFilter === "month"
                      ? "Weeks of the Month"
                      : "Days of the Week",
                },
              },
            },
            plugins: {
              legend: {
                display: false,
              },
              tooltip: {
                enabled: false,
              },
            },
          },
          plugins: [noDataPlugin],
        });
        return;
      }

      const payments = data.payments;
      let labels = [];
      let dataset = [];

      if (selectedFilter === "month") {
        const dayLabels = [
          ...new Set(payments.map((item) => item.weekOfTheMonth)),
        ];
        labels = dayLabels;

        const groupedByType = {};
        payments.forEach((item) => {
          const type = item.paymentStatus || "Unknown";
          if (!groupedByType[type]) groupedByType[type] = {};

          groupedByType[type][item.weekOfTheMonth] =
            parseInt(item.paymentsThisMonth) || 0;

          dataset = Object.keys(groupedByType).map((type) => {
            const color = colors[type.toLowerCase()] || colors.default;
            const data = labels.map((week) => groupedByType[type][week]) || 0;

            return {
              label: type.charAt(0).toUpperCase() + type.slice(1),
              data: data,
              backgroundColor: color.bg,
              borderColor: color.border,
              borderWidth: 2,
            };
          });
        });
      } else {
        const dayLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
        labels = dayLabels;
        title = payments[0]?.weekLabels || "";

        const groupedByType = {};
        payments.forEach((item) => {
          const type = item.paymentStatus || "Unknown";
          groupedByType[type] = dayLabels.map((day) => item[day] || 0);
        });
        dataset = Object.keys(groupedByType).map((type) => {
          const color = colors[type.toLowerCase()] || colors.default;
          return {
            label: type.charAt(0).toUpperCase() + type.slice(1),
            data: groupedByType[type],
            backgroundColor: color.bg,
            borderColor: color.border,
            borderWidth: 2,
          };
        });
      }

      if (paymentsChart) {
        paymentsChart.destroy();
      }

      paymentsChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: dataset,
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
              },
            },
            x: {
              title: {
                display: true,
                text:
                  selectedFilter === "month"
                    ? "Weeks of the Month"
                    : "Days of the Week",
              },
              barThickness: 20,
              maxBarThickness: 30,
              categoryPercentage: 0.7,
              barPercentage: 0.8,
            },
          },
          plugins: {
            legend: {
              display: true,
            },
          },
        },
        plugins: [noDataPlugin],
      });
    })
    .catch((error) => {
      console.error("Error fetching payments data:", error);
      if (paymentsChart) {
        paymentsChart.destroy();
      }
      const ctx = document.getElementById("paymentsBar").getContext("2d");
      paymentsChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: [],
          datasets: [],
        },
        options: {
          responsive: true,
        },
        plugins: [noDataPlugin],
      });
    });
}
