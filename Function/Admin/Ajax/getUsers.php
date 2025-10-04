<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../../Config/dbcon.php';
require '../../Helpers/userFunctions.php';
header('Content-Type: application/json');
session_start();
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

$deletedUserID = 4;

try {
    $getUsersQuery = $conn->prepare("SELECT userID, firstName, lastName, middleInitial, email, userRole as userRoleID,       userStatusID, createdAt 
                            FROM user 
                            WHERE userID != ? AND  userStatusID != ?
                            ORDER BY userRole ASC");
    $getUsersQuery->bind_param("ii", $userID, $deletedUserID);

    if (!$getUsersQuery->execute()) {
        throw new Exception("Execution Failed: " . $getUsersQuery->error);
    }

    $userResult = $getUsersQuery->get_result();
    $users = [];
    while ($row = $userResult->fetch_assoc()) {
        $middleInitial = trim($row['middleInitial'] ?? '');
        $name = ucfirst($row['firstName']) . " " . ucfirst($middleInitial) . " " . ucfirst($row['lastName']);
        $email = $row['email'];

        $userRole = getUserRole($conn, $row['userRoleID']);
        $userRoleName = strtolower($userRole['userTypeName']);

        switch ($userRoleName) {
            case 'admin':
                $roleName = $userRoleName;
                break;
            case 'customer':
                $roleName = $userRoleName;
                break;
            case 'partner':
                $roleName = $userRoleName;
                break;
            case 'partner request':
                $roleName = 'Applicant';
                break;
            default:
                $roleName = 'Customer';
                break;
        }

        $accountStatus = getUserStatus($conn, $row['userStatusID']);

        switch ($accountStatus['userStatusID']) {
            case 1:
                $statusName = $accountStatus['userStatusName'];
                break;
            case 2:
                $statusName = $accountStatus['userStatusName'];
                break;
            case 3:
                $statusName = $accountStatus['userStatusName'];
                break;
            case 4:
                $statusName = $accountStatus['userStatusName'];
                break;
            default:
                $statusName = 'Pending';
                break;
        }

        $creationDate = date('M. d, Y', strtotime($row['createdAt']));

        $users[] = [
            'userID' => $row['userID'],
            'name' => $name,
            'email' => $email,
            'role' => ucfirst($roleName),
            'status' => $statusName,
            'date' => $creationDate
        ];
    }

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected server error. Please try again later.'
    ]);
}
