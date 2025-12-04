window.addEventListener("DOMContentLoaded", () => {
  const guestNoInput = document.getElementById("guestNo");
  const eventVenueInput = document.getElementById("eventVenue");
  const bookNowBtn = document.getElementById("bookNowBtn");
  // const foodButton = document.getElementById("confirmDishBtn");

  // let isFoodSelectionValid = true;
  let isGuestCountValid;

  let guestNoValue = parseInt(guestNoInput.value, 10);
  if (isNaN(guestNoValue)) guestNoValue = 0;

  let eventVenueValue = "";
  let eventVenueCapacity = 0;

  if (eventVenueInput && eventVenueInput.selectedIndex >= 0) {
    const selectedVenue =
      eventVenueInput.options[eventVenueInput.selectedIndex];
    if (selectedVenue && selectedVenue.value !== "") {
      eventVenueValue = selectedVenue.value;
      eventVenueCapacity = parseInt(
        selectedVenue.dataset.maxcapacity || "0",
        10
      );
    }
  }

  guestNoInput.addEventListener("change", () => {
    guestNoValue = parseInt(guestNoInput.value, 10);
    if (isNaN(guestNoValue)) guestNoValue = 0;
    checkCapacity();
  });

  eventVenueInput.addEventListener("change", () => {
    const selectedVenue =
      eventVenueInput.options[eventVenueInput.selectedIndex];
    if (selectedVenue && selectedVenue.value !== "") {
      eventVenueValue = selectedVenue.value;
      eventVenueCapacity = parseInt(
        selectedVenue.dataset.maxcapacity || "0",
        10
      );
    } else {
      eventVenueValue = "";
      eventVenueCapacity = 0;
    }
    checkCapacity();
  });

  function checkCapacity() {
    if (guestNoValue > eventVenueCapacity) {
      Swal.fire({
        title: "Sorry",
        text: `We're sorry. The resort can't accommodate ${guestNoValue} guests in the ${eventVenueValue}.`,
        icon: "info",
        confirmButtonText: "Okay",
      }).then(() => {
        guestNoInput.style.border = "1px solid red";
      });
      isGuestCountValid = false;
    } else {
      guestNoInput.style.border = "1px solid rgb(223, 226, 230)";
      isGuestCountValid = true;
    }
    updateBookNowButton();
  }

  function updateBookNowButton() {
    bookNowBtn.disabled = !isGuestCountValid;
  }
});

//   function countSelected(categoryName) {
//     const selected = document.querySelectorAll(
//       `input[name="${categoryName}Selections[]"]:checked`
//     );
//     return selected.length;
//   }

//   foodButton.addEventListener("click", function () {
//     const mainDish = ["chicken", "pork", "pasta", "beef", "vegie", "seafood"];
//     const drinkCount = countSelected("drink");
//     const dessertCount = countSelected("dessert");
//     let mainDishCount = 1;

//     mainDish.forEach((category) => {
//       mainDishCount += countSelected(category);
//     });

//     let isValid = true;

//     if (mainDishCount > 5) {
//       Swal.fire({
//         title: "Sorry",
//         text: `You can select a maximum of 4 dishes.`,
//         icon: "info",
//       });
//       isValid = false;
//     }

//     if (drinkCount > 2) {
//       Swal.fire({
//         title: "Sorry",
//         text: `You may only select 1 drink.`,
//         icon: "info",
//       });
//       isValid = false;
//     }

//     if (dessertCount > 3) {
//       Swal.fire({
//         title: "Sorry",
//         text: `You can select up to 2 kinds of dessert.`,
//         icon: "info",
//       });
//       isValid = false;
//     }

//     isFoodSelectionValid = isValid;
//     updateBookNowButton();
//   });

//
