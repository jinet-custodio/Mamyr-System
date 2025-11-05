<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');
session_start();
$env = parse_ini_file(__DIR__ . '/../../.env');
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPmailer;
use PHPMailer\PHPMailer\Exception;

require '../emailSenderFunction.php';
require '../Helpers/userFunctions.php';

// require '../../../phpmailer/src/PHPMailer.php';
// require '../../../phpmailer/src/Exception.php';
// require '../../../phpmailer/src/SMTP.php';

$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


//* Send Otp to user email if user agree to delete the 
if (isset($_POST['yesDelete'])) {

    try {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $emailQuery = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $emailQuery->bind_param('s', $email);

        if (!$emailQuery->execute()) {
            throw new Exception("Error executing email query: " . $emailQuery->error);
        }

        $result = $emailQuery->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User Not Found");
        }

        $storedData = $result->fetch_assoc();
        $OTP = generateCode(6);
        $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $insertOTP = $conn->prepare("UPDATE user SET userOTP = ?, 
        OTP_expiration_at = ?
        WHERE userID = ? and userRole = ?");
        $insertOTP->bind_param("ssii", $OTP, $OTP_expiration_at, $userID, $userRole);
        if (!$insertOTP->execute()) {
            throw new Exception("Error inserting data: " . mysqli_error($conn));
        }
        $subject = "Account Deletion - OTP Verification";
        $message = '<body
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
            Account Deletion Verification
          </h2>
        </td>
      </tr>

      <tr>
        <td style="padding: 30px; text-align: left; color: #333333">
          <p style="font-size: 12px; margin: 10px 0 10px">
            We received a request to delete your account. To confirm this
            action, please use the following One-Time Password (OTP):
          </p>

          <h2 style="color: rgb(12, 6, 5); font-size: 24px; text-align: center">
            $OTP
          </h2>
          <p style="font-size: 12px; margin: 8px 0">
            This OTP is valid for <strong>5 minutes</strong>. Do not share it
            with anyone. If you did not request this code, please ignore this
            email.
          </p>
          <br />
          <p style="font-size: 14px">Thank you,</p>
          <p sstyle="font-size: 14px; font-weight:bold ">
            Mamyr Resort and Events Place.
          </p>
        </td>
      </tr>
    </table>
  </body>';
        if (sendEmail($email,   $storedData['firstName'], $subject, $message, $env)) {
            header("Location: ../../Pages/Account/deleteAccount.php?action=success");
            exit;
        } else {
            throw new Exception('Failed to send OTP. Try again.');
        }
    } catch (Exception $e) {
        $_SESSION['deleteAccountMessage'] = $e->getMessage();
        error_log("Error: " . $e->getMessage());
        header("Location: ../../Pages/Account/deleteAccount.php");
        exit();
    }
}


//* Verify OTP and then delete the Account 

elseif (isset($_POST['verifyCode'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $enteredOTP = mysqli_real_escape_string($conn, $_POST['enteredOTP']);

    if (!empty($enteredOTP)) {
        $emailQuery = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $emailQuery->bind_param('s', $email);
        $emailQuery->execute();
        $result = $emailQuery->get_result();

        if ($result->num_rows > 0) {
            $storedData = $result->fetch_assoc();
            $storedOTP = $storedData['userOTP'];
            $storedTime = $storedData['OTP_expiration_at'];
            $userID = $storedData['userID']; // ✅ FIX

            date_default_timezone_set('Asia/Manila');
            $timeNow = time();
            $otpExpiration = strtotime($storedTime);

            if ($timeNow <= $otpExpiration) {
                if ($enteredOTP === $storedOTP) {
                    $today = date('Y-m-d H:i:s');
                    $isDeleted = 1;
                    $anonymousEmail = 'deletedAt_' . bin2hex(random_bytes(4)) . '@gmail.com';

                    $deleteQuery = $conn->prepare("UPDATE user SET email = ?, isDeleted = ?, dateDeleted = ?, userOTP = NULL, OTP_expiration_at = NULL WHERE userID = ? AND email = ?");
                    $deleteQuery->bind_param("sisis", $anonymousEmail, $isDeleted, $today, $userID, $email);

                    if ($deleteQuery->execute()) {
                        header("Location: ../../Pages/register.php?action=deleted");
                        exit;
                    } else {
                        echo "Error deleting account: " . $deleteQuery->error;
                    }
                } else {
                    $_SESSION['deleteAccountMessage'] = 'Invalid OTP.';
                    header("Location: ../../Pages/Account/deleteAccount.php");
                    exit;
                }
            } else {
                $_SESSION['deleteAccountMessage'] = 'Expired OTP.';
                header("Location: ../../Pages/Account/deleteAccount.php");
                exit;
            }
        } else {
            echo 'Email not found.';
        }
    } else {
        echo 'OTP is empty.';
    }
} else {
    echo 'Form not submitted properly.';
}