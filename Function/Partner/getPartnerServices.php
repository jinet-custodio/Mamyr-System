<?php

require '../../Config/dbcon.php';
require '../Helpers/statusFunctions.php';
header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 1);

$details = [];

if (isset($_GET['id'])) {
    $partnershipID = (int) $_GET['id'];
    $getPartnerService = $conn->prepare("SELECT * FROM `partnershipservice` WHERE partnershipID = ?");
    $getPartnerService->bind_param('i', $partnershipID);
    error_log("PID: $partnershipID");
    if (!$getPartnerService->execute()) {
        error_log("Error: $partnershipID" . $getPartnerService->error);
        echo json_encode(['error' => 'Database execution failed']);
        exit;
    }

    $result = $getPartnerService->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'details' => []
        ]);
        exit;
    }

    while ($row = $result->fetch_assoc()) {
        $storedAvailabilityID = intval($row['PSAvailabilityID']);
        $availabilityStatus = getAvailabilityStatus($conn, $storedAvailabilityID);

        $availabilityID = $availabilityStatus['availabilityID'];
        $availabilityName = $availabilityStatus['availabilityName'];

        switch ($availabilityID) {
            case 1:
                $classcolor = 'success';
                $statusName =  $availabilityName;
                break;
            case 2:
                $classcolor = 'info';
                $statusName =  'Booked';
                break;
            case 3:
                $classcolor = 'warning';
                $statusName =  $availabilityName;
                break;
            case 4:
                $classcolor = 'light-green';
                $statusName =  $availabilityName;
                break;
            case 5:
                $classcolor = 'danger';
                $statusName =  $availabilityName;
                break;
            default:
                $classcolor = 'secondary';
                $statusName =  $availabilityName;
        }

        $descriptions = !empty($row['PBDescription']) ? ($row['PBDescription']) : '';

        $details[] = [
            'statusName' => strtolower($statusName),
            'availabilityID' => $availabilityID,
            'classColor' => $classcolor,
            'description' => $descriptions,
            'serviceName' => ucwords($row['PBName']) ?? 'N/A',
            'servicePrice' => "₱" . number_format($row['PBPrice'], 2),
            'serviceCapacity' => $row['PBCapacity'] ?? 'N/A',
            'serviceDuration' => $row['PBduration'] ?? 'N/A',
            'modalID' => 'serviceModal' . $row['partnershipServiceID'],
            'partnershipServiceID' => $row['partnershipServiceID']
        ];
    }

    echo json_encode([
        'success' => true,
        'details' => $details
    ]);
    exit;
}
