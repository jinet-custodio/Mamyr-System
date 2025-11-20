<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../Config/dbcon.php';
session_start();

if (isset($_POST['saveServiceInfo'])) {
    error_log(print_r($_POST, true));

    $serviceID = intval($_POST['partnershipServiceID']);
    $serviceName = mysqli_real_escape_string($conn, $_POST['serviceName']);
    $rawPrice = mysqli_real_escape_string($conn, $_POST['servicePrice']);
    $servicePrice = str_replace(['₱', ','], '', $rawPrice);
    $serviceCapacity = intval($_POST['serviceCapacity']);
    $serviceDuration = mysqli_real_escape_string($conn, $_POST['serviceDuration']);
    $serviceDesc = mysqli_real_escape_string($conn, $_POST['serviceDescription'] ?? 'N/A');
    $serviceAvailabilityID = mysqli_real_escape_string($conn, $_POST['serviceAvailability']);
    $serviceImage = mysqli_real_escape_string($conn, $_POST['serviceImageName']);
    $_SESSION['id'] = $serviceID;
    $_SESSION['service-form-data'] = $_POST;
    $imageMaxSize = 5 * 1024 * 1024; // 5 MB max
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    $storeProofPath = __DIR__ . '/../../Assets/Images/PartnerServiceImage/';
    $tempUploadPath = __DIR__ . '/../../Assets/Images/TempUploads/';

    if (!is_dir($storeProofPath)) mkdir($storeProofPath, 0755, true);
    if (!is_dir($tempUploadPath)) mkdir($tempUploadPath, 0755, true);

    // error_log($storeProofPath);
    // error_log($tempUploadPath);
    $tempFileName = null;

    if (!empty($_FILES['serviceImage']['tmp_name']) && is_uploaded_file($_FILES['serviceImage']['tmp_name'])) {

        $originalName = $_FILES['serviceImage']['name'];
        $imageExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $imageSize = $_FILES['serviceImage']['size'];

        if (!in_array($imageExt, $allowedExt)) {
            $_SESSION['tempImage'] = $tempFileName;
            header("Location: ../../../Pages/Account/bpViewService.php?action=extError");
            exit();
        }

        if ($imageSize > $imageMaxSize) {
            header("Location: ../../../Pages/Account/bpViewService.php?action=imageSize");
            exit();
        }

        $tempFileName = 'temp_' . uniqid() . '_serviceID'  . $serviceID . '.' . $imageExt;
        $tempFilePath = $tempUploadPath . $tempFileName;
        // error_log("THIS: " . $tempFilePath);
        if (!move_uploaded_file($_FILES['serviceImage']['tmp_name'], $tempFilePath)) {
            header("Location: ../../../Pages/Account/bpViewService.php?action=imageFailed");
            exit();
        }

        $_SESSION['tempImage'] = $tempFileName;


        $finalFileName = $tempFileName;
        $finalFilePath = $storeProofPath . $finalFileName;

        rename($tempUploadPath . $tempFileName, $finalFilePath);
    } elseif (!empty($_SESSION['tempImage'])) {
        $tempFileName = $_SESSION['tempImage'];

        $finalFileName = $tempFileName;
        $finalFilePath = $storeProofPath . $finalFileName;

        rename($tempUploadPath . $tempFileName, $finalFilePath);
    } else {
        $finalFileName = $serviceImage;
    }



    unset($_SESSION['tempImage']);

    try {
        $updatePartnerServiceQuery = $conn->prepare("UPDATE `partnershipservice` SET `PBName`= ?,`PBPrice`=?,`PBDescription`= ? ,`PBcapacity`= ?,`PBduration`= ? ,`PSAvailabilityID`= ?, serviceImage = ? WHERE `partnershipServiceID`= ?");
        $updatePartnerServiceQuery->bind_param('sdsssisi', $serviceName, $servicePrice, $serviceDesc, $serviceCapacity, $serviceDuration,  $serviceAvailabilityID, $finalFileName, $serviceID);
        if (!$updatePartnerServiceQuery->execute()) {
            throw new Exception("Error updating the service information: " . $updateMenuItem->error);
        }
        unset($_SESSION['service-form-data']);
        header("Location: ../../../../Pages/Account/bpViewService.php?action=serviceUpdateSuccess");
        exit();
    } catch (Exception $e) {
        error_log('Exception Error: ' . $e->getMessage());
        header("Location: ../../../../Pages/Account/bpViewService.php?action=serviceUpdateFailed");
        exit();
    }
} else {
    header("Location: ../../../../Pages/Account/bpViewService.php?action=serviceUpdateFailed");
    exit();
}
