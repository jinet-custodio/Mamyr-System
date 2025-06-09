<?php
require '../../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" />

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Admin/Account/userManagement.css" />

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/datatables.min.css">
</head>

<body>

    <!-- Get User Info -->

    <?php
    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $userData = mysqli_fetch_assoc($result);
    }
    ?>

    <!-- Side Bar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h5>Account Settings</h5>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/user.png" alt="" class="sidebar-icon">
                    Profile Information
                </a>
            </li>
            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/login_security.png" alt="" class="sidebar-icon">
                    Login & Security
                </a>
            </li>
            <li>
                <a href="userManagement.php" class="list-group-item active">
                    <img src="../../../Assets/Images/Icon/usermanagement.png" alt="" class="sidebar-icon">
                    User Management
                </a>
            </li>
            <!-- <li>
                <a href="" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/systempreferences.png" alt="" class="sidebar-icon">
                    System Preferences
                </a>
            </li> -->
            <li>
                <a href="" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/revenue.png" alt="" class="sidebar-icon">
                    Revenue
                </a>
            </li>
            <li>
                <a href="deleteAccount.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/delete-user.png" alt="" class="sidebar-icon">
                    Delete Account
                </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img src="../../../Assets/Images/Icon/logout.png" alt="" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>
    <!-- End Side Bar -->

    <a href="../adminDashboard.php" class="home-button btn btn-primary"><img src="../../../Assets/Images/Icon/home2.png" alt=""></a>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">User Management</h5>
            <a href="" class="btn btn-light add-button"><img src="../../../Assets/Images/Icon/addUser.png" alt=""> Add an Account</a>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="usertable">
                <thead>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <!-- <th scope="col">Role</th> -->
                    <th scope="col">Status</th>
                    <th scope="col">Date Created</th>
                    <th scope="col">Action</th>
                </thead>
                <tbody>
                    <?php
                    $selectUsers = "SELECT u.*, ut.typeName as roleName, stat.statusName as status
                    FROM users u
                    INNER JOIN usertypes ut ON u.userRole = ut.userTypeID
                    INNER JOIN userstatuses stat ON u.userStatusID = stat.userStatusID
                    WHERE u.userID != '$userID' AND u.userRole != '$userRole'
                    ORDER BY u.userRole DESC";
                    $userResult = mysqli_query($conn, $selectUsers);
                    if (mysqli_num_rows($userResult) > 0) {
                        foreach ($userResult as $userData) {
                            $middleInitial = trim($userData['middleInitial']);
                            $name = ucfirst($userData['firstName']) . ($middleInitial ? " " . ucfirst($middleInitial) . "." : "") . " " . ucfirst($userData['lastName']);
                            $status =  $userData['status'];
                            $dataCreated = date("m-d-y", strtotime($userData['createdAt']));
                            if ($status === 'Verified') {
                                $class = 'badge badge-success';
                            } elseif ($status === 'Pending') {
                                $class = 'badge badge-warning';
                            }
                    ?>
                            <tr scope="row">
                                <td><?= htmlspecialchars($name) ?></td>
                                <td><?= htmlspecialchars($userData['email']) ?> </td>
                                <!-- <td><?= htmlspecialchars(ucfirst($userData['roleName'])) ?> </td> -->
                                <td class="<?= $class ?>"><?= htmlspecialchars(ucfirst($userData['status'])) ?> </td>
                                <td><?= htmlspecialchars($dataCreated) ?> </td>
                                <td>
                                    <div class="button">
                                        <form action="" method="POST">
                                            <a href="" class="btn btn-info">View</a>
                                            <button type="submit" class="btn btn-danger" name="deleteUser">Delete</button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="5">No users found.</td></tr>';
                    }
                    ?>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap Link -->
    <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#usertable').DataTable();
        });
    </script>
</body>

</html>