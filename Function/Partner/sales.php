<?php


function getSales($conn, $userID)
{
    $paymentStatusID = 2; //Partially Paid ID
    $approveStatusID = 2; //Approved
    $approvalStatus = 2;
    $getPartnerSalesQuery = $conn->prepare("SELECT b.bookingID, bpas.price, p.userID
                    FROM booking b
                    LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                    LEFT JOIN businesspartneravailedservice bpas ON b.bookingID = bpas.bookingID
                    LEFT JOIN partnershipservice ps ON bpas.partnershipServiceID = ps.partnershipServiceID 
                    LEFT JOIN partnership p ON ps.partnershipID = p.partnershipID
                    LEFT JOIN payment pay ON cb.confirmedBookingID = pay.confirmedBookingID
                    WHERE p.userID = ? AND cb.paymentApprovalStatus = ? AND pay.paymentStatus = ? AND bpas.approvalStatus = ?
        ");
    $getPartnerSalesQuery->bind_param("iiii", $userID, $approveStatusID, $paymentStatusID, $approvalStatus);
    if (!$getPartnerSalesQuery->execute()) {
        error_log("Error executing the query" . $getPartnerSalesQuery->error);
    }

    $result = $getPartnerSalesQuery->get_result();
    $totalSales = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $price = $row['price'];
            $totalSales += $price;
        }
    }
    return $totalSales;
}
