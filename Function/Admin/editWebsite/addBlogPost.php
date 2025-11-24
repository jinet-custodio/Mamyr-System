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

    $result = mysqli_query(
        $conn,
        "SELECT title FROM websitecontent 
         WHERE sectionName='Blog' 
         AND title LIKE 'BlogPost%'"
    );

    $highest = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        if (preg_match('/^BlogPost(\d+)-/', $row['title'], $matches)) {
            $num = intval($matches[1]);
            if ($num > $highest) $highest = $num;
        }
    }

    $nextPostNumber = $highest + 1;
    $baseTitle = "BlogPost{$nextPostNumber}";

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

    $lastContentIDResult = mysqli_query(
        $conn,
        "SELECT MAX(contentID) as lastID FROM websitecontent"
    );
    $lastContentID = mysqli_fetch_assoc($lastContentIDResult)['lastID'];

    $uploadedImages = [];

    if (!empty($_FILES['eventImages']) && count($_FILES['eventImages']['name']) > 0) {

        $uploadDir = "../../../Assets/Images/blogposts/";

        for ($i = 0; $i < count($_FILES['eventImages']['name']); $i++) {

            if ($_FILES['eventImages']['error'][$i] === UPLOAD_ERR_OK) {

                $ext = pathinfo($_FILES['eventImages']['name'][$i], PATHINFO_EXTENSION);
                $fileName = "blogpost{$nextPostNumber}_{$i}_" . time() . "." . $ext;
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['eventImages']['tmp_name'][$i], $targetPath)) {

                    mysqli_query($conn, "
                        INSERT INTO websitecontentimage (contentID, imageData, altText, imageOrder)
                        VALUES ('$lastContentID', '$fileName', '$eventHeader', " . ($i + 1) . ")
                    ");

                    $uploadedImages[] = $fileName;
                }
            }
        }
    }
    echo json_encode([
        'success' => true,
        'message' => 'Blog post added successfully.',
        'postNumber' => $nextPostNumber,
        'images' => $uploadedImages
    ]);

    exit;
}
