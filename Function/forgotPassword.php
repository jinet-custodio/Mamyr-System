<?php
require '../Config/dbcon.php';
session_start();
date_default_timezone_set('Asia/Manila');
$env = parse_ini_file(__DIR__ . '/../.env');
require '../vendor/autoload.php';
require_once 'Helpers/userFunctions.php';
require_once 'emailSenderFunction.php';


// require '../phpmailer/src/PHPMailer.php';
// require '../phpmailer/src/Exception.php';
// require '../phpmailer/src/SMTP.php';

$isVerified = 2;

if (isset($_POST['verify_email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $emailQuery = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $emailQuery->bind_param('s', $email);
    $emailQuery->execute();
    $result = $emailQuery->get_result();
    if ($result->num_rows > 0) {
        $storedData = $result->fetch_assoc();
        $statusID = intval($storedData['userStatusID']);
        if ($statusID === $isVerified) //Verified User
        {
            $resetPasswordOTP = generateCode(6);
            $time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $updateOTP = $conn->prepare("UPDATE user SET userOTP = ?, OTP_expiration_at = ? WHERE email = ?");
            $updateOTP->bind_param("sss", $resetPasswordOTP, $time, $email);

            if ($updateOTP->execute()) {
                $message = "
                                <h2 style='color: #333;'>Your OTP Code for Changing your Password</h2>
                                <p>Hello,</p>
                                <p>Your One-Time Password (OTP) for Changing your Password is:</p>
                                <h2 style='color:rgb(12, 6, 5); font-size: 24px; margin-left:120px;'> $resetPasswordOTP </h2>
                                <p>This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
                                <p>If you did not request this code, please ignore this email.</p>
                                <br>
                                <p>Thank you,</p>
                                <p><strong>Mamyr</strong></p>
                                ";
                $subject = 'Changing of Password';
                if (sendEmail($email, $firstName, $subject, $message, $env)) {
                    $_SESSION['email'] = $email;
                    $_SESSION['action'] = 'forgot-password';
                    header("Location: ../Pages/verify_email.php");
                    exit;
                } else {
                    $_SESSION['OTP'] = 'Failed to send OTP. Try again.';
                    header("Location: ../Pages/verify_email.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = 'Error Updating the Data. Please try again later.';
                header("Location: ../Pages/forgotPassword.php");
                exit;
            }
        } else {
            $_SESSION['error'] = 'User not verified';
            header("Location: ../Pages/register.php");
            exit;
        }
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['error'] = 'Email not found.';
        header("Location:  ../Pages/enterEmail.php");
        exit;
    }
}

if (isset($_POST['changePassword'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['newPassword']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

    $emailQuery = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $emailQuery->bind_param('s', $email);
    $emailQuery->execute();
    $result = $emailQuery->get_result();
    if ($result->num_rows > 0) {
        $storedData = $result->fetch_assoc();
        if ($password == $confirm_password) {
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
            $updatePassword->bind_param("ss", $hashpassword, $email);
            if ($updatePassword->execute()) {
                unset($_SESSION['email']);
                $_SESSION['success'] = 'Your password has been updated successfully.';
                header("Location: ../Pages/register.php");
                exit;
            } else {
                $_SESSION['error'] = 'Unable to update your password. Please try again later.';
                header("Location: ../Pages/register.php");
                exit;
            }
        } else {
            $_SESSION['email'] = $email;
            $_SESSION['error'] = 'The passwords you entered do not match.';
            header("Location: ../Pages/forgotPassword.php");
            exit;
        }
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['error'] = 'No account found with the provided email.';
        header("Location:  ../Pages/forgotPassword.php");
        exit;
    }
}
