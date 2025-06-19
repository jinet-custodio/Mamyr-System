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

function assembleFullAddress($post)
{
    $street = $post['streetAddress'] ?? '';
    $address2 = $post['address2'] ?? '';
    $city = $post['city'] ?? '';
    $province = $post['province'] ?? '';
    $zip = $post['zip'] ?? '';

    return trim("$street $address2, $city, $province, $zip");
}



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
        $imageData = null;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && array_filter($extensions, fn($ext) => str_ends_with($email, $ext))) {

        $check_email = "SELECT email FROM users WHERE email = '$email' LIMIT 1";
        $check_query = mysqli_query($conn, $check_email);
        $_SESSION['formData'] = $_POST;  // store the data in session that user enter
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['email-message'] = 'Email already exist.'; //Error Message
            header("Location: ../Pages/register.php?page=register");
            exit;
        } elseif ($password == $confirm_password) {
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            date_default_timezone_set('Asia/Manila'); //Set default time zone 
            $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); //Add a 5mins to the time of creation
            unset($_SESSION['formData']);
            $storeData = "INSERT INTO users(userProfile, firstName, middleInitial, lastName, email, userAddress, password, userOTP, OTP_expiration_at, userRole) 
            VALUES('$imageData','$firstName','$middleInitial','$lastName','$email','$userAddress','$hashpassword','$otp', '$OTP_expiration_at', '$userRole')";
            $result = mysqli_query($conn, $storeData);
            if ($result && $registerStatus == "partner") {
                // Save business partner info into session temporarily
                $_SESSION['partnerData'] = [
                    'companyName' => $_POST['companyName'],
                    'partnerType' => $_POST['partnerType'],
                    'phoneNumber' => $_POST['phoneNumber'],
                    'partnerAddress' => $_POST['streetAddress'] . ' ' . $_POST['address2'] . ', ' . $_POST['city'] . ', ' . $_POST['province'] . ', ' . $_POST['zip'],
                    'proofLink' => $_POST['proofLink']
                ];
            }

            if ($result) {
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

                    $message = "
                                <h2 style='color: #333;'>Your OTP Code for Account Verification</h2>
                                <p>Hello,</p>
                                <p>Your One-Time Password (OTP) for account verification is:</p>
                                <h2 style='color:rgb(12, 6, 5); font-size: 24px; margin-left:120px;'> $otp </h2>
                                <p>This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
                                <p>If you did not request this code, please ignore this email.</p>
                                <br>
                                <p>Thank you,</p>
                                <p><strong>Mamyr</strong></p>
                                ";

                    $mail->isHTML(true);
                    $mail->Subject = 'Hello';
                    $mail->Body    = $message;
                    // $mail->AltBody = 'Body in plain text for non-HTML mail clients';
                    if (!$mail->send()) {
                        $_SESSION['OTP'] = 'Failed to send OTP. Try again.';
                        header("Location: ../Pages/verify_email.php");
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

    $loginQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $loginQuery);
    $_SESSION['formData']['email'] = $email;
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $storedPassword = $data['password'];
        $userRole = $data['userRole'];
        $userStatus = $data['userStatusID'];
        if (password_verify($password, $storedPassword)) {
            if ($userStatus == 2) { //The user must be verified
                if ($userRole == 1) { //Customer
                    unset($_SESSION['formData']);
                    $_SESSION['userID'] = $data['userID'];
                    $_SESSION['userRole'] = $userRole;
                    header("Location: ../Pages/Customer/dashboard.php");
                    exit;
                } elseif ($userRole == 2) { //Partner
                    unset($_SESSION['formData']);
                    $_SESSION['userID'] = $data['userID'];
                    $_SESSION['userRole'] = $userRole;
                    header("Location: ../Pages/Customer/dashboard.php");
                    exit;
                } elseif ($userRole == 3) { //Admin
                    unset($_SESSION['formData']);
                    $_SESSION['userID'] = $data['userID'];
                    $_SESSION['userRole'] = $userRole;
                    header("Location: ../Pages/Admin/adminDashboard.php");
                    exit;
                } else {
                    $_SESSION['error'] = 'Unauthorized User Role!';
                    header("Location: ../Pages/register.php?page=login");
                    exit;
                }
            } else {
                $_SESSION['error'] = 'User is not verified!';
                header("Location: ../Pages/register.php?page=login");
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
