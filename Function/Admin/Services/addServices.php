<?php

require '../../../Config/dbcon.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getServiceCategory($conn, $id)
{
    $getCategory = $conn->prepare('SELECT * FROM resortservicescategories WHERE categoryID = ?');
    $getCategory->bind_param('i', $id);
    if ($getCategory->execute()) {
        $result =  $getCategory->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['categoryName'];
        }
    }

    return null;
}

if (isset($_POST['addResortService'])) {
    $serviceType = 'Resort';
    $serviceName = mysqli_real_escape_string($conn, $_POST['serviceName']);
    $servicePrice = floatval(mysqli_real_escape_string($conn, $_POST['servicePrice']));
    $serviceCapacity = intval(mysqli_real_escape_string($conn, $_POST['serviceCapacity'])) ?? 0;
    $serviceMaxCapacity = intval(mysqli_real_escape_string($conn, $_POST['serviceMaxCapacity'])) ?? 0;
    $serviceDuration = mysqli_real_escape_string($conn, $_POST['serviceDuration']) ?? 0;
    $serviceDescription = mysqli_real_escape_string($conn, $_POST['serviceDesc']) ?? 'None';
    $serviceAvailability = intval(mysqli_real_escape_string($conn, $_POST['serviceAvailability']));
    $serviceCategory = intval(mysqli_real_escape_string($conn, $_POST['serviceCategory']));


    $categoryName = getServiceCategory($conn, $serviceCategory);

    if (!$categoryName) {
        error_log("Category not found for ID: $serviceCategory");
        header("Location: ../../../Pages/Admin/services.php?result=errorCategory");
        exit();
    }

    $servicePath = __DIR__ . '/../../../Assets/Images/Services/' . $categoryName . '/';
    // echo "Service path: $servicePath";
    // exit();


    if (!is_dir($servicePath)) {
        mkdir($servicePath, 0755, true);
    }

    $imageMaxSize = 64 * 1024 * 1024;
    if (isset($_FILES['serviceImage']) && $_FILES['serviceImage']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['serviceImage']['size'] <= $imageMaxSize) {
            $filePath = $_FILES['serviceImage']['tmp_name'];
            $fileName = $_FILES['serviceImage']['name'];
            $imageName = $categoryName . '_' . $fileName;
            $image = $servicePath . $imageName;
            move_uploaded_file($filePath, $image);
        } else {
            echo 'IMAGE SIZE';
            // $_SESSION['serviceFormData'] = $_POST;
            header("Location: ../../../Pages/Admin/services.php?action=imageSize&step=1");
            exit();
        }
    } else {
        echo 'IMAGE ERROR';
        // $imageError = "An error occured. Please try again.";
        // $_SESSION['serviceFormData'] = $_POST;
        header("Location: ../../../Pages/Admin/services.php?action=imageError&step=1");
        exit();
    }


    try {
        $insertServiceQuery = $conn->prepare("INSERT INTO resortAmenities(`RServiceName`, `RSprice`, `RScapacity`, `RSmaxCapacity`, `RSduration`, `RScategoryID`, `RSdescription`, `RSimageData`, `RSAvailabilityID`) VALUES(?,?,?,?,?,?,?,?,?)");
        $insertServiceQuery->bind_param(
            'sdiisissi',
            $serviceName,
            $servicePrice,
            $serviceCapacity,
            $serviceMaxCapacity,
            $serviceDuration,
            $serviceCategory,
            $serviceDescription,
            $imageName,
            $serviceAvailability
        );
        if ($insertServiceQuery->execute()) {
            $resortServiceID = $conn->insert_id;
            $insertServiceQuery->close();
            $insertIntoService = $conn->prepare("INSERT INTO services(`resortServiceID`, `serviceType`) VALUES(?,?)");
            $insertIntoService->bind_param('is', $resortServiceID, $serviceType);
            if ($insertIntoService->execute()) {
                header('Location: ../../../Pages/Admin/services.php?result=added');
                $insertIntoService->close();
            } else {
                echo 'INSERTING ERROR';
                error_log("Execution Error: " . $insertIntoService->error);
            }
        } else {
            echo 'INSERTING ERROR';
            error_log("Execution Error: " . $insertServiceQuery->error);
        }
    } catch (Exception $e) {
        echo 'INSERTING ERROR';
        error_log("Exception: " . $e->getMessage());
        header("Location: ../../../Pages/Admin/services.php?result=error");
        exit();
    }
} else {
    echo 'BUTTON ERROR';
}
