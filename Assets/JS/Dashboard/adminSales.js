function filteredSales(selectedFilterValue) {
  fetch(
    `../../Function/Admin/Graph/salesGraph.php?selectedFilter=${encodeURIComponent(
      selectedFilterValue
    )}`
  )
    .then((response) => {
      if (!response.ok) throw new Error("Network error");
      return response.json();
    })
    .then((data) => {
      const ctx = document.getElementById("salesBar").getContext("2d");

      if (!data.success || !data.sales || data.sales.length === 0) {
        if (salesChart) {
          salesChart.destroy();
        }
        salesChart = new Chart(ctx, {
          type: "line",
          data: {
            labels: [],
            datasets: [],
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
                    selectedFilterValue === "month"
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

      const sales = data.sales;
      let labels = [];
      let dataset = [];
      // let title = "";
      let chartType = selectedFilterValue === "month" ? "bar" : "line";
      if (selectedFilterValue === "month") {
        const dayLabels = [...new Set(sales.map((item) => item.weekOfMonth))];
        labels = dayLabels;

        const groupedByType = {};
        sales.forEach((item) => {
          const type = item.bookingType || "Unknown";
          if (!groupedByType[type]) groupedByType[type] = {};

          groupedByType[type][item.weekOfMonth] =
            parseFloat(item.totalSalesThisMonth) || 0;
        });

        dataset = Object.keys(groupedByType).map((type) => {
          const color = colors[type.toLowerCase()] || colors.default;

          const data = labels.map((week) => groupedByType[type][week] || 0);

          return {
            label: type.charAt(0).toUpperCase() + type.slice(1),
            data: data,
            backgroundColor: color.bg,
            borderColor: color.border,
            borderWidth: 2,
          };
        });
      } else {
        const dayLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
        labels = dayLabels;
        title = sales[0]?.weekLabels || "";

        const groupedByType = {};
        sales.forEach((item) => {
          const type = item.bookingType || "Unknown";
          groupedByType[type] = dayLabels.map(
            (day) => parseFloat(item[day]) || 0
          );
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

      if (salesChart) {
        salesChart.destroy();
      }

      salesChart = new Chart(ctx, {
        type: chartType,
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
                  selectedFilterValue === "month"
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
            tooltip: {
              callbacks: {
                label: (ctx) => `₱${ctx.parsed.y.toLocaleString()}`,
              },
            },
          },
        },
        plugins: [noDataPlugin],
      });
    })
    .catch((error) => {
      console.error("Error fetching sales data:", error);
      if (salesChart) {
        salesChart.destroy();
      }
      const ctx = document.getElementById("salesBar").getContext("2d");
      salesChart = new Chart(ctx, {
        type: "line",
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
