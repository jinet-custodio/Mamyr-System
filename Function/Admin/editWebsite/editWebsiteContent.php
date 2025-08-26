<?php
require '../../../Config/dbcon.php';



// === Handle text content updates (JSON) ===
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents("php://input"), true);


    if (!$data || !isset($data['sectionName'])) {
        http_response_code(400);
        echo "Invalid input";
        exit;
    }

    $sectionName = $data['sectionName'];
    unset($data['sectionName']);

    foreach ($data as $title => $content) {
        $stmt = $conn->prepare("UPDATE websiteContents SET content = ?, lastUpdated = NOW() WHERE sectionName = ? AND title = ?");
        $stmt->bind_param("sss", $content, $sectionName, $title);
        $stmt->execute();
    }
    exit;
}

// Handle image updates (multipart/form-data)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wcImageID'], $_POST['altText'])) {
    $wcImageID = intval($_POST['wcImageID']);
    $altText = trim($_POST['altText']);

    // Update alt text
    $stmt = $conn->prepare("UPDATE websiteContentImages SET altText = ?, uploadedAt = NOW() WHERE WCImageID = ?");
    $stmt->bind_param("si", $altText, $wcImageID);
    $stmt->execute();

    $folder = $_POST['folder'] ?? 'landingPage'; // fallback default
    $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folder); // sanitize folder name

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $targetDir = "../../../Assets/Images/" . $folder;
        $targetPath = $targetDir . "/" . $filename;
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("UPDATE websiteContentImages SET imageData = ?, uploadedAt = NOW() WHERE WCImageID = ?");
            $stmt->bind_param("si", $filename, $wcImageID);
            $stmt->execute();
            $stmt->execute();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Image updated successfully']);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
