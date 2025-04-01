<?php

require '../Config/dbcon.php';
session_start();
$env = parse_ini_file(__DIR__ . '/../.env');

use PHPMailer\PHPMailer\PHPmailer;
use PHPMailer\PHPMailer\Exception;


require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/SMTP.php';


if (isset($_POST['signUp'])) {
    $terms = mysqli_real_escape_string($conn, $_POST['terms']);
    echo $terms;
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $userAddress = mysqli_real_escape_string($conn, $_POST['userAddress']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $extensions = ['@gmail.com', '@yahoo.com', '@outlook.com', '@protonmail.com', '@icloud.com'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && array_filter($extensions, fn($ext) => str_ends_with($email, $ext))) {

        $check_email = "SELECT email FROM users WHERE email = '$email' LIMIT 1";
        $check_query = mysqli_query($conn, $check_email);
        $_SESSION['formData'] = $_POST;  // store the data in session that user enter
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['email-message'] = 'Email already exist.';

            header("Location: ../index.php?page=register");
            exit;
        } elseif ($password == $confirm_password) {
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            date_default_timezone_set('Asia/Manila');
            $OTP_expiration_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            unset($_SESSION['formData']);
            $storeData = "INSERT INTO users(firstName, middleInitial, lastName, email, userAddress, password, userOTP, OTP_expiration_at) 
                VALUES('$firstName','$middleInitial','$lastName','$email','$userAddress','$hashpassword','$otp', '$OTP_expiration_at')";
            $result = mysqli_query($conn, $storeData);
            if ($result) {
                $mail = new PHPmailer(true);
                try {
                    $_SESSION['email'] = $email;
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
            header("Location: ../index.php?register");
            exit;
        }
    } else {
        $_SESSION['email-message'] = 'Invalid email format';
        header("Location: ../index.php?register");
        exit;
    }
}



if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['login_email']);
    $password = mysqli_real_escape_string($conn, $_POST['login_password']);

    $loginQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $loginQuery);
    $_SESSION['formData']['email'] = $email;
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $storedPassword = $data['password'];
        $status = $data['userStatus'];
        if (password_verify($password, $storedPassword)) {
            if ($status == 'Verified') {
                unset($_SESSION['formData']);
                header("Location: ../Pages/dashboard.php");
            } else {
                $_SESSION['error'] = 'User not verified';
                header("Location: ../index.php");
                exit;
            }
        } else {
            $_SESSION['error'] = 'Incorrect password or email';
            header("Location: ../index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = 'Incorrect password or email';
        header("Location: ../index.php");
        exit;
    }
} else {
    echo 'ITO BA ERROR';
}
