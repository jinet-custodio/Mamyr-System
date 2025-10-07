const originalResortValues = {};

function editResortService(editBtn) {
  const thisRow = editBtn.closest(".resortdata");
  const thisRowForm = thisRow.querySelectorAll(".form-control");
  const cancelBtn = thisRow.querySelector(".cancelBtn");
  const availabilityForm = thisRow.querySelectorAll(".resortAvailability");
  const resortServiceImage = thisRow.querySelectorAll(".resortServiceImage");
  const editImageBtn = thisRow.querySelectorAll(".editImageBtn");
  cancelBtn.disabled = false;
  if (
    editBtn.innerHTML.trim() === '<i class="fa-solid fa-pen-to-square"></i>Edit'
  ) {
    thisRowForm.forEach((element) => {
      element.removeAttribute("readOnly");
      element.style.setProperty("border", "1px solid red", "important");
      originalResortValues[element.name] = element.value;
    });
    availabilityForm.forEach((element) => {
      originalResortValues[element.name] = element.value;
      element.disabled = false;
      element.style.setProperty("border", "1px solid red", "important");
    });
    // console.log(originalResortValues);

    editImageBtn.forEach((btn) => (btn.disabled = false));

    editImageBtn.forEach((btn, index) => {
      btn.addEventListener("click", function () {
        const fileImage = thisRow.querySelectorAll(".resortServiceImagePicker")[
          index
        ];
        const textImage = resortServiceImage[index];

        // textImage.style.display = 'none';
        // fileImage.hidden = false;
        fileImage.click();

        fileImage.addEventListener("change", function () {
          if (fileImage.files.length > 0) {
            const fileName = fileImage.files["0"].name;
            textImage.value = fileName;
          }
        });
      });
    });

    editBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i>Save';
    cancelBtn.innerHTML = '<i class="fa-solid fa-ban"></i>Cancel';
  } else {
    const resortData = {
      id: thisRow.querySelector(".resortServiceID").value,
      name: thisRow.querySelector(".resortServiceName").value,
      price: thisRow.querySelector(".resortServicePrice").value,
      capacity: thisRow.querySelector(".resortServiceCapacity").value,
      maxCapacity: thisRow.querySelector(".resortServiceMaxCapacity").value,
      duration: thisRow.querySelector(".resortServiceDuration").value,
      description: thisRow.querySelector("textarea").value,
      // image: thisRow.querySelector(".resortServiceImage").value,
      // imageData: thisRow.querySelector(".resortServiceImagePicker").value,
      availability: thisRow.querySelector(".resortAvailability").value,
    };
    // console.log(resortData['imageData']);
    fetch("../../../Function/Admin/Services/updateResortServices.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify(resortData),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Network error");
        return response.json();
      })
      .then((response) => {
        if (response.success) {
          thisRowForm.forEach((element) => {
            element.setAttribute("readOnly", true);
            element.style.setProperty(
              "border",
              "1px solid rgb(223, 226, 230)",
              "important"
            );
          });

          availabilityForm.forEach((element) => {
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
function cancelResortService(cancelBtn) {
  const thisRow = cancelBtn.closest(".resortdata");
  const thisRowForm = thisRow.querySelectorAll(".form-control");
  const editBtn = thisRow.querySelector(".editBtn");
  const availabilityForm = thisRow.querySelectorAll(".resortAvailability");
  // const resortServiceImage = thisRow.querySelectorAll(".resortServiceImage");
  // const editImageBtn = thisRow.querySelectorAll(".editImageBtn");
  // const cancelBtn = thisRow.querySelectorAll('.cancelResortService');
  // const originalResortValues = {};
  // cancelBtn.forEach(btn => btn.disabled = false);

  thisRowForm.forEach((element) => {
    if (element.type === "file") {
      element.value = "";
    } else {
      element.value = originalResortValues[element.name];
    }
    element.setAttribute("readOnly", true);
    element.style.setProperty(
      "border",
      "1px solid rgb(223, 226, 230)",
      "important"
    );
  });

  availabilityForm.forEach((element) => {
    if (element.type === "file") {
      element.value = "";
    } else {
      element.value = originalResortValues[element.name];
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
