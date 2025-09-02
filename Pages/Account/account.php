<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];


if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
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

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/account.css" />
    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>

    <!-- Get the information to the database -->
    <?php
    if ($userRole == 1) {
        $role = "Customer";
    } elseif ($userRole == 2) {
        $role = "Business Partner";
    } elseif ($userRole == 3) {
        $role = "Admin";
    } else {
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
    }


    $getData = $conn->prepare("SELECT u.*, ut.typeName as roleName FROM user u
            INNER JOIN usertype ut ON u.userRole = ut.userTypeID
            WHERE u.userID = ? AND userRole = ?");
    $getData->bind_param("ii", $userID, $userRole);
    $getData->execute();
    $getDataResult = $getData->get_result();
    if ($getDataResult->num_rows > 0) {
        $data =  $getDataResult->fetch_assoc();
        $middleInitial = trim($data['middleInitial'] ?? '');
        $name = ucfirst($data['firstName'] ?? '') . " " .
            ucfirst($data['middleInitial'] ?? '') . " " .
            ucfirst($data['lastName'] ?? '');

        // var_dump($name);
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

    ?>
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="d-flex" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home text-center">
                <?php if ($role === 'Customer') { ?>
                    <a href="../Customer/dashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } elseif ($role === 'Admin') { ?>
                    <a href="../Admin/adminDashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } ?>
            </div>
            <div class="sidebar-header text-center">
                <h5 class="sidebar-text">User Account</h5>
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item active">
                        <i class="fa-regular fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="fa-solid fa-user-shield sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>

                <?php if ($role === 'Customer' || $role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bookingHistory.php" class="list-group-item" id="paymentBookingHist">
                            <i class="fa-solid fa-table-list sidebar-icon"></i>
                            <span class="sidebar-text">Payment & Booking History</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li class="sidebar-item">
                        <a href="userManagement.php" class="list-group-item">
                            <i class="fa-solid fa-people-roof sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>

                <li class="sidebar-item">
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="fa-solid fa-user-slash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                        style="margin: 3vw auto;">
                        <i class="fa-solid fa-arrow-right-from-bracket sidebar-icon"></i>
                        <span class="sidebar-text ms-2">Logout</span>
                    </button>
                </li>
            </ul>
        </aside> <!-- End Side Bar -->


        <!-- Customer Information Container -->
        <main class="main-content" id="main-content">
            <form action="../../Function/Account/editProfile.php" method="POST" enctype="multipart/form-data">
                <div class="card">
                    <div class="account-info">
                        <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
                        <input type="hidden" name="userRole" value="<?= htmlspecialchars($userRole) ?>">
                        <div class="profile-image">
                            <img src="<?= $image ?>" alt="<?= htmlspecialchars($data['firstName']) ?> Picture"
                                class="profile-pic">
                            <button type="button" class="changePfpBtn btn btn-primary" id="changePfp">
                                Change Profile
                            </button>
                            <!-- Profile Picture Modal -->
                            <div class="modal" id="picModal" tabindex="-1" aria-labelledby="picModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="picModalLabel">Change Profile Picture</h5>
                                            <button type="button" class="btn-close btn btn-danger"
                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <img src="<?= $image ?>"
                                                alt="<?= htmlspecialchars($data['firstName']) ?> Picture" id="preview"
                                                class="profile-pic">
                                            <input type="file" name="profilePic" id="profilePic" hidden>
                                            <label for="profilePic"
                                                class="custom-file-button btn btn-outline-primary">Choose Image</label>
                                        </div>
                                        <div class="modal-button">
                                            <button type="submit" class="btn btn-danger"
                                                name="cancelPfp">Cancel</button>
                                            <button type="submit" class="btn btn-success" name="changePfpBtn">Save
                                                Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-info">
                            <h5 class="account-name"> <?= htmlspecialchars($data['firstName']) ?></h5>
                            <h6 class="account-contact"> <?= htmlspecialchars($email) ?> |
                                <?= htmlspecialchars($phoneNumber) ?></h6>
                            <h6 class="roleName"><?= htmlspecialchars($role) ?></h6>
                        </div>
                    </div>
            </form>

            <form action="../../Function/Account/editAccount.php" id="accountForm" method="POST">
                <div class="customer-details">
                    <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
                    <input type="hidden" name="userRole" value="<?= htmlspecialchars($userRole) ?>">
                    <div class="info">
                        <input type="text" name="fullName" id="fullName" value="<?= htmlspecialchars($name) ?>" disabled
                            required>
                        <label for="fullName">Full Name</label>
                    </div>

                    <div class="info">
                        <?php if (!empty($data['birthDate'])) : ?>
                            <input type="date" name="birthday" id="birthday"
                                value="<?= htmlspecialchars($data['birthDate']) ?>" disabled>
                        <?php else : ?>
                            <input type="text" name="birthday" id="birthday" value="--" disabled>
                        <?php endif; ?>
                        <label for="birthday">Birthday</label>
                    </div>

                    <div class="info">
                        <input type="text" name="address" id="address" value="<?= htmlspecialchars($address) ?>"
                            disabled required>
                        <label for="address">Address</label>
                    </div>
                    <div class="info">
                        <input type="text" name="phoneNumber" id="phoneNumber" pattern="^(?:\+63|0)9\d{9}$"
                            title="e.g., +639123456789 or 09123456789" value="<?= htmlspecialchars($phoneNumber) ?>"
                            disabled required>
                        <label for="phoneNumber">Phone Number
                            <?php if ($phoneNumber === '--' || $phoneNumber === Null) { ?>
                                <sup>
                                    <i class="fa-solid fa-asterisk m-2" style="color: #ff0000; "></i>
                                </sup>
                            <?php } ?>
                        </label>

                    </div>
                </div>
                <div class="button-container">
                    <button type="button" class="edit btn btn-primary" name="changeDetails" id="editBtn"
                        onclick="enableEditing()">Edit</button>
                    <button type="button" onclick="cancelEdit()" name="cancelChanges" id="cancelBtn" class="change-info btn btn-danger"
                        style="display: none;">Cancel</button>
                    <button type="submit" name="saveChanges" id="saveBtn" class="change-info btn btn-primary"
                        style="display: none;">Save</button>
                </div>
            </form>
        </main>
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

    <!-- Cancel the edit and bring it back to its orginal data -->
    <script>
        function cancelEdit() {
            document.getElementById('accountForm').reset();
        }
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
        //Handle sidebar for responsiveness
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const items = document.querySelectorAll('.list-group-item');
            const toggleCont = document.getElementById('toggle-container')

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    });
                    toggleCont.style.justifyContent = "center"
                } else {
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    });
                    toggleCont.style.justifyContent = "flex-end"
                }
            });

            function handleResponsiveSidebar() {
                if (window.innerWidth <= 600) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    })

                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    })
                    sidebar.classList.remove('collapsed');
                }
            }

            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener('resize', handleResponsiveSidebar);
        });
    </script>

    <script>
        function enableEditing() {
            const birthdayInput = document.getElementById("birthday");

            if (birthdayInput.type === "text" && birthdayInput.value === "--") {
                const newInput = document.createElement("input");
                newInput.type = "date";
                newInput.name = "birthday";
                newInput.id = "birthday";
                newInput.disabled = false;
                newInput.className = birthdayInput.className;

                birthdayInput.parentNode.replaceChild(newInput, birthdayInput);
            } else {
                birthdayInput.removeAttribute("disabled");
            }

            document.getElementById("fullName").removeAttribute("disabled");
            document.getElementById("address").removeAttribute("disabled");
            document.getElementById("phoneNumber").removeAttribute("disabled");

            document.getElementById("saveBtn").style.display = "inline-block";
            document.getElementById("cancelBtn").style.display = "inline-block";
            document.getElementById("editBtn").style.display = "none";
        };

        document.getElementById("cancelBtn").addEventListener("click", function() {
            document.getElementById("saveBtn").style.display = "none";
            document.getElementById("cancelBtn").style.display = "none";
            document.getElementById("editBtn").style.display = "block";

            document.getElementById("fullName").disabled = true;
            document.getElementById("address").disabled = true;
            document.getElementById("phoneNumber").disabled = true;
            document.getElementById("birthday").disabled = true;

        });
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
        } else if (paramValue === 'emptyPhoneNumber') {
            Swal.fire({
                title: "Oops!",
                text: "Empty Phone Number!",
                icon: "warning",
                confirmButtonText: 'Okay',
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
                    window.location.href = "../../Function/logout.php";
                }
            });
        })
    </script>

</body>

</html>