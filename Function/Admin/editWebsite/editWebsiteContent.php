<?php
require '../../../Config/dbcon.php';
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['sectionName'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input'
        ]);
        exit;
    }

    $sectionName = $data['sectionName'];
    unset($data['sectionName']);

    foreach ($data as $title => $content) {
        $stmt = $conn->prepare("UPDATE websitecontents SET content = ?, lastUpdated = NOW() WHERE sectionName = ? AND title = ?");
        $stmt->bind_param("sss", $content, $sectionName, $title);
        $stmt->execute();
    }
 
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Content updated successfully'
    ]);
    exit;
}

// Handle image updates (multipart/form-data)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wcImageID'], $_POST['altText'])) {
    $wcImageID = intval($_POST['wcImageID']);
    $altText = trim($_POST['altText']);

    // Update alt text
    $stmt = $conn->prepare("UPDATE websitecontentimages SET altText = ?, uploadedAt = NOW() WHERE WCImageID = ?");
    $stmt->bind_param("si", $altText, $wcImageID);
    $stmt->execute();

    $folder = $_POST['folder'] ?? 'landingPage'; // fallback default
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Image upload error: ' . $_FILES['image']['error']]);
        exit;
    }
        $targetDir = "../../../Assets/Images/" . $folder;
        $targetPath = $targetDir . "/" . $filename;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $targetDir = "../../../Assets/Images/" . $folder;
        $targetPath = $targetDir . "/" . $filename;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("UPDATE websitecontentimages SET imageData = ?, uploadedAt = NOW() WHERE WCImageID = ?");
            if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
            exit;
            }
            $stmt->bind_param("si", $filename, $wcImageID);
            $stmt->execute();
            if (!$stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database execute failed']);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Image updated successfully.', 'path_returned' => $targetPath, ]);
    exit;
}