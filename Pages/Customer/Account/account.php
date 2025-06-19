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
    <title>User Account - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/account.css" />

</head>

<body>

    <!-- Get the information to the database -->
    <?php
    if ($userRole == 1) {
        $Customer = "Customer";
    } else {
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
    }

    if ($Customer === "Customer") {
        $query = "SELECT u.*, ut.typeName as roleName FROM users u
            INNER JOIN usertypes ut ON u.userRole = ut.userTypeID
            WHERE u.userID = '$userID' AND userRole = '$userRole'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
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
            $address = $data['userAddress'];
            $profile = $data['userProfile'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $profile);
            finfo_close($finfo);
            $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
        }
    } else {
        $_SESSION['error'] = "Unauthorized Access!";
        header("Location: ../register.php");
        exit();
    }
    ?>
    <!-- Side Bar -->
    <div class="sidebar">

        <div class="home">
            <a href="../dashboard.php"><img src="../../../Assets/Images/Icon/home2.png" alt="home icon"
                    class="homeIcon"></a>
        </div>
        <div class="sidebar-header">
            <h5>User Account</h5>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item active">
                    <img src="../../../Assets/Images/Icon/user.png" alt="Profile Information" class="sidebar-icon">
                    Profile Information
                </a>
            </li>

            <li>
                <a href="userManagement.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/bookingHistory.png" alt="Booking History"
                        class="sidebar-icon">
                    Booking History
                </a>
            </li>

            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/login_security.png" alt="Login Security" class="sidebar-icon">
                    Login & Security
                </a>
            </li>


            <a href="deleteAccount.php" class="list-group-item">
                <img src="../../../Assets/Images/Icon/delete-user.png" alt="Delete Account" class="sidebar-icon">
                Delete Account
            </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>
    <!-- End Side Bar -->


    <!-- Customer Information Container -->
    <div class="customer-account-container">
        <form action="../../../Function/Customer/Account/editProfile.php" method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="account-info">
                    <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
                    <input type="hidden" name="userRole" value="<?= htmlspecialchars($userRole) ?>">
                    <div class="profile-image">
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($data['firstName']) ?> Picture" class="profile-pic">
                        <button type="button" class="changePfpBtn btn btn-primary" id="changePfp">
                            Change Profile
                        </button>
                        <!-- Profile Picture Modal -->
                        <div class="modal" id="picModal" tabindex="-1" aria-labelledby="picModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="picModalLabel">Change Profile Picture</h5>
                                        <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($data['firstName']) ?> Picture" id="preview" class="profile-pic">
                                        <input type="file" name="profilePic" id="profilePic" hidden>
                                        <label for="profilePic" class="custom-file-button btn btn-outline-primary">Choose Image</label>
                                    </div>
                                    <div class="modal-button">
                                        <button type="submit" class="btn btn-danger" name="cancelPfp">Cancel</button>
                                        <button type="submit" class="btn btn-success" name="changePfpBtn">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-info">
                        <h5 class="account-name"> <?= htmlspecialchars($data['firstName']) ?></h5>
                        <h6 class="account-contact"> <?= htmlspecialchars($email) ?> | <?= htmlspecialchars($phoneNumber) ?></h6>
                        <h6 class="roleName"><?= htmlspecialchars($data['roleName']) ?></h6>
                    </div>
                </div>
        </form>

        <form action="../../../Function/Customer/Account/editAccount.php" method="POST">
            <div class="customer-details">
                <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
                <input type="hidden" name="userRole" value="<?= htmlspecialchars($userRole) ?>">
                <div class="info">
                    <input type="text" name="fullName" id="fullName" value="<?= htmlspecialchars($name) ?>" disabled required>
                    <label for="fullName">Full Name</label>
                </div>
                <div class="info">
                    <input type="<?= htmlspecialchars($type) ?>" name="birthday" id="birthday" value="<?= htmlspecialchars($birthday) ?>" disabled>
                    <label for="birthday">Birthday</label>
                </div>
                <div class="info">
                    <input type="text" name="address" id="address" value="<?= htmlspecialchars($address) ?>" disabled required>
                    <label for="address">Address</label>
                </div>
                <div class="info">
                    <input type="text" name="phoneNumber" id="phoneNumber" value="<?= htmlspecialchars($phoneNumber) ?>" disabled required>
                    <label for="phoneNumber">Phone Number</label>
                </div>
            </div>
            <div class="button-container">
                <button type="button" class="edit btn btn-primary" name="changeDetails" id="editBtn" onclick="enableEditing()">Edit</button>
                <button type="submit" name="cancelChanges" id="cancelBtn" class="change-info btn btn-danger" style="display: none;">Cancel</button>
                <button type="submit" name="saveChanges" id="saveBtn" class="change-info btn btn-primary" style="display: none;">Save</button>
            </div>
        </form>
    </div>


    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>
        //Show the image preview
        document.querySelector("input[type='file']").addEventListener("change", function(event) {
            let reader = new FileReader();
            reader.onload = function() {
                let preview = document.getElementById("preview");
                preview.src = reader.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>

    <script>
        //Show Modal
        document.addEventListener("DOMContentLoaded", function() {
            const changeBtn = document.getElementById("changePfp");
            const modalElement = document.getElementById("picModal");

            changeBtn.addEventListener("click", function() {
                const myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            });
        });
    </script>

    <script>
        //All the disbaled input box will be removed
        function enableEditing() {
            document.getElementById("fullName").removeAttribute("disabled");
            document.getElementById("address").removeAttribute("disabled");
            document.getElementById("phoneNumber").removeAttribute("disabled");
            document.getElementById("birthday").removeAttribute("disabled");

            document.getElementById("saveBtn").style.display = "inline-block";
            document.getElementById("cancelBtn").style.display = "inline-block";
            document.getElementById("editBtn").style.display = "none";
        }
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweetalert Popup -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('message');

        if (paramValue === 'success-image') {
            Swal.fire({
                title: "Success!",
                text: "Profile Change Successfully!",
                icon: "success"
            });
        } else if (paramValue === 'error-image') {
            Swal.fire({
                title: "Info!",
                text: "No Image Selected",
                icon: "info"
            });
        } else if (paramValue === 'success-change') {
            Swal.fire({
                title: "Success!",
                text: "Updated Successfully!",
                icon: "success"
            });
        } else if (paramValue === 'error-change') {
            Swal.fire({
                title: "Error!",
                text: "Updating Information Failed!",
                icon: "error"
            });
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        };
    </script>

    <script>
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');

        logoutBtn.addEventListener("click", function() {
            Swal.fire({
                title: "Are you sure you want to log out?",
                text: "You will need to log in again to access your account.",
                icon: "warning",
                showCancelButton: true,
                // confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, logout!",
                customClass: {
                    title: 'swal-custom-title',
                    htmlContainer: 'swal-custom-text'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../../../Function/logout.php";
                }
            });
        })
    </script>

</body>

</html>