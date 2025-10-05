<?php
require '../../../Config/dbcon.php';
session_start();

$partner = 2;
$isApproved = true;
$selectQuery = $conn->prepare("SELECT u.firstName, u.lastName, p.*, s.statusName,  GROUP_CONCAT(pt.partnerTypeDescription SEPARATOR ' & ') AS partnerTypeDescription
    FROM partnership p
    INNER JOIN user u ON p.userID = u.userID
    INNER JOIN status s ON s.statusID = p.partnerStatusID
    LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID AND isApproved = ?
    LEFT JOIN partnershiptype pt ON pt.partnerTypeID = ppt.partnerTypeID
    WHERE u.userRole = ? 
    GROUP BY p.partnershipID");
$selectQuery->bind_param("ii", $isApproved, $partner);
$selectQuery->execute();
$result = $selectQuery->get_result();

ob_start();

if ($result->num_rows > 0) {
    foreach ($result as $applicant) {
        $name = ucwords($applicant['firstName']) . " " . ucwords($applicant['lastName']);
        $partnerID = $applicant['partnershipID'];
        $date = $applicant['startDate'];
        $startDate = date("F d, Y — g:i A", strtotime($date));
        echo "
            <tr>
                <td>{$name}</td>
                <td>" . ($applicant['partnerTypeDescription'] ?? 'N/A') . "</td>
                <td>{$startDate}</td>
                <td>
                    <form action='partnership.php?container=3' method='POST'>
                        <input type='hidden' name='partnerID' value='{$partnerID}'>
                        <button type='submit' class='btn btn-info' name='view-btn'>View</button>
                    </form>
                </td>
            </tr>";
    }
} else {
    echo '<tr><td colspan="4" class="text-center">No Record Found!</td></tr>';
}

echo ob_get_clean();
