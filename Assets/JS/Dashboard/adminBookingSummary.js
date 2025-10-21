function filteredBookingSummary(dateFilterValue, statusFilterValue) {
  fetch(
    `../../Function/Admin/Graph/bookingSummaryCard.php?selectedPeriod=${encodeURIComponent(
      dateFilterValue
    )}&selectedStatus=${encodeURIComponent(statusFilterValue)}`
  )
    .then((result) => {
      if (!result.ok) throw new Error("Network Error");
      return result.json();
    })
    .then((data) => {
      if (!data.success) {
        Swal.fire({
          text:
            data.message ||
            "Server error (500): Something went wrong on our end. Please try again later.",
          title: "Error",
          icon: "error",
        });
      }

      const bookings = data.result;

      let total = 0;

      if (bookings.length > 0) {
        bookings.forEach((item) => {
          let type = item.bookingType.toLowerCase();
          switch (type) {
            case "resort":
              document.getElementById("resort-number").textContent =
                item.totalBookings || 0;
              total += item.totalBookings;
              break;
            case "hotel":
              document.getElementById("hotel-number").textContent =
                item.totalBookings || 0;
              total += item.totalBookings;
              break;
            case "event":
              document.getElementById("event-number").textContent =
                item.totalBookings || 0;
              total += item.totalBookings;
              break;
            default:
              document.getElementById("resort-number").textContent = 0;
              document.getElementById("event-number").textContent = 0;
              document.getElementById("hotel-number").textContent = 0;
              break;
          }
        });
      } else {
        document.getElementById("resort-number").textContent = 0;
        document.getElementById("hotel-number").textContent = 0;
        document.getElementById("event-number").textContent = 0;
      }

      document.getElementById("total-bookings").textContent = total || 0;
    });
}
