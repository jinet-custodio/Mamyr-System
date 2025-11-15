
            <?php
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
            error_reporting(E_ALL);
            require '../../../Config/dbcon.php';

            header('Content-Type: application/json');

            $approvedStatusID = $partiallyPaid = 2;
            $doneStatusID = 6;
            $reservedStatusID = $fullyPaid = 3;
            $getCardData = $conn->prepare("SELECT
                                                    -- Guests this month
                                                    SUM(CASE 
                                                        WHEN YEAR(b.startDate) = YEAR(CURRENT_DATE()) 
                                                        AND MONTH(b.startDate) = MONTH(CURRENT_DATE()) 
                                                        THEN b.guestCount 
                                                        ELSE 0 
                                                    END) AS guestsThisMonth,

                                                    SUM(CASE
                                                        WHEN DATE(b.startDate) = CURDATE() THEN b.guestCount ELSE 0 END
                                                    ) AS guestToday,

                                                    COUNT(
                                                        CASE
                                                            WHEN YEAR(b.startDate) = YEAR(CURRENT_DATE())
                                                                AND MONTH(b.startDate) = MONTH(CURRENT_DATE())
                                                                AND b.bookingType = 'Resort'
                                                            THEN b.bookingID
                                                            ELSE NULL
                                                        END
                                                    ) AS resortCount,

                                                    COUNT(CASE
                                                        WHEN YEAR(b.startDate) = YEAR(CURRENT_DATE()) 
                                                            AND MONTH(b.startDate) = MONTH(CURRENT_DATE()) 
                                                            AND b.bookingType = 'Hotel'
                                                        THEN b.bookingID
                                                        ELSE NULL
                                                        END
                                                    ) AS hotelCount,

                                                    COUNT(CASE
                                                        WHEN YEAR(b.startDate) = YEAR(CURRENT_DATE()) 
                                                            AND MONTH(b.startDate) = MONTH(CURRENT_DATE()) 
                                                            AND b.bookingType = 'Event'
                                                        THEN b.bookingID
                                                        ELSE NULL
                                                        END
                                                    ) AS eventCount,

                                                    SUM(CASE 
                                                        WHEN YEAR(b.startDate) = YEAR(CURRENT_DATE()) 
                                                        AND MONTH(b.startDate) = MONTH(CURRENT_DATE()) 
                                                        THEN COALESCE(cb.finalBill, 0) 
                                                        ELSE 0 
                                                    END) AS salesThisMonth,

                                                    COUNT(CASE WHEN YEAR(b.startDate) = YEAR(CURRENT_DATE()) 
                                                        AND MONTH(b.startDate) = MONTH(CURRENT_DATE()) 
                                                        THEN b.bookingID 
                                                    END) AS bookingCount,

                                                    
                                                    -- Guests last month
                                                    SUM(
                                                        CASE 
                                                            WHEN DATE(b.startDate) >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') - INTERVAL 1 MONTH
                                                            AND DATE(b.startDate) <  DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
                                                            THEN b.guestCount
                                                            ELSE 0
                                                        END
                                                    ) AS guestsLastMonth

                                                    
                                                    -- Total guests overall
                                                    -- SUM(b.guestCount) AS totalGuests,

                                                FROM booking b
                                                LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                                                -- LEFT JOIN payment p ON cb.confirmedBookingID = p.confirmedBookingID
                                                WHERE 
                                                    b.bookingStatus IN (?, ?, ?) 
                                                    AND cb.paymentStatus IN (?,?) 
                                                    -- AND MONTH(b.startDate) = MONTH(CURDATE())
                                                    -- AND YEAR(b.startDate) = YEAR(CURDATE())
                                                ");
            $getCardData->bind_param('iiiii',  $approvedStatusID, $doneStatusID, $reservedStatusID,  $partiallyPaid, $fullyPaid);

            if (!$getCardData->execute()) {
                error_log("Error executing the card data query.");
            }
            $result = $getCardData->get_result();

            if ($result->num_rows === 0) {
                $bookingCount = $guestsThisMonth = $guestsLastMonth = $percentageThisMonth = $percentageLastMonth = $salesThisMonth = 'None';
            }

            $row = $result->fetch_assoc();

            $bookingCount = (int) $row['bookingCount'];
            $guestsThisMonth = (int) $row['guestsThisMonth'];
            $guestsLastMonth = (int) $row['guestsLastMonth'];
            $guestCountToday = (int) $row['guestToday'];
            // $guestsThisMonth = 29;
            // $guestsLastMonth = 55;

            $resort = (int) $row['resortCount'];
            $hotel = (int) $row['hotelCount'];
            $event = (int) $row['eventCount'];


            if ($resort === 0 && $hotel === 0 && $event === 0) {
                $mostBookedService = 'None';
            } else {
                $max = max($resort, $hotel, $event);
                $winners = [];
                if ($resort === $max) $winners[] = 'Resort';
                if ($hotel === $max) $winners[] = 'Hotel';
                if ($event === $max) $winners[] = 'Event';

                if (count($winners) > 1) {
                    $mostBookedService = implode(' & ', $winners);
                } else {
                    $mostBookedService = $winners[0];
                }
            }


            if ($guestsLastMonth === 0) {
                if ($guestsThisMonth === 0) {
                    $percentageChange = 0;
                } else {
                    $percentageChange = 100;
                }
            } else {
                $percentageChange = (($guestsThisMonth - $guestsLastMonth) / $guestsLastMonth) * 100;
            }


            $salesThisMonth = $row['salesThisMonth'] != 0 ?  number_format((float) $row['salesThisMonth'], 2) . ' php' : 'None';

            if ($percentageChange > 0) {
                $arrowClass = "bi-arrow-up-short";
                $statusClass = "text-success";
                $statusColor = 'rgba(135, 255, 124, 0.5)';
            } elseif ($percentageChange < 0) {
                $arrowClass = "bi-arrow-down-short";
                $statusClass = "text-danger";
                $statusColor = 'rgba(219, 53, 69, .5)';
            } else {
                $arrowClass = "bi-dash-lg";
                $statusClass = "text-muted";
                $statusColor = 'rgba(128, 128, 128, 0.5)';
            }

            $displayPercentage = abs($percentageChange);

            echo json_encode([
                'guestThisMonth' => $guestsThisMonth,
                'statusClass' => $statusClass,
                'statusColor' => $statusColor,
                'arrowClass' => $arrowClass,
                'displayPercentage' => number_format($displayPercentage, 1),
                'bookingCount' => $bookingCount,
                'salesThisMonth' => $salesThisMonth,
                'mostBookedService' => $mostBookedService,
                'guestCountToday' => $guestCountToday,
            ]);
