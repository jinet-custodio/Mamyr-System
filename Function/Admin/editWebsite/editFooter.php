<?php
require '../../../Config/dbcon.php';

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');

if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['sectionName'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    $sectionName = $data['sectionName'];
    unset($data['sectionName']);

    foreach ($data as $name => $detail) {
        $stmt = $conn->prepare("UPDATE resortinfo SET resortInfoDetail = ? WHERE resortInfoTitle = ? AND resortInfoName = ?");
        $stmt->bind_param("sss", $detail, $sectionName, $name);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Footer text updated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resortInfoID'], $_POST['altText'])) {
    $resortInfoID = intval($_POST['resortInfoID']);
    $altText = trim($_POST['altText']);
    $folder = trim($_POST['folder'] ?? '');
    $targetDir = "../../../Assets/Images/";

    // Update alt text in database
    $stmt = $conn->prepare("UPDATE resortinfo SET resortInfoDetail = ? WHERE resortInfoID = ?");
    $stmt->bind_param("si", $altText, $resortInfoID);
    $stmt->execute();
    $stmt->close();

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Get current filename from DB
        $stmt = $conn->prepare("SELECT resortInfoName FROM resortinfo WHERE resortInfoID = ?");
        $stmt->bind_param("i", $resortInfoID);
        $stmt->execute();
        $stmt->bind_result($currentFileName);
        $stmt->fetch();
        $stmt->close();

        if (empty($currentFileName)) {
            echo json_encode(['success' => false, 'message' => 'No filename found for this ID']);
            exit;
        }

        // Ensure target folder exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Overwrite the image file
        $targetPath = $targetDir . '/' . $currentFileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            echo json_encode([
                'success' => true,
                'message' => 'Image replaced successfully',
                'path_returned' => $targetPath
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
    }

    // No image uploaded — only alt text was updated
    echo json_encode(['success' => true, 'message' => 'Alt text updated (no image uploaded)']);
    exit;
}

// Default fallback for bad requests
echo json_encode(['success' => false, 'message' => 'No valid action performed']);
exit;
