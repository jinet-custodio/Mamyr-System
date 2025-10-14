<?php
require '../../../Config/dbcon.php';
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;
$adminID = null;

if ($userID !== null) {
    $stmt = $conn->prepare("SELECT adminID FROM admin WHERE userID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($adminID);
        $stmt->fetch();
        $stmt->close();
    } else {
        error_log("Failed to prepare adminID query: " . $conn->error);
    }
}

$action = "Update";
$logDetails = "Edited website contents";

// TEXT UPDATES - via JSON
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['sectionName'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input (missing sectionName)'
        ]);
        exit;
    }

    $sectionName = $data['sectionName'];
    unset($data['sectionName']);
    $target = $sectionName . " Page";

    foreach ($data as $title => $content) {
        $stmt = $conn->prepare("UPDATE websitecontent SET content = ?, lastUpdated = NOW() WHERE sectionName = ? AND title = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            continue;
        }
        $stmt->bind_param("sss", $content, $sectionName, $title);
        if (!$stmt->execute()) {
            error_log("Execute failed for title [$title]: " . $stmt->error);
        }
    }

    // Audit logging
    if ($adminID !== null) {
        $logStmt = $conn->prepare("INSERT INTO auditlog (adminID, action, target, logDetails) VALUES (?, ?, ?, ?)");
        if ($logStmt) {
            $logStmt->bind_param("isss", $adminID, $action, $target, $logDetails);
            if (!$logStmt->execute()) {
                error_log("Audit log execute failed: " . $logStmt->error);
            }
        } else {
            error_log("Audit log prepare failed: " . $conn->error);
        }
    }

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Content updated successfully'
    ]);
    exit;
}

// IMAGE UPDATES - via multipart/form-data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wcImageID'], $_POST['altText'])) {
    $wcImageID = intval($_POST['wcImageID']);
    $altText = trim($_POST['altText']);
    $folder = $_POST['folder'];

    // Update alt text first
    $stmt = $conn->prepare("UPDATE websitecontentimage SET altText = ?, uploadedAt = NOW() WHERE WCImageID = ?");
    $stmt->bind_param("si", $altText, $wcImageID);
    $stmt->execute();

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $targetDir = "../../../Assets/Images/" . $folder;
        $targetPath = $targetDir . "/" . $filename;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("UPDATE websitecontentimage SET imageData = ?, uploadedAt = NOW() WHERE WCImageID = ?");
            $stmt->bind_param("si", $filename, $wcImageID);
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'Image and alt text updated successfully.',
                'path_returned' => $targetPath
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
    }

    // Only alt text was updated
    echo json_encode([
        'success' => true,
        'message' => 'Alt text updated (no image uploaded).'
    ]);
    exit;
}
