<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require '../../../Config/dbcon.php';
session_start();

error_reporting(E_ALL);

function getServiceCategory($conn, $id)
{
    $getCategory = $conn->prepare('SELECT * FROM resortservicescategory WHERE categoryID = ?');
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

if (isset($_POST['addResortService'])) { //*Resort Amenities
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

    $imageMaxSize = 24 * 1024 * 1024;
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


    $conn->begin_transaction();
    try {
        $insertServiceQuery = $conn->prepare("INSERT INTO resortamenity(`RServiceName`, `RSprice`, `RScapacity`, `RSmaxCapacity`, `RSduration`, `RScategoryID`, `RSdescription`, `RSimageData`, `RSAvailabilityID`) VALUES(?,?,?,?,?,?,?,?,?)");
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

            $insertIntoService = $conn->prepare("INSERT INTO service(`resortServiceID`, `serviceType`) VALUES(?,?)");
            $insertIntoService->bind_param('is', $resortServiceID, $serviceType);
            if ($insertIntoService->execute()) {
                $conn->commit();
                header('Location: ../../../Pages/Admin/services.php?result=added');
                $insertIntoService->close();
                exit();
            } else {
                $conn->rollback();
                echo 'INSERTING ERROR';
                error_log("Execution Error: " . $insertIntoService->error);
                exit();
            }
            $insertServiceQuery->close();
        } else {
            $conn->rollback();
            echo 'INSERTING ERROR';
            error_log("Execution Error: " . $insertServiceQuery->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo 'INSERTING ERROR';
        error_log("Exception: " . $e->getMessage());
        header("Location: ../../../Pages/Admin/services.php?result=error");
        exit();
    }
} elseif (isset($_POST['addResortRates'])) { //*Resort Rates
    $serviceType = 'Entrance';
    $tourType = mysqli_real_escape_string($conn, $_POST['tourType']);
    $timeRange = intval($_POST['timeRange']);
    $visitorType = mysqli_real_escape_string($conn, $_POST['visitorType']);
    $entrancePrice = floatval($_POST['entrancePrice']);


    $conn->begin_transaction();

    try {
        $insertRates = $conn->prepare("INSERT INTO entrancerate(`sessionType`, `timeRangeID`, `ERcategory`, `ERprice`) VALUES(?,?,?,?)");
        $insertRates->bind_param('sisd', $tourType, $timeRange, $visitorType, $entrancePrice);
        if ($insertRates->execute()) {
            $entranceRateID = $conn->insert_id;

            $insertIntoService = $conn->prepare("INSERT INTO service(`entranceRateID`, `serviceType`) VALUES(?,?)");
            $insertIntoService->bind_param('is', $entranceRateID, $serviceType);
            if ($insertIntoService->execute()) {
                $conn->commit();
                header('Location: ../../../Pages/Admin/services.php?result=added');
                $insertIntoService->close();
                exit();
            } else {
                $conn->rollback();
                echo 'INSERTING ERROR';
                error_log("Execution Error: " . $insertIntoService->error);
                exit();
            }
        } else {
            $conn->rollback();
            echo 'INSERTING ERROR';
            error_log("Execution Error: " . $insertIntoService->error);
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../../../Pages/Admin/services.php?result=error");
    }
} elseif (isset($_POST['saveHotelRoom'])) { //*Hotels
    $serviceType = 'Resort';
    $roomName = mysqli_real_escape_string($conn, $_POST['roomName']);
    $roomStatus = intval($_POST['roomStat']);
    $roomRate = floatval($_POST['roomRate']);
    $capacity = intval($_POST['capacity']);
    $maxCapacity = intval($_POST['maxCapacity']);
    $roomDescription = mysqli_real_escape_string($conn, $_POST['roomDescription']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $categoryID = 1;

    $categoryName = getServiceCategory($conn, $categoryID);

    $servicePath = __DIR__ . '/../../../Assets/Images/Services/' . $categoryName . '/';

    if (!is_dir($servicePath)) {
        mkdir($servicePath, 0755, true);
    }

    $imageMaxSize = 64 * 1024 * 1024;
    if (isset($_FILES['roomImage']) && $_FILES['roomImage']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['roomImage']['size'] <=  $imageMaxSize) {
            $filePath = $_FILES['roomImage']['tmp_name'];
            $fileName = $_FILES['roomImage']['name'];
            $randomNumber = rand(11, 99);
            $imageName = $categoryName . '_' . $randomNumber . '_' . $fileName;
            $image = $servicePath . $imageName;
            move_uploaded_file($filePath, $image);
        } else {
            echo 'IMAGE SIZE';
            header("Location: ../../../Pages/Admin/roomList.php?action=imageSize&step=1");
            exit();
        }
    } else {
        echo 'IMAGE ERROR';
        header("Location: ../../../Pages/Admin/roomList.php?action=imageError&step=2");
        exit();
    }

    $conn->begin_transaction();
    try {
        $insertHotel = $conn->prepare("INSERT INTO `resortamenity`(`RServiceName`, `RSprice`, `RScapacity`, `RSmaxCapacity`, `RSduration`, `RScategoryID`, `RSdescription`, `RSimageData`, `RSAvailabilityID`) VALUES (?,?,?,?,?,?,?,?,?)");
        $insertHotel->bind_param("sdiisissi", $roomName, $roomRate, $capacity, $maxCapacity, $duration, $categoryID, $roomDescription, $imageName, $roomStatus);
        if ($insertHotel->execute()) {
            $resortServiceID = $conn->insert_id;

            $insertIntoService = $conn->prepare("INSERT INTO `service`(`resortServiceID`,`serviceType`) VALUES (?,?)");
            $insertIntoService->bind_param("is", $resortServiceID, $serviceType);
            if (!$insertIntoService->execute()) {
                throw new Exception("Failed to insert in services" . $insertIntoService->error());
            }
            $conn->commit();
            header("Location: ../../../Pages/Admin/roomList.php");
            exit();
        } else {
            $conn->rollback();
            error_log("Error: " . $insertHotel->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        header("Location: ../../../Pages/Admin/roomList.php?result=error");
        exit();
    } finally {
        if (isset($insertHotel)) $insertHotel->close();
        if (isset($insertIntoService)) $insertIntoService->close();
    }
} elseif (isset($_POST['addFoodItem'])) { //* Catering Food
    $foodName = mysqli_real_escape_string($conn, $_POST['foodName']) ?? '';
    $foodCategory = strtoupper(mysqli_real_escape_string($conn, $_POST['foodCategory'])) ?? '';
    $foodAvailability = intval($_POST['foodAvailability']) ?? 1;


    if (empty($foodName) || empty($foodCategory)) {
        header('Location: ../../../Pages/Admin/services.php?page=catering&result=emptyCateringField');
    }

    $insertFoodItem = $conn->prepare("INSERT INTO `menuitem`(`foodName`, `foodCategory`, `availabilityID`) VALUES (?,?,?,?)");
    $insertFoodItem->bind_param("sdsi", $foodName, $foodCategory, $foodAvailability);
    if ($insertFoodItem->execute()) {
        header('Location: ../../../Pages/Admin/services.php?page=catering&result=menuAdded');
    } else {
        error_log("Error: " . $insertFoodItem->error);
        header('Location: ../../../Pages/Admin/services.php?page=catering&result=executionFailed');
    }
} elseif (isset($_POST['addServicePrice'])) { //*Service Pricing

    $pricingType = mysqli_real_escape_string($conn, $_POST['pricingType']);
    $price = (float) $_POST['SPservicePrice'];
    $chargeType = mysqli_real_escape_string($conn, $_POST['SPchargeType']);
    $ageGroup = mysqli_real_escape_string($conn, $_POST['ageGroup']);
    $notes = mysqli_real_escape_string($conn, $_POST['SPNotes']);

    $insertServicePricingQuery = $conn->prepare("INSERT INTO `servicepricing`(`pricingType`, `price`, `chargeType`, `ageGroup`, `notes`) VALUES (?,?,?,?,?)");
    $insertServicePricingQuery->bind_param("sdsss", $pricingType, $price, $chargeType, $ageGroup, $notes);

    if (!$insertServicePricingQuery->execute()) {
        error_log("Error: " . $insertServicePricingQuery->error);
        header("Location: ../../../../../Pages/Admin/services.php?page=servicePrice&result=error");
    }

    header("Location: ../../../../../Pages/Admin/services.php?page=servicePrice&result=added");
} else {
    header("Location: ../../../../../Pages/Admin/services.php?result=error");
    exit();
}
