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
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventType = mysqli_real_escape_string($conn, $_POST['eventName']);
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $eventHeader = mysqli_real_escape_string($conn, $_POST['eventTitle']);
    $eventContent = mysqli_real_escape_string($conn, $_POST['eventDesc']);

    // Find next post number
    $result = mysqli_query($conn, "SELECT title FROM websitecontent WHERE sectionName='Blog' AND title LIKE 'BlogPost%'");
    $highest = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        if (preg_match('/^BlogPost(\d+)-/', $row['title'], $matches)) {
            $num = (int)$matches[1];
            if ($num > $highest) $highest = $num;
        }
    }
    $nextPostNumber = $highest + 1;
    $baseTitle = "BlogPost{$nextPostNumber}";

    // Insert blog content
    $insertQueries = [
        "INSERT INTO websitecontent (adminID, sectionName, title, content, lastUpdated)
         VALUES ('$adminID', 'Blog', '{$baseTitle}-EventType', '$eventType', NOW())",
        "INSERT INTO websitecontent (adminID, sectionName, title, content, lastUpdated)
         VALUES ('$adminID', 'Blog', '{$baseTitle}-EventDate', '$eventDate', NOW())",
        "INSERT INTO websitecontent (adminID, sectionName, title, content, lastUpdated)
         VALUES ('$adminID', 'Blog', '{$baseTitle}-EventHeader', '$eventHeader', NOW())",
        "INSERT INTO websitecontent (adminID, sectionName, title, content, lastUpdated)
         VALUES ('$adminID', 'Blog', '{$baseTitle}-Content', '$eventContent', NOW())"
    ];
    foreach ($insertQueries as $query) {
        mysqli_query($conn, $query);
    }

    // Handle Image Upload
    $imageUploaded = false;
    if (isset($_FILES['eventImage']) && $_FILES['eventImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../../Assets/Images/blogposts/";
        $fileName = "blogpost{$nextPostNumber}_" . time() . "." . pathinfo($_FILES['eventImage']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['eventImage']['tmp_name'], $targetPath)) {
            $lastContentIDResult = mysqli_query($conn, "SELECT MAX(contentID) as lastID FROM websitecontent");
            $lastContentID = mysqli_fetch_assoc($lastContentIDResult)['lastID'];

            mysqli_query($conn, "INSERT INTO websitecontentimage (contentID, imageData, altText, imageOrder)
                                 VALUES ('$lastContentID', '$fileName', '$eventHeader', 1)");
            $imageUploaded = true;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Blog post added successfully.',
        'postNumber' => $nextPostNumber,
        'image' => $imageUploaded ? $fileName : null
    ]);
    exit;
}
