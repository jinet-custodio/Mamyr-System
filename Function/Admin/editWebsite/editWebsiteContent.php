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

// TEXT UPDATES (JSON)
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
        $stmt->close();
    }

    // Audit logging
    if ($adminID !== null) {
        $logStmt = $conn->prepare("INSERT INTO auditlog (adminID, action, target, logDetails) VALUES (?, ?, ?, ?)");
        if ($logStmt) {
            $logStmt->bind_param("isss", $adminID, $action, $target, $logDetails);
            if (!$logStmt->execute()) {
                error_log("Audit log execute failed: " . $logStmt->error);
            }
            $logStmt->close();
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

// IMAGE UPDATES (Multipart)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wcImageID'], $_POST['altText'])) {
    header('Content-Type: application/json');

    $wcImageID = intval($_POST['wcImageID']);
    $altText = trim($_POST['altText']);
    $folder = isset($_POST['folder']) ? trim($_POST['folder']) : '';

    $altTextUpdated = false;
    $imageUpdated = false;
    $errors = [];

    // Update alt text
    $stmt = $conn->prepare("UPDATE websitecontentimage SET altText = ?, uploadedAt = NOW() WHERE WCImageID = ?");
    if ($stmt) {
        $stmt->bind_param("si", $altText, $wcImageID);
        if ($stmt->execute()) {
            $altTextUpdated = true;
        } else {
            $errors[] = "Failed to update alt text: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errors[] = "Failed to prepare alt text update: " . $conn->error;
    }

    // Update image file (if uploaded)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $targetDir = "../../../Assets/Images/" . $folder;
        $targetPath = $targetDir . "/" . $filename;

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                $errors[] = "Failed to create image folder: $targetDir";
            }
        }

        if (empty($errors) && move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Save image filename to DB
            $stmt = $conn->prepare("UPDATE websitecontentimage SET imageData = ?, uploadedAt = NOW() WHERE WCImageID = ?");
            if ($stmt) {
                $stmt->bind_param("si", $filename, $wcImageID);
                if ($stmt->execute()) {
                    $imageUpdated = true;
                } else {
                    $errors[] = "Failed to update image filename: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Failed to prepare image update: " . $conn->error;
            }
        } else if (empty($errors)) {
            $errors[] = "Failed to move uploaded file to: $targetPath";
        }
    }

    // Response
    if (!empty($errors)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => implode("; ", $errors)
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => ($imageUpdated ? "Image" : "") .
                ($imageUpdated && $altTextUpdated ? " and " : "") .
                ($altTextUpdated ? "alt text" : "") .
                " updated successfully.",
            'imageUpdated' => $imageUpdated,
            'altTextUpdated' => $altTextUpdated
        ]);
    }
    exit;
}
