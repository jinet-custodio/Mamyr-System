 <?php

    require '../../Config/dbcon.php';

    header('Content-Type: application/json');

    if (isset($_GET['startDate']) && isset($_GET['endDate'])) {

        // Date & Time
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
        $availableID = 1;
        $approvedPartner = 2;
        $isApproved = true;
        try {
            $getPartnerService = $conn->prepare(
                "SELECT 
                    p.partnershipID,
                    u.phoneNumber,
                    p.businessEmail,
                    ppt.partnerTypeID, 
                    p.companyName,
                    ps.partnershipServiceID, 
                    ps.PBName, 
                    ps.PBPrice,
                    pt.partnerTypeDescription AS eventCategory
                FROM `partnership` p
                LEFT JOIN `partnershipservice` ps ON p.partnershipID = ps.partnershipID
                LEFT JOIN  `user` u ON p.userID = u.userID
                LEFT JOIN `partnership_partnertype` ppt ON p.partnershipID = ppt.partnershipID
                LEFT JOIN `partnershiptype` pt ON ppt.partnerTypeID = pt.partnerTypeID
                WHERE ps.PSAvailabilityID = ? AND p.partnerStatusID = ? AND ppt.isApproved = ?
                AND NOT EXISTS 
                (
                    SELECT 1 
                    FROM `serviceunavailabledate` sud 
                    WHERE sud.partnershipServiceID = ps.partnershipServiceID
                    AND (? < sud.unavailableEndDate 
                    AND ? > sud.unavailableStartDate) AND sud.status IN ('confirmed', 'hold')
                )"
            );
            if (!$getPartnerService) {
                throw new Exception("Error at query Partner Service: " . $getPartnerService->error);
            }

            $getPartnerService->bind_param('iiiss', $availableID, $approvedPartner, $isApproved, $startDate, $endDate);

            if (!$getPartnerService->execute()) {
                throw new Exception("Error at query Partner Service execution: " . $getPartnerService->error);
            }

            $result = $getPartnerService->get_result();
            if ($result->num_rows === 0) {
                echo json_encode([
                    'Message' => 'No Business Partner Services Available',
                    'Categories' => []
                ]);
                exit;
            }

            $serviceData['Category'] = [];
            while ($row = $result->fetch_assoc()) {
                $serviceData['Category'][] = $row;
            }

            echo json_encode([
                'Message' => 'Successfull Fetching Services',
                'Categories' => $serviceData['Category']
            ]);

            // error_log(print_r($serviceData['Category'], true));
            exit;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            echo json_encode(
                ['Error' => 'Error fetching partner services']
            );
            exit;
        }
    }
