<?php
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <h2 class="title">Business Partner Sign Up </h2>

    <div class="back-icon-container">
        <a href="../index.php">
            <img src="../Assets/Images/Icon/backbtn_black.png" alt="Go back" class="backArrow" style="width: 3vw;">

        </a>
    </div>
    <form action="../Function/register.php" method="POST">
        <div class="container" id="basicInfo">
            <div class="row">
                <div class="col" id="repInfoContainer">
                    <h4 class="repInfoLabel">Representative Information</h4>
                    <div class="repInfoFormContainer">
                        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name"
                            value="<?php echo isset($_SESSION['formData']['firstName']) ? htmlspecialchars(trim($_SESSION['formData']['firstName'])) : ''; ?>"
                            required>
                        <!-- <i class='bx bxs-user-circle'></i> -->

                        <input type="text" class="form-control" id="middleInitial" name="middleInitial"
                            placeholder="M.I. (Optional)"
                            value="<?php echo isset($_SESSION['formData']['middleInitial']) ? htmlspecialchars(trim($_SESSION['formData']['middleInitial'])) : ''; ?>">
                        <!-- <i class='bx bxs-user-circle'></i> -->
                        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name"
                            value="<?php echo isset($_SESSION['formData']['lastName']) ? htmlspecialchars(trim($_SESSION['formData']['lastName'])) : ''; ?>"
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
                        <input type="text" class="form-control" id="companyName" name="companyName" placeholder="Business Name" value="<?php echo isset($_SESSION['partnerData']['companyName']) ? htmlspecialchars(trim($_SESSION['partnerData']['companyName'])) : ''; ?>">

                        <select id="service" name="partnerType" class="form-select primary">
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

                        <input type="text" class="form-control" id="city" name="city" placeholder="Town/City" value="<?php echo isset($_SESSION['partnerData']['city']) ? htmlspecialchars(trim($_SESSION['partnerData']['city'])) : ''; ?>">

                        <div class="row1">
                            <input type="text" class="form-control" id="province" name="province"
                                placeholder="Province" value="<?php echo isset($_SESSION['partnerData']['province']) ? htmlspecialchars(trim($_SESSION['partnerData']['province'])) : ''; ?>">

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

                        <button class="btn btn-primary w-75" id="nextBtn" onclick="openEmailPass()">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" id="emailPassContainer">

            <div class="back-icon-container-login">
                <a href="busPartnerRegister.php?page=basicInfo">
                    <img src="../Assets/Images/Icon/arrow.png" style="height: 4vw" alt="Go back" class="backArrow">

                </a>
            </div>
            <h5 class="accountCreationLabel m-6">Create an Account</h5>

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
                    <input type="hidden" name="userRole" value="2"> <!-- 2 = partner -->
                    <input type="hidden" name="registerStatus" value="partner">

                    <i id="togglePassword2" class='bx bxs-hide'></i>
                </div>
                <div class="confirmErrorMsg" id="passwordMatch"></div>

                <div class="bottomPart">
                    <label for="terms">
                        <input type="checkbox" id="terms" name="terms" class="terms-checkbox" value="1"
                            onchange="validateSignUpForm()"> I agree to the
                        <a href="#" id="open-modal" style="text-decoration: none;">Terms and Conditions</a>.
                    </label><br>
                    <div class="confirmErrorMsg text-center" id="termsError"></div>
                    <button class="btn btn-primary w-75 m-auto" id="signUp" name="signUp" disabled>Sign Up</button>
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
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
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

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const emailPassContainer = document.getElementById("emailPassContainer");
        const basicInfo = document.getElementById("basicInfo")

        emailPassContainer.style.display = "none";

        function openEmailPass() {

            // Get required inputs
            const requiredFields = [
                'firstName', 'lastName', 'phoneNumber',
                'companyName', 'service', 'barangay', 'proofLink'
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

            if (!allValid) {
                Swal.fire({
                    title: 'Oops',
                    text: "Please fill out all required fields before continuing.",
                    icon: 'warning'
                });
                return;
            }

            if (emailPassContainer.style.display == "none") {
                emailPassContainer.style.display = "block";
                basicInfo.style.display = "none"

            } else {
                emailPassContainer.style.display = "block"
            }
        }
    </script>


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