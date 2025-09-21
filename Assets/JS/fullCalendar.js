document.addEventListener("DOMContentLoaded", function () {
  // console.log("DOM fully loaded and parsed");
  var calendarEl = document.getElementById("calendar");
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
  });
  // console.log("Calendar initialized");
  calendar.render();
  // console.log("Calendar rendered");
});
