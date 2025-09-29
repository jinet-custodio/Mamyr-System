<?php


require '../../../Config/dbcon.php';
require '../../functions.php';
header('Content-Type: application/json');
if (isset($_GET['userID'])) {
    $userID = (int) $_GET['userID'];
    error_log($userID);
    try {
        $getBookingInfo = $conn->prepare("SELECT 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.bookingStatus, b.paymentMethod, 
                            b.customPackageID, b.totalCost,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus, cb.confirmedFinalBill, cb.userBalance
                        FROM booking b
                        LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
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
            $paymentStatus = getPaymentStatus($conn, $bookings['paymentStatus']) ?? null;

            $status = '';
            $class = '';
            if (!empty($bookings['confirmedBookingID'])) {
                $status = $paymentApprovalStatus['statusName'];
                switch ($paymentApprovalStatus['statusID']) {
                    case 1:
                        $status = 'Downpayment';
                        $class = 'info';
                        switch ($paymentStatus['paymentStatusID']) {
                            case 5:
                                $class = 'sky-blue';
                                $status =  $paymentStatus['paymentStatusName'];
                                break;
                        }
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
                'confirmedBookingID' => $bookings['confirmedBookingID'],
                'bookingID' => $bookings['bookingID'],
                'bookingType' => $bookings['bookingType'],
                'checkIn' => $checkIn,
                'status' => $status,
                'statusClass' => $class,
                'bookingStatus' => $bookings['bookingStatus'],
                'paymentMethod' => $bookings['paymentMethod'],
                'approvalStatus' => $paymentApprovalStatus['statusName'] ?? '',
                'userBalance' => '₱ ' . number_format($bookings['userBalance'], 2),
                'totalBill' => '₱ ' . number_format($bookings['confirmedFinalBill'], 2) ?? '₱ ' . number_format($bookings['totalCost'], 2)
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
