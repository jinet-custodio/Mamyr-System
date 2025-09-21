<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../Config/dbcon.php';
session_start();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Business Partner Sign Up</title>
    <link rel="shortcut icon" href="../Assets/Images/Icon/favicon.png" type="image/x-icon">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../Assets/CSS/bpRegister.css">

    <!-- Bootstrap Link -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>



    <div class="back-icon-container">
        <a href="../index.php">
            <img src="../Assets/Images/Icon/backbtn_black.png" alt="Go back" class="backArrow">

        </a>
    </div>
    <h2 class="title">Business Partner Sign Up </h2>
    <form action="../Function/register.php" method="POST" enctype="multipart/form-data">
        <div class="container" id="basicInfo">
            <div class="row">
                <div class="col" id="repInfoContainer">
                    <h4 class="repInfoLabel">Representative Information</h4>
                    <div class="repInfoFormContainer">
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name"
                            value="<?php echo isset($_SESSION['registerFormData']['firstName']) ? htmlspecialchars(trim($_SESSION['registerFormData']['firstName'])) : ''; ?>"
                            required>
                        <!-- <i class='bx bxs-user-circle'></i> -->

                        <input type="text" class="form-control" id="middleInitial" name="middleInitial"
                            placeholder="M.I. (Optional)"
                            value="<?php echo isset($_SESSION['registerFormData']['middleInitial']) ? htmlspecialchars(trim($_SESSION['registerFormData']['middleInitial'])) : ''; ?>">
                        <!-- <i class='bx bxs-user-circle'></i> -->
                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name"
                            value="<?php echo isset($_SESSION['registerFormData']['lastName']) ? htmlspecialchars(trim($_SESSION['registerFormData']['lastName'])) : ''; ?>"
                            required>
                        <!-- <i class='bx bxs-user-circle'></i> -->
                        <!-- <input type="text" class="form-control" id="email" name="email" placeholder="Email Address"
                            required> -->
                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber"
                            placeholder="Phone Number"
                            value="<?php echo isset($_SESSION['partnerData']['phoneNumber']) ? htmlspecialchars(trim($_SESSION['partnerData']['phoneNumber'])) : ''; ?>"
                            required>
                        <!-- <i class='bx bxs-phone'></i> -->

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
                            data-bs-target="#busTypenModal" id="partnerTypeButton" require>Type of your Business</button>


                        <!-- modal for type of business -->
                        <div class="modal fade" id="busTypenModal" tabindex="-1" aria-labelledby="busTypeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Type of Business</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                                        value="<?= htmlspecialchars($partnerType) ?>"
                                                        <?= (isset($_SESSION['partnerData']) && in_array($partnerType, $_SESSION['partnerData']['partnerType'])) ? 'checked' : '' ?>>
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
                                        <button type="button" class="btn btn-primary" id="selectPartnerBtn" data-bs-dismiss="modal">Okay</button>
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
                            value="<?php echo isset($_SESSION['partnerData']['city']) ? htmlspecialchars(trim($_SESSION['partnerData']['city'])) : ''; ?>">

                        <div class="row1">
                            <input type="text" class="form-control" id="province" name="province" placeholder="Province"
                                value="<?php echo isset($_SESSION['partnerData']['province']) ? htmlspecialchars(trim($_SESSION['partnerData']['province'])) : ''; ?>">

                            <input type="text" class="form-control" id="zip" name="zip" placeholder="Zip Code"
                                value="<?php echo isset($_SESSION['partnerData']['zip']) ? htmlspecialchars(trim($_SESSION['partnerData']['zip'])) : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="col" id="busProofContainer">
                    <h4 class="busProofLabel">Proof of Business</h4>

                    <p class="description">Please provide a link to your Google Drive or social media page and a valid
                        ID as a
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

                        <button class="btn btn-primary w-75" id="nextBtn" onclick="openEmailPass(event)">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" id="emailPassContainer">
            <div class="labelAndArrow">
                <div class="back-icon-container-login">
                    <a href="busPartnerRegister.php?page=basicInfo">
                        <img src="../Assets/Images/Icon/arrow.png" style="height: 4vw" alt="Go back" class="backArrow"
                            id="emailBackArrow">

                    </a>
                </div>
                <h5 class="accountCreationLabel m-6">Create an Account</h5>
            </div>
            <div class="emailPassForm">
                <div class="input-box">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                </div>

                <div class="passwordContainer">
                    <div class="input-box">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                            oninput="validateSignUpForm();" required>
                        <i id="togglePassword1" class='bx bxs-hide'></i>
                    </div>
                    <div class="confirmErrorMsg" id="passwordValidation"></div>
                    <div class="input-box">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            placeholder="Confirm Password" oninput="validateSignUpForm();" required>
                        <input type="hidden" name="userRole" value="4"> <!-- 4 = partnership applicant -->
                        <input type="hidden" name="registerStatus" value="Partner">

                        <i id="togglePassword2" class='bx bxs-hide'></i>
                    </div>
                    <div class="confirmErrorMsg" id="passwordMatch"></div>

                    <div class="bottomPart">
                        <div class="termsContainer">
                            <input type="checkbox" id="terms" name="terms" class="terms-checkbox" value="1"
                                onchange="validateSignUpForm();"> I agree to the
                            <a href="#termsModal" class="termsLink" id="open-modal" style="text-decoration: none;"
                                data-bs-toggle="modal" data-bs-target="#termsModal">Terms
                                and
                                Conditions</a>.
                            </label><br>
                            <div class="confirmErrorMsg text-center" id="termsError"></div>
                        </div>
                        <button class="btn btn-primary w-75 m-auto" id="signUp" name="signUp" disabled>Sign Up</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- modal -->

    <div class="modal fade modal-lg m-auto" id="openModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="instructionLabel">Documents for Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="max-height:400px; overflow-y: auto;">
                    <p class="modalSubtitle">To verify your business or talent, please upload the following documents or
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
                                    social media pages (such as Instagram, Facebook, YouTube, etc.) that showcase your
                                    business or talent.</p>
                                <ul>
                                    <li><strong>Google Drive:</strong> Create a new folder in Google Drive with your
                                        business or performance name. Upload the required documents or media to this
                                        folder.</li>
                                    <li><strong>Valid ID of the Business Owner/Representative:</strong> Upload a clear
                                        photo or scanned copy of a valid government-issued ID using the input box
                                        provided below.</li>
                                    <li><strong>Social Media Links:</strong> If you have active business pages or
                                        performance content on social media platforms, feel free to share the links to
                                        your profiles or posts that demonstrate your business or performance.</li>
                                </ul>
                            </li>

                            <li>
                                <strong>Share the Folder or Social Media Links</strong>
                                <ul>
                                    <li><strong>Google Drive:</strong> Right-click on the folder, select "Share," and
                                        choose "Anyone with the link" with permissions set to "Viewer" or "Editor." Copy
                                        the link to your folder.</li>
                                    <li><strong>Social Media Links:</strong> Simply copy and paste the URLs to your
                                        active business or performance profiles.</li>
                                </ul>
                            </li>

                            <li>
                                <strong>Paste the Link(s)</strong>
                                <p>Once your documents/media or social media links are ready, paste the link(s) on the
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


    <!-- terms and conditions modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Terms and Conditions</h5>

                </div>
                <div class="modal-body">
                    <p class="termsDescription text-center">Welcome to Mamyr Resort and Events Place! By using our
                        Resort Event Management System, you agree to abide by the terms and conditions outlined below.
                        These terms apply to all bookings made for the resort, hotel, and event venues via this
                        platform.
                        <br><strong> Please read these terms carefully before making any bookings.</strong>
                    </p>
                    <h6>1. Booking & Reservation</h6>
                    <ul>
                        <li><strong>Eligibility:</strong> Users must be at least 18 years of age to book any
                            services via the platform. They must also provide a valid ID and show their business
                            credentials to the resort to prove their eligibility.</li>
                        <li><strong>Booking Process:</strong> To make a booking, users must provide accurate details,
                            including full name, contact information, payment details, and any additional requirements
                            (e.g., room preferences, event specifications).</li>
                        <li><strong>Confirmation:</strong> A booking is considered confirmed once you receive an
                            official booking confirmation email or notification. Any reservation made without
                            this confirmation will not be considered valid.</li>
                        <li><strong>Booking Modifications:</strong> You may modify or cancel your booking
                            through the system, provided such changes comply with the cancellation and
                            modification policy.</li>
                    </ul>
                    <h6>2. Payments & Charges</h6>
                    <ul>
                        <li><strong>Pricing:</strong> All pricing for resort accommodations, hotel rooms, and
                            event venues are displayed clearly on the platform. Prices are subject to change
                            based on seasonality, availability, or promotions.</li>
                        <li><strong>Payment Methods:</strong> We only accept certain payment methods, namely
                            GCash and on-site cash payments. Down payments must be made before the time of
                            booking unless otherwise stated.</li>
                        <li><strong>Refunds:</strong> Our business does not provide refunds for down payment
                            upon cancellation. Users are encouraged to ensure that their booking information, as
                            well as their schedules for their desired booking dates are accurately provided to
                            avoid the need for cancellations.</li>
                    </ul>
                    <h6>3. Check-in & Check-out</h6>
                    <ul>
                        <li><strong>Hotel & Resort:</strong> Early check-ins or late check-outs are subject to
                            availability and may incur additional charges.</li>
                        <li><strong>Event Venue:</strong> Event venue access will be granted as per the
                            agreed-upon event time. Additional charges may apply for extended event hours.</li>
                    </ul>
                    <h6>4. Limitation of Liability</h6>
                    <ul>
                        <li><strong>Hotel/Resort Liability:</strong> Our liability for any loss, injury, or
                            damage incurred during a stay or event is limited to the amount paid for the
                            booking. We are not liable for any indirect or consequential damages.</li>
                        <li><strong>Event Liability:</strong> The resort is not responsible for any third-party
                            event organizer’s actions or services. Any complaints regarding event services
                            should be directed to the event organizer.</li>
                    </ul>
                    <h6>5. Privacy & User Data Policy</h6>
                    <p>We respect your privacy and are committed to protecting your personal data in compliance with the
                        Data Privacy Act of 2012 (Republic Act No. 10173) and other relevant Philippine data protection
                        laws. By using our platform, you agree to the collection, storage, and use of your data as
                        outlined below.</p>
                    <ul>
                        <li><strong>Types of Data Collected:</strong>
                            <ul>
                                <li>Personal Information: We collect your name, email address, phone number, and other
                                    personal details you provide during booking.</li>
                                <li>Payment Information: Payment details, such as GCash account numbers and billing
                                    information, are processed securely through third-party payment gateways.</li>
                                <li>Booking Data: We collect details of your bookings, such as accommodation type,
                                    check-in/check-out dates, event preferences, and any additional services requested.
                                </li>
                            </ul>
                        </li>
                        <li><strong>Use of Data:</strong>
                            <ul>
                                <li>We use your personal and booking information to process and manage your
                                    reservations, send booking confirmations, and provide customer support.</li>
                                <li>Payment details are used exclusively for processing payments and are never stored on
                                    our servers.</li>
                                <li>We may use your contact information to send promotional offers, newsletters, and
                                    updates about our services (you can opt-out at any time).</li>
                            </ul>
                        </li>
                        <li><strong>Data Protection:</strong>
                            <ul>
                                <li>We implement security measures to protect your personal and payment information
                                    during transmission and storage.</li>
                                <li>We comply with the Data Privacy Act of 2012 and other applicable laws in the
                                    Philippines to ensure your data is handled with utmost care and confidentiality.
                                </li>
                            </ul>
                        </li>
                        <li><strong>Retention of Data:</strong> We retain your data only for as long as necessary to
                            fulfill the purpose for which it was collected, including legal and accounting obligations.
                            If you wish to delete your data, please contact us directly.</li>
                        <li><strong>Your Rights:</strong>
                            <ul>
                                <li>Access: You have the right to request a copy of your personal data.</li>
                                <li>Rectification: You can request corrections to any inaccuracies in your personal
                                    data.</li>
                                <li>Deletion: You can request the deletion of your personal data, subject to certain
                                    legal exceptions.</li>
                                <li>Opt-Out: You can opt out of marketing communications at any time by following the
                                    unsubscribe instructions in emails or contacting us directly.</li>
                            </ul>
                        </li>
                    </ul>
                    <p>For more information about how we handle your personal data, please refer to our full Privacy
                        Policy available on our website.</p>
                    <h6>6. Modifications to Terms & Conditions</h6>
                    <p>We reserve the right to modify these terms and conditions at any time. Any changes will be
                        effective immediately upon posting on the platform. Users are encouraged to review these terms
                        regularly.</p>
                    <h6>7. Dispute Resolution</h6>
                    <p>Any disputes arising from bookings or the use of our Resort Event Management System shall be
                        resolved through binding arbitration in the jurisdiction of San Ildefonso, Bulacan, Philippines.
                        In the event that arbitration is not possible, disputes will be subject to Philippine laws and
                        resolved in the appropriate court.</p>
                    <h6>8. Governing Law</h6>
                    <p>These terms and conditions shall be governed by and construed in accordance with the laws of the
                        Philippines.</p>
                    <h6>9. Contact Information</h6>
                    <ul>
                        <li>Email: <a href="mailto:mamyresort128@gmail.com">mamyresort128@gmail.com</a></li>
                        <li>Phone: (0998) 962 4697</li>
                        <li>Address: Sitio Colonia Gabihan, San Ildefonso, Bulacan</li>
                    </ul>
                    <h6>10. Business Partner Terms</h6>
                    <ul>
                        <li><strong>Eligibility:</strong> Business Partners must be at least 18 years of age to register
                            and offer services via the platform.</li>
                        <li><strong>Registration:</strong> Business Partners must complete a registration process and
                            provide accurate business details, including the business name, contact information,
                            services offered, and any additional requirements. Once approved, Business Partners will be
                            granted access to manage and offer their services through the system.</li>
                        <li><strong>Bookings & Reservations:</strong>
                            <ul>
                                <li><strong>Customer Interaction:</strong> Business Partners can list their services on
                                    the platform for customers to book. While Business Partners can view the bookings
                                    made for their services, they do not have the ability to approve or reject bookings.
                                </li>
                                <li><strong>Booking Details:</strong> All bookings made through the platform will be
                                    reflected on the Business Partner’s page, and any relevant customer details will be
                                    provided for them to coordinate and prepare for the service being offered.</li>
                                <li><strong>Admin Communication:</strong> The Admin will contact the Business Partner
                                    directly once their services have been booked by a customer. This communication will
                                    include the booking details and any necessary information regarding the event or
                                    service.</li>
                            </ul>
                        </li>
                        <li><strong>Commission & Fees:</strong>
                            <ul>
                                <li><strong>Commission Disclosure:</strong> The commission rate applicable to Business
                                    Partners will be disclosed after the scheduled event between the Admin and the
                                    Business Partner. This will allow for transparent and mutually agreed-upon terms
                                    following the completion of the event.</li>
                                <li><strong>Payment Terms:</strong> After the scheduled event, the Admin will inform the
                                    Business Partner of their commission, and payments will be made according to the
                                    agreed-upon schedule, after deducting the commission fee.</li>
                            </ul>
                        </li>
                        <li><strong>Liability & Responsibilities:</strong>
                            <ul>
                                <li><strong>Service Delivery:</strong> Business Partners are fully responsible for
                                    delivering the services they offer to customers. They must ensure that services are
                                    provided as described, in a timely manner, and meet the standards expected by the
                                    customer.</li>
                                <li><strong>Customer Complaints:</strong> Any complaints or disputes regarding the
                                    services provided by the Business Partner should be resolved directly between the
                                    Business Partner and the customer. The resort is not responsible for the actions or
                                    services of Business Partners.</li>
                                <li><strong>Indemnity:</strong> Business Partners agree to indemnify and hold harmless
                                    Mamyr Resort from any claims, losses, or damages that arise from their services or
                                    the actions of their employees, contractors, or representatives.</li>
                            </ul>
                        </li>
                        <li><strong>Booking Modifications & Cancellations:</strong>
                            <ul>
                                <li>Business Partners may request modifications or cancellations to bookings if
                                    necessary, but such changes will still be subject to the customer’s terms, as well
                                    as Mamyr Resort’s cancellation and modification policy.</li>
                                <li>Business Partners should communicate directly with customers if any changes need to
                                    be made to the booking or service.</li>
                            </ul>
                        </li>
                        <li><strong>Promotions & Advertising:</strong> Business Partners may participate in promotional
                            campaigns, discounts, or special offers on the platform. Any such offers or campaigns will
                            be subject to mutual agreement and will be advertised on the platform.</li>
                        <li><strong>Compliance with Laws:</strong> Business Partners are responsible for ensuring that
                            their business and services comply with all applicable laws and regulations, including those
                            related to safety, licensing, and tax obligations. Mamyr Resort is not responsible for the
                            legality of the services offered by Business Partners.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <div class="declineBtnContainer">
                        <button type="button" class="btn btn-secondary" id="declineBtn" data-bs-dismiss="modal"
                            aria-label="Close">Decline</button>
                    </div>
                    <div class="acceptBtnContainer">
                        <button type="button" class="btn btn-primary" id="acceptBtn">Accept</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- terms and conditions modal -->

    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Password and terms Validation -->
    <script src="../Assets/JS/passwordValidation.js"></script>


    <!-- <script>
        function validateStepOne() {
            const partnerType = document.getElementById('service').value;
            if (!partnerType) {
                alert("Please select a business type.");
                return false;
            }
            return true;
        }
    </script> -->
    <!-- Script for loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderOverlay = document.getElementById('loaderOverlay');
            const form = document.querySelector('form');

            if (form) {
                form.addEventListener('submit', function() {
                    loaderOverlay.style.display = 'flex';
                });
            }
        });

        function hideLoader() {
            const overlay = document.getElementById('loaderOverlay');
            if (overlay) overlay.style.display = 'none';
        }

        // Hide loader on normal load
        window.addEventListener('load', hideLoader);

        // Hide loader on back/forward navigation (from browser cache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });
    </script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- For validation -->
    <script>
        const emailPassContainer = document.getElementById("emailPassContainer");
        const basicInfo = document.getElementById("basicInfo")

        emailPassContainer.style.display = "none";

        function openEmailPass(event) {
            event.preventDefault(); // Prevent form from submitting

            const requiredFields = [
                'firstName', 'lastName', 'phoneNumber',
                'companyName', 'barangay', 'proofLink', 'validID'
            ];

            let allValid = true;

            requiredFields.forEach(id => {
                const field = document.getElementById(id);
                if (!field || !field.value.trim()) {
                    field.classList.add('is-invalid');
                    allValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Validate business type selection
            const checkboxes = document.querySelectorAll('input[name="partnerType[]"]:checked');
            if (checkboxes.length < 1 || checkboxes.length > 2) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'You must select 1 or 2 business types.',
                });
                allValid = false;

                const typeModal = document.getElementById('busTypenModal');
                const modal = new bootstrap.Modal(typeModal);
                modal.show();
            }

            if (!allValid) {
                Swal.fire({
                    title: 'Oops',
                    text: "Please fill out all required fields before continuing.",
                    icon: 'warning'
                });
                return;
            }
            emailPassContainer.style.display = "block";
            basicInfo.style.display = "none";
        }
    </script>

    <!-- For Messages -->
    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get("action");

        if (paramValue === 'emailExist') {
            Swal.fire({
                icon: 'warning',
                title: 'Email Already Exist!',
                text: 'The email address you entered is already registered.'
            })
        }
        if (paramValue === 'exceedImageSize') {
            Swal.fire({
                icon: 'warning',
                title: 'Exceed Image Size!',
                text: 'The image you uploaded exceeds the allowed size limit. Please upload an image smaller than 15 MB.'
            });
        }


        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>



    <!-- Eye icon of password show and hide -->
    <script>
        const passwordField1 = document.getElementById('password');
        const passwordField2 = document.getElementById('confirm_password');
        const togglePassword1 = document.getElementById('togglePassword1');
        const togglePassword2 = document.getElementById('togglePassword2');

        function togglePasswordVisibility(passwordField, toggleIcon) {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bxs-hide');
                toggleIcon.classList.add('bx-show-alt');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bx-show-alt');
                toggleIcon.classList.add('bxs-hide');
            }
        }

        togglePassword1.addEventListener('click', () => {
            togglePasswordVisibility(passwordField1, togglePassword1);
        });

        togglePassword2.addEventListener('click', () => {
            togglePasswordVisibility(passwordField2, togglePassword2);
        });
    </script>
</body>

</html>