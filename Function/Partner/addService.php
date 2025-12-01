<?php
session_start();

require '../../Config/dbcon.php';
require '../Helpers/userFunctions.php';

$userID = intval($_SESSION['userID']);
$userRole = intval($_SESSION['userRole']);

if (isset($_POST['addService'])) {
    $partnershipID = intval($_POST['partnershipID']);

    $serviceName = mysqli_real_escape_string($conn, $_POST['serviceName']);
    $availabilityID = intval($_POST['serviceAvailability'] ?? 1);
    $servicePrice = floatval($_POST['price']);
    $serviceCapacity = intval($_POST['capacity']);
    $serviceDuration = mysqli_real_escape_string($conn, $_POST['duration']);
    $serviceDesc = mysqli_real_escape_string($conn, $_POST['serviceDesc']) ?? 'N/A';
    $pptID = (int) $_POST['pptID'];
    $_SESSION['addServiceForm'] = $_POST;

    $storeProofPath = '../../Assets/Images/PartnerServiceImage/';


    if (!is_dir($storeProofPath)) {
        mkdir($storeProofPath, 0775, true);
    }

    if (isset($_FILES['serviceImage']) && is_uploaded_file($_FILES['serviceImage']['tmp_name'])) {
        $fileName = $_FILES['serviceImage']['name'];
        $fileSize = $_FILES['serviceImage']['size'];
        $tmpName = $_FILES['serviceImage']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $imageMaxSize = 5 * 1024 * 1024;

        if (!in_array($fileExt, $allowedExt)) {
            header('Location: ../../../../Pages/Account/bpServices.php?action=imageExt');
            exit();
        }

        if ($fileSize > $imageMaxSize) {
            header('Location: ../../../../Pages/Account/bpServices.php?action=imageSize');
            exit();
        }
        $uniqueFileName = $partnershipID . '_' . generateCode(3) . $fileName;
        $file = $storeProofPath . $uniqueFileName;

        if (move_uploaded_file($tmpName, $file)) {
            $continue;
        } else {
            header('Location: ../../../../Pages/Account/bpServices.php?action=uploadingFailed');
            exit();
        }
    } else {
        header('Location: ../../../../Pages/Account/bpServices.php?action=imageFailed');
        exit();
    }

    // error_log(print_r($_FILES['serviceImage'], true));

    $conn->begin_transaction();
    try {
        $insertPartnerServiceQuery = $conn->prepare("INSERT INTO `partnershipservice`(`partnershipID`, `PBName`, `PBPrice`, `PBDescription`, `PBcapacity`, `PBduration`, `PSAvailabilityID`, `partnerTypeID`, `serviceImage`) VALUES (?,?,?,?,?,?,?,?,?)");
        $insertPartnerServiceQuery->bind_param("isdsisiis", $partnershipID, $serviceName, $servicePrice, $serviceDesc, $serviceCapacity, $serviceDuration, $availabilityID, $pptID, $uniqueFileName);

        if (!$insertPartnerServiceQuery->execute()) {
            $conn->rollback();
            throw new Exception("Failed excuting the insertion of business partner service: " . $serviceName);
        }

        $partnershipServiceID = $conn->insert_id;
        $serviceType = 'Partnership';

        $insertIntoServiceQuery = $conn->prepare("INSERT INTO `service`(`partnershipServiceID`, `serviceType`) VALUES (?,?)");
        $insertIntoServiceQuery->bind_param("is", $partnershipServiceID, $serviceType);

        if (!$insertIntoServiceQuery->execute()) {
            $conn->rollback();
            throw new Exception("Failed excuting the insertion of service: " . $partnershipServiceID);
        }
        unset($_SESSION['addServiceForm']);
        $conn->commit();
        header("Location: ../../Pages/Account/bpServices.php?action=success");
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        header("Location: ../../Pages/Account/bpServices.php?action=error");
    }
}
