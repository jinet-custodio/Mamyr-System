<?php

require '../../../Config/dbcon.php';

session_start();
$env = parse_ini_file(__DIR__ . '/../../../.env');
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPmailer;
use PHPMailer\PHPMailer\Exception;


// require '../../../phpmailer/src/PHPMailer.php';
// require '../../../phpmailer/src/Exception.php';
// require '../../../phpmailer/src/SMTP.php';

$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);

if (isset($_POST['validatePassword'])) {
    $passwordEntered = mysqli_real_escape_string($conn, $_POST['passwordEntered']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);


    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole' AND email = '$email'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $storedPassword = $data['password'];
        if (password_verify($passwordEntered, $storedPassword)) {
            header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=2");
            exit;
        } else {
            $_SESSION['email-change'] = "Incorrect Password";
            header("Location: ../../../Pages/Admin/Account/loginSecurity.php");
            exit;
        }
    }
} elseif (isset($_POST['verifyEmail'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['newEmail']);

    $extensions = ['@gmail.com', '@yahoo.com', '@outlook.com', '@protonmail.com', '@icloud.com'];
    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL) && array_filter($extensions, fn($ext) => str_ends_with($newEmail, $ext))) {
        $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole' AND email = '$email'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $storedEmail = $data['email'];
            $firstName = $data['firstName'];
            if ($storedEmail === $email && $newEmail !== $storedEmail) {
                $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                date_default_timezone_set('Asia/Manila'); //Set default time zone 
                $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); //Add a 5mins to the time of creation
                unset($_SESSION['formData']);
                $storeData = "UPDATE users SET 
            userOTP = '$otp',
            OTP_expiration_at = '$OTP_expiration_at' 
            WHERE userID = '$userID' AND userRole = '$userRole' AND email = '$email'";
                $result = mysqli_query($conn, $storeData);
                if ($result) {
                    $mail = new PHPmailer(true);
                    try {
                        $_SESSION['newEmail'] = $newEmail;
                        $mail->isSMTP();
                        $mail->Host       =  $env['SMTP_HOST'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $env['SMTP_USER'];
                        $mail->Password   =  $env['SMTP_PASS'];
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       =  $env['SMTP_PORT'];

                        $mail->setFrom($env['SMTP_USER'], 'Mamyr Resort and Event Place');
                        $mail->addAddress($newEmail, $firstName);
                        $subject = "Email Change Verification Code";
                        $message = "
                                <h2 style='color: #333;'>Email Change Verification Code</h2>
                                <p> Good Day, {$firstName},</p>
                                <p>You have requested to update the email address associated with your account.</p>
                                <p>To proceed, please use the One-Time Password (OTP) provided below to verify this change:</p>
                                <h2 style='color:rgb(12, 6, 5); font-size: 24px; margin-left:120px;'> $otp </h2>
                                <p>This OTP is valid for <strong>5 minutes</strong>. For your security, please do not share this code with anyone.</p>
                                <p>If you did not request this code, please ignore this email.</p>
                                <br>
                                <p>Thank you,</p>
                                <p><strong>Mamyr</strong></p>
                                ";

                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body    = $message;
                        // $mail->AltBody = 'Body in plain text for non-HTML mail clients';
                        if (!$mail->send()) {
                            $_SESSION['modal-error'] = "Unable to send verification code.";
                            header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=3");
                            exit;
                        } else {
                            header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=3");
                            exit;
                        }
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $_SESSION['modal-error'] = "Unable to generate OTP. Please try again later";
                    header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=2");
                    exit;
                }
            } else {
                $_SESSION['newEmail'] = $newEmail;
                $_SESSION['modal-error'] = "The new email address cannot be the same as the existing email address.";
                header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=2");
                exit;
            }
        }
    } else {
        $_SESSION['modal-error'] = "Invalid Email Format";
        header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=2");
        exit;
    }
} elseif (isset($_POST['verifyCode'])) {
    $enteredOTP = mysqli_real_escape_string($conn, $_POST['enteredOTP']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['newEmail']);
    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole' AND email = '$email'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $storedOTP = $data['userOTP'];
        $stored_expiration = $data['OTP_expiration_at'];
        date_default_timezone_set('Asia/Manila');
        $time_now = date('Y-m-d H:i:s');
        if ($storedOTP !== "") {
            if ($stored_expiration > $time_now) {
                if ($storedOTP == $enteredOTP) {
                    $updateEmail = "UPDATE users SET email = '$newEmail', 
                    userOTP = NULL, 
                    OTP_expiration_at = NULL WHERE email = '$email' AND userID = '$userID'";
                    $result = mysqli_query($conn, $updateEmail);
                    if ($result) {
                        header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=success");
                        exit;
                    }
                } else {
                    $_SESSION['modal-error'] = "The OTP you entered is incorrect. Please try again.";
                    header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=3");
                    exit;
                }
            } else {
                $_SESSION['modal-error'] = "The OTP has expired. Please request a new one.";
                header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=3");
                exit;
            }
        } else {
            $_SESSION['modal-error'] = "Please enter the OTP sent to your email.";
            header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=3");
            exit;
        }
    }
} elseif (isset($_POST['changePassword'])) {

    $newPassword = mysqli_real_escape_string($conn, $_POST['newPassword']);
    $currentPassword = mysqli_real_escape_string($conn, $_POST['currentPassword']);

    $query = "SELECT * FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $storedPassword = $data['password'];
        if (password_verify($currentPassword, $storedPassword)) {
            $hashPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            if ($currentPassword !== $newPassword) {
                $updatePassword = "UPDATE users SET 
                password = '$hashPassword'
                 WHERE userID = '$userID' AND userRole = '$userRole'
                ";
                $result = mysqli_query($conn, $updatePassword);
                if ($result) {
                    header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=success-password");
                    exit;
                }
            } else {
                $_SESSION['password-error'] = "The new password must be different from the current password.";
                header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=4");
                exit;
            }
        } else {
            $_SESSION['password-error'] = "The current password is incorrect.";
            header("Location: ../../../Pages/Admin/Account/loginSecurity.php?step=4");
            exit;
        }
    }
}
