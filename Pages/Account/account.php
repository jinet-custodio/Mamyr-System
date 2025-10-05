<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);


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

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

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

    <!-- Flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

</head>

<body>

    <!-- Get the information to the database -->
    <?php
    switch ($userRole) {
        case 1: //customer
            $role = "Customer";
            break;
        case 2:
            $role = "Business Partner";
            break;
        case 3:
            $role = "Admin";
            break;
        case 4:
            $role = "Partnership Applicant";
            break;
        default:
            $_SESSION['error'] = "Unauthorized Access eh!";
            session_destroy();
            header("Location: ../register.php");
            exit();
    }


    $getData = $conn->prepare("SELECT u.firstName, u.middleInitial, u.lastName, u.userProfile, u.email, u.phoneNumber, u.birthDate, u.userAddress, pt.partnerTypeDescription, ppt.isApproved
            FROM user u
            LEFT JOIN partnership p ON u.userID = p.userID
            LEFT JOIN partnership_partnertype ppt ON p.partnershipID = ppt.partnershipID
            LEFT JOIN partnershiptype pt ON ppt.partnerTypeID = pt.partnerTypeID
            WHERE u.userID = ? AND userRole = ?");
    $getData->bind_param("ii", $userID, $userRole);
    $getData->execute();
    $getDataResult = $getData->get_result();

    $partnerTypes = [];
    $isApproved;
    if ($getDataResult->num_rows > 0) {
        $partnerTypes = [];
        $name = $email = $phoneNumber = $birthday = $address = $image = "";
        $type = "text";

        while ($data = $getDataResult->fetch_assoc()) {
            if (empty($name)) {
                $firstName = $data['firstName'] ?? '';
                $middleInitial = trim($data['middleInitial'] ?? '');
                $lastName = $data['lastName'] ?? '';
                $name = ucfirst($firstName ?? '') . " " .
                    ucfirst($middleInitial) . " " .
                    ucfirst($lastName);

                $email = $data['email'];
                $phoneNumber = $data['phoneNumber'] ?: "--";

                $birthday = $data['birthDate'];
                // $type = ($birthday === NULL || $birthday === "") ? "text" : "date";
                $birthday = $birthday ?: "--";

                $address = $data['userAddress'];

                // Handle image
                $profile = $data['userProfile'];
                if (!empty($profile)) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $profile);
                    finfo_close($finfo);
                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
                }
            }

            $isApproved = $data['isApproved'] ?? false;

            if ($isApproved) {
                $partnerTypes[] = $data['partnerTypeDescription'] ?? 'N/A';
            }
        }
    }

    // error_log($birthday);

    // foreach ($partnerTypes as $partnerType):
    //     error_log($partnerType);
    // endforeach;
    ?>
    <div class="wrapper d-flex">

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="d-flex justify-content-center" id="toggle-container">
                <button id="toggle-btn" type="button" class="btn toggle-button" style="display: none;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
            <div class="home">
                <?php if ($role === 'Customer' || $role === 'Partnership Applicant') { ?>
                    <a href="../Customer/dashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } elseif ($role === 'Admin') { ?>
                    <a href="../Admin/adminDashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } elseif ($role === 'Business Partner') { ?>
                    <a href="../BusinessPartner/bpDashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="Go Back" class="homeIcon">
                    </a>
                <?php } ?>
            </div>

            <div class="sidebar-header text-center">
                <h5 class="sidebar-text">User Account</h5>
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($firstName) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item active">
                        <i class="fa-solid fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>

                <?php if ($role === 'Customer' || $role === 'Partnership Applicant' || $role === 'Business Partner') { ?>
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
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item">
                            <i class="fa-regular fa-calendar-days sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpServices.php" class="list-group-item">
                            <i class="fa-solid fa-bell-concierge sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpSales.php" class="list-group-item">
                            <i class="fa-solid fa-money-bill-trend-up sidebar-icon"></i>
                            <span class="sidebar-text">Sales</span>
                        </a>
                    </li>
                <?php } ?>

                <li class="sidebar-item">
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="fa-solid fa-user-shield sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="fa-solid fa-user-slash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
            </ul>
            <div class="logout">
                <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn" style="margin: 3vw auto;">
                    <i class="fa-solid fa-arrow-right-from-bracket sidebar-icon"></i>
                    <span class="sidebar-text ms-2">Logout</span>
            </div>
        </aside>
        <!-- End Side Bar -->

        <!-- Customer Information Container -->
        <main class="main-content" id="main-content">
            <form action="../../Function/Account/editProfile.php" method="POST" enctype="multipart/form-data">
                <div class="card">
                    <div class="account-info">
                        <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
                        <input type="hidden" name="userRole" value="<?= htmlspecialchars($userRole) ?>">
                        <div class="profile-image">
                            <img src="<?= $image ?>" alt="<?= htmlspecialchars($firstName) ?> Picture"
                                class="profile-pic">
                            <button type="button" class="changePfpBtn btn btn-primary" id="changePfp"><i class="fa-solid fa-camera-retro"></i>
                                Change Profile
                            </button>
                            <!-- Profile Picture Modal -->
                            <div class="modal" id="picModal" tabindex="-1" aria-labelledby="picModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="picModalLabel"> Change Profile Picture</h5>
                                            <button type="button" class="btn-close btn btn-danger"
                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <img src="<?= $image ?>"
                                                alt="<?= htmlspecialchars($firstName) ?> Picture" id="preview"
                                                class="profile-pic">
                                            <input type="file" name="profilePic" id="profilePic" hidden>
                                            <label for="profilePic"
                                                class="custom-file-button btn btn-outline-primary">Choose Image</label>
                                        </div>
                                        <div class="modal-button">
                                            <button type="submit" class="btn btn-danger"
                                                name="cancelPfp"><i class="fa-solid fa-ban"></i> Cancel</button>
                                            <button type="submit" class="btn btn-success" name="changePfpBtn"><i class="fa-solid fa-floppy-disk"></i> Save
                                                Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-info">
                            <h5 class="account-name"> <?= htmlspecialchars($firstName) ?></h5>
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
                    <div class="info form-floating">
                        <input type="text" class="form-control editable" name="fullName" id="fullName" value="<?= htmlspecialchars($name) ?>" readonly
                            required>
                        <label for="fullName">Full Name</label>
                    </div>
                    <div class="info form-floating">
                        <?php if (!empty($birthday)) : ?>
                            <input type="text" class="form-control editable" name="birthday" id="birthday"
                                value="<?= htmlspecialchars($birthday) ?>">
                        <?php else : ?>
                            <input type="text" class="form-control" name="birthday" id="birthday" value="--" readonly>
                        <?php endif; ?>
                        <label for="birthday">Birthday</label>
                    </div>
                    <div class="info form-floating">
                        <input type="text" name="address" id="address" value="<?= htmlspecialchars($address) ?>" class="form-control editable"
                            readonly required>
                        <label for="address">Address</label>
                    </div>
                    <div class="info form-floating">
                        <input type="text" name="phoneNumber" id="phoneNumber" pattern="^(?:\+63|0)9\d{9}$"
                            title="e.g., +639123456789 or 09123456789" value="<?= htmlspecialchars($phoneNumber) ?>" class="form-control editable"
                            readonly required>
                        <label for="phoneNumber">Phone Number
                            <?php if ($phoneNumber === '--' || $phoneNumber === Null) { ?>
                                <sup>
                                    <i class="fa-solid fa-asterisk" style="color: #ff0000; "></i>
                                </sup>
                            <?php } ?>
                        </label>
                        <div id="tooltip" class="custom-tooltip">Please input number</div>
                    </div>
                </div>

                <?php if ($role === 'Business Partner'): ?>
                    <h5 class="partner-info-label">Partner Type/s</h5>
                    <div class="partner-info">
                        <?php foreach ($partnerTypes as $partnerType): ?>
                            <div>
                                <input type="text" name="partnerType" id="partnerType" value="<?= htmlspecialchars($partnerType) ?>"
                                    readonly required class="form-control">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="button-container">
                    <button type="button" class="edit btn btn-primary" name="changeDetails" id="editBtn"
                        onclick="enableEditing()"><i class="fa-solid fa-pen-to-square ms-10"></i> Edit</button>
                    <button type="button" onclick="cancelEdit()" name="cancelChanges" id="cancelBtn" class="change-info btn btn-danger"
                        style="display: none;"><i class="fa-solid fa-ban"></i> Cancel</button>
                    <button type="submit" name="saveChanges" id="saveBtn" class="change-info btn btn-primary"
                        style="display: none;"><i class="fa-solid fa-floppy-disk"></i> Save</button>
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

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
        const input = document.getElementById('phoneNumber');
        const tooltip = document.getElementById('tooltip');
        input.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
            tooltip.classList.add('show');


            clearTimeout(tooltip.hideTimeout);
            tooltip.hideTimeout = setTimeout(() => {
                tooltip.classList.remove('show');
            }, 2000);
        });
    </script>

    <script>
        let birthdayPicker = null;

        function enableEditing() {
            const birthdayInput = document.getElementById("birthday");
            birthdayInput.removeAttribute("readonly");
            const editable = document.querySelectorAll('.editable');
            if (!birthdayPicker) {
                birthdayPicker = flatpickr('#birthday', {
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    allowInput: true
                });
            }

            editable.forEach((input) => {
                input.style.border = ' 1px solid red';
            })

            document.getElementById("fullName").removeAttribute("readonly");
            document.getElementById("address").removeAttribute("readonly");
            document.getElementById("phoneNumber").removeAttribute("readonly");

            document.getElementById("saveBtn").style.display = "inline-block";
            document.getElementById("cancelBtn").style.display = "inline-block";
            document.getElementById("editBtn").style.display = "none";
        }

        document.getElementById("cancelBtn").addEventListener("click", function() {
            const editable = document.querySelectorAll('.editable');
            document.getElementById("fullName").setAttribute('readonly', true);
            document.getElementById("address").setAttribute('readonly', true);
            document.getElementById("phoneNumber").setAttribute('readonly', true);
            document.getElementById("birthday").setAttribute('readonly', true);

            editable.forEach((input) => {
                input.style.border = '1px solid rgb(223, 226, 230)';
            })

            if (birthdayPicker) {
                birthdayPicker.destroy();
                birthdayPicker = null;
            }

            document.getElementById("saveBtn").style.display = "none";
            document.getElementById("cancelBtn").style.display = "none";
            document.getElementById("editBtn").style.display = "block";
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