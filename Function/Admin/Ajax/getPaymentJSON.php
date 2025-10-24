<?php


require '../../../Config/dbcon.php';
require '../../Helpers/statusFunctions.php';
header('Content-Type: application/json');

try {
    $getBookingInfo = $conn->prepare("SELECT LPAD(b.bookingID, 4, 0) AS formattedBookingID,  
                            b.bookingID, b.bookingType, b.userID, b.startDate, b.bookingStatus, b.paymentMethod,
                            u.firstName, u.middleInitial, u.lastName, 
                            b.customPackageID, b.totalCost,
                            cb.paymentApprovalStatus, cb.confirmedBookingID, cb.paymentStatus, cb.finalBill, cb.userBalance
                        FROM confirmedbooking cb
                        LEFT JOIN  booking b ON  cb.bookingID = b.bookingID 
                        INNER JOIN user u ON b.userID = u.userID
                        -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                        WHERE b.bookingStatus not in (1, 4, 5, 7)");
    $getBookingInfo->execute();
    $result = $getBookingInfo->get_result();

    $rows = [];

    while ($payments = $result->fetch_assoc()) {
        $middleInitial = trim($payments['middleInitial'] ?? '');
        $name = ucfirst($payments['firstName']) . " " . ucfirst($middleInitial) . " " . ucfirst($payments['lastName']);
        $checkIn = date("F d, Y", strtotime($payments['startDate']));

        $paymentApprovalStatus = getStatuses($conn, $payments['paymentApprovalStatus'] ?? null);
        $bookingStatus = getStatuses($conn, $payments['bookingStatus'] ?? null);
        $paymentStatus = getPaymentStatus($conn, $payments['paymentStatus'] ?? 1);

        $status = '';
        $class = '';
        if (!empty($payments['confirmedBookingID'])) {
            $status = $paymentApprovalStatus['statusName'];
            switch ($paymentApprovalStatus['statusID']) {
                case 1:
                    $status = 'Awaiting Payment';
                    $class = 'warning';
                    switch ($paymentStatus['paymentStatusID']) {
                        case 5:
                            $status = 'Awaiting review';
                            $paymentClass = 'warning';
                            break;
                    }
                    break;
                case 2:
                    $class = 'green';
                    break;
                case 4:
                    $class = 'danger';
                    break;
                case 5:
                    $class = 'red';
                    break;
                case 7:
                    $class = 'muted';
                    break;
                default:
                    $class = 'warning';
                    break;
            }

            $paymentStatusName =  $paymentStatus['paymentStatusName'];
            switch ($paymentStatus['paymentStatusID']) {
                case  1:
                    $paymentClass = 'orange';
                    break;
                case 2:
                    $paymentClass = 'light-blue';
                    break;
                case 3:
                    $paymentClass = 'bright-green';
                    break;
                case 4:
                    $paymentClass = 'danger';
                    break;
                case 5:
                    $paymentStatusName = 'Review Payment';
                    $paymentClass = 'primary';
                    break;
            }
        }


        $rows[] = [
            'bookingID' => $payments['bookingID'],
            'formattedBookingID' => $payments['formattedBookingID'],
            'name' => $name,
            'bookingType' => $payments['bookingType'],
            'checkIn' => $checkIn,
            'status' => $status,
            'statusClass' => $class,
            'bookingStatus' => $payments['bookingStatus'],
            'paymentMethod' => $payments['paymentMethod'],
            'paymentStatusName' =>  $paymentStatusName,
            'paymentClass' => $paymentClass,
            'userBalance' => '₱ ' . number_format($payments['userBalance'], 2),
            'totalBill' => '₱ ' . number_format($payments['finalBill'], 2) ?? '₱ ' . number_format($payments['totalCost'], 2)
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
