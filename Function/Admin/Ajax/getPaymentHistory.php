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
                                        FROM confirmedbooking cb
                                        LEFT JOIN booking b ON cb.bookingID = b.bookingID
                                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                                        WHERE b.userID = ?
                                        ORDER BY
                                        b.createdAt");
        $getBookingInfo->bind_param("i", $userID);
        $getBookingInfo->execute();
        $result = $getBookingInfo->get_result();

        $rows = [];

        while ($bookings = $result->fetch_assoc()) {
            // $checkIn = date("M. d, Y", strtotime($bookings['startDate']));
            // $paymentID = $bookings['paymentID'];
            $paymentApprovalStatus = getStatuses($conn, $bookings['paymentApprovalStatus'] ?? null);
            $bookingStatus = getStatuses($conn, $bookings['bookingStatus'] ?? null);
            $paymentStatus =  getPaymentStatus($conn, ($bookings['paymentStatus'] ?? 1));

            $status = '';
            $approvalClass = '';
            $paymentClass = '';
            $paymentStatusName = 'Awaiting Payment';
            $approvalStatusName = $paymentApprovalStatus['statusName'];
            switch ($paymentApprovalStatus['statusID']) {
                case 1: //Pending
                    $approvalClass = 'warning';
                    switch ($paymentStatus['paymentStatusID']) {
                        case 5:
                            $approvalStatusName = 'Awaiting Review';
                            $approvalClass = 'green';
                            break;
                    }
                    break;
                case 2:
                    $status = 'Reserved';
                    $class = 'success';
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

            switch ($paymentStatus['paymentStatusID']) {
                case 1:
                    $paymentClass = 'orange';
                    break;
                case 2:
                    $class = 'light-blue';
                    break;
                case 3:
                    $class = 'bright-green';
                    break;
                case 5:
                    $paymentClass = 'green';
                    $status =  'Payment Sent';
                    break;
            }



            $rows[] = [
                'bookingCode' => $bookings['bookingCode'],
                'confirmedBookingID' => $bookings['confirmedBookingID'],
                'bookingID' => $bookings['bookingID'],
                'approvalClass' => $approvalClass,
                'paymentClass' => $paymentClass,
                'paymentMethod' => $bookings['paymentMethod'],
                'approvalStatus' => $approvalStatusName,
                'paymentStatus' => $paymentStatusName,
                'userBalance' => !empty($bookings['userBalance']) ?  '₱ ' . number_format($bookings['userBalance'], 2) : '₱ ' . number_format($bookings['totalCost'], 2),
                'totalBill' => !empty($bookings['finalBill']) ? '₱ ' . number_format($bookings['finalBill'], 2) : '₱ ' . number_format($bookings['totalCost'], 2)
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
