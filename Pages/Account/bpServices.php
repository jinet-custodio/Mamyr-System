<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

require_once '../../Function/functions.php';

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
    <title>Business Partner Services - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/bpServices.css">
    <!-- DataTables Link -->
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
    if ($userRole == 2) {
        $role = "Business Partner";
    } else {
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
    }


    $getData = $conn->prepare("SELECT u.firstName, u.lastName, u.middleInitial, u.userProfile, ut.typeName as roleName , p.partnershipID FROM user u
            INNER JOIN usertype ut ON u.userRole = ut.userTypeID
            LEFT JOIN partnership p ON u.userID = p.userID
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
        $profile = $data['userProfile'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $profile);
        finfo_close($finfo);
        $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);

        $partnershipID = $data['partnershipID'];
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
                        alt="<?= htmlspecialchars($data['firstName']) ?> Picture">
                </div>
            </div>
            <ul class="list-group sidebar-nav">
                <li class="sidebar-item">
                    <a href="account.php" class="list-group-item">
                        <i class="fa-solid fa-user sidebar-icon"></i>
                        <span class="sidebar-text">Profile Information</span>
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
                <?php if ($role === 'Business Partner') { ?>
                <li class="sidebar-item">
                    <a href="bpBookings.php" class="list-group-item">
                        <i class="fa-regular fa-calendar-days sidebar-icon"></i>
                        <span class="sidebar-text">Bookings</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="bpServices.php" class="list-group-item active">
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
                <li class="sidebar-item">
                    <button type="button" class="btn btn-outline-danger d-flex align-items-center" id="logoutBtn"
                        style="margin: 3vw auto;">
                        <i class="fa-solid fa-arrow-right-from-bracket sidebar-icon"></i>
                        <span class="sidebar-text ms-2">Logout</span>
                    </button>
                </li>
            </ul>
        </aside> <!-- End Side Bar -->

        <main class="main-content" id="main-content">
            <div class="container">
                <h3 class="welcomeText" id="title">Services</h3>

                <div class="btnContainer" id="addServiceButtonContainer">
                    <button class="btn btn-primary" id="addServiceButton" onclick="addService()">Add
                        Service</button>
                </div>

                <div class="tableContainer" id="servicesTable">
                    <table class=" table table-striped" id="services">
                        <thead>
                            <th scope="col">Service Name</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </thead>

                        <tbody>
                            <?php
                            $getPartnerService = $conn->prepare("SELECT * FROM `partnershipservice` WHERE partnershipID = ?");
                            $getPartnerService->bind_param('i', $partnershipID);

                            if (!$getPartnerService->execute()) {
                                error_log("Error: " . $getPartnerService->error);
                            }

                            $result = $getPartnerService->get_result();

                            if (!$result->num_rows === 0) {
                            ?>
                            <tr>
                                <td colspan="8" class="text-center no-data-text">No data available</td>
                            </tr>
                            <?php
                            }
                            $details = [];
                            while ($row = $result->fetch_assoc()) {
                                $details[] = $row;
                            }

                            foreach ($details as $service):

                                $storedAvailabilityID = intval($service['PSAvailabilityID']);

                                $availabilityStatus = getAvailabilityStatus($conn, $storedAvailabilityID);

                                $availabilityID = $availabilityStatus['availabilityID'];
                                $availabilityName = $availabilityStatus['availabilityName'];
                                switch ($availabilityID) {
                                    case 1:
                                        $classcolor = 'success';
                                        $statusName =  $availabilityName;
                                        break;
                                    case 2:
                                        $classcolor = 'info';
                                        $statusName =  'Booked';
                                        break;
                                    case 3:
                                        $classcolor = 'warning';
                                        $statusName =  $availabilityName;
                                        break;
                                    case 4:
                                        $classcolor = 'success';
                                        $statusName =  $availabilityName;
                                        break;
                                    case 5:
                                        $classcolor = 'danger';
                                        $statusName =  $availabilityName;
                                        break;
                                    default:
                                        $classcolor = 'secondary';
                                        $availabilityID;
                                        $statusName =  $availabilityName;
                                }

                            ?>
                            <tr>
                                <td><?= htmlspecialchars(ucfirst($service['PBName'])) ?></td>
                                <td>₱<?= number_format($service['PBPrice'], 2) ?></td>
                                <td><span class="btn btn-<?= $classcolor ?> w-75"
                                        id="<?= $statusName ?>"><?= $statusName ?></span>
                                </td>
                                <td><button type="button" class="btn btn-primary w-75" data-bs-toggle="modal"
                                        data-bs-target="#serviceModal">View</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="addServiceContainer" id="addServiceContainer">
                    <div class="backArrowContainer" id="backArrowContainer">
                        <a href="bpServices.php"><img src="../../Assets/Images/Icon/arrowBtnBlue.png" alt="Back Button"
                                class="backArrow">
                        </a>
                    </div>
                    <form action="../../Function/Partner/addService.php" method="POST">
                        <div class="serviceInputContainer">
                            <div class="serviceNameContainer">
                                <label for="serviceName" class="addServiceLabel">Service Name</label>
                                <input type="text" class="form-control" id="serviceName" name="serviceName"
                                    placeholder="e.g Wedding Photography" required>
                            </div>
                            <div class="partnerTypeContainer">
                                <label for="partnerType" class="addServiceLabel">Partner Type</label>
                                <select name="partnerTypeID" id="partnerTypeID" class="form-select">
                                    <option value="" disabled selected>Select Partner Service Type</option>
                                    <?php
                                    $isApproved = true;
                                    $getPartnerTypes = $conn->prepare("SELECT pt.partnerTypeDescription as description, pt.partnerTypeID FROM partnership_partnertype ppt 
                                                        LEFT JOIN partnershiptype pt ON ppt.partnerTypeID = pt.partnerTypeID 
                                                        WHERE  ppt.isApproved = ? AND ppt.partnershipID = ?");
                                    $getPartnerTypes->bind_param('ii', $isApproved, $partnershipID);
                                    if ($getPartnerTypes->execute()) {
                                        $result = $getPartnerTypes->get_result();
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= htmlspecialchars($row['partnerTypeID']) ?>">
                                        <?= htmlspecialchars($row['description']) ?></option>
                                    <?php
                                            }
                                        }
                                        $result->free();
                                        $getPartnerTypes->close();
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="AvailabilityContainer">
                                <label for="availability" class="addServiceLabel">Availability</label>
                                <select class="form-select" name="availability" id="availability">
                                    <option value="" disabled selected>Select Availability</option>
                                    <?php
                                    $getAvailability = $conn->prepare("SELECT * FROM serviceavailability WHERE availabilityName  NOT IN ('Occupied', 'Private')");
                                    if ($getAvailability->execute()) {
                                        $result = $getAvailability->get_result();
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <option value="<?= htmlspecialchars($row['availabilityID']) ?>">
                                        <?= htmlspecialchars($row['availabilityName']) ?></option>
                                    <?php
                                            }
                                        }
                                        $result->free();
                                        $getAvailability->close();
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="priceContainer">
                                <label for="price" class="addServiceLabel">Price</label>
                                <input type="text" class="form-control" id="price" name="price" placeholder="e.g. 1000"
                                    required>
                            </div>
                            <div class="capacityContainer">
                                <label for="capacity" class="addServiceLabel">Capacity</label>
                                <input type="number" class="form-control" id="capacity" name="capacity"
                                    placeholder="e.g. 5" min='1'>
                            </div>
                            <div class="durationContainer">
                                <label for="duration" class="addServiceLabel">Duration</label>
                                <input type="text" class="form-control" id="duration" name="duration"
                                    placeholder="e.g. 1 hour">
                            </div>
                            <div class="descContainer">
                                <label for="description" class="description" class="addServiceLabel">Description</label>
                                <textarea class="form-control" id="description" name="serviceDesc"
                                    placeholder="Service information/description (Optional)"></textarea>
                            </div>
                        </div>
                        <div class="submitBtnContainer ">
                            <input type="hidden" name="partnershipID" value="<?= (int) $partnershipID ?>">
                            <button type="submit" class="btn btn-success w-25" name="addService">Add Service</button>
                        </div>
                    </form>



                </div>

            </div>

            <!-- serviceModal -->
            <div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModal"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel">Service Info</h4>
                        </div>
                        <div class="modal-body">
                            <section class="pic-info">
                                <div class="picContainer">
                                    <img src="../../Assets/Images/no-picture.jpg" alt="Service Picture"
                                        class="servicePic">
                                </div>

                                <div class="infoContainer">
                                    <div class="info-container">
                                        <label for="serviceName">Service Name</label>
                                        <input type="text" class="form-control" name="serviceName" id="serviceName"
                                            value="Snapshot Photography" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="servicePrice">Price</label>
                                        <input type="text" class="form-control" name="servicePrice" id="servicePrice"
                                            value="₱2000" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="serviceCapacity">Capacity</label>
                                        <input type="text" class="form-control" name="serviceCapacity"
                                            id="serviceCapacity" value="Unlimited Shots" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="serviceDuration">Service Duration</label>
                                        <input type="text" class="form-control" name="serviceDuration"
                                            id="serviceDuration" value="5 hours" readonly>
                                    </div>
                                    <div class="info-container">
                                        <label for="serviceAvailable">Service Availability</label>
                                        <input type="text" class="form-control" name="serviceAvailability"
                                            id="serviceAvalability" value="Available" readonly>
                                    </div>
                                </div>
                            </section>

                            <section class="descContainer">
                                <div class="form-group">
                                    <label for="serviceDescription">Service Description</label>
                                    <textarea class="form-control" name="serviceDescription" id="serviceDescription"
                                        rows="60">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Neque expedita maxime quo obcaecati, corporis, sunt mollitia similique suscipit dolorem ipsam quia iure laborum, esse ducimus explicabo voluptatum autem temporibus quidem!</textarea>
                                </div>
                            </section>
                        </div>
                        <div class="modal-footer">
                            <div class="declineBtnContainer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">Close</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- serviceModal -->
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
    <!-- DataTables Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>

    <script>
    const servicesTable = document.getElementById("servicesTable")
    const addServiceContainer = document.getElementById("addServiceContainer")
    const addServiceButtonContainer = document.getElementById("addServiceButtonContainer")
    const homeBtnContainer = document.getElementById("homeBtnContainer")

    addServiceContainer.style.display = "none"

    function addService() {
        if (addServiceContainer.style.display == "none") {
            addServiceContainer.style.display = "block";
            servicesTable.style.display = "none";
            addServiceButtonContainer.style.display = "none";
            homeBtnContainer.style.display = "none";
            document.getElementById("title").innerHTML = "Add Service"

        } else {
            addServiceContainer.style.display = "block";
        }
    }
    </script>

    <!-- Table JS -->
    <script>
    $(document).ready(function() {
        $('#services').DataTable({
            language: {
                emptyTable: "No Services"
            },
            columnDefs: [{
                width: '25%',
                target: 0

            }]
        });
    });
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <!-- <script>
        document.querySelector("input[type='file']").addEventListener("change", function(event) {
            let reader = new FileReader();
            reader.onload = function() {
                let preview = document.getElementById("preview");
                preview.src = reader.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script> -->
    <!-- Message pop up -->
    <script>
    const params = new URLSearchParams(window.location.search);
    const paramValue = params.get('action');
    if (paramValue === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Service Added Successfully',
            text: ''
        })
    } else if (paramValue === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Failed to Add Service',
            text: 'Please try again later.'
        })
    }

    if (paramValue) {
        const url = new URL(window.location);
        url.search = '';
        history.replaceState({}, document.title, url.toString());
    }
    </script>



</body>

</html>