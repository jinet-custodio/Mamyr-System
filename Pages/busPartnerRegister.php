<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../Config/dbcon.php';
session_start();
//for setting image paths in 'include' statements
$baseURL = '..';

$sectionName = 'TermsAndConditions';
$colTitle = "busPartnerTerms";

$getContent = $conn->prepare("SELECT title, content FROM websitecontent WHERE sectionName = ? AND title = ?");
$getContent->bind_param("ss", $sectionName, $colTitle);
$getContent->execute();
$contentResult = $getContent->get_result();

$fullText = "";
while ($row = $contentResult->fetch_assoc()) {
    $fullText .= $row['content'] . "\n\n"; // Keep spacing between sections
}
$fullText = trim($fullText);
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
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="back-icon-container">
        <a href="beOurPartnerNew.php">
            <i class="fa-solid fa-arrow-left backArrow" style="color: #f9f9f9ff;"></i>
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


                        <div class="phone-container">
                            <input type="text" name="phoneNumber" id="phoneNumber" pattern="^(?:\+63|0)9\d{9}$"
                                title="e.g., +639123456789 or 09123456789"
                                value="<?php echo isset($_SESSION['partnerData']['phoneNumber']) ? htmlspecialchars(trim($_SESSION['partnerData']['phoneNumber']) ?? '') : ''; ?>"
                                class="form-control" placeholder="Phone Number" required>
                            <div id="tooltip-phone" class="custom-tooltip">Please input numbers only</div>
                        </div>

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
                            data-bs-target="#busTypenModal" id="partnerTypeButton" require>Type of your
                            Business</button>
                        <div id="selectedBusinessTypes" class="mt-2 text-black"></div>

                        <!-- modal for type of business -->
                        <div class="modal fade" id="busTypenModal" tabindex="-1" aria-labelledby="busTypeModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Type of Business</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="busTypeBody">
                                            <?php
                                            $serviceType = $conn->prepare("SELECT * FROM partnershiptype");
                                            $serviceType->execute();
                                            $serviceTypeResult = $serviceType->get_result();
                                            if ($serviceTypeResult->num_rows > 0) {
                                                while ($serviceTypes = $serviceTypeResult->fetch_assoc()) {
                                                    $partnerType = $serviceTypes['partnerTypeID'];
                                                    $partnerTypeDescription = htmlspecialchars($serviceTypes['partnerTypeDescription']);
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
                                                        class="bi bi-x"></i>
                                                    Remove field</button>
                                            </div>

                                        </div>
                                        <?php else: ?>
                                        <div class="form-group other-container" id="otherInput" style="display: none;">
                                            <label for="otherText" class="other-label">Please specify:</label>
                                            <input type="text" class="form-control" id="otherText"
                                                name="other-partner-type[]">

                                            <div class="button">
                                                <!-- //TODO: Pakipalitan na lang -->
                                                <button type="button" class="btn btn-success" id="addInputField"><i
                                                        class="bi bi-plus"></i> Add
                                                    other</button>

                                                <button type="button" class="btn btn-danger" id="removeInputField"><i
                                                        class="bi bi-x"></i>
                                                    Remove field</button>
                                            </div>

                                        </div>

                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" id="selectedPartnerTypes"
                                            data-bs-dismiss="modal">Select</button>
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
                            <div class="zip-code">
                                <input type="text" class="form-control" id="zip" name="zip" placeholder="Zip Code"
                                    pattern="^\d{4}$"
                                    value="<?php echo isset($_SESSION['partnerData']['zip']) ? htmlspecialchars(trim($_SESSION['partnerData']['zip'])) : ''; ?>">
                                <div id="tooltip-zip" class="custom-tooltip-zip">Please input numbers only</div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col" id="busProofContainer">
                    <h4 class="busProofLabel">Proof of Business</h4>

                    <p class="description">Please provide a link to your Google Drive or social media page and a
                        valid
                        ID as a proof of your business</p>

                    <div class="busProofFormContainer">
                        <input type="text" class="form-control" id="proofLink" name="proofLink"
                            placeholder="Paste the link here"
                            value="<?php echo isset($_SESSION['partnerData']['proofLink']) ? htmlspecialchars(trim($_SESSION['partnerData']['proofLink'])) : ''; ?>"
                            required>

                        <a href="#moreDetailsModal" class="moreDetails" data-bs-toggle="modal"
                            data-bs-target="#documentModal">More Details</a>



                        <h6 class="label">Upload a Valid ID</h6>
                        <?php if (isset($_SESSION['partnerData']['imageName'])) { ?>
                        <input type="text" class="form-control validIDFIle" id="validID" name="validID"
                            value="<?php echo isset($_SESSION['partnerData']['imageName']) ? htmlspecialchars(trim($_SESSION['partnerData']['imageName'])) : ''; ?>">
                        <?php } else { ?>
                        <input type="file" class="form-control validIDFIle" id="validID" name="validID">
                        <?php } ?>
                        <button type="button" class="btn btn-primary w-75" id="nextBtn"
                            onclick="openEmailPass(event)">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" id="emailPassContainer">
            <div class="labelAndArrow">
                <div class="back-icon-container-login">
                    <div class="back-icon-container-login">
                        <button type="button" class="backArrowBtn" id="emailBackArrow">
                            <i class="fa-solid fa-arrow-left backArrow" style="color: #121212;"></i>
                        </button>
                    </div>

                </div>
                <h5 class="accountCreationLabel m-6">Create an Account</h5>
            </div>
            <div class="emailPassForm">
                <div class="input-box">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                        value="<?php echo isset($_SESSION['partnerData']['email']) ? htmlspecialchars(trim($_SESSION['partnerData']['email'])) : ''; ?>"
                        required>
                    <i class='bx bxs-envelope'></i>
                </div>

                <div class="passwordContainer">
                    <div class="input-box">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                            oninput="validateSignUpForm();" required>
                        <i id="togglePassword1" class='bx bxs-hide'></i>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" id="password-strength" aria-valuemin="0"
                            aria-valuemax="100"></div>
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
                            <input type="checkbox" id="terms-condition" name="terms" class="terms-checkbox" value="1"
                                onchange="validateSignUpForm();"> I agree to the
                            <a href="#termsModal" class="termsLink" id="open-modal" style="text-decoration: none;"
                                data-bs-toggle="modal" data-bs-target="#termsModal">Terms
                                and
                                Conditions</a>.
                            </label><br>
                            <div class="confirmErrorMsg text-center" id="termsError"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-75 m-auto" id="signUp" name="signUp"
                            onclick="isValid(event);">Sign Up</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- modal -->

    <div class="modal fade modal-lg m-auto" id="documentModal" tabindex="-1" role="dialog"
        aria-labelledby="documentModalLabel" aria-hidden="true">

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
                    <div class="declineBtnContainer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                            aria-label="Close">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- terms and conditions modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Terms and Conditions</h5>

                </div>
                <div class="modal-body">
                    <?= nl2br(htmlspecialchars($fullText)) ?>
                </div>
                <div class="modal-footer">
                    <div class="declineBtnContainer">
                        <button type="button" class="btn btn-secondary" id="declineTermsBtn"
                            onclick="declineTerms()">Decline</button>
                    </div>
                    <div class="acceptBtnContainer">
                        <button type="button" class="btn btn-primary" id="acceptTermsBtn"
                            onclick="AcceptTerms()">Accept</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- terms and conditions modal -->

    <?php include '../Pages/Customer/loader.php'; ?>

    <!-- Bootstrap JS -->
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>

    <!-- Password and terms Validation -->
    <script src="../Assets/JS/passwordValidation.js"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- For validation -->
    <script>
    const emailPassContainer = document.getElementById("emailPassContainer");
    const basicInfo = document.getElementById("basicInfo")

    emailPassContainer.style.display = "none";

    function openEmailPass(event) {
        event.preventDefault();

        const requiredFields = [
            'firstName', 'lastName', 'phoneNumber',
            'companyName', 'barangay', 'proofLink', 'validID', 'province', 'city'
        ];

        let allValid = true;
        let partnerType = true;
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
            partnerType = false;
        }

        if (!allValid) {
            Swal.fire({
                title: 'Oops',
                text: "Please fill out all required fields before continuing.",
                icon: 'warning'
            });
            return;
        }

        if (!partnerType) {
            Swal.fire({
                title: 'Oops',
                text: "You must select 1 or 2 types.",
                icon: 'warning'
            });
            return;
        }
        emailPassContainer.style.display = "block";
        basicInfo.style.display = "none";
    }

    document.getElementById('selectedPartnerTypes').addEventListener('click', function() {
        // Get all checked checkboxes
        const selectedCheckboxes = document.querySelectorAll('input[name="partnerType[]"]:checked');
        const displayDiv = document.getElementById('selectedBusinessTypes');

        // Clear previous content
        displayDiv.innerHTML = '';

        if (selectedCheckboxes.length === 0) {
            displayDiv.innerHTML = '<em>No business type selected! Required!</em>';
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
            displayDiv.innerHTML = '<em>No business type selected! Required!</em>';
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


    document.getElementById('emailBackArrow').addEventListener('click', function(e) {
        e.preventDefault();
        emailPassContainer.style.display = "none";
        basicInfo.style.display = "block";
    });
    </script>

    <!-- For Messages -->
    <script>
    const params = new URLSearchParams(window.location.search);
    const paramValue = params.get("action");

    if (paramValue === 'emailExist') {
        Swal.fire({
            icon: 'warning',
            title: 'Email Already Exist!',
            text: 'The email address you entered is already registered. Please use a different email or log in if you already have an account.',
            confirmButtonText: 'Okay'
        }).then(() => {
            emailPassContainer.style.display = "block";
            basicInfo.style.display = "none";

            document.getElementById('email').style.border = '1px solid red';
        });
    } else if (paramValue === 'extError') {
        Swal.fire({
            title: 'Oops',
            text: `Invalid file type. Please upload JPG, JPEG, WEBP or PNG.`,
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

    emailPassContainer.addEventListener('input', () => {
        emailPassContainer.style.border = '1px solid rgb(223, 226, 230)';
    });

    if (paramValue) {
        const url = new URL(window.location);
        url.search = '';
        history.replaceState({}, document.title, url.toString());
    }
    </script>

    <!-- For password — weak, medium, strong -->
    <script>
    document.getElementById('password').addEventListener('input', function() {
        const password = document.getElementById("password").value;
        const weakPattern = /^.{0,5}$/;
        const mediumPattern = /^(?=.*[A-Za-z])(?=.*\d).{6,}$/;
        const strongPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
        const passwordBar = document.getElementById("password-strength");
        // console.log(password);

        passwordBar.className = "progress-bar";
        let color = "";
        let number = "";
        let strength = 'too  weak';
        if (strongPattern.test(password)) {
            color = "bg-success";
            number = "100";
            strength = 'strong';
        } else if (mediumPattern.test(password)) {
            color = "bg-warning";
            number = "75";
            strength = 'moderate';
        } else if (weakPattern.test(password)) {
            color = "bg-danger";
            number = "50";
            strength = 'weak';
        } else {
            color = "bg-danger";
            number = "25";
            strength = 'too weak';
        }

        // console.log(color);
        // console.log(number);

        passwordBar.classList.add(color, `w-${number}`);
        passwordBar.setAttribute("aria-valuenow", number);
        passwordBar.textContent = strength;
    });
    </script>

    <!-- For numbers input only -->
    <script>
    const input = document.getElementById('phoneNumber');
    const tooltipPhone = document.getElementById('tooltip-phone');
    const zip = document.getElementById('zip');
    const tooltipZip = document.getElementById('tooltip-zip');

    input.addEventListener('keydown', function(e) {
        const allowedKeys = ['Backspace', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Delete'];

        if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key)) {
            e.preventDefault();

            tooltipPhone.classList.add('show');
            clearTimeout(tooltipPhone.hideTimeout);
            tooltipPhone.hideTimeout = setTimeout(() => {
                tooltipPhone.classList.remove('show');
            }, 2000);
        }
    });

    zip.addEventListener('keydown', function(e) {
        const allowedKeys = ['Backspace', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Delete'];

        if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key)) {
            e.preventDefault();

            tooltipZip.classList.add('show');
            clearTimeout(tooltipZip.hideTimeout);
            tooltipZip.hideTimeout = setTimeout(() => {
                tooltipZip.classList.remove('show');
            }, 2000);
        }
    });
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