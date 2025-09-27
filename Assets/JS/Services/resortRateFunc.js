const OriginalRatesValues = {};

function editRates(editBtn) {
  const thisRow = editBtn.closest(".ratesdata");
  const formControl = thisRow.querySelectorAll(".form-control");
  const formSelect = thisRow.querySelectorAll(".form-select");
  const entrancePrice = thisRow.querySelector(".entrancePrice");
  const availability = thisRow.querySelector(".availability");
  const cancelBtn = thisRow.querySelector(".cancelRatesBtn");
  cancelBtn.disabled = false;
  if (
    editBtn.innerHTML.trim() === '<i class="fa-solid fa-pen-to-square"></i>Edit'
  ) {
    formControl.forEach((element) => {
      OriginalRatesValues[element.name] = element.value;
    });

    entrancePrice.removeAttribute("readOnly");
    entrancePrice.style.setProperty(
      "border",
      "1px solid rgba(253, 10, 10, 1)",
      "important"
    );

    formSelect.forEach((element) => {
      OriginalRatesValues[element.name] = element.value;
      element.disabled = true;
    });

    availability.disabled = false;
    // console.log(availability.disabled);
    availability.style.setProperty(
      "border",
      "1px solid rgba(253, 10, 10, 1)",
      "important"
    );

    editBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>Save';
    cancelBtn.innerHTML = '<i class="fa-solid fa-ban"></i>Cancel';
  } else {
    const ratesData = {
      id: thisRow.querySelector(".entranceRateID").value,
      timeRangeID: thisRow.querySelector(".timeRangeID").value,
      tourType: thisRow.querySelector(".tourType").value,
      time: thisRow.querySelector(".timeRange").value,
      visitorType: thisRow.querySelector(".visitorType").value,
      price: thisRow.querySelector(".entrancePrice").value,
      availability: thisRow.querySelector(".availability").value,
    };
    // console.log(ratesData.availability);
    fetch("../../../Function/Admin/Services/updateEntranceRates.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(ratesData),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Network error");
        return response.json();
      })
      .then((response) => {
        if (response.success) {
          formControl.forEach((element) => {
            element.setAttribute("readOnly", true);
            element.style.setProperty(
              "border",
              "1px solid rgb(223, 226, 230)",
              "important"
            );
          });

          formSelect.forEach((element) => {
            element.disabled = true;
            element.style.setProperty(
              "border",
              "1px solid rgb(223, 226, 230)",
              "important"
            );
          });

          editBtn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>Edit';
          cancelBtn.innerHTML = '<i class="fa-solid fa-delete-left"></i>Cancel';
          cancelBtn.disabled = true;
          Swal.fire({
            title: "Success",
            text: `${response.message}`,
            icon: "success",
          });
        } else {
          alert("Error saving: " + response.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Something went wrong.");
      });
  }
}
function cancelEditRates(cancelBtn) {
  const thisRow = cancelBtn.closest(".ratesdata");
  const formControl = thisRow.querySelectorAll(".form-control");
  const formSelect = thisRow.querySelectorAll(".form-select");
  const editBtn = thisRow.querySelector(".editRatesBtn");
  // const cancelBtn = thisRow.querySelectorAll('.cancelResortService');
  // const OriginalRatesValues = {};
  // cancelBtn.forEach(btn => btn.disabled = false);

  formControl.forEach((element) => {
    if (element.type === "file") {
      element.value = "";
    } else {
      element.value = OriginalRatesValues[element.name];
    }
    element.setAttribute("readOnly", true);
    element.style.setProperty(
      "border",
      "1px solid rgb(223, 226, 230)",
      "important"
    );
  });

  formSelect.forEach((element) => {
    if (element.type === "file") {
      element.value = "";
    } else {
      element.value = OriginalRatesValues[element.name];
    }
    element.disabled = true;
    element.style.setProperty(
      "border",
      "1px solid rgb(223, 226, 230)",
      "important"
    );
  });

  editBtn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>Edit';
  cancelBtn.innerHTML = '<i class="fa-solid fa-delete-left"></i>Cancel';
  cancelBtn.disabled = true;
}
