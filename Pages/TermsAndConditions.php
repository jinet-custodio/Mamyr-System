<?php
require '../Config/dbcon.php';

$type = $_GET['type'] ?? 'customer';
if ($type === 'partner') {
    $colTitle = "busPartnerTerms";
    $pageTitle = 'Business Partner Terms & Conditions';
} else {
    $colTitle = "CustomerTerms";
    $pageTitle = 'Customer Terms & Conditions';
}


$sectionName = 'TermsAndConditions';

$getContent = $conn->prepare("SELECT title, content FROM websitecontent WHERE sectionName = ? AND title = ?");
$getContent->bind_param("ss", $sectionName, $colTitle);
$getContent->execute();
$contentResult = $getContent->get_result();

$fullText = "";
while ($row = $contentResult->fetch_assoc()) {
    $fullText .= $row['content'] . "\n\n"; // Keep spacing between sections
}
$fullText = trim($fullText);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <title>Terms and Conditions</title>
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">

    <style>
        body {
            background: #f8f9fa;
        }

        .content-card {
            border-radius: 12px;
            padding: 30px;
            background: #ffffff;
            box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.08);
        }

        .content-block {
            margin-bottom: 20px;
            font-size: 1.02rem;
            line-height: 1.75;
        }

        h1 {
            font-weight: 600;
            margin-bottom: 20px;
        }
    </style>

</head>

<body>

    <div class="container py-5">
        <div class="col-lg-10 mx-auto">
            <div class="editor-card border-danger d-grid">

                <h1 class="text-primary mb-4">Edit <?= $pageTitle ?></h1>

                <textarea id="termsTextarea" rows="20" cols="20" class="editable-input" data-title="<?= $colTitle ?>"><?= htmlspecialchars($fullText) ?></textarea>

                <button class="btn btn-success mt-3 w-50 mx-auto" id="saveChangesBtn">Save Changes</button>
                <div id="statusMessage" class="mt-3"></div>

            </div>
        </div>
    </div>

    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module">
        import {
            initWebsiteEditor
        } from '../Assets/JS/EditWebsite/editWebsiteContent.js';

        initWebsiteEditor("<?= $sectionName ?>", "../Function/Admin/editWebsite/editWebsiteContent.php");
    </script>
</body>

</html>