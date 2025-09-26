<?php
require '../../../Config/dbcon.php';
session_start();

$pendingStatus = 1;
$rejectedStatus = 3;
$applicant = 4;

$selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName,  GROUP_CONCAT(pt.partnerTypeDescription SEPARATOR ' & ') AS partnerTypeDescription
    FROM partnership p
    INNER JOIN user u ON p.userID = u.userID
    INNER JOIN status s ON s.statusID = p.partnerStatusID
    LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID
    LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
    WHERE (p.partnerStatusID = ? OR p.partnerStatusID = ?) AND u.userRole = ?
    GROUP BY p.partnershipID");
$selectQuery->bind_param("iii", $pendingStatus, $rejectedStatus, $applicant);
$selectQuery->execute();
$result = $selectQuery->get_result();

ob_start();

if ($result->num_rows > 0) {
    foreach ($result as $applicant) {
        $name = ucwords($applicant['firstName']) . " " . ucwords($applicant['lastName']);
        $partnerID = $applicant['partnershipID'];
        $status = $applicant['statusName'];
        $date = $applicant['requestDate'];
        $requestDate = date("F d, Y — g:i A", strtotime($date));

        $statusClass = ($status == 'Pending') ? 'btn-warning' : 'btn-danger';
        $statusColor = ($status == 'Pending') ? '#ffc108' : 'rgb(219, 53, 69)';
        $statusTextColor = ($status == 'Pending') ? 'black' : '#fff';

        echo "
            <tr>
                <td>{$name}</td>
                <td>{$applicant['partnerTypeDescription']}</td>
                <td>{$requestDate}</td>
                <td class='btn {$statusClass} w-75 d-block m-auto mt-1' style='background-color:{$statusColor}; color:{$statusTextColor};'>{$status}</td>
                <td>
                    <form action='partnership.php?container=4' method='POST'>
                        <input type='hidden' name='partnerID' value='{$partnerID}'>
                        <button type='submit' class='btn btn-info w-75' name='view-partner'>View</button>
                    </form>
                </td>
            </tr>";
    }
} else {
    echo '<tr><td colspan="5" class="text-center">No Record Found!</td></tr>';
}

echo ob_get_clean();
