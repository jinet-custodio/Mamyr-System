<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require __DIR__ . '/../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');
require __DIR__ . '/../emailSenderFunction.php';
require __DIR__ . '/../Helpers/userFunctions.php';
$env = parse_ini_file(__DIR__ . '/../../.env');
require __DIR__ . '/../../vendor/autoload.php';

session_start();
$userID = (int) $_SESSION['userID'];
$userRole = (int) $_SESSION['userRole'];

if (isset($_POST['send-otp']) || isset($_POST['resend-otp'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);

    $otp = generateCode(6);
    $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); //Add a 5mins to the time of creation

    $searchOTP = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $searchOTP->bind_param("s", $email);
    $searchOTP->execute();
    $searchOTPResult = $searchOTP->get_result();
    if ($searchOTPResult->num_rows > 0) {
        $data = $searchOTPResult->fetch_assoc();

        $stored_expiration = $data['OTP_expiration_at'];
        $storedOTP = $data['userOTP'];
        $time_now = date('Y-m-d H:i:s');
        $time_left = strtotime($stored_expiration) - strtotime($time_now);
        if ($time_left > 0) {
            header("Location: ../../../../Pages/Admin/adminDashboard.php?result=OTPNotExpired");
            exit();
        }

        $insertOTPQuery = $conn->prepare("UPDATE user SET userOTP = ?, OTP_expiration_at = ? WHERE userID = ?");
        $insertOTPQuery->bind_param("ssi", $otp, $OTP_expiration_at, $userID);
        if ($insertOTPQuery->execute()) {
            $subject = 'One-Time Password (OTP) for Password Verification';
            $body = '<body
                    style="
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    padding: 20px;
                    margin: 0;
                    "
                >
                    <table
                    align="center"
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    style="
                        max-width: 600px;
                        background-color: #ffffff;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    "
                    >
                    <tr style="background-color: #365cce">
                        <td style="text-align: center">
                        <h2
                            style="
                            font-family: Poppins Light;
                            color: #ffffff;
                            font-size: 18px;
                            margin-top: 25px;
                            "
                        >
                            Your One-Time Password (OTP) Code
                        </h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: 10px 0 10px;">Dear ' . $firstName . ',</p>
                                        <p style="font-size: 12px; margin: 8px 0;">Upon logging in, you are required to change the temporary password provided for security reasons.
                                        Please use the following One Time Password(OTP) to verify
                                            your account:
                                        </p>

                                        <div style="text-align: center; margin: 25px 0;">
                                            <span
                                                style="display: inline-block; color: #0c0605; font-size: 20px; padding: 15px 30px; border-radius: 6px; font-weight: bold;">
                                                ' . $otp . '
                                            </span>
                                        </div>
                                        <p style="font-size: 12px; margin: 8px 0;">This OTP is valid for <strong>5 minutes</strong>. Do not
                                            share it with anyone. If you did not request this code, please ignore this email and contact the admin.
                                        </p>
                                        <br>
                                        <p style="font-size: 14px;">Thank you,</p>
                                        <p style="font-size: 14px; font-weight: bold;">Mamyr Resort and Events Place.</p>
                        </td>
                    </tr>
                    </table>
                </body>
                                ';

            if (sendEmail($email, $firstName, $subject, $body, $env)) {
                header("Location: ../../../../Pages/Admin/adminDashboard.php?result=successOTP");
                exit();
            } else {
                error_log("Sending Email Failed");
                header("Location: ../../../../Pages/Admin/adminDashboard.php?result=failedOTP");
                exit();
            }
        } else {
            error_log("Something went wrong." . $insertOTPQuery->error);
            header("Location: ../../../../Pages/Admin/adminDashboard.php?result=failedUpdatingUser");
            exit();
        }
    }
}


if (isset($_POST['submit-code'])) {
    $otp = mysqli_real_escape_string($conn, $_POST['otp-code']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $searchOTP = $conn->prepare("SELECT OTP_expiration_at AS expirationDate FROM user WHERE email = ? AND userOTP = ?");
    $searchOTP->bind_param("ss", $email, $otp);
    $searchOTP->execute();
    $searchOTPResult = $searchOTP->get_result();
    if ($searchOTPResult->num_rows > 0) {
        $data = $searchOTPResult->fetch_assoc();

        $stored_expiration = $data['expirationDate'];
        $time_now = date('Y-m-d H:i:s');
        $time_left = strtotime($stored_expiration) - strtotime($time_now);
        if ($time_left > 0) {
            header("Location: ../../../../Pages/Admin/adminDashboard.php?result=correctOTP");
            exit();
        } else {
            header("Location: ../../../../Pages/Admin/adminDashboard.php?result=expiredOTP");
            exit();
        }
    } else {
        header("Location: ../../../../Pages/Admin/adminDashboard.php?result=wrongOTP");
        exit();
    }
}


if (isset($_POST['password-change'])) {
    error_log(print_r($_POST, true));
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm-password']);


    if ($password === $confirmPassword) {
        $updateAdminPassword = $conn->prepare("UPDATE user SET password = ? WHERE userID = ?");
        $updateAdminPassword->bind_param('si', $password, $userID);
        if ($updateAdminPassword->execute()) {
            header("Location: ../../../../Pages/Admin/adminDashboard.php?result=successPassword");
            exit();
        }
    } else {
        header("Location: ../../../../Pages/Admin/adminDashboard.php?result=passwordNotSame");
        exit();
    }
}
