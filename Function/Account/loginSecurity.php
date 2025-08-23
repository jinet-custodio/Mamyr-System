<?php

require '../../Config/dbcon.php';
require '../../vendor/autoload.php';
require_once '../emailSenderFunction.php';

session_start();
$env = parse_ini_file(__DIR__ . '/../../.env');

date_default_timezone_set('Asia/Manila'); //Set default time zone 


$userRole = (int) $_SESSION['userRole'];
$userID = (int) $_SESSION['userID'];


function getUserByEmail($conn, $userID, $userRole, $email)
{
    $getEmailQuery = $conn->prepare("SELECT * FROM users WHERE userID = ? AND userRole = ? AND email = ?");
    $getEmailQuery->bind_param('iis', $userID, $userRole, $email);
    if ($getEmailQuery->execute()) {

        $emailResult = $getEmailQuery->get_result();
        if ($emailResult->num_rows > 0) {
            return $emailResult->fetch_assoc();
        }
    }

    return null;
}


if (isset($_POST['validatePassword'])) {

    $passwordEntered = mysqli_real_escape_string($conn, $_POST['passwordEntered']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);


    $data = getUserByEmail($conn, $userID, $userRole, $email);

    if ($data) {
        $storedPassword = $data['password'];
        if (password_verify($passwordEntered, $storedPassword)) {
            header("Location: ../../Pages/Account/loginSecurity.php?step=2");
            exit;
        } else {
            $_SESSION['email-change'] = "Incorrect Password";
            header("Location: ../../Pages/Account/loginSecurity.php");
            exit;
        }
    } else {
        $_SESSION['email-change'] = "No matching user found.";
        header("Location: ../../Pages/Account/loginSecurity.php");
        exit;
    }
} elseif (isset($_POST['verifyEmail'])) {
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $newEmail = trim(mysqli_real_escape_string($conn, $_POST['newEmail']));

    $extensions = ['gmail.com', 'yahoo.com', 'outlook.com', 'protonmail.com', 'icloud.com'];
    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {

        $emailParts = explode('@', $newEmail);
        $domain = strtolower(end($emailParts));

        if (in_array($domain, $extensions)) {
            $data = getUserByEmail($conn, $userID, $userRole, $email);
            if ($data) {
                $storedEmail = $data['email'];
                $firstName = $data['firstName'];
                if ($storedEmail === $email && $newEmail !== $storedEmail) {
                    $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                    $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); //Add a 5mins to the time of creation
                    unset($_SESSION['formData']);

                    $storeData = $conn->prepare("UPDATE users SET userOTP = ?, OTP_expiration_at = ? 
                        WHERE userID = ? AND userRole = ? AND email = ?");
                    $storeData->bind_param("ssiis", $otp, $OTP_expiration_at, $userID, $userRole, $email);

                    if ($storeData->execute()) {

                        $subject = "Email Change Verification Code";
                        $message = '<body style="font-family: \'Times New Roman\', Times, serif;
                                        background-color: #f4f4f4;
                                        padding: 20px;
                                        margin: 0;"
                                    >
                                    <table
                                        align="center"
                                        width="100%"
                                        cellpadding="0"
                                        cellspacing="0"
                                        style="max-width: 600px; background-color: #ffffff;
                                        border-radius: 8px; overflow: hidden;
                                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                        ">
                                    <tr>
                                        <td style="padding: 30px; text-align: left; color: #333333">
                                            <h2 style="color: #333333; margin-top: 0">
                                           ' . $subject . '
                                            </h2>
                                            <p style="font-size: 16px; margin: 20px 0 10px">Hello,</p>
                                            <p style="font-size: 16px; margin: 10px 0; text-align: justify">
                                            You have requested to update the email address associated with your
                                            account. To proceed, please use the One-Time Password (OTP) provided
                                            below to verify this change:
                                            </p>

                                            <div style="text-align: center; margin: 30px 0">
                                            <span
                                                style="
                                                display: inline-block;
                                                background-color: #00e1ff;
                                                color: #0c0605;
                                                font-size: 24px;
                                                padding: 15px 30px;
                                                border-radius: 6px;
                                                font-weight: bold;
                                                "
                                            >
                                                ' . $otp . '
                                            </span>
                                            </div>
                                            <p style="font-size: 16px; margin: 10px 0">
                                            This OTP is valid for <strong>5 minutes</strong>. For your security,
                                            please do not share this code with anyone. If you did not request this
                                            code, please ignore this email.
                                            </p>
                                            <br />
                                            <p style="font-size: 16px">Thank you,</p>
                                            <p style="font-size: 16px; font-weight: bold">Mamyr.</p>
                                        </td>
                                        </tr>
                                    </table>
                                    </body>
                                ';

                        if (sendEmail($newEmail, $firstName, $subject, $message, $env)) {
                            $_SESSION['newEmail'] = $newEmail;
                            header("Location: ../../Pages/Account/loginSecurity.php?step=3");
                            exit;
                        } else {
                            $_SESSION['modal-error'] = "Unable to send verification code.";
                            header("Location: ../../Pages/Account/loginSecurity.php?step=3");
                            exit;
                        }
                    } else {
                        $_SESSION['modal-error'] = "Unable to generate OTP. Please try again later";
                        header("Location: ../../Pages/Account/loginSecurity.php?step=2");
                        exit;
                    }
                } else {
                    $_SESSION['newEmail'] = $newEmail;
                    $_SESSION['modal-error'] = "The new email address cannot be the same as the existing email address.";
                    header("Location: ../../Pages/Account/loginSecurity.php?step=2");
                    exit;
                }
            } else {
                $_SESSION['newEmail'] = $newEmail;
                $_SESSION['modal-error'] = "No matching user found.";
                header("Location: ../../Pages/Account/loginSecurity.php?step=2");
                exit;
            }
        } else {
            $_SESSION['modal-error'] = "Only common email providers are allowed.";
            header("Location: ../../Pages/Account/loginSecurity.php?step=2");
            exit;
        }
    } else {
        $_SESSION['modal-error'] = "Invalid Email Format";
        header("Location: ../../Pages/Account/loginSecurity.php?step=2");
        exit;
    }
} elseif (isset($_POST['verifyCode'])) {
    $enteredOTP = mysqli_real_escape_string($conn, $_POST['enteredOTP']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['newEmail']);

    $data = getUserByEmail($conn, $userID, $userRole, $email);
    if ($data) {
        $storedOTP = $data['userOTP'];
        $stored_expiration = $data['OTP_expiration_at'];
        $time_now = date('Y-m-d H:i:s');

        if ($storedOTP !== "") {
            if ($stored_expiration > $time_now) {
                if ($storedOTP === $enteredOTP) {
                    $updateEmail = $conn->prepare("UPDATE users SET email = ?, 
                    userOTP = NULL, 
                    OTP_expiration_at = NULL WHERE email = ? AND userID = ?");
                    $updateEmail->bind_param('ssi', $newEmail, $email, $userID);
                    if ($updateEmail->execute()) {
                        header("Location: ../../Pages/Account/loginSecurity.php?step=success");
                        exit;
                    }
                } else {
                    $_SESSION['modal-error'] = "The OTP you entered is incorrect. Please try again.";
                    header("Location: ../../Pages/Account/loginSecurity.php?step=3");
                    exit;
                }
            } else {
                $_SESSION['modal-error'] = "The OTP has expired. Please request a new one.";
                header("Location: ../../Pages/Account/loginSecurity.php?step=3");
                exit;
            }
        } else {
            $_SESSION['modal-error'] = "Please enter the OTP sent to your email.";
            header("Location: ../../Pages/Account/loginSecurity.php?step=3");
            exit;
        }
    } else {
        $_SESSION['email-change'] = "No matching user found.";
        header("Location: ../../Pages/Account/loginSecurity.php");
        exit;
    }
} elseif (isset($_POST['changePassword'])) {

    $newPassword = mysqli_real_escape_string($conn, $_POST['newPassword']);
    $currentPassword = mysqli_real_escape_string($conn, $_POST['currentPassword']);

    $query = $conn->prepare("SELECT * FROM users WHERE userID = ? AND userRole = ?");
    $query->bind_param('ii', $userID, $userRole);
    if ($query->execute()) {
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $storedPassword = $data['password'];
            if (password_verify($currentPassword, $storedPassword)) {
                $hashPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($currentPassword !== $newPassword) {
                    $updatePassword = $conn->prepare("UPDATE users SET 
                        password = ?
                        WHERE userID = ? AND userRole = ?
                        ");
                    $updatePassword->bind_param('sii', $hashPassword, $userID, $userRole);

                    if ($updatePassword->execute()) {
                        header("Location: ../../Pages/Account/loginSecurity.php?step=success-password");
                        exit;
                    }
                } else {
                    $_SESSION['password-error'] = "The new password must be different from the current password.";
                    header("Location:../../Pages/Account/loginSecurity.php?step=4");
                    exit;
                }
            } else {
                $_SESSION['password-error'] = "The current password is incorrect.";
                header("Location: ../../Pages/Account/loginSecurity.php?step=4");
                exit;
            }
        }
    }
}
