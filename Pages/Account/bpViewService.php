<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

require_once '../../Function/Helpers/statusFunctions.php';

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
//for setting image paths in 'include' statements
$baseURL = '../..';

switch ($userRole) {
    case 2:
        $role = "Business Partner";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner Services - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS Link -->
    <link rel=" stylesheet" href="../../Assets/CSS/Account/bpViewService.css">
    <link rel="stylesheet" href="../../Assets/CSS/Account/account-sidebar.css" />
    <!-- DataTables Link 
        -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css" />

    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <!-- Get the information to the database -->
    <?php
    $getData = $conn->prepare("SELECT u.firstName, u.lastName, u.middleInitial, u.userProfile, ut.typeName as roleName , p.partnershipID FROM user u
            INNER JOIN usertype ut ON u.userRole = ut.userTypeID
            LEFT JOIN partnership p ON u.userID = p.userID
            WHERE u.userID = ? AND userRole = ?");
    $getData->bind_param("ii", $userID, $userRole);
    $getData->execute();
    $getDataResult = $getData->get_result();
    if ($getDataResult->num_rows > 0) {
        $data =  $getDataResult->fetch_assoc();
        $firstName = $data['firstName'] ?? '';
        $middleInitial = trim($data['middleInitial'] ?? '');
        $name = ucfirst($firstName) . " " .
            ucfirst($middleInitial) . " " .
            ucfirst($data['lastName'] ?? '');
        $profile = $data['userProfile'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $profile);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);

        $partnershipID = $data['partnershipID'];
    }

    ?>


    <?php

    if (isset($_POST['id']) || isset($_SESSION['id'])) {
        $partnershipServiceID = !empty($_POST['id']) ? intval($_POST['id']) : intval($_SESSION['id']);


        $getServiceData = $conn->prepare("SELECT * FROM partnershipservice WHERE partnershipServiceID = ?");
        $getServiceData->bind_param("i", $partnershipServiceID);
        if (!$getServiceData->execute()) {
            error_log("An error occured: " . $getServiceData->error);
        }

        $result = $getServiceData->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $serviceName = $row['PBName'];
            $servicePrice = $row['PBPrice'];
            $serviceImage = $row['serviceImage'];
            $serviceDesc = !empty($row['PBDescription']) ? $row['PBDescription'] : 'No Provided Description';
            $serviceCapacity  = $row['PBcapacity'] ?? 'Not Stated';
            $serviceDuration = $row['PBduration'] ?? 'Not Stated';
            $createdAt = $row['createdAt'];

            $storedAvailabilityID = intval($row['PSAvailabilityID']);
            $availabilityStatus = getAvailabilityStatus($conn, $storedAvailabilityID);

            $availabilityID = $availabilityStatus['availabilityID'];
            $availabilityName = $availabilityStatus['availabilityName'];

            switch ($availabilityID) {
                case 1:
                    $statusName =  $availabilityName;
                    break;
                case 2:
                    $statusName =  'Booked';
                    break;
                case 3:
                    $statusName =  $availabilityName;
                    break;
                case 4:
                    $statusName =  $availabilityName;
                    break;
                case 5:
                    $statusName =  $availabilityName;
                    break;
                default:
                    $statusName =  $availabilityName;
            }
        }
    } else {
        header("Location: ./bpServices.php");
        exit();
    }

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
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } elseif ($role === 'Admin') { ?>
                    <a href="../Admin/adminDashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } elseif ($role === 'Business Partner') { ?>
                    <a href="../BusinessPartner/bpDashboard.php">
                        <i class="bi bi-house homeIcon"></i>
                    </a>
                <?php } ?>
            </div>

            <div class="sidebar-header text-center">
                <h5 class="sidebar-text">User Account</h5>
                <div class="profileImage">
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($firstName) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item">
                        <i class="bi bi-person sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
                    </a>
                </li>

                <?php if ($role === 'Customer' || $role === 'Partnership Applicant' || $role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bookingHistory.php" class="list-group-item" id="BookingHist">
                            <i class="bi bi-calendar2-check sidebar-icon"></i>
                            <span class="sidebar-text">Booking History</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="paymentHistory.php" class="list-group-item" id="paymentHist">
                            <i class="bi bi-credit-card-2-front sidebar-icon"></i>
                            <span class="sidebar-text">Payment</span>
                        </a>
                    </li>
                <?php } elseif ($role === 'Admin') { ?>
                    <li class="sidebar-item">
                        <a href="userManagement.php" class="list-group-item">
                            <i class="bi bi-person-gear sidebar-icon"></i>
                            <span class="sidebar-text">Manage Users</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($role === 'Business Partner') { ?>
                    <li class="sidebar-item">
                        <a href="bpBookings.php" class="list-group-item">
                            <i class="bi bi-calendar-week sidebar-icon"></i>
                            <span class="sidebar-text">Bookings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="bpServices.php" class="list-group-item active">
                            <i class="bi bi-bell sidebar-icon"></i>
                            <span class="sidebar-text">Services</span>
                        </a>
                    </li>
                <?php } ?>

                <li class="sidebar-item">
                    <a href="loginSecurity.php" class="list-group-item">
                        <i class="bi bi-shield-check sidebar-icon"></i>
                        <span class="sidebar-text">Login & Security</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="deleteAccount.php" class="list-group-item">
                        <i class="bi bi-person-dash sidebar-icon"></i>
                        <span class="sidebar-text">Delete Account</span>
                    </a>
                </li>
            </ul>
            <div class="logout">
                <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                    style="margin: 3vw auto;">
                    <i class="bi bi-box-arrow-right logout-icon"></i>
                    <span class="sidebar-text ms-2">Logout</span>
            </div>
        </aside>
        <!-- End Side Bar -->

        <form action="../../Function/Partner/updatePartnerService.php" method="POST" enctype="multipart/form-data">
            <main class="main-content" id="main-content">
                <div class="container">
                    <div class="backBtn-container">
                        <a href="bpServices.php"><img src="../../Assets/Images/Icon/arrowBtnBlue.png" alt="Back Button"
                                class="backBtn"></a>
                    </div>

                    <div class="titleContainer">
                        <h2 class="title">Service Information</h2>
                    </div>


                    <input type="hidden" name="partnershipServiceID" id="partnershipServiceID" value="<?= $partnershipServiceID ?>">

                    <section class="serviceInfo-container">
                        <div class="servicePic-container">
                            <?php
                            // error_log($_SESSION['tempImage']);
                            if (isset($_SESSION['tempImage']) && file_exists(__DIR__ . '/../../Assets/Images/TempUploads/' . $_SESSION['tempImage'])) {
                                $imageSrc = '../../Assets/Images/TempUploads/' . $_SESSION['tempImage'];
                            } else {
                                $imageSrc = '../../Assets/Images/PartnerServiceImage/' . $serviceImage;
                            }
                            ?>
                            <input type="hidden" name="serviceImageName" id="serviceImageName" value="<?= $serviceImage ?>">
                            <img src="<?= $imageSrc ?>" alt="" class="service-image" id="preview">
                            <input type="file" name="serviceImage" id="service-image" hidden>
                            <div class="btn-note" style="display: none;" id="changeImageContainer">
                                <label for="service-image" class="changePfpBtn btn btn-primary">Change Service Image</label>
                                <p class="note">Adding a service image in <strong>Landscape Orientation</strong> is advisable.</p>
                            </div>

                        </div>

                        <div class="infoContainer">
                            <div class="info-container">
                                <label for="serviceName">Service Name</label>
                                <input type="text" class="form-control text-capitalize editable" name="serviceName" id="serviceName"
                                    placeholder="eg. Snapshot Photography" readonly="" value="<?= htmlspecialchars($serviceName) ?>">
                            </div>
                            <div class="info-container">
                                <label for="servicePrice">Service Price</label>
                                <input type="text" class="form-control editable" name="servicePrice" id="servicePrice"
                                    placeholder="eg. ₱2000" readonly="" value="₱<?= number_format($servicePrice, 2) ?>">
                            </div>
                            <div class="info-container">
                                <label for="serviceCapacity">Service Capacity</label>
                                <input type="text" class="form-control editable" name="serviceCapacity" placeholder="(eg. N/A)"
                                    id="serviceCapacity" readonly="" value="<?= $serviceCapacity ?>">
                            </div>
                            <div class="info-container">
                                <label for="serviceDuration">Service Duration</label>
                                <input type="text" class="form-control editable" name="serviceDuration" id="serviceDuration"
                                    placeholder="eg. 7 hours" readonly="" value="<?= $serviceDuration ?>">
                            </div>
                            <div class="info-container">
                                <label for="serviceAvailability">Availability</label>
                                <select class="form-select editable" name="serviceAvailability" id="serviceAvailability" disabled>
                                    <option value="">Select Availability</option>
                                    <option value="1" <?= $availabilityID == 1 ? 'selected' : '' ?>>Available</option>
                                    <option value="2" <?= $availabilityID == 2 ? 'selected' : '' ?>>Booked</option>
                                    <option value="5" <?= $availabilityID == 3 ? 'selected' : '' ?>>Unavailable</option>
                                </select>

                            </div>
                            <div class="descContainer">
                                <div class="form-group">
                                    <label for="serviceDescription">Service Description</label>
                                    <textarea name="serviceDescription" class="form-control editable serviceDesc"
                                        placeholder="Please provide for your service" readonly=""><?= $serviceDesc ?></textarea>
                                </div>
                            </div>
                        </div>

                    </section>
                    <div class="editService-btn-container">
                        <button type="button" class="btn btn-danger w-25 cancel-info-button" onclick="canEditInfo()"
                            style="display: none;"><i class="fa-solid fa-xmark"></i>
                            Cancel</button>

                        <button type="button" class="btn btn-primary w-25 edit-info-button"
                            onclick="editServiceInfo()"><i class="fa-solid fa-pen-to-square"></i> Edit</button>

                        <button type="submit" class="btn btn-primary w-25 save-info-button" name="saveServiceInfo" style="display: none;"><i class="fa-solid fa-pen-to-square"></i> Save</button>
                    </div>
                </div>
            </main>
        </form>
    </div>
    <?php include '../Customer/loader.php'; ?>

    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- DataTables Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Edit Service Information -->
    <script>
        const originalValues = {};

        function editServiceInfo() {
            const formControl = document.querySelectorAll(".form-control");
            const select = document.querySelector("#serviceAvailability");
            const changeImageContainer = document.getElementById('changeImageContainer');
            const editBtn = document.querySelector('.edit-info-button');
            const saveBtn = document.querySelector('.save-info-button');
            const cancelBtn = document.querySelector('.cancel-info-button');

            formControl.forEach((input) => {
                originalValues[input.name] = input.value;
                input.style.border = "1px solid red";
                input.removeAttribute("readonly");
            });
            select.style.border = "1px solid red";
            select.disabled = false;
            originalValues[select.name] = select.value;
            changeImageContainer.style.display = 'flex';
            editBtn.style.display = 'none';
            saveBtn.style.display = 'block';
            cancelBtn.style.display = 'block';
        }

        function canEditInfo() {
            const formControl = document.querySelectorAll(".form-control");
            const select = document.querySelector("#serviceAvailability");
            const changeImageContainer = document.getElementById('changeImageContainer');
            const editBtn = document.querySelector('.edit-info-button');
            const saveBtn = document.querySelector('.save-info-button');
            const cancelBtn = document.querySelector('.cancel-info-button');

            formControl.forEach((input) => {
                input.value = originalValues[input.name];
                input.style.border = "1px solid  rgb(247, 247, 247)";
                input.setAttribute("readonly", true);
            });

            select.style.border = "1px solid  rgb(247, 247, 247)";
            select.disabled = true;
            select.value = originalValues[select.name];
            changeImageContainer.style.display = 'none';
            editBtn.style.display = 'block';
            cancelBtn.style.display = "none";
            saveBtn.style.display = 'none';
        }
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
                if (window.innerWidth <= 1240) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    mainContent.style.marginLeft = "15vw";
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    })

                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    });
                    mainContent.style.marginLeft = "290px";
                    sidebar.classList.remove('collapsed');
                }
            }

            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener('resize', handleResponsiveSidebar);
        });
    </script>

    <!-- Show when want to logout-->
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

    <!-- Preview Image -->
    <script>
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

    <!-- Sweet alert message pop up -->
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        const param = new URLSearchParams(window.location.search);
        const paramValue = param.get('action');
        switch (paramValue) {
            case 'success-image':
                Swal.fire({
                    title: "Success!",
                    text: "Profile Change Successfully!",
                    icon: "success"
                });
                break;
            case 'error-image':
                Swal.fire({
                    title: "Info!",
                    text: "No Image Selected",
                    icon: "info"
                });
                break;
            case 'imageSize':
                Swal.fire({
                    title: "Oops!",
                    text: "File is too large. Maximum allowed size is 5MB.",
                    icon: "warning",
                    confirmButtonText: "Okay",
                });
                break;
            case 'imageFailed':
                Swal.fire({
                    title: 'Oops',
                    text: `Make sure you uploaded an image`,
                    icon: 'warning',
                });
                break;
            case 'extError':
                Swal.fire({
                    title: 'Oops',
                    text: `Invalid file type. Please upload JPG, JPEG, WEBP, or PNG.`,
                    icon: 'warning',
                });
                break;
            case 'serviceUpdateSuccess':
                Toast.fire({
                    title: 'Service Information Updated Successfully',
                    icon: 'success',
                });
                break;
            default:
                const url = new URL(window.location);
                url.search = '';
                history.replaceState({}, document.title, url.toString());
                break;
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>
</body>

</html>