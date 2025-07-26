<?php
require '../../Config/dbcon.php';

if (isset($_GET['resortBookingDate'])) {
    $resortBookingDate = new DateTime($_GET['resortBookingDate']);
    $selectedDate = $resortBookingDate->format('Y-m-d');
    $cottageCategoryID = 2;
    $availableID = 1;

    $stmt = $conn->prepare("SELECT * 
        FROM resortAmenities 
        WHERE RScategoryID = ?  
        AND RSAvailabilityID = ?
        AND resortServiceID NOT IN (
            SELECT resortServiceID 
            FROM serviceUnavailableDates 
            WHERE ? BETWEEN DATE(unavailableStartDate) AND DATE(unavailableEndDate)
        )
    ");
    $stmt->bind_param("iis", $cottageCategoryID, $availableID, $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<option value='' disabled selected>Select a Cottage</option>";
        while ($cottage = $result->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($cottage['RServiceName']) . "'>";
            echo "₱" . number_format($cottage['RSprice'], 0) . " - Good for " . $cottage['RScapacity'] . " pax";
            echo "</option>";
        }
    } else {
        echo "<option disabled>No cottages available for selected date</option>";
    }
}
