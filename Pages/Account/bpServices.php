<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

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
    <link rel=" stylesheet" href="../../Assets/CSS/Account/bpServices.css">
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
                        <a href="bookingHistory.php" class="list-group-item" id="paymentBookingHist">
                            <i class="bi bi-calendar2-check sidebar-icon"></i>
                            <span class="sidebar-text">Payment & Booking History</span>
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
                    <li class="sidebar-item">
                        <a href="bpSales.php" class="list-group-item">
                            <i class="bi bi-tags sidebar-icon"></i>
                            <span class="sidebar-text">Sales</span>
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

        <main class="main-content" id="main-content">
            <div class="container">
                <h3 class="welcomeText" id="title">Services</h3>

                <input type="hidden" name="partnershipID" id="partnershipID" value="<?= $partnershipID ?>">
                <div class="btnContainer" id="addServiceButtonContainer">
                    <button class="btn btn-primary  add-service-btn" id="addServiceButton" onclick="addService()"><i
                            class="fas fa-plus-circle"></i> Add Service</button>
                </div>

                <div class="serviceContainer" id="service-card-container"></div>
                <div class="no-data-container" id="no-data-container"></div>

                <!--  Form for adding a service  -->
                <div class=" addServiceContainer" id="addServiceContainer">
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
                                    placeholder="e.g Wedding Photography" value="<?= isset($_SESSION['addServiceForm']['serviceName']) ? $_SESSION['addServiceForm']['serviceName'] : '' ?>" required>
                            </div>
                            <div class="partnerTypeContainer">
                                <label for="partnerType" class="addServiceLabel">Partner Type</label>
                                <select name="partnerTypeID" id="partnerTypeID" class="form-select">
                                    <option value="" disabled <?= empty($_SESSION['addServiceForm']['partnerTypeID']) ? 'selected' : '' ?>>
                                        Select Partner Service Type
                                    </option>

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
                                                $selected = ($_SESSION['addServiceForm']['partnerTypeID'] ?? '') == $row['partnerTypeID'] ? 'selected' : '';
                                    ?>
                                                <option value="<?= htmlspecialchars($row['partnerTypeID']) ?>" <?= $selected ?>>
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
                                <label for="serviceAvailability" class="addServiceLabel">Availability</label>
                                <select class="form-select" name="serviceAvailability" id="serviceAvailability">
                                    <option value="" disabled <?= empty($_SESSION['addServiceForm']['serviceAvailability']) ? 'selected' : '' ?>>Select Availability</option>
                                    <?php
                                    $getAvailability = $conn->prepare("SELECT * FROM serviceavailability WHERE availabilityName  NOT IN ('Occupied', 'Private')");
                                    if ($getAvailability->execute()) {
                                        $result = $getAvailability->get_result();
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $selected = ($_SESSION['addServiceForm']['serviceAvailability'] ?? '') == $row['serviceAvailability'] ? 'selected' : '';
                                    ?>
                                                <option value="<?= htmlspecialchars($row['availabilityID']) ?>" <?= $selected ?>>
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
                                <input type="text" class="form-control" id="price" name="price" placeholder="e.g. 1000" value="<?= isset($_SESSION['addServiceForm']['price']) ? $_SESSION['addServiceForm']['price'] : '' ?>"
                                    required>
                            </div>
                            <div class="capacityContainer">
                                <label for="capacity" class="addServiceLabel">Capacity</label>
                                <input type="number" class="form-control" id="capacity" name="capacity"
                                    placeholder="e.g. 5" min='1' value="<?= isset($_SESSION['addServiceForm']['capacity']) ? $_SESSION['addServiceForm']['capacity'] : '' ?>">
                            </div>
                            <div class="durationContainer">
                                <label for="duration" class="addServiceLabel">Duration</label>
                                <input type="text" class="form-control" id="duration" name="duration"
                                    placeholder="e.g. 1 hour" value="<?= isset($_SESSION['addServiceForm']['duration']) ? $_SESSION['addServiceForm']['duration'] : '' ?>">
                            </div>
                            <div class="descContainer">
                                <label for="description" class="description" class="addServiceLabel">Description</label>
                                <textarea id="description" name="serviceDesc" class="form-control"
                                    placeholder="Service information/description (Optional)"><?= isset($_SESSION['addServiceForm']['serviceDesc']) ? $_SESSION['addServiceForm']['serviceDesc'] : '' ?></textarea>
                            </div>
                        </div>
                        <div class="submitBtnContainer">
                            <input type="hidden" name="partnershipID" value="<?= (int) $partnershipID ?>">
                            <button type="submit" class="btn btn-success w-25" name="addService"><i
                                    class="fas fa-plus-circle"></i> Add Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>



    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>

    <script>
        const serviceCardContainer = document.getElementById("service-card-container");
        const addServiceContainer = document.getElementById("addServiceContainer");
        const addServiceButtonContainer = document.getElementById("addServiceButtonContainer");
        const emptyContainer = document.getElementById("no-data-container");
        // const homeBtnContainer = document.getElementById("homeBtnContainer")

        addServiceContainer.style.display = "none"

        function addService() {
            if (addServiceContainer.style.display == "none") {
                addServiceContainer.style.display = "block";
                serviceCardContainer.style.display = "none";
                addServiceButtonContainer.style.display = "none";
                // homeBtnContainer.style.display = "none";
                document.getElementById("title").innerHTML = "Add Service"
                emptyContainer.style.display = 'none';

            } else {
                addServiceContainer.style.display = "block";
            }
        }
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

    <script>
        function getStatusBadge(colorClass, status) {
            return `<span class="badge bg-${colorClass} text-capitalize w-75">${status}</span>`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const partnershipID = document.getElementById('partnershipID').value;

            fetch(`../../Function/Partner/getPartnerServices.php?id=${encodeURIComponent(partnershipID)}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'An unknown error occurred.',
                            showConfirmButton: false,
                            timer: 1500,
                        });
                        return;
                    }

                    const services = data.details;
                    const serviceContainer = document.getElementById('service-card-container');
                    serviceContainer.innerHTML = '';
                    const emptyContainer = document.getElementById('no-data-container');
                    emptyContainer.innerHTML = '';


                    if (services && services.length !== 0) {
                        emptyContainer.innerHTML = '';
                        services.forEach((service, index) => {
                            // Create card
                            const card = document.createElement('div');
                            card.classList.add('card', 'service-card');

                            const cardHeader = document.createElement('div');
                            cardHeader.classList.add('card-header');

                            const pHeader = document.createElement('p');
                            pHeader.classList.add('card-text');
                            pHeader.innerHTML = getStatusBadge(service.classColor, service.statusName);

                            const cardBody = document.createElement('div');
                            cardBody.classList.add('card-body');

                            const h5 = document.createElement('h5');
                            h5.classList.add('card-title');
                            h5.innerHTML = service.serviceName;

                            const pBody = document.createElement('p');
                            pBody.classList.add('card-text');
                            pBody.innerHTML = `Price: ${service.servicePrice}`;

                            const cardFooter = document.createElement('div');
                            cardFooter.classList.add('card-footer');

                            const viewModalBtn = document.createElement('button');
                            viewModalBtn.classList.add('btn', 'btn-primary', 'w-50');
                            viewModalBtn.setAttribute('data-bs-toggle', 'modal');
                            viewModalBtn.setAttribute('data-bs-target', `#${service.modalID}`);
                            viewModalBtn.innerHTML = 'View';

                            // Assemble card
                            cardHeader.appendChild(pHeader);
                            cardBody.appendChild(h5);
                            cardBody.appendChild(pBody);
                            cardFooter.appendChild(viewModalBtn);

                            card.appendChild(cardHeader);
                            card.appendChild(cardBody);
                            card.appendChild(cardFooter);

                            // Create Modal
                            const viewModal = document.createElement('div');
                            viewModal.classList.add('modal', 'fade', 'serviceModal');
                            viewModal.id = service.modalID;
                            viewModal.tabIndex = -1;
                            viewModal.setAttribute('role', 'dialog');
                            viewModal.setAttribute('aria-labelledby', `modalLabel-${index}`);

                            const modalDialog = document.createElement('div');
                            modalDialog.className = 'modal-dialog modal-dialog-centered';
                            modalDialog.setAttribute('role', 'document');

                            const modalContent = document.createElement('div');
                            modalContent.className = 'modal-content';

                            const modalHeader = document.createElement('div');
                            modalHeader.className = 'modal-header';

                            const modalTitle = document.createElement('h4');
                            modalTitle.className = 'modal-title';
                            modalTitle.id = `modalLabel-${index}`;
                            modalTitle.textContent = 'Service Info';

                            const closeButton = document.createElement('button');
                            closeButton.type = 'button';
                            closeButton.className = 'btn-close';
                            closeButton.setAttribute('data-bs-dismiss', 'modal');
                            closeButton.setAttribute('aria-label', 'Close');

                            modalHeader.appendChild(modalTitle);
                            modalHeader.appendChild(closeButton);

                            const modalBody = document.createElement('div');
                            modalBody.className = 'modal-body';
                            modalBody.id = 'view-modal-container';

                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'partnershipServiceID';
                            hiddenInput.id = 'partnershipServiceID';
                            hiddenInput.value = service.partnershipServiceID || '';

                            // Info section
                            const sectionInfo = document.createElement('section');
                            const infoContainer = document.createElement('div');
                            infoContainer.className = 'infoContainer';

                            function inputContainer(inputLabel, IDName, inputValue = '', isReadonly = true) {
                                const container = document.createElement('div');
                                container.className = 'info-container';

                                const label = document.createElement('label');
                                label.setAttribute('for', IDName);
                                label.textContent = inputLabel;

                                const input = document.createElement('input');
                                input.type = 'text';
                                input.className = 'form-control';
                                input.name = IDName;
                                input.id = IDName;
                                input.value = inputValue;
                                input.readOnly = isReadonly;

                                container.appendChild(label);
                                container.appendChild(input);

                                return container;
                            }

                            infoContainer.appendChild(inputContainer('Service Name', 'serviceName', service.serviceName));
                            infoContainer.appendChild(inputContainer('Service Price', 'servicePrice', service.servicePrice));
                            infoContainer.appendChild(inputContainer('Service Capacity', 'serviceCapacity', service.serviceCapacity));
                            infoContainer.appendChild(inputContainer('Service Duration', 'serviceDuration', service.serviceDuration));

                            // Availability dropdown
                            const availContainer = document.createElement('div');
                            availContainer.className = 'info-container';

                            const availLabel = document.createElement('label');
                            availLabel.setAttribute('for', 'serviceAvailability');
                            availLabel.textContent = 'Availability';

                            const availSelect = document.createElement('select');
                            availSelect.className = 'form-select';
                            availSelect.name = 'serviceAvailability';
                            availSelect.id = 'serviceAvailability';
                            availSelect.disabled = true;

                            const options = [{
                                    text: 'Select Availability',
                                    value: '',
                                    selected: service.statusName === ''
                                },
                                {
                                    text: 'Available',
                                    value: 'available',
                                    selected: service.statusName.toLowerCase() === 'available'
                                },
                                {
                                    text: 'Booked',
                                    value: 'occupied',
                                    selected: ['occupied', 'booked'].includes(service.statusName.toLowerCase())
                                },
                                {
                                    text: 'Unavailable',
                                    value: 'not available',
                                    selected: service.statusName.toLowerCase() === 'not available'
                                }
                            ];

                            options.forEach(opt => {
                                const option = document.createElement('option');
                                option.value = opt.value;
                                option.textContent = opt.text;
                                if (opt.selected) option.selected = true;
                                availSelect.appendChild(option);
                            });

                            availContainer.appendChild(availLabel);
                            availContainer.appendChild(availSelect);
                            infoContainer.appendChild(availContainer);


                            sectionInfo.appendChild(hiddenInput);
                            sectionInfo.appendChild(infoContainer);
                            modalBody.appendChild(sectionInfo);

                            const sectionDesc = document.createElement('section');
                            sectionDesc.className = 'descContainer';

                            const formGroup = document.createElement('div');
                            formGroup.className = 'form-group';

                            const descLabel = document.createElement('label');
                            descLabel.setAttribute('for', 'serviceDescription');
                            descLabel.textContent = 'Service Description';

                            formGroup.appendChild(descLabel);

                            const descriptions = service.description || 'N/A';

                            const textarea = document.createElement('textarea');
                            textarea.name = 'serviceDescription';
                            textarea.classList.add('form-control', 'serviceDesc');
                            textarea.value = descriptions;
                            textarea.readOnly = true;
                            formGroup.appendChild(textarea);


                            sectionDesc.appendChild(formGroup);
                            modalBody.appendChild(sectionDesc);

                            // Modal Buttons
                            const modalBtnContainer = document.createElement('div');
                            modalBtnContainer.className = 'modal-btn-container';

                            const cancelBtn = document.createElement('button');
                            cancelBtn.type = 'button';
                            cancelBtn.className = 'btn btn-danger w-75 cancel-info-button';
                            cancelBtn.style.display = 'none';
                            cancelBtn.setAttribute('onclick', 'canEditInfo(this)');
                            cancelBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> Cancel';

                            const editBtn = document.createElement('button');
                            editBtn.type = 'button';
                            editBtn.className = 'btn btn-primary w-75 edit-info-button';
                            editBtn.setAttribute('onclick', 'editServiceInfo(this)');
                            editBtn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit';

                            modalBtnContainer.appendChild(cancelBtn);
                            modalBtnContainer.appendChild(editBtn);

                            // Assemble Modal
                            modalContent.appendChild(modalHeader);
                            modalContent.appendChild(modalBody);
                            modalContent.appendChild(modalBtnContainer);
                            modalDialog.appendChild(modalContent);
                            viewModal.appendChild(modalDialog);

                            // Append card and modal to DOM
                            serviceContainer.appendChild(card);
                            serviceContainer.appendChild(viewModal);
                        });
                    } else {
                        // Create card
                        const card = document.createElement('div');
                        card.classList.add('card', 'empty-card');

                        const cardBody = document.createElement('div');
                        cardBody.classList.add('card-body');

                        const h5 = document.createElement('h5');
                        h5.classList.add('card-title');
                        h5.innerHTML = "No Services To Display";

                        const pBody = document.createElement('p');
                        pBody.classList.add('card-text');
                        pBody.innerHTML = "click <strong> Add Service </strong> button to add a service";

                        cardBody.appendChild(h5);
                        cardBody.appendChild(pBody);

                        card.appendChild(cardBody);

                        emptyContainer.appendChild(card);
                    }

                });
        });
    </script>

    <script src="../../Assets/JS/Services/editCancelPartnerService.js"></script>

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
                text: 'Please try again later.',
                confirmButtonText: 'Okay',
            }).then((result) => {
                addService();
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