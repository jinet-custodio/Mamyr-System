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
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus, cb.finalBill, cb.userBalance
                        FROM booking b
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        WHERE b.userID = ?
                        GROUP BY
                            b.bookingID
                        ORDER BY 
                            b.createdAt DESC");
        $getBookingInfo->bind_param("i", $userID);
        $getBookingInfo->execute();
        $result = $getBookingInfo->get_result();

        $rows = [];

        while ($bookings = $result->fetch_assoc()) {
            $checkIn = date("M. d, Y", strtotime($bookings['startDate']));
            // $paymentID = $bookings['paymentID'];
            $paymentApprovalStatus = getStatuses($conn, $bookings['paymentApprovalStatus'] ?? null);
            $bookingStatus = getStatuses($conn, $bookings['bookingStatus'] ?? null);
            $paymentStatus = getPaymentStatus($conn, ($bookings['paymentStatus'] ?? 1));

            $status = '';
            $class = '';


            $status   = $bookingStatus['statusName'];
            switch ($bookingStatus['statusID']) {
                case 1: //Pending
                    $status = 'Awaiting Review';
                    $class = 'warning';
                    break;
                case 2: //Approved
                    $status = 'Awaiting Payment';
                    $class = 'orange';
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
                                    break;
                            }
                            break;
                        case 2: //Approved
                            $class = 'success';
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2:
                                    $status = 'Reserved - ' . $paymentStatus['paymentStatusName'];
                                    $class = 'light-blue';
                                    break;
                                case 3:
                                    $status = 'Reserved - ' . $paymentStatus['paymentStatusName'];
                                    $class = 'bright-green';
                                    break;
                            }
                            break;
                        case 5: //Rejected
                            $status =  'Payment Rejected';
                            $class = 'red';
                            break;
                        default:
                            $class = 'orange';
                            break;
                    }
                    break;
                case 3: //Reserved
                    $class = 'success';
                    switch ($paymentStatus['paymentStatusID']) {
                        case 2:
                            $status = 'Reserved - ' . $paymentStatus['paymentStatusName'];
                            $class = 'light-blue';
                            break;
                        case 3:
                            $status = 'Reserved - ' . $paymentStatus['paymentStatusName'];
                            $class = 'bright-green';
                            break;
                    }
                    break;
                case 4: //Cancelled
                    $class = 'danger';
                    break;
                case 5: //Rejected
                    $class = 'red';
                    break;
                case 6: //Done
                    $class = 'light-green';
                    break;

                case 7: //Expired
                    $class = 'muted';
                    break;
                default:
                    $class = 'warning';
                    break;
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
