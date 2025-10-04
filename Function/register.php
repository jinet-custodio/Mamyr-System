<?php

require '../Config/dbcon.php';
session_start();
$env = parse_ini_file(__DIR__ . '/../.env');
require '../vendor/autoload.php';

require_once 'emailSenderFunction.php';
require_once 'Helpers/userFunctions.php';

date_default_timezone_set('Asia/Manila'); //Set default time zone 

$imageName = '';
// For sign up
if (isset($_POST['signUp'])) {
    $terms = isset($_POST['terms']); //isset lang to kasi true or false lang naman need 
    $userRole = isset($_POST['userRole']) ? intval($_POST['userRole']) : 1;
    $registerStatus = mysqli_real_escape_string($conn, $_POST['registerStatus']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);

    //If the user is directly signing up as business partner, their personal address will not be provided
    if ($registerStatus === "Partner") {
        $userAddress = "Personal address not provided";

        //For validID

        $partnerFilePath = __DIR__ . '../../Assets/Images/BusinessPartnerIDs/';

        if (!is_dir($partnerFilePath)) {
            mkdir($partnerFilePath, 0755, true);
        }
        $imageMaxSize = 10 * 1024 * 1024; //10mb
        if (isset($_FILES['validID']) && $_FILES['validID']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['validID']['size'] <= $imageMaxSize) {
                $filePath = $_FILES['validID']['tmp_name'];
                $fileName = $_FILES['validID']['name'];
                $imageName = $firstName . '_' . $fileName;
                $image = $partnerFilePath . $imageName;
                move_uploaded_file($filePath, $image);
            } else {
                $_SESSION['registerFormData'] = $_POST;
                $_SESSION['partnerData'] = $_POST;
                error_log(print_r($_POST, true));
                header('Location: ../../../Pages/busPartnerRegister.php?action=exceedImageSize');
            }
        }
    } else {
        $userAddress = mysqli_real_escape_string($conn, $_POST['userAddress']);
        $imageName = null;
    }

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $extensions = ['gmail.com', 'yahoo.com', 'outlook.com', 'protonmail.com', 'icloud.com'];



    $partnerData = [
        'imageName' => $imageName,
        'partnerType' => $_POST['partnerType'] ?? [],
        'companyName'    => trim($_POST['companyName']),
        'phoneNumber'    => trim($_POST['phoneNumber']),
        'barangay'       => trim($_POST['barangay']),
        'streetAddress'  => trim($_POST['streetAddress']),
        'city'           => trim($_POST['city']),
        'province'       => trim($_POST['province']),
        'zip'            => trim($_POST['zip']),
        'partnerAddress' => trim($_POST['barangay']) . ' ' .
            trim($_POST['streetAddress']) . ', ' .
            trim($_POST['city']) . ', ' .
            trim($_POST['province']) . ', ' .
            trim($_POST['zip']),
        'proofLink'      => trim($_POST['proofLink'])
    ];


    $defaultImage = '../Assets/Images/defaultProfile.png';
    if (file_exists($defaultImage)) {
        $userProfile = file_get_contents($defaultImage);
    } else {
        echo 'not found';
    }


    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $emailParts = explode('@', $email);
        $domain = strtolower(end($emailParts));
        if (in_array($domain, $extensions)) {

            $checkEmail = $conn->prepare("SELECT email FROM user WHERE email = ? LIMIT 1");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $checkEmailResult = $checkEmail->get_result();
            $_SESSION['registerFormData'] = $_POST;  // store the data in session that user enter
            if ($checkEmailResult->num_rows > 0) {
                //Unchecked - partnerside
                if ($registerStatus == "Partner") {
                    $_SESSION['partnerData'] = $partnerData;
                    header("Location: ../Pages/busPartnerRegister.php?action=emailExist");
                    exit;
                } else {
                    header("Location: ../Pages/register.php?page=register&action=emailExist");
                    exit;
                }
            } elseif ($password == $confirm_password) {
                $hashpassword = password_hash($password, PASSWORD_DEFAULT);
                $otp = generateOTP(6);
                $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); //Add a 5mins to the time of creation

                $conn->begin_transaction();
                try {
                    $insertUser = $conn->prepare("INSERT INTO user(userProfile, firstName, middleInitial, lastName, email, userAddress, password, userRole, userOTP, OTP_expiration_at)  VALUES(?,?,?,?,?,?,?,?,?,?)");
                    $dummyBlob = null;
                    $insertUser->bind_param("bssssssiss", $dummyBlob,  $firstName, $middleInitial, $lastName, $email, $userAddress, $hashpassword, $userRole, $otp, $OTP_expiration_at);
                    $insertUser->send_long_data(0, $userProfile);
                    if ($registerStatus == "Partner") {
                        // error_log("PartnerTypeID :" .  $_POST['partnerType']);
                        $_SESSION['partnerData'] = $partnerData;
                    }

                    if ($insertUser->execute()) {
                        $subject = 'Mamyr Resort and Events Place Account Verification';
                        $message = '<body style="font-family: Arial, sans-serif;         background-color: #f4f4f4; padding: 20px; margin: 0;">

                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <tr>
                                    <td style="display: flex; justify-content:center; align-items:center; gap: 15px; margin: 10px 0 10px 0"><img
                                            src="../Assets/Images/MamyrLogo.png" alt="Mamyr Logo" style="height:29.76px; width:130.56px; ">

                                        <h4>Mamyr Resort and
                                            Events Place</h4>
                                    </td>
                                </tr>

                                <tr style="background-color:#365CCE;">
                                    <td style="text-align:center; ">
                                        <h4 style="font-family:Poppins Light; color:#ffffff; font-size: 18px;  margin-top: 25px">THANKS FOR
                                            SIGNING UP!</h4>
                                        <h2 style="font-family:Poppins Light; color:#ffffff; margin-top: -20px">Verify Your
                                            Email Address </h2>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <h2 style="color: #333333; margin-top: 0;">Your OTP Code for Account Verification</h2>
                                        <p style="font-size: 16px; margin: 20px 0 10px;">Hello,</p>
                                        <p style="font-size: 16px; margin: 10px 0;">Please use the following One Time Password(OTP) to verify
                                            your account:
                                        </p>

                                        <div style="text-align: center; margin: 30px 0;">
                                            <span
                                                style="display: inline-block; color: #0c0605; font-size: 24px; padding: 15px 30px; border-radius: 6px; font-weight: bold;">
                                                ' . $otp . '
                                            </span>
                                        </div>
                                        <p style="font-size: 16px; margin: 10px 0;">This OTP is valid for <strong>5 minutes</strong>. Do not
                                            share it with anyone. If you did not request this code, please ignore this email.
                                        </p>
                                        <br>
                                        <p style="font-size: 16px;">Thank you,</p>
                                        <p style="font-size: 16px; font-weight: bold;">Mamyr Resort and Events Place.</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
                                ';
                        if (sendEmail($email, $firstName, $subject, $message, $env)) {
                            $conn->commit();
                            $_SESSION['email'] = $email;
                            $_SESSION['action'] = ($registerStatus === 'Partner') ? 'Partner' : 'Register';
                            unset($_SESSION['registerFormData']);
                            header("Location: ../Pages/verify_email.php");
                            exit;
                        } else {
                            $conn->rollback();
                            exit;
                        }
                    } else {
                        $conn->rollback();
                        die("Error inserting data: " . mysqli_error($conn));
                    }
                } catch (Exception $e) {
                    $_SESSION['registerError'] = 'Registration failed. An unexpected error occurred while processing your request. Please try again.';
                    $conn->rollback();
                    error_log("Registration error: " . $e->getMessage());
                    header("Location: ../Pages/register.php?page=register");
                    exit;
                } finally {
                    if ($insertUser) {
                        $insertUser->close();
                    }
                }
            } else {
                $_SESSION['registerError'] = 'Password doesn`t match';
                header("Location: ../Pages/register.php?page=register");
                exit;
            }
            $checkEmailResult->free();
            $checkEmail->close();
        } else {
            $_SESSION['registerError'] = 'Invalid email format';
            header("Location: ../Pages/register.php?page=register");
            exit;
        }
    } else {
        $_SESSION['registerError'] = 'Invalid email format';
        header("Location: ../Pages/register.php?page=register");
        exit;
    }
}  //For Login
elseif (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['login_email']);
    $password = mysqli_real_escape_string($conn, $_POST['login_password']);

    $loginQuery = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $loginQuery->bind_param("s", $email);
    $loginQuery->execute();
    $loginResult = $loginQuery->get_result();
    $_SESSION['loginFormData']['email'] = $email;

    if ($loginResult->num_rows > 0) {
        $data = $loginResult->fetch_assoc();
        $storedPassword = $data['password'];
        $storedRoleID = $data['userRole'];
        $storedStatusID = $data['userStatusID'];

        $userRole = getUserRole($conn, $storedRoleID);
        $userStatus = getUserStatus($conn, $storedStatusID);

        $roleName = $userRole['userTypeName'];
        $roleID = $userRole['userTypeID'];
        $statusName = $userStatus['userStatusName'];
        $statusID = $userStatus['userStatusID'];

        if (password_verify($password, $storedPassword)) {
            //The user must be verified else he can`t login
            if ($statusName === 'Verified') {
                unset($_SESSION['loginFormData']);
                $_SESSION['userID'] = $data['userID'];
                $_SESSION['userRole'] = $roleID;
                switch ($roleName) {
                    case 'Customer':
                        header("Location: ../Pages/Customer/dashboard.php?action=successLogin");
                        break;
                    case 'Partner Request':
                        header("Location: ../Pages/Customer/dashboard.php?action=successLogin");
                        break;
                    case 'Partner':
                        header("Location: ../Pages/BusinessPartner/bpDashboard.php?action=successLogin");
                        break;
                    case 'Admin':
                        header("Location: ../Pages/Admin/adminDashboard.php?action=successLogin");
                        break;
                    default:
                        header("Location: ../Pages/register.php?page=login&action=unauthorized");
                        break;
                }
                exit;
            } else {
                header("Location: ../Pages/register.php?page=login&action=notVerified");
                exit;
            }
        } else {
            $_SESSION['loginError'] = 'Invalid email or password. Please try again.';
            header("Location: ../Pages/register.php?page=login");
            exit;
        }
    } else {
        $_SESSION['loginError'] = 'Invalid email or password. Please try again.';
        header("Location: ../Pages/register.php?page=login");
        exit;
    }
} else {
    $_SESSION['error'] = 'Form not submitted';
    header("Location: ../Pages/register.php");
    exit;
}
