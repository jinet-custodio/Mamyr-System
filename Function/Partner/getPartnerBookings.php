<?php

require '../../Config/dbcon.php';
require '../Helpers/statusFunctions.php';


header('Content-Type: application/json');

if (isset($_GET['userID'])) {

    $userID = (int) $_GET['userID'];
    try {
        $getAvailedService = $conn->prepare("SELECT 
                                                b.bookingID,
                                                LPAD(b.bookingID, 4, '0') AS formattedBookingID,
                                                b.userID AS guestID,
                                                u.userRole,
                                                u.userProfile,
                                                MAX(u.firstName) AS firstName,
                                                MAX(u.lastName) AS lastName,
                                                MAX(u.email) AS email,
                                                MAX(u.phoneNumber) AS phoneNumber,
                                                MAX(u.userAddress) AS userAddress,
                                                bpas.price AS price,
                                                MAX(b.bookingType) AS bookingType,
                                                MAX(b.durationCount) AS durationCount,
                                                MAX(b.startDate) AS startDate,
                                                MAX(b.endDate) AS endDate,
                                                GROUP_CONCAT(DISTINCT ra.resortServiceID) AS resortServiceID,
                                                GROUP_CONCAT(DISTINCT ra.RServiceName) AS RServiceName,
                                                MAX(b.additionalRequest) AS additionalRequest,
                                                GROUP_CONCAT(DISTINCT ps.PBName) AS PBName,
                                                MAX(bpas.approvalStatus) AS approvalStatus,
                                                MAX(ec.categoryName) AS categoryName,
                                                bpas.availedDate,
                                                b.createdAt
                                            FROM businesspartneravailedservice bpas
                                            LEFT JOIN booking b ON bpas.bookingID = b.bookingID
                                            LEFT JOIN user u ON u.userID = b.userID
                                            LEFT JOIN partnershipservice ps ON bpas.partnershipServiceID = ps.partnershipServiceID
                                            LEFT JOIN partnership p ON ps.partnershipID = p.partnershipID
                                            LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
                                            LEFT JOIN custompackageitem cpi ON cp.customPackageID = cpi.customPackageID
                                            LEFT JOIN service s ON cpi.serviceID = s.serviceID
                                            LEFT JOIN resortamenity ra ON s.resortServiceID = ra.resortServiceID
                                            LEFT JOIN eventcategory ec ON cp.eventTypeID = ec.categoryID
                                            WHERE p.userID = ?
                                            GROUP BY b.bookingID
                                            ORDER BY b.createdAt DESC
                                            ");
        $getAvailedService->bind_param('i', $userID);
        if (!$getAvailedService->execute()) {
            throw new Exception("Error executing availed service: User ID: $userID. Error=>" . $getAvailedService->error);
        }

        $result = $getAvailedService->get_result();

        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            // Date and Time
            $rawStartDate = $row['startDate'] ?? null;
            $rawEndDate = $row['endDate'] ?? null;
            $availedDate = new DateTime($row['availedDate'] ?? $row['createdAt']);
            $arrivalTime = !empty($row['arrivalTime'])
                ? date('H:i A', strtotime($row['arrivalTime']))
                : 'Not Stated';

            $startDate = !empty($rawStartDate)
                ? date('M. d, Y', strtotime($rawStartDate))
                : 'Not Stated';

            $endDate = !empty($rawEndDate)
                ? date('M. d, Y', strtotime($rawEndDate))
                : 'Not Stated';

            $createdAt = $row['createdAt'] ?? null;

            if (!empty($rawStartDate) || $rawStartDate ===  $rawEndDate) {
                $bookingDate = date('M. d, Y', strtotime($rawStartDate));
            } elseif (!empty($rawStartDate) && !empty($rawEndDate)) {
                $bookingDate = $startDate . " to " . $endDate;
            } else {
                $bookingDate = 'Date not available';
            }

            $time = date("g:i A", strtotime($rawStartDate)) . " - " . date("g:i A", strtotime($rawEndDate));
            $duration = $row['durationCount'] . " hours";

            $approvalTimeRange = (clone $availedDate)->modify('+24 hours');
            $approvalTimeUntil = $approvalTimeRange->format('M. d,Y g:i A');

            $profile = $row['userProfile'];
            if (!empty($profile)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $profile);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
            }

            $venue = $row['RServiceName'] ?? null;

            //Status
            $status = getStatuses($conn, $row['approvalStatus']);
            $statusName = ucwords($status['statusName']);
            switch ($statusName) {
                case 'Pending':
                    $className = 'warning';
                    break;
                case 'Approved':
                    $className = 'success';
                    break;
                case 'Cancelled':
                    $className = 'red';
                    break;
                case 'Rejected':
                    $className = 'danger';
                    break;
                case 'Done':
                    $className = 'light-green';
                    break;
                case 'Expired':
                    $className = 'secondary';
                    break;
                default:
                    $className = 'warning';
                    break;
            }
            $eventType =  $row['categoryName'] ?? 'N/A';

            $serviceInfo =  $row['PBName'] . " — ₱" . number_format($row['price'], 2);

            $bookings[] = [
                'formattedBookingID' => $row['formattedBookingID'],
                'bookingID' => $row['bookingID'],
                'guestRole' => $row['userRole'],
                'guestID' => $row['guestID'],
                'guestName' => $row['firstName'] . ' ' . $row['lastName'],
                'bookingType' => $row['bookingType'] . ' Booking',
                'service' => ucfirst($row['PBName'] ?? ''),
                'bookingDate' =>  $bookingDate,
                'color' => $className,
                'statusName' => $statusName,
                'contact' => $row['email'] . " | " . $row['phoneNumber'],
                'address' => $row['userAddress'] ?? 'Not Stated',
                'eventType' => $eventType,
                'timeDuration' => $time . "(" . $duration . ")",
                'venue' => $venue,
                'notes' => $row['additionalRequest'],
                'serviceInfo' =>  $serviceInfo,
                'approvalTimeUntil' =>  $approvalTimeUntil,
                'profileImage' => $image
            ];
        }

        echo json_encode([
            'success' => true,
            'bookings' => $bookings
        ]);
    } catch (Exception $e) {
        error_log("An error occured. Error-> " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Unexpected server error. Please try again later.'
        ]);
    }
}
