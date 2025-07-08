<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
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
            $query = "SELECT userProfile FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $imageData = $data['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            ?>
            <li class="nav-item account-nav">
                <a href="Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile">
                </a>
            </li>
        </ul>


        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
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
                <li class="nav-item">
                    <a class="nav-link active" href="#">BE OUR PARTNER</a>
                </li>
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


    <?php
    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $firstName = $data['firstName'];
        $middleInitial = $data['middleInitial'];
        $lastName = $data['lastName'];
        $phoneNumber = $data['phoneNumber'];
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
                echo htmlspecialchars_decode(strip_tags($_SESSION['message']));
                unset($_SESSION['message']);
                ?>
            </p>
        <?php endif; ?>

        <?php
        if (isset($_SESSION['success'])): ?>
            <p class="alert alert-success">
                <?php
                echo htmlspecialchars_decode(strip_tags($_SESSION['success']));
                unset($_SESSION['success']);
                ?>
            </p>
        <?php endif; ?>
    </div>


    <form action="../../Function/partnershipRequest.php" method="POST" enctype="multipart/form-data" onsubmit="return submitRequest();">

        <div class=" container" id="basicInfo">

            <input type="hidden" class="form-control" id="userdID" name="userID"
                value="<?= htmlspecialchars_decode($userID) ?>">
            <input type="hidden" class="form-control" id="userdRole" name="userRole"
                value="<?= htmlspecialchars_decode($userRole) ?>">
            <div class="row">
                <div class="col" id="repInfoContainer">
                    <h4 class="repInfoLabel">Representative Information</h4>

                    <div class="repInfoFormContainer">
                        <input type="email" class="form-control" id="businessEmail" name="businessEmail"
                            placeholder="Business Email" required>

                        <input type="text" class="form-control" id="firstName" name="firstName"
                            value="<?= htmlspecialchars_decode($firstName) ?>" placeholder="First Name" required>
                        <input type="text" class="form-control" id="middleInitial"
                            value="<?= htmlspecialchars_decode($middleInitial) ?>" name="middleInitial"
                            placeholder="Middle Initial (Optional)">
                        <input type="text" class="form-control" id="lastName" name="lastName"
                            value="<?= htmlspecialchars_decode($lastName) ?>" placeholder="Last Name" required>
                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                            placeholder="Phone Number" value="<?= htmlspecialchars_decode($phoneNumber) ?>" required>
                    </div>
                </div>

                <div class="col" id="busInfoContainer">
                    <h4 class="busInfoLabel">Business Information</h4>

                    <div class="busInfoFormContainer">
                        <!--purpose of this div: going to put margin top para pumantay sa left and right column-->
                        <input type="text" class="form-control" id="companyName" name="companyName" placeholder="Business Name" value="<?php echo isset($_SESSION['partnerData']['companyName']) ? htmlspecialchars(trim($_SESSION['partnerData']['companyName'])) : ''; ?>">

                        <select id="partnerType" name="partnerType" class="form-select primary">
                            <option value="" disabled selected>Type of Business</option>
                            <?php
                            $serviceType = $conn->prepare("SELECT * FROM partnershipTypes");
                            $serviceType->execute();
                            $serviceTypeResult = $serviceType->get_result();
                            if ($serviceTypeResult->num_rows > 0) {
                                while ($serviceTypes = $serviceTypeResult->fetch_assoc()) {
                                    $partnerType = $serviceTypes['partnerType'];
                                    $partnerTypeDescription = $serviceTypes['partnerTypeDescription'];
                            ?>
                                    <option value="<?= htmlspecialchars($partnerType) ?>"><?= htmlspecialchars($partnerTypeDescription) ?></option>

                            <?php
                                }
                            }
                            ?>
                        </select>

                        <input type="text" class="form-control" id="streetAddress" name="streetAddress"
                            placeholder="Street Address(optional)" value="<?php echo isset($_SESSION['partnerData']['streetAddress']) ? htmlspecialchars(trim($_SESSION['partnerData']['streetAddress'])) : ''; ?>">

                        <input type="text" class="form-control" id="barangay" name="barangay"
                            placeholder="Barangay" value="<?php echo isset($_SESSION['partnerData']['barangay']) ? htmlspecialchars(trim($_SESSION['partnerData']['barangay'])) : ''; ?>" required>

                        <input type="text" class="form-control" id="city" name="city" placeholder="Town/City" value="<?php echo isset($_SESSION['partnerData']['city']) ? htmlspecialchars(trim($_SESSION['partnerData']['city'])) : ''; ?>" required>

                        <div class="row1">
                            <input type="text" class="form-control" id="province" name="province"
                                placeholder="Province" value="<?php echo isset($_SESSION['partnerData']['province']) ? htmlspecialchars(trim($_SESSION['partnerData']['province'])) : ''; ?>" required>

                            <input type="text" class="form-control" id="zip" name="zip" placeholder="Zip Code" value="<?php echo isset($_SESSION['partnerData']['zip']) ? htmlspecialchars(trim($_SESSION['partnerData']['zip'])) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="col" id="busProofContainer">
                    <h4 class="busProofLabel">Proof of Business</h4>

                    <p class="description">Please provide a link to your Google Drive or social media page as a
                        proof of your business</p>

                    <div class="busProofFormContainer">


                        <input type="text" class="form-control" id="proofLink" name="proofLink"
                            placeholder="Paste the link here" value="<?php echo isset($_SESSION['partnerData']['proofLink']) ? htmlspecialchars(trim($_SESSION['partnerData']['proofLink'])) : ''; ?>" required>

                        <a href="#moreDetailsModal" class="moreDetails" data-bs-toggle="modal"
                            data-bs-target="#openModal">More Details</a>

                        <button type="submit" class="btn btn-primary w-75" name="submit_request"
                            id="submit-request" onclick="submitRequest()">Submit Request</button>

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





        <!-- <div class="container-fluid center-card d-flex justify-content-center align-items-center">
            <div class="card" style="width: 50rem;">

                <div class="card-header">
                    <div class="card-title">
                        <h5 class="titleCard">Partner Application Form</h5>
                        <h6 class="titleDesc">Tell us about you and your company</h6>
                    </div>
                </div>
                <input type="hidden" class="form-control" id="userdID" name="userID" value="<?= htmlspecialchars_decode($userID) ?>" placeholder="First Name"
                    required>
                <input type="hidden" class="form-control" id="userdID" name="userRole" value="<?= htmlspecialchars_decode($userRole) ?>" placeholder="First Name"
                    required>
                <div class="card-body">
                    <h5 class="repName">Representative Name</h5>
                    <div class="name">
                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?= htmlspecialchars_decode($firstName) ?>" placeholder="First Name"
                            required>
                        <input type="text" class="form-control" id="middleInitial" name="middleInitial" value="<?= htmlspecialchars_decode($middleInitial) ?>"
                            placeholder="Middle Initial">
                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name" value="<?= htmlspecialchars_decode($lastName) ?>"
                            required>
                    </div>

                    <h5 class="contactInfo">Contact Info</h5>
                    <div class="contact">
                        <input type="email" class="form-control" id="emailAddress" name="emailAddress"
                            placeholder="Business Email">
                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="<?= htmlspecialchars_decode($phoneNumber) ?>"
                            placeholder="Phone Number">
                    </div>
                </div>

                <hr class="line">

                <div class="card-body">

                    <h5 class="companyName">Company Information</h5>
                    <div class="name">
                        <input type="text" class="form-control" id="comapanyName" name="companyName"
                            placeholder="Company Name" required>

                    </div>


                    <div class="businessType">
                        <h5 class="busTypeName">Type of Business</h5>

                        <select id="service" name="partnerType" class="form-select" required>
                            <option value="" disabled selected>Select Service</option>
                            <option value="catering">Catering</option>
                            <option value="photography">Photography/Videography</option>
                            <option value="sound-lighting">Sound and Lighting</option>
                            <option value="event-hosting">Event Hosting</option>
                            <option value="photo-booth">Photo Booth</option>
                            <option value="performer">Performer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div id="other-container" style="display: none; margin-left: 1vw;">
                        <input type="text" id="other-input" name="other_input" class="form-control "
                            placeholder="Please specify..." />
                    </div>

                    <h5 class="busAddress">Business Address</h5>
                    <div class="busAddForm">

                        <div class="streetAddRow">
                            <input type="text" class="form-control" id="streetAddress" name="streetAddress"
                                placeholder="Street Address" required>

                            <input type="text" class="form-control" id="address2" name="address2"
                                placeholder="Street Address Line 2 (optional)">
                        </div>

                        <input type="text" class="form-control" id="city" name="city" placeholder="Town/City" required>

                        <input type="text" class="form-control" id="province" name="province" placeholder="Province">

                        <input type="text" class="form-control" id="zip" name="zip" placeholder="ZIP/Postal Code"
                            required>
                    </div>

                    <h5 class="docuTitle">Documents for Verification</h5>
                    <p>To verify your business or talent, please upload the following documents or media:</p>

                    <p><strong>For Business Partners:</strong></p>
                    <ol type="A" class="BPrequirements">
                        <li>Business Permit</li>
                        <li>License to Operate</li>
                        <li>Valid ID of the Representative</li>
                        <li>Business Operations Photos (3-5)</li>
                        <li>Business Operations Video (Optional)</li>
                    </ol>

                    <p><strong>For Talents & Performers:</strong></p>
                    <ol type="A" class="TPrequirements">
                        <li>Social Media Links (Instagram, Facebook, YouTube, etc.)</li>
                        <li>Performance Photos (3-5)</li>
                        <li>Performance Videos (at least 1-2)</li>
                        <li>Introduction Video (Optional)</li>
                    </ol>

                    <p><strong>Step 1: Create a Google Drive Folder</strong></p>
                    <p>Sign in to Google Drive and create a new folder with your business or performance name. Then,
                        upload the required documents or media to this folder.</p>

                    <p><strong>Step 2: Share the Folder</strong></p>
                    <p>Once your folder is ready, click on the folder, then right-click and select
                        <strong>"Share"</strong>. Make sure to select <strong>"Anyone with the link"</strong> and set
                        permissions to <strong>"Viewer" or "Editor"</strong> depending on your preference. Copy the link
                        to your folder.
                    </p>

                    <p><strong>Step 3: Paste the Google Drive Link</strong></p>
                    <p>Paste the link to your shared Google Drive folder in the <strong>"Google Drive Folder
                            Link"</strong> input box below.</p>

                    <h5 class="importantNotesTitle">Important Notes</h5>
                    <ol type="1" class="BPrequirements">
                        <li>Please ensure that all documents and media files are clear and legible.</li>
                        <li>If you encounter any issues with uploading documents or creating the Google Drive folder,
                            feel free to contact us at <a
                                href="mailto:mamyresort128@gmail.com">mamyresort128@gmail.com</a>.
                        </li>
                        <li>The information you provide will be kept confidential and used solely for partnership
                            verification purposes.</li>
                        <li>Thank you for your interest in partnering with us. We look forward to the possibility of
                            working together!</li>
                    </ol>


                    <input class="form-control" type="text" name="documentLink"
                        placeholder="Example: https://drive.google.com/drive/folders/your-folder-id-here">
                </div>

                <button type="submit" class="btn btn-success btn-md" name="submit_request" id="submit-request">Submit Request</button>
            </div>

        </div> -->

    </form>


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
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Scroll Nav BG -->
    <script src="../../Assets/JS/scrollNavbg.js"></script>


    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function submitRequest() {
            const requiredFields = [
                'firstName', 'lastName', 'phoneNumber', 'partnerType', 'proofLink', 'barangay', 'city', 'province', 'businessEmail'
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