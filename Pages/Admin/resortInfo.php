<?php
error_reporting(0);
ini_set('display_errors', 0);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
$baseURL = '../..';
//for edit website, this will enable edit mode from the iframe
$editMode = isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true;

$contentMap = [];
$getContent = $conn->prepare("SELECT * FROM resortinfo");
$getContent->execute();
$contentResult = $getContent->get_result();

while ($row = $contentResult->fetch_assoc()) {

    $cleanTitle = $row['resortInfoName'];
    if (isset($contentMap[$cleanTitle])) {
        $cleanTitle .= '_' . $row['resortInfoName'];
    }

    // Assign the content
    $contentMap[$cleanTitle] = $row['resortInfoDetail'];
}

$fieldsToShow = [
    ['key' => 'DisplayName', 'label' => 'Display Name'],
    ['key' => 'FullName', 'label' => 'Full Resort Name'],
    ['key' => 'ShortDesc', 'label' => 'Short Description'],
    ['key' => 'ShortDesc2', 'label' => 'Second Short Description'],
    ['key' => 'ContactNum', 'label' => 'Contact Number'],
    ['key' => 'Email', 'label' => 'Contact Email'],
    ['key' => 'Address', 'label' => 'Complete Address'],
    ['key' => 'FBLink', 'label' => 'Facebook Link'],
    ['key' => 'Owner Full Name', 'label' => 'Owner Full Name'],
    ['key' => 'gcashNumber', 'label' => 'Gcash Number and Name'],
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Resort Information</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @font-face {
            font-family: "Poppins";
            src: url(../../Assets/Fonts/Poppins/Poppins-Regular.ttf);
        }

        body {
            font-family: "Poppins";
        }

        .form-control {
            border: 1px solid red;
        }

        #saveChangesBtn {
            margin-left: 80vw;
            font-size: 1.8vw !important;
            position: fixed;
        }
    </style>
</head>

<body>
    <?php if ($editMode): ?>
        <button id="saveChangesBtn" class="btn btn-success">Save Changes</button>
    <?php endif; ?>

    <div class="container my-4">
        <h1 class="text-center mb-5 fw-bold">Resort Information</h1>

        <div class="row g-4">
            <?php foreach ($fieldsToShow as $fieldItem): ?>
                <?php
                $key = $fieldItem['key'];
                $label = $fieldItem['label'];
                if (!isset($contentMap[$key])) continue;
                $value = $contentMap[$key];
                ?>

                <div class="col-12 col-md-6">
                    <div class="p-3 border rounded shadow-sm bg-light">

                        <!-- Custom Label -->
                        <label class="form-label fw-bold">
                            <?= htmlspecialchars($label) ?>
                        </label>

                        <?php if ($editMode): ?>

                            <?php if (strlen($value) > 80): ?>
                                <textarea
                                    class="editable-input form-control"
                                    rows="4"
                                    data-title="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></textarea>
                            <?php else: ?>
                                <input
                                    type="text"
                                    class="editable-input form-control"
                                    data-title="<?= htmlspecialchars($key) ?>"
                                    value="<?= htmlspecialchars($value) ?>">
                            <?php endif; ?>

                        <?php else: ?>

                            <p class="mt-2">
                                <?= nl2br(htmlspecialchars($value)) ?>
                            </p>

                        <?php endif; ?>

                    </div>
                </div>

            <?php endforeach; ?>

        </div>
    </div>


    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("saveChangesBtn")?.addEventListener("click", function() {

            // Collect all editable inputs/textareas
            const inputs = document.querySelectorAll(".editable-input");

            // Build an object to send as JSON
            let dataToSend = {};

            inputs.forEach(input => {
                const key = input.getAttribute("data-title");
                const value = input.value;
                dataToSend[key] = value;
            });

            // Send to PHP using fetch
            fetch("../../Function/Admin/editWebsite/editResortInfo.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(dataToSend)
                })
                .then(response => response.text())
                .then(result => {
                    console.log(result);
                    alert("Changes saved successfully!");
                })
                .catch(err => {
                    console.error(err);
                    alert("Error saving changes.");
                });

        });
    </script>

</body>

</html>