<?php
require '../../../Config/dbcon.php';
require '../../Helpers/statusFunctions.php';

header('Content-Type: application/json');

if (isset($_GET['userID'])) {
    $userID = (int) $_GET['userID'];

    try {
        $getBookingInfo = $conn->prepare("SELECT 
                                            b.bookingCode,
                                            cb.paymentDueDate,
                                            b.bookingID,
                                            b.bookingType,
                                            b.userID,
                                            b.startDate,
                                            b.bookingStatus,
                                            b.paymentMethod AS bookingPaymentMethod,
                                            b.customPackageID,
                                            b.totalCost,
                                            cb.paymentApprovalStatus,
                                            cb.confirmedBookingID,
                                            cb.paymentStatus,
                                            cb.finalBill,
                                            cb.userBalance,
                                            GROUP_CONCAT(p.paymentID ORDER BY p.paymentID) AS paymentIDs,
                                            GROUP_CONCAT(p.amount ORDER BY p.paymentID) AS paymentAmounts,
                                            GROUP_CONCAT(p.paymentMethod ORDER BY p.paymentID) AS paymentMethods,
                                            GROUP_CONCAT(p.paymentDate ORDER BY p.paymentID) AS paymentDates,
                                            GROUP_CONCAT(p.downpaymentImage ORDER BY p.paymentID) AS dpImages
                                        FROM confirmedbooking cb
                                        LEFT JOIN booking b ON cb.bookingID = b.bookingID
                                        LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                                        WHERE b.userID = ?
                                        GROUP BY b.bookingID
                                        ORDER BY b.createdAt
                                    ");

        $getBookingInfo->bind_param("i", $userID);
        $getBookingInfo->execute();
        $result = $getBookingInfo->get_result();

        $rows = [];

        while ($bookings = $result->fetch_assoc()) {

            $paymentDueDate = date('F d, Y — l', strtotime($bookings['paymentDueDate'] ?? $bookings['startDate']));


            $paymentIDs = !empty($bookings['paymentIDs']) ? explode(',', $bookings['paymentIDs']) : [];
            $paymentAmounts = !empty($bookings['paymentAmounts']) ? explode(',', $bookings['paymentAmounts']) : [];
            $paymentMethods = !empty($bookings['paymentMethods']) ? explode(',', $bookings['paymentMethods']) : [];
            $paymentDates = !empty($bookings['paymentDates']) ? explode(',', $bookings['paymentDates']) : [];
            $dpImages = !empty($bookings['dpImages']) ? explode(',', $bookings['dpImages']) : [];

            $payments = [];
            foreach ($paymentIDs as $i => $pid) {
                $rawDate = $paymentDates[$i] ?? null;
                $formattedDate = null;

                if (!empty($rawDate)) {
                    $formattedDate = date("M. d, Y — l", strtotime($rawDate));
                }

                $payments[] = [
                    'paymentID' => $pid,
                    'amount' => isset($paymentAmounts[$i]) ? number_format((float)$paymentAmounts[$i], 2) : null,
                    'method' => $paymentMethods[$i] ?? null,
                    'date' => $formattedDate,
                    'image' => $dpImages[$i] ?? null
                ];
            }


            // Get related statuses
            $paymentApprovalStatus = getStatuses($conn, $bookings['paymentApprovalStatus'] ?? null);
            $bookingStatus = getStatuses($conn, $bookings['bookingStatus'] ?? null);
            $paymentStatus = getPaymentStatus($conn, ($bookings['paymentStatus'] ?? 1));

            $approvalClass = '';
            $paymentClass = '';
            $paymentStatusName = 'Awaiting Payment';
            $approvalStatusName = $paymentApprovalStatus['statusName'];

            // Handle approval statuses
            switch ($paymentApprovalStatus['statusID']) {
                case 1:
                    $approvalClass = 'warning';
                    if ($paymentStatus['paymentStatusID'] == 5) {
                        $approvalStatusName = 'Awaiting Review';
                    }
                    break;
                case 2:
                    $approvalClass = 'success';
                    $approvalStatusName = 'Reserved';
                    break;
                case 4:
                    $approvalClass = 'danger';
                    break;
                case 5:
                    $approvalClass = 'red';
                    break;
                default:
                    $approvalClass = 'orange';
                    break;
            }

            // Handle payment statuses
            switch ($paymentStatus['paymentStatusID']) {
                case 1:
                    $paymentStatusName = $paymentStatus['paymentStatusName'];
                    $paymentClass = 'orange';
                    break;
                case 2:
                    $paymentStatusName = $paymentStatus['paymentStatusName'];
                    $paymentClass = 'light-blue';
                    break;
                case 3:
                    $paymentStatusName = $paymentStatus['paymentStatusName'];
                    $paymentClass = 'bright-green';
                    break;
                case 5:
                    $paymentStatusName = 'Payment Sent';
                    $paymentClass = 'primary';
                    break;
            }

            $rows[] = [
                'bookingID' => $bookings['bookingID'],
                'bookingCode' => $bookings['bookingCode'],
                'bookingType' => $bookings['bookingType'],
                'confirmedBookingID' => $bookings['confirmedBookingID'],
                'approvalClass' => $approvalClass,
                'paymentClass' => $paymentClass,
                'approvalStatus' => $approvalStatusName,
                'paymentStatus' => $paymentStatusName,
                'userBalance' => '₱ ' . number_format($bookings['userBalance'] ?? $bookings['totalCost'], 2),
                'totalBill' => '₱ ' . number_format($bookings['finalBill'] ?? $bookings['totalCost'], 2),
                'payments' => $payments,
                'paymentMethod' => $bookings['bookingPaymentMethod'],
                'paymentDueDate' =>  $paymentDueDate
            ];
        }

        echo json_encode([
            'success' => true,
            'bookings' => $rows
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Unexpected server error. Please try again later.'
        ]);
    }
}
