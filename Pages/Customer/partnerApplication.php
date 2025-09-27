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
require '../../Function/notification.php';
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
    <title>Mamyr - Be Our Partner</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/beOurPartner.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav">
            <?php
            $getProfile = $conn->prepare("SELECT userProfile FROM user WHERE userID = ? AND userRole = ?");
            $getProfile->bind_param("ii", $userID, $userRole);
            $getProfile->execute();
            $getProfileResult = $getProfile->get_result();
            if ($getProfileResult->num_rows > 0) {
                $data = $getProfileResult->fetch_assoc();
                $imageData = $data['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            ?>
            <li class="nav-item account-nav">
                <a href="../Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
                </a>
            </li>

            <!-- Get notification -->
            <?php

            if ($userRole === 1 || $userRole === 4) {
                $receiver = 'Customer';
            } elseif ($userRole === 2) {
                $receiver = 'Partner';
            }

            $notifications = getNotification($conn, $userID, $receiver);
            $counter = $notifications['count'];
            $notificationsArray = $notifications['messages'];
            $color = $notifications['colors'];
            $notificationIDs = $notifications['ids'];
            ?>


            <div class="nav-item notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= htmlspecialchars($counter) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>

        </ul>


        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"> HOME</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item " href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>

                <?php if ($userRole !== 2): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookNow.php">BOOK NOW</a>
                </li>
                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">LOG OUT</a>
                </li>
            </ul>
        </div>
    </nav>


    <!-- Notification Modal -->
    <?php include '../notificationModal.php' ?>

    <?php
    $getUserInfo = $conn->prepare("SELECT * FROM user WHERE userID = ? AND userRole = ?");
    $getUserInfo->bind_param("ii", $userID, $userRole);
    $getUserInfo->execute();
    $getUserInfoResult = $getUserInfo->get_result();
    if ($getUserInfoResult->num_rows > 0) {
        $data =  $getUserInfoResult->fetch_assoc();


        $firstName = $data['firstName'];
        $middleInitial = $data['middleInitial'];
        $lastName = $data['lastName'];
        $phoneNumber = $data['phoneNumber'] ?? '--';
        $email = $data['email'];

        if ($phoneNumber === "--") {
            $phoneNumber = "";
        } else {
            $phoneNumber;
        }
    }
    ?>


    <div class="titleContainer">
        <h4 class="title">BE OUR PARTNER</h4>
        <p class="titleDescription">At Mamyr Resort and Events Place, we’re looking for talented event management
            professionals to
            help us create unforgettable experiences. If your business specializes in photography, catering, sound &
            lighting, or other event services, we’d love to collaborate with you. Partnering with us gives you access to
            a variety of events, from weddings to corporate gatherings, all while showcasing your expertise in a
            stunning resort setting. Reach out today to explore how we can work together to elevate every event we host!
        </p>
        <?php
        if (isset($_SESSION['message'])): ?>
            <p class="alert alert-danger">
                <?php
                echo htmlspecialchars(strip_tags($_SESSION['message']));
                unset($_SESSION['message']);
                ?>
            </p>
        <?php endif; ?>

        <?php
        if (isset($_SESSION['success'])): ?>
            <p class="alert alert-success">
                <?php
                echo htmlspecialchars(strip_tags($_SESSION['success']));
                unset($_SESSION['success']);
                ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if ($userRole === 1) { ?>
        <form action="../../Function/partnershipRequest.php" method="POST" enctype="multipart/form-data"
            onsubmit="return submitRequest();">

            <div class=" container" id="basicInfo">

                <input type="hidden" class="form-control" id="userdID" name="userID"
                    value="<?= htmlspecialchars($userID) ?>">
                <input type="hidden" class="form-control" id="userdRole" name="userRole"
                    value="<?= htmlspecialchars($userRole) ?>">
                <div class="row">
                    <div class="col" id="repInfoContainer">
                        <h4 class="repInfoLabel">Representative Information</h4>

                        <div class="repInfoFormContainer">
                            <input type="email" class="form-control" id="businessEmail" name="businessEmail"
                                placeholder="Business Email" required>

                            <input type="text" class="form-control" id="firstName" name="firstName"
                                value="<?= htmlspecialchars($firstName) ?>" placeholder="First Name" required>
                            <input type="text" class="form-control" id="middleInitial"
                                value="<?= htmlspecialchars($middleInitial) ?>" name="middleInitial"
                                placeholder="Middle Initial (Optional)">
                            <input type="text" class="form-control" id="lastName" name="lastName"
                                value="<?= htmlspecialchars($lastName) ?>" placeholder="Last Name" required>
                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                                placeholder="Phone Number" value="<?= htmlspecialchars($phoneNumber) ?>" required>
                        </div>
                    </div>

                    <div class="col" id="busInfoContainer">
                        <h4 class="busInfoLabel">Business Information</h4>

                        <div class="busInfoFormContainer">
                            <!--purpose of this div: going to put margin top para pumantay sa left and right column-->
                            <input type="text" class="form-control" id="companyName" name="companyName"
                                placeholder="Business Name"
                                value="<?php echo isset($_SESSION['partnerData']['companyName']) ? htmlspecialchars(trim($_SESSION['partnerData']['companyName'])) : ''; ?>">

                            <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#busTypenModal">Type of Business</button>


                            <!-- modal for type of business -->
                            <div class="modal fade" id="busTypenModal" tabindex="-1" aria-labelledby="busTypeModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Type of Business</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body busTypeBody">
                                            <?php
                                            $serviceType = $conn->prepare("SELECT * FROM partnershiptype");
                                            $serviceType->execute();
                                            $serviceTypeResult = $serviceType->get_result();
                                            if ($serviceTypeResult->num_rows > 0) {
                                                while ($serviceTypes = $serviceTypeResult->fetch_assoc()) {
                                                    $partnerType = $serviceTypes['partnerTypeID'];
                                                    $partnerTypeDescription = $serviceTypes['partnerTypeDescription'];
                                            ?>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="partnerType[]"
                                                            id="partnerType<?= htmlspecialchars($partnerType) ?>"
                                                            value="<?= htmlspecialchars($partnerType) ?>">
                                                        <label class="form-check-label"
                                                            for="partnerType<?= htmlspecialchars($partnerType) ?>">
                                                            <?= htmlspecialchars($partnerTypeDescription) ?>
                                                        </label>
                                                    </div>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary">Select</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- modal for type of business -->

                            <input type="text" class="form-control" id="streetAddress" name="streetAddress"
                                placeholder="Street Address(optional)"
                                value="<?php echo isset($_SESSION['partnerData']['streetAddress']) ? htmlspecialchars(trim($_SESSION['partnerData']['streetAddress'])) : ''; ?>">

                            <input type="text" class="form-control" id="barangay" name="barangay" placeholder="Barangay"
                                value="<?php echo isset($_SESSION['partnerData']['barangay']) ? htmlspecialchars(trim($_SESSION['partnerData']['barangay'])) : ''; ?>"
                                required>

                            <input type="text" class="form-control" id="city" name="city" placeholder="Town/City"
                                value="<?php echo isset($_SESSION['partnerData']['city']) ? htmlspecialchars(trim($_SESSION['partnerData']['city'])) : ''; ?>"
                                required>

                            <div class="row1">
                                <input type="text" class="form-control" id="province" name="province" placeholder="Province"
                                    value="<?php echo isset($_SESSION['partnerData']['province']) ? htmlspecialchars(trim($_SESSION['partnerData']['province'])) : ''; ?>"
                                    required>

                                <input type="text" class="form-control" id="zip" name="zip" placeholder="Zip Code"
                                    value="<?php echo isset($_SESSION['partnerData']['zip']) ? htmlspecialchars(trim($_SESSION['partnerData']['zip'])) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col" id="busProofContainer">
                        <h4 class="busProofLabel">Proof of Business</h4>

                        <p class="description">Please provide a link to your Google Drive or social media page as a
                            proof of your business</p>

                        <div class="busProofFormContainer">


                            <input type="text" class="form-control" id="proofLink" name="proofLink"
                                placeholder="Paste the link here"
                                value="<?php echo isset($_SESSION['partnerData']['proofLink']) ? htmlspecialchars(trim($_SESSION['partnerData']['proofLink'])) : ''; ?>"
                                required>

                            <a href="#moreDetailsModal" class="moreDetails" data-bs-toggle="modal"
                                data-bs-target="#openModal">More Details</a>

                            <h6 class="label">Upload a Valid ID</h6>
                            <input type="file" class="form-control validIDFIle" id="validID" name="validID">

                            <button type="submit" class="btn btn-primary w-75" name="submit_request" id="submit-request"
                                onclick="submitRequest()">Submit Request</button>

                        </div>
                    </div>
                </div>
            </div>

            <!-- modal -->

            <div class="modal fade modal-lg m-auto" id="openModal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">

                <div class="modal-dialog" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="instructionLabel">Documents for Verification</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body" style="max-height:400px; overflow-y: auto;">
                            <p class="modalSubtitle">To verify your business or talent, please upload the following
                                documents or
                                media:</p>


                            <div class="modalRequirementsContainer">
                                <div class="busPartnerRequirement">
                                    <p><strong>For Business Partners:</strong></p>
                                    <ol type="A" class="BPrequirements">
                                        <li>Business Permit</li>
                                        <li>License to Operate</li>
                                        <li>Valid ID of the Representative</li>
                                        <li>Business Operations Photos (3-5)</li>
                                        <li>Business Operations Video (Optional)</li>
                                    </ol>
                                </div>

                                <div class="talentsRequirement">
                                    <p><strong>For Talents & Performers:</strong></p>
                                    <ol type="A" class="TPrequirements">
                                        <li>Social Media Links (Instagram, Facebook, </br> YouTube, etc.)</li>
                                        <li>Performance Photos (3-5)</li>
                                        <li>Performance Videos (at least 1-2)</li>
                                        <li>Introduction Video (Optional)</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="stepsContainer">
                                <ol>
                                    <li>
                                        <strong>Upload Your Documents or Media</strong>
                                        <p>You can either upload your documents/media to Google Drive or share links to your
                                            social media pages (such as Instagram, Facebook, YouTube, etc.) that showcase
                                            your
                                            business or talent.</p>
                                        <ul>
                                            <li><strong>Google Drive:</strong> Create a new folder in Google Drive with your
                                                business or performance name. Upload the required documents or media to this
                                                folder.</li>
                                            <li><strong>Valid ID of the Business Owner/Representative:</strong> Upload a
                                                clear
                                                photo or scanned copy of a valid government-issued ID using the input box
                                                provided below.</li>
                                            <li><strong>Social Media Links:</strong> If you have active business pages or
                                                performance content on social media platforms, feel free to share the links
                                                to
                                                your profiles or posts that demonstrate your business or performance.</li>
                                        </ul>
                                    </li>

                                    <li>
                                        <strong>Share the Folder or Social Media Links</strong>
                                        <ul>
                                            <li><strong>Google Drive:</strong> Right-click on the folder, select "Share,"
                                                and
                                                choose "Anyone with the link" with permissions set to "Viewer" or "Editor."
                                                Copy
                                                the link to your folder.</li>
                                            <li><strong>Social Media Links:</strong> Simply copy and paste the URLs to your
                                                active business or performance profiles.</li>
                                        </ul>
                                    </li>

                                    <li>
                                        <strong>Paste the Link(s)</strong>
                                        <p>Once your documents/media or social media links are ready, paste the link(s) on
                                            the
                                            input box of the “Proof of Business” section.</p>
                                    </li>
                                </ol>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                                aria-label="Close">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php } elseif ($userRole === 4) { ?>
        <h1>YOU'RE REQUEST HAS BEEN SUBMITTED</h1>
    <?php } ?>
    <footer class="py-1" style="margin-top: 5vw !important;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            <h3 class="mb-0">MAMYR RESORT AND EVENTS PLACE</h3>
        </div>

        <div class="info">
            <div class="reservation">
                <h4 class="reservationTitle">Reservation</h4>
                <h4 class="numberFooter">(0998) 962 4697 </h4>
                <h4 class="emailAddressTextFooter">mamyr@gmail.com</h4>
            </div>
            <div class="locationFooter">
                <h4 class="locationTitle">Location</h4>
                <h4 class="addressTextFooter">Sitio Colonia, Gabihan, San Ildefonso, Bulacan</h4>

            </div>
        </div>
        <hr class="footerLine">
        <div class="socialIcons">
            <a href="https://www.facebook.com/p/Mamyr-Resort-Restaurant-Events-Place-100083298304476/"><i
                    class='bx bxl-facebook-circle'></i></a>
            <a href="https://workspace.google.com/intl/en-US/gmail/"><i class='bx bxl-gmail'></i></a>
            <a href="tel:+09989624697">
                <i class='bx bxs-phone'></i>
            </a>
        </div>

    </footer>


    <!-- <script>
        const serviceSelect = document.getElementById('service');
        const otherContainer = document.getElementById('other-container');
        const otherInput = document.getElementById('other-input');

        serviceSelect.addEventListener('change', () => {
            if (serviceSelect.value === 'other') {
                otherContainer.style.display = 'block';
                otherInput.required = true;
            } else {
                otherContainer.style.display = 'none';
                otherInput.required = false;
            }
        });
    </script> -->

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>


    <!-- Notification Ajax -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.querySelector('.notification-container .badge');

            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notificationID = this.dataset.id;

                    fetch('../../Function/notificationFunction.php', {
                            method: 'POST',
                            headers: {
                                'Content-type': 'application/x-www-form-urlencoded'
                            },
                            body: 'notificationID=' + encodeURIComponent(notificationID)
                        })
                        .then(response => response.text())
                        .then(data => {

                            this.style.transition = 'background-color 0.3s ease';
                            this.style.backgroundColor = 'white';


                            if (badge) {
                                let currentCount = parseInt(badge.textContent, 10);

                                if (currentCount > 1) {
                                    badge.textContent = currentCount - 1;
                                } else {
                                    badge.remove();
                                }
                            }
                        });
                });
            });
        });
    </script>



    <!-- Scroll Nav BG -->
    <script src="../../Assets/JS/scrollNavbg.js"></script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function submitRequest() {
            const requiredFields = [
                'firstName', 'lastName', 'phoneNumber', 'partnerType', 'proofLink', 'barangay', 'city', 'province',
                'businessEmail'
            ];

            let allValid = true;

            requiredFields.forEach(id => {
                const field = document.getElementById(id);
                if (!field || !field.value.trim()) {
                    if (field) field.classList.add('is-invalid');
                    allValid = false;
                } else {
                    if (field) field.classList.remove('is-invalid');
                }
            });

            if (!allValid) {
                Swal.fire({
                    title: 'Oops',
                    text: "Please fill out all required fields before continuing.",
                    icon: 'warning'
                });
                return false; // stop form submission
            }

            return true; // allow form submission
        }
    </script>

    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('result');

        if (paramValue === 'emailExist') {
            Swal.fire({
                icon: 'warning',
                title: 'Email Already Exist!',
                text: 'The email address you entered is already registered.'
            })
        }
    </script>


</body>

</html>