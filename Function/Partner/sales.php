              
  <?php


    function getSales($conn, $userID)
    {
        $paymentStatusID = 3; //Fully Paid ID
        $approveStatusID = 5; //Done
        $getPartnerSalesQuery = $conn->prepare("SELECT b.bookingID, bs.bookingServicePrice, cpi.servicePrice, s.serviceType, s.serviceID
              FROM booking b
              LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
              LEFT JOIN bookingservice bs ON b.bookingID = bs.bookingID
              LEFT JOIN custompackage cp ON b.customPackageID = cp.customPackageID
              LEFT JOIN custompackageitem cpi ON cp.customPackageID = cpi.customPackageID
              LEFT JOIN service s ON (bs.serviceID = s.serviceID OR cpi.serviceID = s.serviceID)
              LEFT JOIN partnershipservice ps ON s.partnershipServiceID = ps.partnershipServiceID
              LEFT JOIN partnership p ON ps.partnershipID = p.partnershipID
              WHERE p.userID = ? AND cb.paymentApprovalStatus = ? AND cb.paymentStatus = ?
              ");
        $getPartnerSalesQuery->bind_param("iii", $userID, $approveStatusID, $paymentStatusID);
        if (!$getPartnerSalesQuery->execute()) {
            error_log("Error executing the query" . $getPartnerSalesQuery->error);
        }

        $result = $getPartnerSalesQuery->get_result();
        $totalSales = 0;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $serviceID = $row['serviceID'];
                $serviceType = $row['serviceType'];
                if (!empty($serviceID)) {
                    if ($serviceType === 'Partnership') {
                        $price = $row['bookingServicePrice'] ?? $row['servicePrice'];
                        $totalSales += $price;
                    }
                }
            }
        }
        return $totalSales;
    }
