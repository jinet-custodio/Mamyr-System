<?php
require '../../../Config/dbcon.php';
session_start();
header('Content-Type: application/json');
$partner = 2;
$isApproved = true;

$selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName,  pt.partnerTypeDescription, ppt.otherPartnerType
    FROM partnership p
    INNER JOIN user u ON p.userID = u.userID
    INNER JOIN partnerstatus s ON s.partnerStatusID = p.partnerStatusID
    LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID AND isApproved = ?
    LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
    WHERE  u.userRole = ?
    ORDER BY p.startDate DESC");
$selectQuery->bind_param("ii", $isApproved, $partner);
$selectQuery->execute();
$result = $selectQuery->get_result();

// ob_start();
$table = [];
if ($result->num_rows > 0) {
    foreach ($result as $applicant) {
        $name = ucwords($applicant['firstName']) . " " . ucwords($applicant['lastName']);
        $partnerID = $applicant['partnershipID'];
        $date = $applicant['startDate'];
        $startDate = date("F d, Y — g:i A", strtotime($date));

        $type = strtolower(trim($applicant['partnerTypeDescription'])) === 'other'
            ? $applicant['otherPartnerType']
            : $applicant['partnerTypeDescription'];


        if (isset($table[$partnerID])) {
            $table[$partnerID]['types'][] = $type;
        } else {
            $table[$partnerID] = [
                'name' => $name,
                'partnershipID' => $partnerID,
                'startDate' => $startDate,
                'types' => [$type]
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'partners' => $table
]);


// if (!empty($table)) {
//     foreach ($table as $row) {
//         $typesString = implode(' & ', $row['types']);
//         echo "
//             <tr>
//                 <td>{$row['partnershipID']}</td>
//                 <td>{$row['name']}</td>
//                 <td>{$typesString}</td>
//                 <td>{$row['startDate']}</td>
//                 <td>
//                         <form action='partnership.php?container=3' method='POST'>
//                             <input type='hidden' name='partnerID' value='{$row['partnershipID']}'>
//                             <button type='submit' class='btn btn-info' name='view-btn'>View</button>
//                         </form>
//                 </td>
//             </tr>";
//     }
// } else {
//     echo '<tr><td colspan="5" class="text-center">No Record Found!</td></tr>';
// }

// echo ob_get_clean();
