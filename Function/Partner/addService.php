<?php
session_start();

require '../../Config/dbcon.php';

$userID = intval($_SESSION['userID']);
$userRole = intval($_SESSION['userRole']);

if (isset($_POST['addService'])) {
    $partnershipID = intval($_POST['partnershipID']);

    $serviceName = mysqli_real_escape_string($conn, $_POST['serviceName']);
    $availabilityID = intval($_POST['serviceAvailabilityName']) ?? 1;
    $servicePrice = floatval($_POST['price']);
    $serviceCapacity = intval($_POST['capacity']);
    $serviceDuration = mysqli_real_escape_string($conn, $_POST['duration']);
    $serviceDesc = mysqli_real_escape_string($conn, $_POST['serviceDesc']) ?? 'N/A';
    $partnerTypeID = (int) $_POST['partnerTypeID'];


    $conn->begin_transaction();
    try {
        $insertPartnerServiceQuery = $conn->prepare("INSERT INTO `partnershipservice`(`partnershipID`, `PBName`, `PBPrice`, `PBDescription`, `PBcapacity`, `PBduration`, `PSAvailabilityID`, `partnerTypeID`) VALUES (?,?,?,?,?,?,?,?)");
        $insertPartnerServiceQuery->bind_param("isdsisii", $partnershipID, $serviceName, $servicePrice, $serviceDesc, $serviceCapacity, $serviceDuration, $availabilityID, $partnerTypeID);

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
        $_SESSION['addServiceForm'] = $_POST;
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        header("Location: ../../Pages/Account/bpServices.php?action=error");
    }
}
