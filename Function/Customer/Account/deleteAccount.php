<?php

require '../../../Config/dbcon.php';

session_start();
$env = parse_ini_file(__DIR__ . '/../../../.env');
require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPmailer;
use PHPMailer\PHPMailer\Exception;


// require '../../../phpmailer/src/PHPMailer.php';
// require '../../../phpmailer/src/Exception.php';
// require '../../../phpmailer/src/SMTP.php';

$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);
//Send Otp to user email if user agree to delete the 
if (isset($_POST['yesDelete'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);


    $check_email = "SELECT * FROM users WHERE userID = '$userID' AND email = '$email'";
    $check_query = mysqli_query($conn, $check_email);
    if (mysqli_num_rows($check_query) > 0) {
        $data = mysqli_fetch_assoc($check_query);
        $OTP = str_pad(random_int(100000, 999999), 6, 0, STR_PAD_LEFT);
        date_default_timezone_set('Asia/Manila');
        $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $insertOTP = "UPDATE users SET userOTP = '$OTP', 
        OTP_expiration_at = '$OTP_expiration_at' 
        WHERE userID = '$userID' and userRole = '$userRole'";
        $resultOTP = mysqli_query($conn, $insertOTP);
        if ($resultOTP) {
            $mail = new PHPmailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       =  $env['SMTP_HOST'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $env['SMTP_USER'];
                $mail->Password   =  $env['SMTP_PASS'];
                $mail->SMTPSecure = 'tls';
                $mail->Port       =  $env['SMTP_PORT'];

                $mail->setFrom($env['SMTP_USER'], 'Mamyr Resort and Event Place');
                $mail->addAddress($email, $data['firstName']);
                $subject = "Account Deletion - OTP Verification";
                $message = "
                                <h2 style='color: #333;'>Account Deletion Verification</h2>
                                <p>Hello,</p>
                                <p>We received a request to delete your account. To confirm this action, please use the following One-Time Password (OTP):</p>
                                <h2 style='color:rgb(12, 6, 5); font-size: 24px; margin-left:120px;'> $OTP </h2>
                                <p>This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
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
                    $_SESSION['deleteAccountMessage'] = 'Failed to send OTP. Try again.';
                    header("Location: ../../../Pages/Customer/Account/deleteAccount.php");
                    exit;
                } else {
                    header("Location: ../../../Pages/Customer/Account/deleteAccount.php?action=success");
                    exit;
                }
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            die("Error inserting data: " . mysqli_error($conn));
        }
    } else {
        $_SESSION['deleteAccountMessage'] = 'User Not Found';
        header("Location: ../../../Pages/Customer/Account/deleteAccount.php");
        exit;
    }
}


//Verify OTP and then delete the Account 
elseif (isset($_POST['verifyCode'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $enteredOTP = mysqli_real_escape_string($conn, $_POST['enteredOTP']);

    if ($enteredOTP !== "") {
        $check_query = "SELECT * FROM users WHERE userID = '$userID' AND email = '$email'";
        $result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $storedOTP = $data['userOTP'];
            $storedTime = $data['OTP_expiration_at'];
            date_default_timezone_set('Asia/Manila');
            $timeNow = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            if ($timeNow > strtotime($storedTime)) {
                if ($enteredOTP ===  $storedOTP) {
                    $deleteQuery = "DELETE FROM users WHERE userID = '$userID' and email = '$email'";
                    $deleteResult = mysqli_query($conn, $deleteQuery);
                    // $deleteQuery = "UPDATE users SET
                    //             userStatusID = '4' WHERE userID = '$userID' and email = '$email'";
                    // $result = mysqli_query($conn, $deleteQuery);
                    if ($deleteQuery) {
                        header("Location: ../../../Pages/register.php?action=deleted");
                        exit;
                    }
                }
            } else {
                $_SESSION['deleteAccountMessage'] = 'Expired OTP.';
                header("Location: ../../../Pages/Customer/Account/deleteAccount.php");
                exit;
            }
        }
    }
}
