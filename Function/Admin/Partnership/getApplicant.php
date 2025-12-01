<?php
require '../../../Config/dbcon.php';
session_start();
header('Content-Type: application/json');
$pendingStatus = 1;
$rejectedStatus = 3;
$applicant = 4;

$selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName,  pt.partnerTypeDescription, ppt.otherPartnerType
    FROM partnership p
    INNER JOIN user u ON p.userID = u.userID
    INNER JOIN partnerstatus s ON s.partnerStatusID = p.partnerStatusID
    LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID
    LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
    WHERE (p.partnerStatusID = ? OR p.partnerStatusID = ?) AND u.userRole = ?
    ORDER BY p.requestDate DESC");
$selectQuery->bind_param("iii", $pendingStatus, $rejectedStatus, $applicant);
$selectQuery->execute();
$result = $selectQuery->get_result();

// ob_start();
$table = [];

if ($result->num_rows > 0) {
    foreach ($result as $applicant) {
        $partnerID = $applicant['partnershipID'];

        // Build the name once
        $name = ucwords($applicant['firstName']) . " " . ucwords($applicant['lastName']);
        $status = $applicant['statusName'];
        $statusClass = ($status == 'Pending') ? 'bg-warning' : 'bg-danger';
        $requestDate = date("F d, Y — g:i A", strtotime($applicant['requestDate']));

        $type = strtolower(trim($applicant['partnerTypeDescription'])) === 'other'
            ? $applicant['otherPartnerType']
            : $applicant['partnerTypeDescription'];


        if (isset($table[$partnerID])) {
            $table[$partnerID]['types'][] = $type;
        } else {
            $table[$partnerID] = [
                'name' => $name,
                'partnershipID' => $partnerID,
                'requestDate' => $requestDate,
                'class' => $statusClass,
                'status' => $status,
                'types' => [$type]
            ];
        }
    }
}


echo json_encode([
    'success' => true,
    'applicants' => $table
]);


// if (!empty($table)) {
//     foreach ($table as $row) {
//         $typesString = implode(' & ', $row['types']);
//         echo "
//             <tr>
//                 <td>{$row['partnershipID']}</td>
//                 <td>{$row['name']}</td>
//                 <td>{$typesString}</td>
//                 <td>{$row['requestDate']}</td>
//                 <td><span class='badge {$row['class']}'>{$row['status']}</span></td>
//                 <td>
//                     <form action='partnership.php?container=4' method='POST'>
//                         <input type='hidden' name='partnerID' value='{$row['partnershipID']}'>
//                         <button type='submit' class='btn btn-info w-50 viewApplicantBtn' name='view-partner'>View</button>
//                     </form>
//                 </td>
//             </tr>";
//     }
// } else {
//     echo "<tr><td colspan='6' class='text-center'>No requests found.</td></tr>";
// }

// echo ob_get_clean();
