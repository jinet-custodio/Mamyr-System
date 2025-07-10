<?php

require '../Config/dbcon.php';
session_start();
$env = parse_ini_file(__DIR__ . '/../.env');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPmailer;
use PHPMailer\PHPMailer\Exception;


// require '../phpmailer/src/PHPMailer.php';
// require '../phpmailer/src/Exception.php';
// require '../phpmailer/src/SMTP.php';

// function assembleFullAddress($post)
// {
//     $street = $post['barangay'] ?? '';
//     $streetAddress = $post['streetAddress'] ?? '';
//     $city = $post['city'] ?? '';
//     $province = $post['province'] ?? '';
//     $zip = $post['zip'] ?? '';

//     return trim("$street $streetAddress, $city, $province, $zip");
// }



if (isset($_POST['signUp'])) {
    $terms = mysqli_real_escape_string($conn, $_POST['terms']);
    echo $terms;
    $userRole = isset($_POST['userRole']) ? intval($_POST['userRole']) : 1;
    $registerStatus = $_POST['registerStatus'];
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    //If the user is directly signing up as business partner, their personal address will not be provided
    if ($registerStatus == "partner") {
        $userAddress = "Personal address not provided";
    } else {
        $userAddress = mysqli_real_escape_string($conn, $_POST['userAddress']);
    }
    $partnerType = mysqli_real_escape_string($conn, $_POST['partnerType']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $extensions = ['@gmail.com', '@yahoo.com', '@outlook.com', '@protonmail.com', '@icloud.com'];


    // Set the default image when a user registered
    $defaultImage = '../Assets/Images/defaultProfile.png';
    if (file_exists($defaultImage)) {
        $imageData = file_get_contents('../Assets/Images/defaultProfile.png');
        $imageData = mysqli_real_escape_string($conn, $imageData);
    } else {
        $imageData = NULL;
    }


    if (filter_var($email, FILTER_VALIDATE_EMAIL) && array_filter($extensions, fn($ext) => str_ends_with($email, $ext))) {

        $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmailResult = $checkEmail->get_result();
        $_SESSION['formData'] = $_POST;  // store the data in session that user enter
        if ($checkEmailResult->num_rows > 0) {
            $_SESSION['formData'] = $_POST;

            if ($registerStatus == "partner") {
                $_SESSION['partnerData'] = [
                    'companyName'    => $_POST['companyName'],
                    'partnerType'    => $_POST['partnerType'],
                    'phoneNumber'    => $_POST['phoneNumber'],
                    'barangay'       => $_POST['barangay'],
                    'streetAddress'  => $_POST['streetAddress'],
                    'city'           => $_POST['city'],
                    'province'       => $_POST['province'],
                    'zip'            => $_POST['zip'],
                    'proofLink'      => $_POST['proofLink']
                ];
                header("Location: ../Pages/busPartnerRegister.php?action=emailExist");
                exit;
            } else {
                header("Location: ../Pages/register.php?page=register&action=emailExist");
                exit;
            }
        } elseif ($password == $confirm_password) {
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            date_default_timezone_set('Asia/Manila'); //Set default time zone 
            $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); //Add a 5mins to the time of creation
            unset($_SESSION['formData']);

            $null = NULL;
            $insertUser = $conn->prepare("INSERT INTO users(userProfile, firstName, middleInitial, lastName, email, userAddress, password, userOTP, OTP_expiration_at) 
            VALUES(?,?,?,?,?,?,?,?,?)");
            $insertUser->bind_param("sssssssss", $null, $firstName, $middleInitial, $lastName, $email, $userAddress, $hashpassword, $otp, $OTP_expiration_at);
            $insertUser->send_long_data(0, $imageData);

            if ($registerStatus == "partner") {
                // Save business partner info into session temporarily
                $_SESSION['partnerData'] = [
                    'companyName'    => $_POST['companyName'],
                    'partnerType'    => $_POST['partnerType'],
                    'phoneNumber'    => $_POST['phoneNumber'],
                    'barangay'       => $_POST['barangay'],
                    'streetAddress'       => $_POST['streetAddress'],
                    'city'           => $_POST['city'],
                    'province'       => $_POST['province'],
                    'zip'            => $_POST['zip'],
                    'partnerAddress' =>
                    $_POST['barangay'] . ' ' .
                        $_POST['streetAddress'] . ', ' .
                        $_POST['city'] . ', ' .
                        $_POST['province'] . ', ' .
                        $_POST['zip'],
                    'proofLink'      => $_POST['proofLink']
                ];
            }

            if ($insertUser->execute()) {
                $mail = new PHPmailer(true);
                try {
                    $_SESSION['email'] = $email;
                    $_SESSION['action'] = ($registerStatus === 'partner') ? 'partner' : 'register';
                    $mail->isSMTP();
                    $mail->Host       =  $env['SMTP_HOST'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $env['SMTP_USER'];
                    $mail->Password   =  $env['SMTP_PASS'];
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       =  $env['SMTP_PORT'];

                    $mail->setFrom($env['SMTP_USER'], 'Mamyr Resort and Event Place');
                    $mail->addAddress($email, $firstName);

                    $message = '<body style="font-family: Arial, sans-serif;                  background-color: #f4f4f4; padding: 20px; margin: 0;">
                                <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                                    <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <h2 style="color: #333333; margin-top: 0;">Your OTP Code for Account Verification</h2>
                                        <p style="font-size: 16px; margin: 20px 0 10px;">Hello,</p>
                                        <p style="font-size: 16px; margin: 10px 0;">Your One-Time Password (OTP) for account verification is:</p>

                                        <div style="text-align: center; margin: 30px 0;">
                                        <span style="display: inline-block; background-color: #00e1ff; color: #0c0605; font-size: 24px; padding: 15px 30px; border-radius: 6px; font-weight: bold;">
                                           ' . $otp . '
                                        </span>
                                        </div>
                                        <p style="font-size: 16px; margin: 10px 0;">This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
                                        <p style="font-size: 16px; margin: 10px 0;">If you did not request this code, please ignore this email.</p>
                                        <br>
                                        <p style="font-size: 16px;">Thank you,</p>
                                        <p style="font-size: 16px; font-weight: bold;">Mamyr.</p>
                                    </td>
                                    </tr>
                                </table>
                                </body>
                                ';
                    $mail->isHTML(true);
                    $mail->Subject = 'Account Verification';
                    $mail->Body    = $message;
                    // $mail->AltBody = 'Body in plain text for non-HTML mail clients';
                    if (!$mail->send()) {
                        // $_SESSION['error'] = 'Failed to send OTP. Try again.';
                        header("Location: ../Pages/register.php?page=register&action=OTPFailed");
                        exit;
                    } else {
                        header("Location: ../Pages/verify_email.php");
                        exit;
                    }
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                die("Error inserting data: " . mysqli_error($conn));
            }
        } else {
            $_SESSION['password'] = 'Password doesn`t match';
            header("Location: ../Pages/register.php?page=register");
            exit;
        }
    } else {
        $_SESSION['email-message'] =  $email . 'Invalid email format';
        header("Location: ../Pages/register.php?page=register&email = $email");
        exit;
    }
} elseif (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['login_email']);
    $password = mysqli_real_escape_string($conn, $_POST['login_password']);

    $loginQuery = $conn->prepare("SELECT u.*, 
    ut.typeName AS roleName, ut.userTypeID AS roleID,
    us.userStatusID AS statusID, us.statusName FROM users u
    LEFT JOIN userTypes ut ON u.userRole = ut.userTypeID
    LEFT JOIN userStatuses us ON u.userStatusID = us.userStatusID 
    WHERE email = ?");
    $loginQuery->bind_param("s", $email);
    $loginQuery->execute();
    $loginResult = $loginQuery->get_result();
    $_SESSION['formData']['email'] = $email;
    if ($loginResult->num_rows > 0) {
        $data = $loginResult->fetch_assoc();
        $storedPassword = $data['password'];
        $roleName = $data['roleName'];
        $statusName = $data['statusName'];
        $userRole = $data['userRole'];
        $userStatusID = $data['userStatusID'];
        if (password_verify($password, $storedPassword)) {

            //Get the corresponding ID depend on the role
            if ($roleName === 'Admin') {
                $admin = $data['roleID'];
            } elseif ($roleName === 'Customer') {
                $customer = $data['roleID'];
            } elseif ($roleName === 'Partner') {
                $partner = $data['roleID'];
            }

            //Get the corresponding ID depend on the status
            if ($statusName === 'Pending') {
                $pending = $data['statusID'];
            } elseif ($statusName === 'Verified') {
                $verified = $data['statusID'];
            } elseif ($statusName === 'Not Verified') {
                $notVerified = $data['statusID'];
            } elseif ($statusName === 'Deleted') {
                $deleted = $data['statusID'];
            }

            if ($userStatusID == $verified) { //The user must be verified
                if ($userRole == $customer) { //Customer
                    unset($_SESSION['formData']);
                    $_SESSION['userID'] = $data['userID'];
                    $_SESSION['userRole'] = $userRole;
                    header("Location: ../Pages/Customer/dashboard.php?action=successLogin");
                    exit;
                } elseif ($userRole == $partner) { //Partner
                    unset($_SESSION['formData']);
                    $_SESSION['userID'] = $data['userID'];
                    $_SESSION['userRole'] = $userRole;
                    header("Location: ../Pages/Customer/dashboard.php?action=successLogin");
                    exit;
                } elseif ($userRole == $admin) { //Admin
                    unset($_SESSION['formData']);
                    $_SESSION['userID'] = $data['userID'];
                    $_SESSION['userRole'] = $userRole;
                    header("Location: ../Pages/Admin/adminDashboard.php?action=successLogin");
                    exit;
                } else {
                    // $_SESSION['error'] = 'Unauthorized User Role!';
                    // header("Location: ../Pages/register.php?page=login");
                    // exit;
                    header("Location: ../Pages/register.php?page=login&action=unauthorized");
                    exit;
                }
            } else {
                // $_SESSION['error'] = 'User is not verified!';
                header("Location: ../Pages/register.php?page=login&action=notVerified");
                exit;
            }
        } else {
            $_SESSION['error'] = 'Incorrect password or email';
            header("Location: ../Pages/register.php?page=login");
            exit;
        }
    } else {
        $_SESSION['error'] = 'Incorrect password or email';
        header("Location: ../Pages/register.php?page=login");
        exit;
    }
} else {
    $_SESSION['error'] = 'Form not submitted';
    header("Location: ../Pages/register.php");
    exit;
}
