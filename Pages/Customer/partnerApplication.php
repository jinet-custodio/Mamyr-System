<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout();

//for setting image paths in 'include' statements
$baseURL = '../..';

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 4:
        $role = "Partnership Applicant";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

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
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Be Our Partner</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/beOurPartner.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <div class="wrapper">
        <nav class="navbar navbar-expand-lg fixed-top white-text" id="navbar-half2">
            <!-- Account Icon on the Left -->
            <ul class="navbar-nav d-flex flex-row align-items-center" id="profileAndNotif">
                <?php
                $getProfile = $conn->prepare("SELECT userProfile FROM user WHERE userID = ? AND userRole = ?");
                $getProfile->bind_param("ii", $userID, $userRole);
                $getProfile->execute();
                $getProfileResult = $getProfile->get_result();
                if ($getProfileResult->num_rows > 0) {
                    $data = $getProfileResult->fetch_assoc();
                    $imageData = $data['userProfile'];
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($imageData);

                    $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
                ?>
                <li class="nav-item account-nav">
                    <a href="../Account/account.php">
                        <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
                    </a>
                </li>

                <div class="notification-container position-relative">
                    <button type="button" class="btn position-relative" data-bs-toggle="modal"
                        data-bs-target="#notificationModal" id="notificationButton">
                        <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    </button>
                </div>

                <div class="hidden-inputs" style="display: none;">
                    <input type="hidden" id="receiver" value="<?= $role ?>">
                    <input type="hidden" id="userID" value="<?= $userID ?>">
                </div>

            </ul>


            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                    <li class="nav-item">
                        <?php if ($userRole !== 2): ?>
                            <a class="nav-link" href="dashboard.php"> Home</a>
                        <?php else: ?>
                            <a class="nav-link" href="../BusinessPartner/bpDashboard.php"> Home</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Amenities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="amenities.php">Resort Amenities</a></li>
                            <li><a class="dropdown-item" href="ratesAndHotelRooms.php">Rates and Hotel Rooms</a></li>
                            <li><a class="dropdown-item" href="events.php">Events</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">Blog</a>
                    </li>

                    <?php if ($userRole !== 2): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="beOurPartner.php">Be Our Partner</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookNow.php">Book Now</a>
                    </li>
                    <li class="nav-item">
                        <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">Log Out</a>
                    </li>
                </ul>
            </div>
        </nav>

        <?php
        $getUserInfo = $conn->prepare("SELECT * FROM user WHERE userID = ? AND userRole = ?");
        $getUserInfo->bind_param("ii", $userID, $userRole);
        $getUserInfo->execute();
        $getUserInfoResult = $getUserInfo->get_result();
        if ($getUserInfoResult->num_rows > 0) {
            $data =  $getUserInfoResult->fetch_assoc();


            $firstName = $data['firstName'] ?? "";
            $middleInitial = $data['middleInitial'] ?? "";
            $lastName = $data['lastName'] ?? "";
            $phoneNumber = $data['phoneNumber'] ?? '--';
            $email = $data['email'] ?? "";

            if ($phoneNumber === "--") {
                $phoneNumber = "";
            } else {
                $phoneNumber;
            }
        }
        ?>

        <main>

            <div class="titleContainer">
                <div class="backButton-container">
                    <a href="beOurPartner.php"><img src="../../Assets/Images/Icon/arrowBtnWhite.png" alt=""></a>
                </div>
                <h4 class="title">BE OUR PARTNER</h4>
                <p class="titleDescription">At Mamyr Resort and Events Place, we’re looking for talented event
                    management
                    professionals to
                    help us create unforgettable experiences. If your business specializes in photography, catering,
                    sound &
                    lighting, or other event services, we’d love to collaborate with you. Partnering with us gives you
                    access to
                    a variety of events, from weddings to corporate gatherings, all while showcasing your expertise in a
                    stunning resort setting. Reach out today to explore how we can work together to elevate every event
                    we host!
                </p>
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
                                        placeholder="Business Email"
                                        value="<?php echo isset($_SESSION['partnerData']['businessEmail']) ? htmlspecialchars(trim($_SESSION['partnerData']['businessEmail'])) : ''; ?>"
                                        required>

                                    <input type="text" class="form-control" id="firstName" name="firstName" pattern="^[A-Za-zÀ-ÖØ-öø-ÿĀ-žḀ-ỹẀ-ẕ'.\- ]{2,100}$"
                                        value="<?= htmlspecialchars($firstName) ?>" placeholder="First Name" required>
                                    <input type="text" class="form-control" id="middleInitial"
                                        value="<?= htmlspecialchars($middleInitial) ?>" name="middleInitial" pattern="^[A-Za-zÀ-ÖØ-öø-ÿĀ-žḀ-ỹẀ-ẕ'.\- ]{1,100}$"
                                        placeholder="Middle Initial (Optional)">
                                    <input type="text" class="form-control" id="lastName" name="lastName" pattern="^[A-Za-zÀ-ÖØ-öø-ÿĀ-žḀ-ỹẀ-ẕ'.\- ]{2,100}$"
                                        value="<?= htmlspecialchars($lastName) ?>" placeholder="Last Name" required>
                                    <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                                        pattern="^(?:\+63|0)9\d{9}$" title="e.g., +639123456789 or 09123456789"
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
                                    <div id="selectedBusinessTypes" class="mt-2 text-black"></div>

                                    <input type="text" class="form-control" id="streetAddress" name="streetAddress"
                                        placeholder="Street Address(optional)"
                                        value="<?php echo isset($_SESSION['partnerData']['streetAddress']) ? htmlspecialchars(trim($_SESSION['partnerData']['streetAddress'])) : ''; ?>">

                                    <input type="text" class="form-control" id="barangay" name="barangay"
                                        placeholder="Barangay"
                                        value="<?php echo isset($_SESSION['partnerData']['barangay']) ? htmlspecialchars(trim($_SESSION['partnerData']['barangay'])) : ''; ?>"
                                        required>

                                    <input type="text" class="form-control" id="city" name="city" placeholder="Town/City"
                                        value="<?php echo isset($_SESSION['partnerData']['city']) ? htmlspecialchars(trim($_SESSION['partnerData']['city'])) : ''; ?>"
                                        required>

                                    <div class="row1">
                                        <input type="text" class="form-control" id="province" name="province"
                                            placeholder="Province"
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
                                    <?php if (isset($_SESSION['partnerData']['imageName'])) { ?>
                                        <input type="text" class="form-control validIDFIle" value="<?php echo isset($_SESSION['partnerData']['imageName']) ? htmlspecialchars(trim($_SESSION['partnerData']['imageName'])) : ''; ?>">
                                    <?php }  ?>
                                    <input type="file" class="form-control validIDFIle" id="validID" name="validID">

                                    <button type="submit" class="btn btn-primary w-75" name="submit_request"
                                        id="submit-request" onclick="submitRequest()">Submit Request</button>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- modal -->

                    <!-- modal for type of business -->
                    <div class="modal fade" id="busTypenModal" tabindex="-1" aria-labelledby="busTypeModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">Type of Business</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="busTypeBody">
                                        <?php
                                        $serviceType = $conn->prepare("SELECT * FROM partnershiptype");
                                        $serviceType->execute();
                                        $serviceTypeResult = $serviceType->get_result();
                                        $selectedTypes = $_SESSION['partnerData']['partnerType'] ?? [];
                                        if ($serviceTypeResult->num_rows > 0) {
                                            while ($serviceTypes = $serviceTypeResult->fetch_assoc()) {
                                                $partnerType = $serviceTypes['partnerTypeID'];
                                                $partnerTypeDescription = $serviceTypes['partnerTypeDescription'];
                                        ?>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="partnerType[]"
                                                        id="partnerType<?= htmlspecialchars($partnerType) ?>"
                                                        value="<?= htmlspecialchars($partnerType) ?>"
                                                        <?php echo in_array($partnerType, $selectedTypes) ? 'checked' : ''; ?>>
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
                                    <?php
                                    $otherPartnerTypes = $_SESSION['partnerData']['other-partner-type'] ?? [];

                                    if (!empty($otherPartnerTypes)):
                                    ?>
                                        <div class="form-group other-container" id="otherInput">
                                            <?php foreach ($otherPartnerTypes as $val): ?>
                                                <input type="text" class="form-control" name="other-partner-type[]"
                                                    value="<?= htmlspecialchars($val) ?>">
                                            <?php endforeach; ?>
                                            <div class="button">
                                                <!-- //TODO: Pakipalitan na lang -->
                                                <button type="button" class="btn btn-success" id="addInputField"><i
                                                        class="bi bi-plus"></i> Add other</button>

                                                <button type="button" class="btn btn-danger" id="removeInputField"><i
                                                        class="bi bi-x"></i> Remove field</button>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="form-group other-container" id="otherInput" style="display: none;">
                                            <label for="otherText" class="other-label">Please specify:</label>
                                            <input type="text" class="form-control" id="otherText" name="other-partner-type[]">
                                            <div class="button">
                                                <!-- //TODO: Pakipalitan na lang -->
                                                <button type="button" class="btn btn-success" id="addInputField"><i
                                                        class="bi bi-plus"></i> Add other</button>

                                                <button type="button" class="btn btn-danger" id="removeInputField"><i
                                                        class="bi bi-x"></i> Remove field</button>
                                            </div>
                                        </div>

                                    <?php endif; ?>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" id="selectedPartnerTypes"
                                        data-bs-dismiss="modal">Select</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- modal for type of business -->

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
                                                <p>You can either upload your documents/media to Google Drive or share links
                                                    to your
                                                    social media pages (such as Instagram, Facebook, YouTube, etc.) that
                                                    showcase
                                                    your
                                                    business or talent.</p>
                                                <ul>
                                                    <li><strong>Google Drive:</strong> Create a new folder in Google Drive
                                                        with your
                                                        business or performance name. Upload the required documents or media
                                                        to this
                                                        folder.</li>
                                                    <li><strong>Valid ID of the Business Owner/Representative:</strong>
                                                        Upload a
                                                        clear
                                                        photo or scanned copy of a valid government-issued ID using the
                                                        input box
                                                        provided below.</li>
                                                    <li><strong>Social Media Links:</strong> If you have active business
                                                        pages or
                                                        performance content on social media platforms, feel free to share
                                                        the links
                                                        to
                                                        your profiles or posts that demonstrate your business or
                                                        performance.</li>
                                                </ul>
                                            </li>

                                            <li>
                                                <strong>Share the Folder or Social Media Links</strong>
                                                <ul>
                                                    <li><strong>Google Drive:</strong> Right-click on the folder, select
                                                        "Share,"
                                                        and
                                                        choose "Anyone with the link" with permissions set to "Viewer" or
                                                        "Editor."
                                                        Copy
                                                        the link to your folder.</li>
                                                    <li><strong>Social Media Links:</strong> Simply copy and paste the URLs
                                                        to your
                                                        active business or performance profiles.</li>
                                                </ul>
                                            </li>

                                            <li>
                                                <strong>Paste the Link(s)</strong>
                                                <p>Once your documents/media or social media links are ready, paste the
                                                    link(s) on
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
                <div class="defaultDiv">
                    <h2>Your request has been submitted! Please wait for admin's evaluation of your application.</h2>
                </div>
            <?php } else {
                header("Location: ../register.php");
                exit();
            } ?>
        </main>

        <?php
        include 'footer.php';
        include 'loader.php';
        include '../Notification/notification.php';
        ?>
    </div>
    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>


    <!-- Scroll Nav BG -->
    <script src="../../Assets/JS/scrollNavbg.js"></script>

    <script>
        const submitRequest = () => {
            const requiredFields = [
                'firstName', 'lastName', 'phoneNumber', 'proofLink',
                'barangay', 'city', 'province', 'businessEmail', 'validID'
            ];

            let allValid = true;
            let partnerType = true;
            requiredFields.forEach(id => {
                const field = document.getElementById(id);
                if (!field || !field.value.trim()) {
                    if (field) field.classList.add('is-invalid');
                    allValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Validate business type selection
            const checkboxes = document.querySelectorAll('input[name="partnerType[]"]:checked');
            if (checkboxes.length < 1 || checkboxes.length > 2) {
                partnerType = false;
            };

            if (!allValid) {
                Swal.fire({
                    title: 'Oops',
                    text: "Please fill out all required fields before continuing.",
                    icon: 'warning'
                });
                return false;
            }
            if (!partnerType) {
                Swal.fire({
                    title: 'Oops',
                    text: "You must select 1 or 2 types.",
                    icon: 'warning'
                });
                return;
            }
            return true;
        };


        document.getElementById('selectedPartnerTypes').addEventListener('click', function() {
            // Get all checked checkboxes
            const selectedCheckboxes = document.querySelectorAll('input[name="partnerType[]"]:checked');
            const displayDiv = document.getElementById('selectedBusinessTypes');

            // Clear previous content
            displayDiv.innerHTML = '';

            if (selectedCheckboxes.length === 0) {
                displayDiv.innerHTML = '<em>No business type selected</em>';
                return;
            }

            selectedCheckboxes.forEach(checkbox => {
                const label = document.querySelector(`label[for="${checkbox.id}"]`);
                if (label) {
                    const span = document.createElement('span');
                    span.textContent = label.textContent.trim();
                    span.className = 'badge bg-light me-1';
                    displayDiv.appendChild(span);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const displayDiv = document.getElementById('selectedBusinessTypes');
            displayDiv.innerHTML = '';

            const checkedCheckboxes = document.querySelectorAll('input[name="partnerType[]"]:checked');

            if (checkedCheckboxes.length === 0) {
                displayDiv.innerHTML = '<em>No business type selected</em>';
                return;
            }

            checkedCheckboxes.forEach(checkbox => {
                const label = document.querySelector(`label[for="${checkbox.id}"]`);
                if (label) {
                    const span = document.createElement('span');
                    span.textContent = label.textContent.trim();
                    span.className = 'badge bg-info me-1 text-black';
                    displayDiv.appendChild(span);
                }
            });
        });
    </script>


    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('result');
        if (paramValue === 'emailExist') {
            Swal.fire({
                icon: 'warning',
                title: 'Email Already Exist!',
                text: 'The business email address you entered is already registered. Please use a different email or log in if you already have an account.',
                confirmButtonText: 'Okay'
            }).then(() => {
                document.getElementById('businessEmail').style.border = '1px solid red';
            });
        } else if (paramValue === 'imageFailed') {
            Swal.fire({
                title: 'Oops',
                text: `Make sure you uploaded an image`,
                icon: 'warning',
                confirmButtonText: 'Okay'
            });
        } else if (paramValue === 'extError') {
            Swal.fire({
                title: 'Oops',
                text: `Invalid file type. Please upload JPG, JPEG, WEBP, or PNG.`,
                icon: 'warning',
                confirmButtonText: 'Okay'
            });
        } else if (paramValue === 'imageSize') {
            Swal.fire({
                title: "Oops!",
                text: "File is too large. Maximum allowed size is 5MB.",
                icon: "warning",
                confirmButtonText: "Okay",
            });
        } else if (paramValue === 'selectPartner') {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: 'You must select 1 or 2 business types.',
                showConfirmButton: 'Okay',
            }).then(() => {
                const typeModal = document.getElementById('busTypenModal');
                const modal = new bootstrap.Modal(typeModal);
                modal.show();
            });
        } else if (paramValue === 'zipCode') {
            Swal.fire({
                title: "Oops!",
                text: "Please enter a valid ZIP code.",
                icon: "warning",
                confirmButtonText: "Okay",
            }).then(() => {
                document.getElementById('zip').style.border = '1px solid red';
            });
        }

        document.getElementById('zip').addEventListener('input', () => {
            document.getElementById('zip').style.border = '1px solid rgb(223, 226, 230)';
        });

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

    <?php if (isset($_SESSION['message']) || isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                <?php if (isset($_SESSION['message'])): ?>
                    Toast.fire({
                        icon: 'error',
                        title: <?= json_encode(strip_tags($_SESSION['message'])) ?>
                    });
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    Toast.fire({
                        icon: 'success',
                        title: <?= json_encode(strip_tags($_SESSION['success'])) ?>
                    });
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>


    <script>
        const phoneNumber = document.getElementById('phoneNumber');
        const zipCode = document.getElementById('zip');
        // const tooltip = document.getElementById('tooltip');

        const inputs = [
            phoneNumber,
            zip
        ].filter(Boolean);
        inputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
                // tooltip.classList.add('show');


                // clearTimeout(tooltip.hideTimeout);
                // tooltip.hideTimeout = setTimeout(() => {
                //     tooltip.classList.remove('show');
                // }, 2000);
            });
        });
    </script>

    <!-- Patner type others -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkboxes = document.querySelectorAll('input[name="partnerType[]"]');
            const otherInput = document.getElementById("otherInput");

            checkboxes.forEach(cb => {
                cb.addEventListener("change", function() {
                    const label = document.querySelector(`label[for="${this.id}"]`);
                    console.log(label.textContent.trim());
                    const labelText = label.textContent.trim();

                    if (labelText.toLowerCase() === "other") {
                        // otherInput.style.display = this.checked ? "block" : "none";

                        if (this.checked) {
                            otherInput.style.display = "block";
                        } else {
                            otherInput.style.display = "none";
                            otherInput.querySelectorAll('input[name="other-partner-type[]"]')
                                .forEach(input => input.value = '');
                        }
                    }

                });
            });


            document.getElementById('addInputField').addEventListener('click', () => {
                console.log('clicked');

                const existing = document.querySelectorAll('input[name="other-partner-type[]"]').length;

                if (existing >= 3) {
                    Swal.fire({
                        icon: 'info',
                        text: 'You can only add up to 3 fields.'
                    });
                    return;
                }

                const input = document.createElement('input');
                input.classList.add("form-control");
                input.setAttribute('name', 'other-partner-type[]');
                input.type = 'text';
                otherInput.appendChild(input);
            });

            document.getElementById("removeInputField").addEventListener('click', () => {

                const fields = document.querySelectorAll('input[name="other-partner-type[]"]');
                console.log(fields.length);

                if (fields.length <= 1) {
                    Swal.fire({
                        icon: 'info',
                        text: 'No added fields to remove'
                    });
                    return;
                };

                const lastField = fields[fields.length - 1];
                lastField.remove();
            });
        });
    </script>


</body>

</html>