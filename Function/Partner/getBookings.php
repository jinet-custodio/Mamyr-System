<?php

require '../../Config/dbcon.php';

if (isset($_GET['id'])) {
    $userID = (int) $_GET['id'];
    try {
        $getPartnerBooking = $conn->prepare("SELECT
                    COUNT(DISTINCT b.bookingID) AS totalBookings,
                    COUNT(DISTINCT CASE WHEN bpas.approvalStatus = 2 THEN b.bookingID END) AS totalApprovedBooking,
                    COUNT(DISTINCT CASE WHEN bpas.approvalStatus = 1 THEN b.bookingID END) AS totalPendingBooking,
                    COUNT(DISTINCT CASE WHEN bpas.approvalStatus = 4 THEN b.bookingID END) AS totalCancelledBooking,
                    COUNT(DISTINCT CASE WHEN bpas.approvalStatus = 5 THEN b.bookingID END) AS rejectedBookings
                FROM booking b
                LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
                LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
                LEFT JOIN custompackageitem cpi ON cp.customPackageID = cpi.customPackageID
                LEFT JOIN service s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)
                LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
                LEFT JOIN partnership p ON ps.partnershipID = p.partnershipID
                WHERE p.userID = ?
                ");
        $getPartnerBooking->bind_param('i', $userID);
        if (!$getPartnerBooking->execute()) {
            throw new Exception("Error Executing Query: " . $getPartnerBooking->error);
        }
        $result = $getPartnerBooking->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            // error_log(print_r($data, true));
            $totalPendingBooking = $data['totalPendingBooking'] ?? 0;
            $allBookingStatus = $data['totalBookings'] ?? 0;
            $approvedBookings = $data['totalApprovedBooking'] ?? 0;
            $cancelledBooking = $data['totalCancelledBooking'] ?? 0;
            $rejectedBookings = $data['rejectedBookings'] ?? 0;

            echo json_encode([
                'success' => true,
                'totalPendingBooking' => $totalPendingBooking,
                'allBookingStatus' => $allBookingStatus,
                'cancelledBooking' => $cancelledBooking,
                'approvedBookings' => $approvedBookings,
                'rejectedBookings' => $rejectedBookings
            ]);
        }
    } catch (Exception $e) {
        error_log("Error: PartnerBooking: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'totalPendingBooking' => $totalPendingBooking,
            'allBookingStatus' => $allBookingStatus,
            'cancelledBooking' => $cancelledBooking,
            'approvedBookings' => $approvedBookings,
        ]);
    }
}
