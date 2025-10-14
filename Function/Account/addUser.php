<?php

require '../../Config/dbcon.php';

session_start();
require '../emailSenderFunction.php';
$env = parse_ini_file(__DIR__ . '/../../.env');
require '../../vendor/autoload.php';
date_default_timezone_set('Asia/Manila');



if (isset($_POST['createAccount'])) {

    $role = mysqli_real_escape_string($conn, $_POST['roleSelect']) ?? 'admin';
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $address = mysqli_real_escape_string($conn, $_POST['address']) ?? 'Not Stated';
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $temporaryPassword = 'admin@mamyr_25';
    $hash_password = password_hash($temporaryPassword, PASSWORD_DEFAULT);

    if ($role = "admin") {
        $userRole = 3;
    }

    $verifiedStatus = 2;

    $defaultImage = '../../Assets/Images/defaultProfile.png';
    $image_data = file_exists($defaultImage) ? file_get_contents($defaultImage) : NULL;

    $birthday = $birthday !== "" ? $birthday : NULL;
    $middleInitial = $middleInitial !== "" ? $middleInitial : NULL;

    try {
        $date = new DateTime();
        $date->modify('+1 day');
        $expirationDate =  $date->format('Y-m-d H:i:s');
        $isTemporaryPassword = true;
        $termAccepted = true;


        $checkEmailQuery = $conn->prepare("SELECT email FROM user WHERE email = ?");
        $checkEmailQuery->bind_param("s", $email);
        $checkEmailQuery->execute();
        $result = $checkEmailQuery->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['account-form'] = $_POST;
            header("Location: ../../../../Pages/Account/addAccount.php?action=emailExist");
            exit();
        }


        $addUserQuery = $conn->prepare("INSERT INTO user(firstName, middleInitial, lastName, email, userAddress, phoneNumber, birthdate, password, userRole, userStatusID, isTemporaryPassword, isTermsAccepted) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $addUserQuery->bind_param("ssssssssiiii", $firstName, $middleInitial, $lastName, $email, $address, $phoneNumber, $birthday, $hash_password, $userRole, $verifiedStatus, $isTemporaryPassword, $termAccepted);

        if (!$addUserQuery->execute()) {
            throw new Exception("Error: " . $addUserQuery->error);
        }
        $subject = 'Mamyr: Your Account Has Been Created - Temporary Password Inside';

        $message = '<body style="font-family: Arial, sans-serif;         background-color: #f4f4f4; padding: 20px; margin: 0;">

                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <tr>
                                    <td style="display: flex; justify-content:center; align-items:center; gap: 15px; margin: 10px 0 10px 0"><img
                                            src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Logo" style="height:29.76px; width:130.56px; ">

                                        <h4>Mamyr Resort and
                                            Events Place</h4>
                                    </td>
                                </tr>

                                <tr style="background-color:#365CCE;">
                                    <td style="text-align:center;">
                                        <h4 style="font-family:Poppins Light; color:#ffffff; font-size: 18px; margin-top: 25px;">
                                            ADMIN ACCOUNT CREATED
                                        </h4>
                                        <h2 style="font-family:Poppins Light; color:#ffffff; margin-top: -20px;">
                                            Temporary Password Expiring in 24 Hours
                                        </h2>
                                    </td>
                                </tr>


                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <h2 style="color: #333333; margin-top: 0;">Your Temporary Password for this Account</h2>
                                        <p style="font-size: 16px; margin: 20px 0 10px;">Hello,</p>
                                        <p style="font-size: 16px; margin: 10px 0;">Please use the temporary password below to login: 
                                        </p>

                                        <div style="text-align: center; margin: 30px 0;">
                                            <span
                                                style="display: inline-block; color: #0c0605; font-size: 24px; padding: 15px 30px; border-radius: 6px; font-weight: bold;">
                                                ' . htmlspecialchars($temporaryPassword, ENT_QUOTES) . '
                                            </span>
                                        </div>
                                        <p style="font-size: 16px; margin: 10px 0;">This temporary password is valid for <strong>24 hours</strong>, until ' . htmlspecialchars($expirationDate, ENT_QUOTES) . '. Do not
                                            share this password with anyone. If you did not request this, please ignore this email.
                                        </p>
                                        <br>
                                        <p style="font-size: 16px;">Thank you,</p>
                                        <p style="font-size: 16px; font-weight: bold;">Mamyr Resort and Events Place.</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
                                ';
        if (sendEmail($email, $firstName, $subject, $message, $env)) {
            header("Location: ../../../../Pages/Account/userManagement.php?action=accountCreated");
            exit();
        }
    } catch (Exception $e) {
        error_log('Exception Error: ' . $e->getMessage());
        header("Location: ../../../../Pages/Account/addUser.php?action=errorAccountCreation");
        exit();
    }
}
