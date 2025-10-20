<?php


require '../../../Config/dbcon.php';
require '../../Helpers/statusFunctions.php';
header('Content-Type: application/json');

try {
    $getBookingInfo = $conn->prepare("SELECT LPAD(b.bookingID, 4, 0) AS formattedBookingID,  
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.endDate, b.bookingStatus,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, 
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus
                        FROM booking b
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        ORDER BY
                            cb.paymentStatus
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        ");
    $getBookingInfo->execute();
    $result = $getBookingInfo->get_result();

    $rows = [];

    while ($bookings = $result->fetch_assoc()) {
        $middleInitial = trim($bookings['middleInitial'] ?? '');
        $name = ucfirst($bookings['firstName']) . " " . ucfirst($middleInitial) . " " . ucfirst($bookings['lastName']);
        $checkIn = date("F d, Y", strtotime($bookings['startDate']));
        $checkOut = date("F d, Y", strtotime($bookings['endDate']));
        $paymentApprovalStatus = getStatuses($conn, $bookings['paymentApprovalStatus'] ?? null);
        $bookingStatus = getStatuses($conn, $bookings['bookingStatus'] ?? null);
        // $paymentStatus = getPaymentStatus($conn, $bookings['paymentStatus']) ?? null;

        $status = '';
        $class = '';
        if (!empty($bookings['confirmedBookingID'])) {
            $status = $paymentApprovalStatus['statusName'];
            switch ($paymentApprovalStatus['statusID']) {
                case 1: //Pending
                    $status = 'Awaiting Payment';
                    $class = 'orange';
                    switch ($bookingStatus['statusID']) {
                        case 4: //Cancelled
                            $status = $bookingStatus['statusName'];
                            $class = 'danger';
                            break;
                        case 7: //Expired
                            $status = $bookingStatus['statusName'];
                            $class = 'muted';
                            break;
                    }
                    break;
                case 2: //Approved
                    $status = 'Reserved';
                    $class = 'success';
                    switch ($bookingStatus['statusID']) {
                        case 4: //Cancelled
                            $status = $bookingStatus['statusName'];
                            $class = 'danger';
                            break;
                    }
                    break;
                case 5: // Rejected
                    $class = 'red';
                    break;
                default:
                    $class = 'orange';
                    break;
            }
        } else {
            $status   = $bookingStatus['statusName'];
            switch ($bookingStatus['statusID']) {
                case 1: //Pending
                    $status = 'Awaiting Review';
                    $class = 'warning';
                    break;
                case 2: //Approved
                    $status = 'Awaiting Payment';
                    $class = 'orange';
                    break;
                case 3: //Reserved
                    $status = 'Reserved';
                    $class = 'success';
                    break;
                case 5: //Rejected
                    $class = 'red';
                    break;
                case 4: //Cancelled
                    $class = 'danger';
                    break;
                case 6: // Done
                    $class = 'light-green';
                    break;
                case 7: //Expired
                    $class = 'muted';
                    break;
                default:
                    $class = 'warning';
                    break;
            }
        }


        $rows[] = [
            'bookingID' => $bookings['bookingID'],
            'formattedBookingID' => $bookings['formattedBookingID'],
            'name' => $name,
            'bookingType' => $bookings['bookingType'],
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'status' => $status,
            'statusClass' => $class,
            'bookingStatus' => $bookings['bookingStatus'],
        ];
    }
    echo json_encode(
        [
            'success' => true,
            'bookings' => $rows
        ]
    );
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected server error. Please try again later.'
    ]);
}
