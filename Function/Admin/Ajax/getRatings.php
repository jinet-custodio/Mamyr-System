<?php

require '../../../Config/dbcon.php';

header('Content-Type: application/json');

$getRatingsQuery = $conn->prepare("SELECT 
                                        bookingType,
                                        reviewRating
                                    FROM userreview ur
                                    GROUP BY
                                        bookingType
                                ");

if (!$getRatingsQuery->execute()) {
    error_log('Error executing get ratings query' . $getRatingsQuery->error);
}

$result = $getRatingsQuery->get_result();

$resortRatingValue = $hotelRatingValue = $eventRatingValue = 0.00;
$resortPercent = $hotelPercent = $eventPercent = 0.00;
if ($result->num_rows === 0) {
    $resortRatingValue = $hotelRatingValue = $eventRatingValue = 0.00;
    $resortPercent = $hotelPercent = $eventPercent = 0.00;
}
$resortCounter = $hotelCounter = $eventCounter = 0;

while ($row = $result->fetch_assoc()) {
    $reviewRating = (float) $row['reviewRating'];
    switch (strtolower($row['bookingType'])) {
        case 'resort':
            $resortCounter++;
            $resortRatingValue += $reviewRating;
            break;
        case 'hotel':
            $hotelCounter++;
            $hotelRatingValue += $reviewRating;
            break;
        case 'event':
            $eventCounter++;
            $eventRatingValue += $reviewRating;
            break;
    }
}

$resortRating = 0;
$resortPercent = 0;

$hotelRating = 0;
$hotelPercent = 0;

$eventRating = 0;
$eventPercent = 0;

$overAllRating = 0;

if ($result->num_rows > 0) {
    $counter = 0;
    if ($resortCounter != 0) {
        $resortRating =  number_format(($resortRatingValue / $resortCounter), 2);
        $resortPercent = ($resortRating / 5) * 100;
        $counter++;
    }

    if ($hotelCounter != 0) {
        $hotelRating =  number_format(($hotelRatingValue / $hotelCounter), 2);
        $hotelPercent = ($hotelRating / 5) * 100;
        $counter++;
    }

    if ($eventCounter != 0) {
        $eventRating = number_format(($eventRatingValue / $eventCounter), 2);
        $eventPercent = ($eventRating / 5) * 100;
        $counter++;
    }


    $overAllRating = number_format((($resortRating + $hotelRating + $eventRating) / $counter), 2);
}



echo json_encode([
    'resortRating' => $resortRating,
    'resortPercent' => $resortPercent,
    'hotelRating' => $hotelRating,
    'hotelPercent' => $hotelPercent,
    'eventRating' => $eventRating,
    'eventPercent' => $eventPercent,
    'overAllRating' => $overAllRating,
]);
