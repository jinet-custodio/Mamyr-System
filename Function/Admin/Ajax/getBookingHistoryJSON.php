<?php


require '../../../Config/dbcon.php';
require '../../Helpers/statusFunctions.php';
header('Content-Type: application/json');
if (isset($_GET['userID'])) {
    $userID = (int) $_GET['userID'];
    // error_log($userID);
    try {
        $getBookingInfo = $conn->prepare("SELECT b.bookingCode,
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.bookingStatus, b.paymentMethod, 
                            b.customPackageID, b.totalCost,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, p.paymentID, p.paymentStatus, cb.finalBill, cb.userBalance
                        FROM booking b
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        WHERE b.userID = ?
                        ORDER BY
                        b.createdAt");
        $getBookingInfo->bind_param("i", $userID);
        $getBookingInfo->execute();
        $result = $getBookingInfo->get_result();

        $rows = [];

        while ($bookings = $result->fetch_assoc()) {
            $checkIn = date("M. d, Y", strtotime($bookings['startDate']));

            $paymentApprovalStatus = getStatuses($conn, $bookings['paymentApprovalStatus'] ?? null);
            $bookingStatus = getStatuses($conn, $bookings['bookingStatus'] ?? null);
            $paymentStatus = !empty($paymentID) ? getPaymentStatus($conn, $bookings['paymentStatus']) : getPaymentStatus($conn, 1);

            $status = '';
            $class = '';

            if (!empty($bookings['confirmedBookingID'])) {
                $status = $paymentApprovalStatus['statusName'];
                switch ($paymentApprovalStatus['statusID']) {
                    case 1:
                        $status = 'Awaiting Payment';
                        $class = 'orange';
                        switch ($paymentStatus['paymentStatusID']) {
                            case 1:
                                $class = 'orange';
                                break;
                            case 5:
                                $class = 'green';
                                $status =  'Payment Sent';
                        }
                        switch ($bookingStatus['statusID']) {
                            case 7:
                                $class = 'muted';
                                $status = $bookingStatus['statusName'];
                                break;
                            case 4:
                                $class = 'danger';
                                $status = $bookingStatus['statusName'];
                                break;
                        }
                        break;
                    case 2:
                        $status = 'Reserved';
                        $class = 'success';
                        switch ($paymentStatus['paymentStatusID']) {
                            case 2:
                                $class = 'light-blue';
                                break;
                            case 3:
                                $class = 'bright-green';
                                $status =  $paymentStatus['paymentStatusName'];
                        }
                        break;
                    case 4:
                        $class = 'danger';
                        break;
                    case 5:
                        $class = 'red';
                        break;
                    default:
                        $class = 'orange';
                        break;
                }
            } else {
                $status   = $bookingStatus['statusName'];
                switch ($bookingStatus['statusID']) {
                    case 1:
                        $status = 'Awaiting Review';
                        $class = 'warning';
                        break;
                    case 2:
                        $status = 'Awaiting Payment';
                        $class = 'orange';
                        break;
                    case 3:
                        $status = 'Reserved';
                        $class = 'success';
                        break;
                    case 5:
                        $class = 'red';
                        break;
                    case 4:
                        $class = 'danger';
                        break;
                    case 6:
                        $class = 'light-green';
                        break;
                    case 7:
                        $class = 'muted';
                        break;
                    default:
                        $class = 'warning';
                        break;
                }
            }


            $rows[] = [
                'bookingCode' => $bookings['bookingCode'],
                'confirmedBookingID' => $bookings['confirmedBookingID'],
                'bookingID' => $bookings['bookingID'],
                'bookingType' => $bookings['bookingType'],
                'checkIn' => $checkIn,
                'status' => $status,
                'statusClass' => $class,
                'bookingStatus' => $bookings['bookingStatus'],
                'approvalStatus' => $paymentApprovalStatus['statusName'] ?? ''
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
