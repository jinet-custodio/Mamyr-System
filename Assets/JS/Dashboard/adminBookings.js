function filteredBookings(selectedFilterValue) {
  fetch(
    `../../Function/Admin/Graph/bookingsGraph.php?selectedFilter=${encodeURIComponent(
      selectedFilterValue
    )}`
  )
    .then((res) => {
      if (!res.ok) throw new Error("Network Error");
      return res.json();
    })
    .then((data) => {
      const ctx = document.getElementById("bookingsBar").getContext("2d");

      if (!data.success || !data.bookings || data.bookings.length === 0) {
        if (bookingsChart) {
          bookingsChart.destroy();
        }

        bookingsChart = new Chart(ctx, {
          type: "bar",
          data: {
            labels: [],
            datasets: [],
          },
          options: {
            responsive: true,
            indexAxis: "y",
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
            devicePixelRatio: window.devicePixelRatio,
          },
          plugins: [noDataPlugin],
        });
        return;
      }

      const bookings = data.bookings;
      let labels = [];
      let dataset = [];

      if (selectedFilterValue === "month") {
        const dataLabels = [
          ...new Set(bookings.map((item) => item.weekOfMonth)),
        ];
        labels = dataLabels;

        const groupedByType = {};

        bookings.forEach((item) => {
          const type = item.bookingType || "Unknown";
          if (!groupedByType[type]) {
            groupedByType[type] = {};
          }

          groupedByType[type][item.weekOfMonth] =
            item.totalBookingThisMonth || 0;
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

        const groupedByType = {};
        bookings.forEach((item) => {
          const type = item.bookingType || "Unknown";
          if (!groupedByType[type]) {
            groupedByType[type] = {};
          }
          console.log(item);
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
      if (bookingsChart) {
        bookingsChart.destroy();
      }

      bookingsChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: dataset,
        },
        options: {
          indexAxis: "y",
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
          },
          devicePixelRatio: window.devicePixelRatio,
        },
        plugins: [noDataPlugin],
      });
    })
    .catch((error) => {
      console.error("Error fetching bookings data:", error);
      if (bookingsChart) {
        bookingsChart.destroy();
      }
      const ctx = document.getElementById("bookingsBar").getContext("2d");
      bookingsChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: [],
          datasets: [],
        },
        options: {
          indexAxis: "y",
          responsive: true,
          devicePixelRatio: window.devicePixelRatio,
        },
        plugins: [noDataPlugin],
      });
    });
}
