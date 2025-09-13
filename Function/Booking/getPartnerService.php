 <?php

    require '../../Config/dbcon.php';

    header('Content-Type: application/json');

    if (isset($_GET['startDate']) && isset($_GET['endDate'])) {

        // Date & Time
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
        $availableID = 1;
        try {
            $getPartnerService = $conn->prepare(
                "SELECT 
                    ppt.partnerTypeID, 
                    p.companyName,
                    ps.partnershipServiceID, 
                    ps.PBName, 
                    ps.PBPrice,
                    pt.partnerTypeDescription AS eventCategory
                FROM `partnership` p
                LEFT JOIN `partnershipservice` ps ON p.partnershipID = ps.partnershipID
                LEFT JOIN `partnershiptype` pt ON p.partnerTypeID = pt.partnerTypeID
                LEFT JOIN `partnership_partnertype` ppt ON p.partnershipID = ppt.partnershipID
                WHERE ps.PSAvailabilityID = ?
                AND NOT EXISTS (
                    SELECT 1 
                    FROM `serviceunavailabledate` sud 
                    WHERE sud.partnershipServiceID = ps.partnershipServiceID
                    AND (? < sud.unavailableEndDate 
                    AND ? > sud.unavailableStartDate)
                             )
                GROUP BY partnerTypeID"
            );
            if (!$getPartnerService) {
                throw new Exception("Error at query Partner Service: " . $getPartnerService->error);
            }

            $getPartnerService->bind_param('iss', $availableID, $startDate, $endDate);

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
            exit;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            echo json_encode(
                ['Error' => 'Error fetching partner services']
            );
            exit;
        }
    }
