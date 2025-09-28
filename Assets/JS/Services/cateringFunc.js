const originalMenuValues = {};

function editMenuItem(editBtn) {
  const thisRow = editBtn.closest("#menuData");
  const formControl = thisRow.querySelectorAll(".form-control");
  const formSelect = thisRow.querySelectorAll(".form-select");
  const cancelBtn = thisRow.querySelector(".cancelEditItem");

  cancelBtn.disabled = false;

  if (
    editBtn.innerHTML.trim() === '<i class="fa-solid fa-pen-to-square"></i>Edit'
  ) {
    formControl.forEach((element) => {
      element.removeAttribute("readOnly");
      element.style.setProperty("border", "1px solid red", "important");
      originalMenuValues[element.name] = element.value;
    });

    formSelect.forEach((select) => {
      originalMenuValues[select.name] = select.value;
      select.disabled = false;
      select.style.setProperty("border", "1px solid red", "important");
    });

    editBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>Save';
    cancelBtn.innerHTML = '<i class="fa-solid fa-ban"></i>Cancel';
  } else {
    const foodData = {
      id: thisRow.querySelector(".foodID").value,
      name: thisRow.querySelector(".foodName").value,
      // price: thisRow.querySelector(".foodPrice").value,
      category: thisRow.querySelector(".foodCategory").value,
      availability: thisRow.querySelector(".foodAvailability").value,
    };

    fetch("../../../Function/Admin/Services/updateMenuItem.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(foodData),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network Error");
        }
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

function cancelEditItem(cancelBtn) {
  const thisRow = cancelBtn.closest("#menuData");
  const formControl = thisRow.querySelectorAll(".form-control");
  const formSelect = thisRow.querySelectorAll(".form-select");
  const editBtn = thisRow.querySelector(".editMenuItem");

  formControl.forEach((element) => {
    element.setAttribute("readOnly", true);
    element.style.setProperty(
      "border",
      "1px solid rgb(223, 226, 230)",
      "important"
    );
    element.value = originalMenuValues[element.name];
  });

  formSelect.forEach((select) => {
    select.value = originalMenuValues[select.name];
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
}
