<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

$_SESSION['selectedUserID'] = mysqli_real_escape_string($conn, $_POST['selectedUserID']);
$selectedUserID = $_SESSION['selectedUserID'];
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
        href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/viewUser.css" />

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">
</head>

<body>

    <!-- Get User Info (Customer pa lang nagagawa ko) -->

    <?php
    $getUserData = $conn->prepare("SELECT u.*,
                ut.typeName as roleName, 
                us.statusName as userStatusName,
                b.*, cb.*, p.*,
                s.statusName, u.createdAt AS userCreatedAt
                FROM user u
                LEFT JOIN usertype ut ON u.userRole = ut.userTypeID
                LEFT JOIN userstatus us ON u.userStatusID = us.userStatusID
                LEFT JOIN partnership p ON  u.userID = p.userID
                LEFT JOIN booking b ON u.userID = b.userID
                LEFT JOIN confirmedbooking cb ON b.bookingID = cb.bookingID
                LEFT JOIN status s ON  cb.paymentApprovalStatus = s.statusID
                WHERE u.userID = ?");
    $getUserData->bind_param("i", $selectedUserID);
    $getUserData->execute();
    $getUserDataResult =  $getUserData->get_result();
    if ($getUserDataResult->num_rows > 0) {
        $data =  $getUserDataResult->fetch_assoc();
        $middleInitial = trim($data['middleInitial']);
        $name = ucfirst($data['firstName']) . " " . ucfirst($data['middleInitial']) . " "  . ucfirst($data['lastName']);
        $email = $data['email'];
        $phoneNumber = $data['phoneNumber'];
        if ($phoneNumber === NULL || $phoneNumber === "") {
            $phoneNumber = "--";
        } else {
            $phoneNumber;
        }
        $birthday = $data['birthDate'];
        if ($birthday === NULL || $birthday === "") {
            $type = "text";
            $birthday = "--";
        } else {
            $type = "date";
            $birthday;
        }
        $accountCreated = date('Y-m-d', strtotime($data['userCreatedAt']));
        $address = $data['userAddress'];
        $profile = $data['userProfile'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $profile);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);


        //Count number of bookings (cancelled, booked, pending)
        $confirmedBookingQuery = "SELECT  userBalance, COUNT(*) AS confirmedBookingCount FROM confirmedbooking cb
        LEFT JOIN booking b ON cb.bookingID = b.bookingID
        WHERE userID = '$selectedUserID'";
        $confirmbookingresult = $conn->query($confirmedBookingQuery);

        if (mysqli_num_rows($confirmbookingresult) > 0) {
            $row = mysqli_fetch_assoc($confirmbookingresult);
            $confirmedBookingCount = $row['confirmedBookingCount'];
            $userBalance = $data['userBalance'];
            if ($confirmedBookingCount > 0 || $userBalance > 0) {
                $confirmedBookingCount;
                $userBalance;
            } else {
                $confirmedBookingCount = "None";
                $userBalance = "No Balance";
            }
        }
    }
    ?>

    <a href="userManagement.php" class="back-button btn"><img src="../../Assets/Images/Icon/arrowBtnBlue.png" alt=""></a>
    <div class="wrapper">
        <div class="card">
            <div class="card-header">
                <div class="profile-image">
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($data['firstName']) ?> Picture" class="profile-pic">
                </div>
                <div class="profile-info">
                    <h5 class="account-name"> <?= htmlspecialchars($data['firstName']) ?></h5>
                    <h6 class="account-contact"> <?= htmlspecialchars($email) ?> | <?= htmlspecialchars($phoneNumber) ?></h6>
                    <h6 class="roleName"><?= htmlspecialchars($data['roleName']) ?></h6>
                </div>
            </div>
            <div class="card-body">
                <div class="user-details">
                    <div class="info form-floating">
                        <input type="text" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($name) ?>" readonly>
                        <label for="floatingInputValue">Full Name</label>
                    </div>
                    <div class="info form-floating">
                        <input type="<?= $type ?>" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($phoneNumber) ?>" readonly>
                        <label for="floatingInputValue">Phone Number</label>
                    </div>
                    <div class="info form-floating">
                        <input type="<?= $type ?>" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($birthday) ?>" readonly>
                        <label for="floatingInputValue">Birthday</label>
                    </div>
                    <div class="info form-floating">
                        <input type="texts" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($address) ?>" readonly>
                        <label for="floatingInputValue">Address</label>
                    </div>
                    <div class="info form-floating">
                        <input type="text" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($data['userStatusName']) ?>" readonly>
                        <label for="floatingInputValue">Status</label>
                    </div>

                    <div class="info form-floating">
                        <input type="text" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($confirmedBookingCount) ?>" readonly>
                        <label for="floatingInputValue">Bookings Made</label>
                    </div>
                    <div class="info form-floating">
                        <input type="text" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($userBalance) ?>" readonly>
                        <label for="floatingInputValue">Balance</label>
                    </div>
                    <input type="hidden" id="roleName" value="<?= htmlspecialchars($data['roleName']) ?>">
                    <div class="info form-floating" id="companyName" style="display: none;">
                        <input type=" text" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($data['companyName']) ?>" readonly>
                        <label for="floatingInputValue">Company Name</label>
                    </div>
                    <div class="info form-floating">
                        <input type="text" class="form-control" id="floatingInputValue" value="<?= htmlspecialchars($accountCreated) ?>" readonly>
                        <label for="floatingInputValue">Account Creation Date</label>
                    </div>
                </div>
            </div>
        </div>


        <!-- Bootstrap Link -->
        <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

        <script>
            const role = document.getElementById("roleName");

            if (role && role.value === 'Partner') {
                document.getElementById("companyName").style.display = 'block';
            }
        </script>

</body>

</html>