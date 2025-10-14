export function initWebsiteEditor(sectionName, endpointUrl) {
    document.addEventListener('DOMContentLoaded', () => {
        const saveBtn = document.getElementById('saveChangesBtn');
        saveBtn?.addEventListener('click', () => {
            saveTextContent();
            saveEditableImages();
        });

        function saveTextContent() {
            const inputs = document.querySelectorAll('.editable-input');
            const data = { sectionName };

            inputs.forEach(input => {
                const title = input.getAttribute('data-title');
                const value = input.value;
                data[title] = value;
            });

            fetch(endpointUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(res => res.text())
                .then(text => {
                    try {
                        const json = JSON.parse(text);
                        return json;
                    } catch (e) {
                        console.error("Server response was not valid JSON:", text);
                        throw e;
                    }
                })
                .then(response => {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Content Updated!',
                            text: 'Text content has been successfully updated.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Failed to update text content: ' + response.message,
                        });
                    }
                })
                .catch(err => {
                    console.error('Error saving content:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'An error occurred!',
                        text: 'Something went wrong while saving the content.',
                    });
                });
        }

        function saveEditableImages() {
            const editableImages = document.querySelectorAll('.editable-img');

            editableImages.forEach(img => {
                const wcImageID = img.dataset.wcimageid;
                const altText = img.dataset.alttext;
                const folder = img.dataset.folder || '';
                const file = img.fileObject || null;

                if (!wcImageID || (!file && !altText)) {
                    console.log("No data");
                    return;
                }

                const formData = new FormData();
                formData.append('wcImageID', wcImageID);
                formData.append('altText', altText);
                formData.append('folder', folder);

                if (file) {
                    formData.append('image', file);
                }

                fetch(endpointUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(response => {
                        console.log("Full Response:", response);
                        if (response.success) {
                            setTimeout(() => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Image Updated!',
                                    text: `Image ${altText} has been updated`,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }, 3000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: `Update Failed for Image ${wcImageID}`,
                                text: `Failed to update image ${wcImageID}: ${response.message}`,
                            });
                        }
                    })
                    .catch(err => {
                        console.error(`Image update failed for ${wcImageID}:`, err);
                        Swal.fire({
                            icon: 'error',
                            title: 'An error occurred!',
                            text: `Something went wrong while updating the image ${wcImageID}.`,
                        });
                    });
            });
        }
    });
}
