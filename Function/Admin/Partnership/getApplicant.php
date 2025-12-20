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
