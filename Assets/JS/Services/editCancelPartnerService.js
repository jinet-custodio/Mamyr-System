const originalValues = {};

function editServiceInfo(edit) {
  const modal = edit.closest(".modal");
  const formControl = modal.querySelectorAll(".form-control");
  const select = modal.querySelector("#serviceAvailability");
  const cancelBtn = modal.querySelector(".cancel-info-button");

  if (
    edit.innerHTML.trim() === '<i class="fa-solid fa-pen-to-square"></i> Edit'
  ) {
    formControl.forEach((input) => {
      originalValues[input.name] = input.value;
      input.style.border = "1px solid red";
      input.removeAttribute("readonly");
    });
    select.style.border = "1px solid red";
    select.disabled = false;
    originalValues[select.name] = select.value;

    edit.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save';
    cancelBtn.style.display = "block";

    formControl.forEach((input) =>
      input.addEventListener("change", () => {
        input.style.border = "1px solid rgb(247, 247, 247)";
      })
    );

    select.addEventListener("change", () => {
      select.style.border = "1px solid rgb(247, 247, 247)";
    });
  } else {
    const serviceData = {
      id: modal.querySelector("#partnershipServiceID").value,
      name: modal.querySelector("#serviceName").value,
      price: modal.querySelector("#servicePrice").value,
      capacity: modal.querySelector("#serviceCapacity").value,
      duration: modal.querySelector("#serviceDuration").value,
      availability: modal.querySelector("#serviceAvailability").value,
      descriptions: Array.from(
        modal.querySelectorAll('input[name="serviceDescription"]')
      ).map((input) => input.value.trim()),
    };

    fetch(`../../../Function/Partner/updatePartnerService.php`, {
      method: "POST",
      headers: {
        "Content-type": "application/json",
      },
      body: JSON.stringify(serviceData),
    })
      .then((response) => response.json())
      .then((data) => {
        // console.log('Response from server:', data);
        if (!data.success) {
          console.log(data.message);
          Swal.fire({
            position: "center",
            timer: 1500,
            icon: "error",
            title: "Failed1",
            text:
              data.message ?? "An error occured while updating the information",
          });
        } else {
          formControl.forEach((input) => {
            input.style.border = "1px solid rgb(247, 247, 247)";
            input.setAttribute("readonly", true);
          });

          select.style.border = "1px solid rgb(247, 247, 247)";
          select.disabled = true;

          Swal.fire({
            position: "center",
            timer: 1000,
            icon: "success",
            title: "Success",
            text: data.message ?? "Service updated successfully",
            showConfirmButton: false,
            timerProgressBar: true,
            didClose: () => {
              location.reload();
            },
          });
          const serviceModal = bootstrap.Modal.getInstance(modal);
          serviceModal.hide();
        }
      })
      .catch((error) => {
        console.log(error.message);

        Swal.fire({
          position: "center",
          timer: 1500,
          icon: "error",
          title: "Failed2",
          text:
            error.message ?? "An error occured while updating the information",
        });
      });
  }
}

function canEditInfo(cancel) {
  // console.log('cancel clicked')
  const modal = cancel.closest(".modal");
  const formControl = modal.querySelectorAll(".form-control");
  const select = modal.querySelector("#serviceAvailability");
  const editBtn = modal.querySelector(".edit-info-button");

  formControl.forEach((input) => {
    input.value = originalValues[input.name];
    input.style.border = "1px solid  rgb(247, 247, 247)";
    input.setAttribute("readonly", true);
  });

  select.style.border = "1px solid  rgb(247, 247, 247)";
  select.disabled = true;
  select.value = originalValues[select.name];

  editBtn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit';
  cancel.style.display = "none";
}
