<?php


require '../../../Config/dbcon.php';
require '../../functions.php';
header('Content-Type: application/json');

try {
    $getBookingInfo = $conn->prepare("SELECT LPAD(b.bookingID, 4, 0) AS formattedBookingID,  
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.bookingStatus,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, 
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus
                        FROM booking b
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID");
    $getBookingInfo->execute();
    $result = $getBookingInfo->get_result();

    $rows = [];

    while ($bookings = $result->fetch_assoc()) {
        $middleInitial = trim($bookings['middleInitial'] ?? '');
        $name = ucfirst($bookings['firstName']) . " " . ucfirst($middleInitial) . " " . ucfirst($bookings['lastName']);
        $checkIn = date("F d, Y", strtotime($bookings['startDate']));

        $paymentApprovalStatus = getStatuses($conn, $bookings['paymentApprovalStatus'] ?? null);
        $bookingStatus = getStatuses($conn, $bookings['bookingStatus'] ?? null);
        // $paymentStatus = getPaymentStatus($conn, $bookings['paymentStatus']) ?? null;

        $status = '';
        $class = '';
        if (!empty($bookings['confirmedBookingID'])) {
            $status = $paymentApprovalStatus['statusName'];
            switch ($paymentApprovalStatus['statusID']) {
                case 1:
                    $status = 'Downpayment';
                    $class = 'info';
                    break;
                case 2:
                    $class = 'success';
                    break;
                case 3:
                    $class = 'danger';
                    break;
                case 4:
                    $class = 'red';
                    break;
                case 5:
                    $class = 'light-green';
                    break;
                case 6:
                    $class = 'secondary';
                    break;
                default:
                    $class = 'warning';
                    break;
            }
        } else {
            $status = $bookingStatus['statusName'];
            switch ($bookingStatus['statusID']) {
                case 1:
                    $class = 'warning';
                    break;
                case 2:
                    $status = 'Downpayment';
                    $class = 'info';
                    break;
                case 3:
                    $class = 'danger';
                    break;
                case 4:
                    $class = 'red';
                    break;
                case 5:
                    $class = 'light-green';
                    break;
                case 6:
                    $class = 'secondary';
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
