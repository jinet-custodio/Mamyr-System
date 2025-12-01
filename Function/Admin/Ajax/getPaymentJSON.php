<?php


require '../../../Config/dbcon.php';
require '../../Helpers/statusFunctions.php';
header('Content-Type: application/json');

if (isset($_GET['filter'])):

    $filter = trim(mysqli_real_escape_string($conn, $_GET['filter']) ?? 'all');

    try {

        switch ($filter) {
            case 'all':
                $getBookingInfo = $conn->prepare("SELECT b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.bookingStatus, b.paymentMethod,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.totalCost, 
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus, cb.finalBill, cb.userBalance,
                            p.amount, p.paymentDate, cb.amountPaid
                        FROM confirmedbooking cb
                        LEFT JOIN  booking b ON  cb.bookingID = b.bookingID 
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        WHERE b.bookingStatus IN (?,?,?)
                        GROUP BY b.bookingID
                        ORDER BY b.createdAt DESC
                        ");
                break;
            case 'pending-payment':
            case 'pending-review':
            case 'partially-paid':
            case 'fully-paid':
                $getBookingInfo = $conn->prepare("SELECT b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.bookingStatus, b.paymentMethod,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.totalCost, 
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus, cb.finalBill, cb.userBalance,
                            p.amount, p.paymentDate, cb.amountPaid
                        FROM confirmedbooking cb
                        LEFT JOIN  booking b ON  cb.bookingID = b.bookingID 
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        WHERE b.bookingStatus IN (?,?,?) AND cb.paymentStatus = ?
                        GROUP BY b.bookingID
                        ORDER BY b.createdAt DESC
                        ");
                break;
            case 'cancelled':
                $getBookingInfo = $conn->prepare("SELECT b.bookingCode, 
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.bookingStatus, b.paymentMethod,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.totalCost, 
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus, cb.finalBill, cb.userBalance,
                            p.amount, p.paymentDate, cb.amountPaid
                        FROM confirmedbooking cb
                        LEFT JOIN  booking b ON  cb.bookingID = b.bookingID 
                        INNER JOIN user u ON b.userID = u.userID
                        LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        WHERE b.bookingStatus IN (?,?,?) AND cb.paymentApprovalStatus IN (?,?)
                        GROUP BY b.bookingID
                        ORDER BY b.createdAt DESC
                        ");
                break;
        }
        $pending = $unpaid = 1;
        $approved = $partiallyPaid = 2;
        $reserved = $fullyPaid = 3;
        $cancelled = $paymentIssue = 4;
        $rejected = $paymentSent = 5;
        $done = 6;

        switch ($filter) {
            case 'all':
                $getBookingInfo->bind_param("iii", $approved, $reserved, $done);
                break;
            case 'pending-payment':
                $getBookingInfo->bind_param("iiii", $approved, $reserved, $done, $unpaid);
                break;
            case 'pending-review':
                $getBookingInfo->bind_param("iiii", $approved, $reserved, $done, $paymentSent);
                break;
            case 'partially-paid':
                $getBookingInfo->bind_param("iiii", $approved, $reserved, $done, $partiallyPaid);
                break;
            case 'fully-paid':
                $getBookingInfo->bind_param("iiii", $approved, $reserved, $done, $fullyPaid);
                break;
            case 'cancelled':
                $getBookingInfo->bind_param("iiiii", $approved, $reserved, $done, $cancelled, $rejected);
                break;
        }

        $getBookingInfo->execute();
        $result = $getBookingInfo->get_result();

        $rows = [];
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $middleInitial = trim($row['middleInitial'] ?? '');
            $name = ucfirst($row['firstName']) . " " . ucfirst($middleInitial) . " " . ucfirst($row['lastName']);
            $checkIn = date("F d, Y", strtotime($row['startDate']));

            $paymentApprovalStatus = getStatuses($conn, $row['paymentApprovalStatus'] ?? null);
            $bookingStatus = getStatuses($conn, $row['bookingStatus'] ?? null);
            $paymentStatus = getPaymentStatus($conn, $row['paymentStatus'] ?? 1);

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
                                    $class = 'primary';
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
                    switch ($paymentApprovalStatus['statusID']) {
                        case 2: //Approved
                            switch ($paymentStatus['paymentStatusID']) {
                                case 2: // Partially Paid
                                    $status = 'Event Completed - Partially Paid';
                                    $class = 'light-blue';
                                    break;

                                case 3: // Fully Paid
                                    $status = 'Event Completed - Fully Paid';
                                    $class = 'bright-green';
                                    break;
                            }
                            break;
                    }
                    break;
                    break;
                case 7: //Expired
                    $class = 'muted';
                    break;
                default:
                    $class = 'warning';
                    break;
            }


            $rows[] = [
                'bookingID' => $row['bookingID'],
                'bookingCode' => $row['bookingCode'],
                'name' => $name,
                'bookingType' => $row['bookingType'],
                'checkIn' => $checkIn,
                'status' => $status,
                'statusClass' => $class,
                'bookingStatus' => $row['bookingStatus'],
                'paymentMethod' => strtoupper($row['paymentMethod']),
                // 'paymentStatusName' =>  $paymentStatusName,
                // 'paymentClass' => $paymentClass,
                'paymentAmount' => '₱' . number_format($row['amountPaid'] ?? 0, 2),
                'userBalance' => '₱' . number_format($row['userBalance'], 2),
                'totalBill' => '₱' . number_format($row['finalBill'], 2) ?? '₱ ' . number_format($row['totalCost'], 2)
            ];
        }
        echo json_encode(
            [
                'success' => true,
                'payments' => $rows
            ]
        );
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Unexpected server error. Please try again later.'
        ]);
    }

endif;
