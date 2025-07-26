<?php
require '../../Config/dbcon.php';

if (isset($_GET['date'])) {
    $resortBookingDate = new DateTime($_GET['date']);
    $selectedDate = $resortBookingDate->format('Y-m-d');
    $availableID = 1;
    $cottageCategoryID = 2;

    $getAvailableAmenities = $conn->prepare("SELECT * FROM resortAmenities ra 
                                    WHERE ra.RSAvailabilityID = ?
                                    AND NOT EXISTS (
                                        SELECT 1 FROM serviceUnavailableDates sud 
                                        WHERE sud.resortServiceID = ra.resortServiceID
                                        AND DATE(?) BETWEEN DATE(sud.unavailableStartDate) AND DATE(sud.unavailableEndDate) )
                                ");
    $getAvailableAmenities->bind_param("is", $availableID, $selectedDate);
    $getAvailableAmenities->execute();
    $getAvailableAmenitiesResult =  $getAvailableAmenities->get_result();


    $cottages = [];
    $rooms = [];
    $entertainments = [];


    while ($row =  $getAvailableAmenitiesResult->fetch_assoc()) {
        $duration = $row['RSduration'];

        switch ($row['RScategoryID']) {
            case 1: //Hotel ID to
                if ($duration === '11 hours') {
                    $rooms[] = $row;
                }
                break;
            case 2: //Cottage ID
                $cottages[] = $row;
                break;
            case 3: //Entertainment ID
                $entertainments[] = $row;
                break;
        }
    }

    echo json_encode([
        'cottages' => $cottages,
        'rooms' => $rooms,
        'entertainments' => $entertainments
    ]);
} else {
    echo json_encode(['error' => 'Date not provided']);
    exit();
}
