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


if (isset($_POST['verify_email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // $password = mysqli_real_escape_string($conn, $_POST['newPassword']);

    $emailQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $emailQuery);
    if (mysqli_num_rows($result) > 0) {
        $storedData = mysqli_fetch_assoc($result);
        $status = $storedData['userStatusID'];
        if ($status == 2) {
            $resetOTP = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            date_default_timezone_set('Asia/Manila');
            $time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            // $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $newOTP = "UPDATE users SET userOTP = '$resetOTP', OTP_expiration_at = '$time' WHERE email = '$email'";
            $OTPresult = mysqli_query($conn, $newOTP);
            if ($OTPresult) {
                $mail = new PHPmailer(true);
                try {
                    $_SESSION['email'] = $email;
                    $_SESSION['action'] = 'forgot-password';
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
                                <h2 style='color: #333;'>Your OTP Code for Changing your Password</h2>
                                <p>Hello,</p>
                                <p>Your One-Time Password (OTP) for Changing your Password is:</p>
                                <h2 style='color:rgb(12, 6, 5); font-size: 24px; margin-left:120px;'> $resetOTP </h2>
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
                $_SESSION['error'] = 'Error Updating Data.';
                header("Location: ../Pages/forgotPassword.php");
                exit;
            }
        } else {
            $_SESSION['error'] = 'User not verified';
            header("Location: ../Pages/register.php");
            exit;
        }
    } else {
        print $email . "wala?";
        $_SESSION['error'] = 'Email not found.';
        header("Location:  ../Pages/enterEmail.php");
        exit;
    }
} else {
    echo 'error???';
}

if (isset($_POST['changePassword'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['newPassword']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

    $emailQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $emailQuery);
    if (mysqli_num_rows($result) > 0) {
        if ($password == $confirm_password) {
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $newPass = "UPDATE users SET password = '$hashpassword' WHERE email = '$email'";
            $passwordResult = mysqli_query($conn, $newPass);
            if ($passwordResult) {
                $_SESSION['success'] = 'Password Updated';
                header("Location: ../Pages/register.php");
                exit;
            } else {
                $_SESSION['error'] = 'Password Update Failed';
                header("Location: ../Pages/register.php");
                exit;
            }
        } else {
            $_SESSION['error'] = 'Password doesn`t match';
            header("Location: ../Pages/register.php");
            exit;
        }
    } else {
        print $email . "wala?";
        $_SESSION['error'] = 'Email not found.';
        header("Location:  ../Pages/forgotPassword.php");
        exit;
    }
}
