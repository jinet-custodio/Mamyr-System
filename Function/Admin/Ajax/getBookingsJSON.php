<?php


require '../../../Config/dbcon.php';
require '../../Helpers/statusFunctions.php';
header('Content-Type: application/json');

if (isset($_GET['filter'])) {

    $filterValue = mysqli_real_escape_string($conn, $_GET['filter'] ?? 'all');
    // error_log($filterValue);
    try {

        switch (strtolower($filterValue)) {
            case 'all':
                $getBookingInfo = $conn->prepare("SELECT  b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.endDate, b.bookingStatus,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.createdAt,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus
                        FROM booking b
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        ORDER BY
                            b.createdAt DESC
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        ");
                break;
            case 'pending':
            case 'finished':
                $getBookingInfo = $conn->prepare("SELECT  b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.endDate, b.bookingStatus,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.createdAt,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus
                        FROM booking b
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        WHERE b.bookingStatus = ?
                        ORDER BY
                            b.createdAt DESC
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        ");
                break;
            case 'expired':
                $getBookingInfo = $conn->prepare("SELECT  b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.endDate, b.bookingStatus,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.createdAt,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus
                        FROM booking b
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        WHERE b.bookingStatus IN (?,?,?)
                        ORDER BY
                            b.createdAt DESC
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        ");
                break;
            case 'incoming':
                $getBookingInfo = $conn->prepare("SELECT  b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.endDate, b.bookingStatus,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.createdAt,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus
                        FROM booking b
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        WHERE 
                            b.bookingStatus IN (?,?) AND b.startDate > NOW()
                        ORDER BY
                            b.createdAt DESC
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        ");
                break;
            case 'ongoing':
                $getBookingInfo = $conn->prepare("SELECT  b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.endDate, b.bookingStatus,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.createdAt,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus
                        FROM booking b
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        WHERE 
                            b.bookingStatus = ? AND b.startDate <= NOW() AND b.endDate >= NOW()
                        ORDER BY
                            b.createdAt DESC
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        ");
                break;
        }
        $pendingID = 1;
        $approvedID = 2;
        $reservedID = 3;
        $cancelledID = 4;
        $rejectedID = 5;
        $doneID = 6;
        $expiredID = 7;
        switch ($filterValue) {
            case 'pending':
                $getBookingInfo->bind_param("i", $pendingID);
                break;
            case 'incoming':
                $getBookingInfo->bind_param("ii", $approvedID, $reservedID);
                break;
            case 'ongoing':
                $getBookingInfo->bind_param("i", $reservedID);
                break;
            case 'finished':
                $getBookingInfo->bind_param("i", $doneID);
                break;
            case 'expired':
                $getBookingInfo->bind_param("iii", $cancelledID, $rejectedID, $expiredID);
                break;
        }
        $getBookingInfo->execute();
        $result = $getBookingInfo->get_result();

        $rows = [];

        while ($bookings = $result->fetch_assoc()) {
            $middleInitial = trim($bookings['middleInitial'] ?? '');
            $name = ucfirst($bookings['firstName']) . " " . ucfirst($middleInitial) . " " . ucfirst($bookings['lastName']);
            $checkIn = date("M. d, Y", strtotime($bookings['startDate']));
            $checkOut = date("M. d, Y", strtotime($bookings['endDate']));

            if (date("md", strtotime($bookings['startDate'])) == date("md", strtotime($bookings['endDate']))) {
                $bookingDate = $checkIn = date("F d, Y", strtotime($bookings['startDate']));
            } else if (date("m", strtotime($bookings['startDate'])) == date("m", strtotime($bookings['endDate']))) {
                $bookingDate = date("F d - ", strtotime($bookings['startDate'])) . date(" d, Y", strtotime($bookings['endDate']));
            } else {
                $bookingDate = $checkIn . " - " . $checkOut;
            }


            $paymentApprovalStatus = getStatuses($conn, $bookings['paymentApprovalStatus'] ?? null);
            $bookingStatus = getStatuses($conn, $bookings['bookingStatus'] ?? null);
            $paymentStatus = getPaymentStatus($conn, $bookings['paymentStatus']) ?? 1;
            $createdOn = date('M. d, Y', strtotime($bookings['createdAt']));
            $status = '';
            $class = '';

            $status = $bookingStatus['statusName'];
            switch ($bookingStatus['statusID']) {
                case 1: //Pending
                    $status = 'Awaiting Review';
                    $class = 'warning';
                    break;
                case 2: //Approved
                    switch ($paymentApprovalStatus['statusID']) {
                        case 1: //Pending
                            switch ($paymentStatus['paymentStatusID']) {
                                case 1: //Unpaid
                                    $status = 'Awaiting Payment';
                                    $class = 'orange';
                                    break;
                                case 5: //Payment Sent
                                    $status = 'Review Payment';
                                    $class = 'orange';
                                    break;
                            }
                            break;
                        case 2: //Approved
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2: //Partially Paid
                                    $status = 'Reserved - Partially Paid';
                                    $class = 'light-blue';
                                    break;
                                case 3: //Fully Paid
                                    $status = 'Reserved - Fully Paid';
                                    $class = 'bright-green';
                                    break;
                            }
                            break;
                        case 5: // Rejected
                            $status = 'Payment Rejected';
                            $class = 'red';
                            break;
                        default:
                            $class = 'orange';
                            break;
                    }
                    break;
                case 3: //Reserved
                    switch ($paymentApprovalStatus['statusID']) {
                        case 2: //Approved
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2: //Partially Paid
                                    $status = 'Reserved - Partially Paid';
                                    $class = 'light-blue';
                                    break;
                                case 3: //Fully Paid
                                    $status = 'Reserved - Fully Paid';
                                    $class = 'bright-green';
                                    break;
                            }
                            break;
                    }
                    break;
                case 5: //Rejected
                    $class = 'red';
                    break;
                case 4: //Cancelled
                    $class = 'danger';
                    break;
                case 6: // Done
                    $class = 'light-green';
                    switch ($paymentApprovalStatus['statusID'] ?? '') {
                        case 2: //Approved
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2: //Partially Paid
                                    $status = 'Completed - Partially Paid';
                                    $class = 'light-blue';
                                    break;
                                case 3: //Fully Paid
                                    $status = 'Completed - Fully Paid';
                                    $class = 'bright-green';
                                    break;
                            }
                            break;
                    }
                    break;
                case 7: //Expired
                    $class = 'muted';
                    break;
                default:
                    $class = 'warning';
                    break;
            }

            $rows[] = [
                'bookingID' => $bookings['bookingID'],
                'bookingCode' => $bookings['bookingCode'],
                'name' => $name,
                'bookingType' => $bookings['bookingType'],
                // 'checkIn' => $checkIn,
                // 'checkOut' => $checkOut,
                'bookingDate' => $bookingDate,
                'status' => $status,
                'statusClass' => $class,
                'bookingStatus' => $bookings['bookingStatus'],
                'createdOn' => $createdOn
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
}
