export function initWebsiteEditor(sectionName, endpointUrl) {
  document.addEventListener("DOMContentLoaded", () => {
    const saveBtn = document.getElementById("saveChangesBtn");

    saveBtn?.addEventListener("click", () => {
      saveTextContent();

      const editableImages = document.querySelectorAll(".editable-img");

      if (editableImages.length === 0) {
        console.log("No editable images found — skipping image save.");
        return;
      }

      // Check if any image has a file selected or changed alt text
      const hasModifiedImages = Array.from(editableImages).some(
        (img) => img.fileObject || img.dataset.alttextChanged === "true"
      );

      if (hasModifiedImages) {
        saveEditableImages();
      } else {
        console.log("No image changes detected — skipping image save.");
      }
    });

    function saveTextContent() {
      const inputs = document.querySelectorAll(".editable-input");
      const data = { sectionName };

      inputs.forEach((input) => {
        const title = input.getAttribute("data-title");
        const value = input.value;
        data[title] = value;
      });

      fetch(endpointUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      })
        .then((res) => res.text())
        .then((text) => {
          try {
            const json = JSON.parse(text);
            return json;
          } catch (e) {
            console.error("Server response was not valid JSON:", text);
            throw e;
          }
        })
        .then((response) => {
          if (response.success) {
            Swal.fire({
              icon: "success",
              title: "Content Updated!",
              text: "Text content has been successfully updated. Title was ",
              timer: 2000,
              showConfirmButton: false,
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Update Failed",
              text: "Failed to update text content: " + response.message,
            });
          }
        })
        .catch((err) => {
          console.error("Error saving content:", err);
          Swal.fire({
            icon: "error",
            title: "An error occurred!",
            text: "Something went wrong while saving the content.",
          });
        });
    }

    function saveEditableImages() {
      const editableImages = document.querySelectorAll(".editable-img");

      editableImages.forEach((img) => {
        const wcImageID = img.dataset.wcimageid;
        const altText = img.dataset.alttext;
        const folder = img.dataset.folder || "";
        const file = img.fileObject || null;
        const altTextChanged = img.dataset.alttextChanged === "true";

        if (!wcImageID || (!file && !altTextChanged)) {
          return; // skip if no actual change
        }

        const formData = new FormData();
        formData.append("wcImageID", wcImageID);
        formData.append("altText", altText);
        formData.append("folder", folder);

        if (file) {
          formData.append("image", file);
        }

        fetch(endpointUrl, {
          method: "POST",
          body: formData,
        })
          .then((res) => res.json())
          .then((response) => {
            console.log("Full Response:", response);
            if (response.success) {
              // Reset change tracking
              img.dataset.alttextChanged = "false";
              delete img.fileObject;

              setTimeout(() => {
                Swal.fire({
                  icon: "success",
                  title: "Image Updated!",
                  text: `Image ${altText} has been updated`,
                  timer: 2000,
                  showConfirmButton: false,
                });
              }, 3000);
            } else {
              Swal.fire({
                icon: "error",
                title: `Update Failed for Image ${wcImageID}`,
                text: `Failed to update image ${wcImageID}: ${response.message}`,
              });
            }
          })
          .catch((err) => {
            console.error(`Image update failed for ${wcImageID}:`, err);
            Swal.fire({
              icon: "error",
              title: "An error occurred!",
              text: `Something went wrong while updating the image ${wcImageID}.`,
            });
          });
      });
    }

    const altTextInputs = document.querySelectorAll(".altTextInput");
    altTextInputs.forEach((input) => {
      input.addEventListener("input", () => {
        const imgID = input.dataset.imgId;
        const newAlt = input.value;
        const img = document.querySelector(
          `.editable-img[data-wcimageid="${imgID}"]`
        );
        if (img) {
          img.dataset.alttext = newAlt;
          img.dataset.alttextChanged = "true";
        }
      });
    });
  });
}
