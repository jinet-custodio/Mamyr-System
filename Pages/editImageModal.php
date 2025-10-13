<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <!-- Edit Image Modal -->
    <div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h5 class="modal-title" id="editImageModalLabel">Edit Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImagePreview" src="" alt="" class="img-thumbnail mb-3">

                    <input type="file" id="modalImageUpload" class="form-control mb-2">

                    <input type="text" id="modalAltText" class="form-control mb-3" placeholder="Alt text">
                    <div class="d-flex justify-content-around">
                        <button id="selectImageBtn" class="btn btn-primary me-2">Select Image</button>
                        <button id="chooseImageBtn" class="btn btn-success me-2" data-bs-dismiss="modal">Confirm</button>
                        <button id="cancelImageBtn" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let activeImageElement = null;
            let activeWCImageID = null;
            let originalImageSrc = '';
            let originalAltText = '';

            const modalImagePreview = document.getElementById('modalImagePreview');
            const modalAltText = document.getElementById('modalAltText');
            const fileInput = document.getElementById('modalImageUpload');

            // Open modal and store original image + alt text
            document.querySelectorAll('.editable-img').forEach(img => {
                img.addEventListener('click', function() {
                    activeImageElement = this;
                    activeWCImageID = this.dataset.wcimageid;

                    // Save original state
                    originalImageSrc = this.src;
                    originalAltText = this.alt;

                    // Load modal preview
                    modalImagePreview.src = originalImageSrc;
                    modalAltText.value = originalAltText;

                    // Clear previous file input
                    fileInput.value = '';
                });
            });

            // Trigger file input from a custom button
            document.getElementById('selectImageBtn').addEventListener('click', function(e) {
                e.preventDefault(); // prevent any accidental form submission
                fileInput.click();
            });

            // Show live preview when file is selected
            fileInput.addEventListener('change', function() {
                const newFile = fileInput.files[0];
                if (newFile) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        modalImagePreview.src = e.target.result;
                        activeImageElement.setAttribute('data-tempfile', newFile.name);
                        activeImageElement.fileObject = newFile; // Store file for saving
                    };
                    reader.readAsDataURL(newFile);
                }
            });

            // Confirm button - apply changes to active image
            document.getElementById('chooseImageBtn').addEventListener('click', function() {
                if (!activeImageElement) return;

                const newAlt = modalAltText.value;
                const newFile = fileInput.files[0];

                // Apply alt text
                activeImageElement.alt = newAlt;
                activeImageElement.setAttribute('data-alttext', newAlt);

                // Image preview already handled during file input change
                // File is already stored as `fileObject` for future upload
            });

            // Cancel button - revert to original image and alt text
            document.getElementById('cancelImageBtn').addEventListener('click', function() {
                if (!activeImageElement) return;

                // Revert values
                activeImageElement.src = originalImageSrc;
                activeImageElement.alt = originalAltText;
                activeImageElement.setAttribute('data-alttext', originalAltText);

                // Clean up
                delete activeImageElement.fileObject;
                activeImageElement.removeAttribute('data-tempfile');
            });
        });
    </script>


</body>

</html>