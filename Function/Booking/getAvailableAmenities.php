<?php
require '../../Config/dbcon.php';

if (isset($_GET['date']) && isset($_GET['tour'])) {
    $selectedTour = trim(mysqli_real_escape_string($conn, $_GET['tour']));
    $resortBookingDate = new DateTime($_GET['date']);
    $selectedDate = $resortBookingDate->format('Y-m-d');
    $availableID = 1;

    $getTimeRange = $conn->prepare("SELECT * FROM entrancetimeranges WHERE session_type = ?");
    $getTimeRange->bind_param("s", $selectedTour);
    $getTimeRange->execute();
    $resultTimeRange = $getTimeRange->get_result();

    $startTime = $endTime = null;
    if ($resultTimeRange->num_rows > 0) {
        while ($row = $resultTimeRange->fetch_assoc()) {
            if ($row['session_type'] === $selectedTour) {
                list($startTime, $endTime) = explode('-', $row['time_range']);
                $startTime = trim($startTime);
                $endTime = trim($endTime);
                break;
            }
        }
    } else {
        echo json_encode(['error' => 'No time range found for selected tour']);
        exit();
    }

    if (!$startTime || !$endTime) {
        echo json_encode(['error' => 'Invalid time range']);
        exit();
    }

    $startDateTime = DateTime::createFromFormat('Y-m-d h:i a', $selectedDate . ' ' . $startTime);
    $endDateTime = DateTime::createFromFormat('Y-m-d h:i a', $selectedDate . ' ' . $endTime);

    $startStr = $startDateTime->format('Y-m-d H:i:s');
    $endStr = $endDateTime->format('Y-m-d H:i:s');

    $getAvailableAmenities = $conn->prepare("SELECT * FROM resortAmenities ra
        WHERE ra.RSAvailabilityID = ?
        AND NOT EXISTS (
            SELECT 1 FROM serviceUnavailableDates sud
            WHERE sud.resortServiceID = ra.resortServiceID
            AND (? < sud.unavailableEndDate AND ? > sud.unavailableStartDate)
        )
    ");

    $getAvailableAmenities->bind_param("iss", $availableID, $startStr, $endStr);
    $getAvailableAmenities->execute();
    $getAvailableAmenitiesResult = $getAvailableAmenities->get_result();

    $cottages = [];
    $rooms = [];
    $entertainments = [];

    while ($row = $getAvailableAmenitiesResult->fetch_assoc()) {
        $duration = $row['RSduration'];
        $serviceName = trim($row['RServiceName']);

        switch ($row['RScategoryID']) {
            case 1: // Hotel rooms
                if ($duration === '11 hours') {
                    $rooms[] = $row;
                }
                break;
            case 2: // Cottage
                $cottages[] = $row;
                break;
            case 3: // Entertainment
                $entertainments[] = $row;
                break;
        }
    }

    echo json_encode([
        'cottages' => $cottages,
        'rooms' => $rooms,
        'entertainments' => $entertainments
    ]);
} else if (isset($_GET['hotelCheckInDate']) && isset($_GET['hotelCheckOutDate']) && isset($_GET['duration'])) {

    $hotelCheckInDate = new DateTime($_GET['hotelCheckInDate']);
    $hotelCheckOutDate = new DateTime($_GET['hotelCheckOutDate']);
    $startDate = $hotelCheckInDate->format('Y-m-d H:i:s');
    $endDate = $hotelCheckOutDate->format('Y-m-d H:i:s');
    $duration = trim(mysqli_real_escape_string($conn, $_GET['duration']));

    $availableID = 1;
    $hotelCategoryID = 1;

    $getAvailableHotel = $conn->prepare("SELECT * FROM resortAmenities ra WHERE ra.RSAvailabilityID = ? AND ra.RScategoryID = ? AND ra.RSduration = ?
                            AND NOT EXISTS (
                            SELECT 1 FROM serviceUnavailableDates sud
                            WHERE sud.resortServiceID = ra.resortServiceID
                            AND (? < sud.unavailableEndDate AND ? > sud.unavailableStartDate)
                        )");

    $getAvailableHotel->bind_param("iisss", $availableID, $hotelCategoryID, $duration, $startDate, $endDate);
    $getAvailableHotel->execute();
    $getAvailableHotelResult = $getAvailableHotel->get_result();

    $hotels = [];

    while ($row = $getAvailableHotelResult->fetch_assoc()) {
        $hotels[] = $row;
    }



    echo json_encode([
        'hotels' => $hotels
    ]);
    $getAvailableHotel->close();
} else {
    echo json_encode(['error' => 'Required data not provided']);
    exit();
}
