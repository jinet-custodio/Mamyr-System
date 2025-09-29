const originalPriceValues = {};

function editServicePricing(editBtn) {
  const thisRow = editBtn.closest("#service-pricing");
  const formSelect = thisRow.querySelectorAll(".form-select");
  const formControl = thisRow.querySelectorAll(".form-control");
  const cancelBtn = thisRow.querySelector(".cancelEditPricingBtn");
  cancelBtn.disabled = false;
  if (
    editBtn.innerHTML.trim() === '<i class="fa-solid fa-pen-to-square"></i>Edit'
  ) {
    formSelect.forEach((element) => {
      element.disabled = false;
      element.style.setProperty("border", "1px solid red", "important");
      originalPriceValues[element.name] = element.value;
    });

    formControl.forEach((element) => {
      element.removeAttribute("readOnly");
      element.style.setProperty("border", "1px solid red", "important");
      originalMenuValues[element.name] = element.value;
    });

    editBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>Save';
    cancelBtn.innerHTML = '<i class="fa-solid fa-ban"></i>Cancel';
  } else {
    const pricingData = {
      pricingID: thisRow.querySelector(".pricingID").value,
      pricingType: thisRow.querySelector(".pricingType").value,
      servicePrice: thisRow.querySelector(".servicePrice").value,
      chargeType: thisRow.querySelector(".chargeType").value,
      ageGroup: thisRow.querySelector(".ageGroup").value,
      notes: thisRow.querySelector(".SPNotes").value,
    };

    fetch("../../../Function/Admin/Services/updateServicePricing.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(pricingData),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Network Error");
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

          formSelect.forEach((select) => {
            select.disabled = true;
            select.style.setProperty(
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

function cancelEditServicePricing(cancelBtn) {
  const thisRow = cancelBtn.closest("#service-pricing");
  const formControl = thisRow.querySelectorAll(".form-control");
  const formSelect = thisRow.querySelectorAll(".form-select");
  const editBtn = thisRow.querySelector(".editServicePricing");
  // const cancelBtn = thisRow.querySelectorAll('.cancelResortService');
  // const OriginalRatesValues = {};
  // cancelBtn.forEach(btn => btn.disabled = false);

  formControl.forEach((element) => {
    if (element.type === "file") {
      element.value = "";
    } else {
      element.value = originalPriceValues[element.name];
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
