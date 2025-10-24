<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 900);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID, userRole FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['userRole'] = $user['userRole'];
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
$data = $_SESSION['account-form'] ?? [];
// echo '<pre>';
// print_r($data);
// echo '</pre>';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Account/addAccount.css" />

    <!-- Font Awesome Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

</head>

<body>
    <a href="userManagement.php" class="back-button btn"><img src="../../Assets/Images/Icon/arrowBtnBlue.png" alt=""></a>
    <div class="wrapper">
        <form action="../../Function/Account/addUser.php" method="POST" enctype="multipart/form-data">
            <div class="card">
                <h5 class="card-title">Create New Account</h5>
                <div class="card-body">
                    <!-- <div class="profile-pic">
                        <img src="../../Assets/Images/defaultProfile.png" alt="" class="profile-image" id="preview">
                        <input type="file" name="profile-image" id="profile-image" hidden>
                        <label for="profile-image" class="uploadPfpBtn btn btn-primary">Upload Profile</label>
                    </div> -->
                    <div class="information-container">
                        <div class="input-container form-floating fullwidth">
                            <select class="form-select" id="roleSelect" name="roleSelect" aria-label="Floating label select example" required>
                                <option value="" selected <?= ($data['roleSelect'] ?? '') == " " ? 'selected' : '' ?> disabled>Select Role</option>
                                <option value="admin" <?= ($data['roleSelect'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <!-- <option value="partner">Business Partner</option>
                                <option value="customer">Customer</option> -->
                            </select>
                            <label for="roleSelect">Role</label>
                        </div>
                        <div class="name-container fullwidth">
                            <div class="input-container form-floating">
                                <input type="text" class="form-control" required name="firstName" id="firstName" value="<?= !empty($data['firstName']) ? ucfirst($data['firstName']) : '' ?>">
                                <label for="firstName">First Name</label>
                            </div>
                            <div class="input-container form-floating">
                                <input type="text" class="form-control" name="middleInitial" id="middleInitial" value="<?= !empty($data['middleInitial']) ? ucfirst($data['middleInitial']) : '' ?>">
                                <label for="middleInitial">M.I.</label>
                            </div>
                            <div class="input-container form-floating">
                                <input type="text" class="form-control" required name="lastName" id="lastName" value="<?= !empty($data['lastName']) ? ucfirst($data['lastName']) : '' ?>">
                                <label for=" lastName">Last Name</label>
                            </div>
                        </div>
                        <div class="input-container form-floating fullwidth">
                            <input type="text" class="form-control" name="address" id="address" value="<?= !empty($data['address']) ? ucfirst($data['address']) : '' ?>">
                            <label for=" address">Address</label>
                        </div>
                        <div class="input-container form-floating">
                            <!-- <input type="text" class="form-control" name="phoneNumber" id="phoneNumber" required> -->
                            <input type="text" class="form-control" name="phoneNumber" id="phoneNumber" pattern="^(?:\+63|0)9\d{9}$" value="<?= !empty($data['phoneNumber']) ? ucfirst($data['phoneNumber']) : '' ?>"
                                title="e.g., +639123456789 or 09123456789"
                                required>
                            <label for="phoneNumber">Phone Number</label>
                        </div>
                        <div class="input-container form-floating">
                            <input type="date" class="form-control" name="birthday" id="birthday" value="<?= !empty($data['birthday']) ? ucfirst($data['birthday']) : '' ?>">
                            <label for="birthday">Birthday</label>
                        </div>
                        <div class="input-container form-floating">
                            <input type="email" class="form-control" name="email" id="email" required value="<?= !empty($data['email']) ? $data['email'] : '' ?>">
                            <label for="email">Email</label>
                            <div class="form-text text-muted">
                                <i class="fa-solid fa-info-circle" style="color: #74C0FC;"></i>
                                Make sure that the email is correct — the temporary password will be sent there.
                            </div>
                        </div>

                        <?php
                        $passwordDisplay = "**************";
                        ?>

                        <div class="input-container form-floating">
                            <input type="password" class="form-control" name="password" id="password" value="<?= $passwordDisplay ?>" required>
                            <label for="password">Password</label>
                        </div>
                    </div>
                </div>
                <div class="button-container">
                    <button type="submit" class="btn btn-primary" name="createAccount" id="createAccount">Create Account</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Password Validation -->
    <script src="../../Assets/JS/passwordValidation.js"></script>
    <!-- Preview Image -->
    <script>
        document.querySelector("input[type='file']").addEventListener("change", function(event) {
            let reader = new FileReader();
            reader.onload = function() {
                let preview = document.getElementById("preview");
                preview.src = reader.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>

    <script>
        const params = new URLSearchParams(window.location.search);
        const paramValue = params.get('action');

        if (paramValue === 'emailExist') {
            Swal.fire({
                title: "Error",
                text: "Please Select a Role.",
                icon: "error",
                confirmButtonText: '<i class="fa-solid fa-thumbs-up"></i> Okay'
            }).then((result) => {
                document.getElementById('email').style.border = '1px solid red';
            })
        } else if (paramValue === 'errorAccountCreation') {
            Swal.fire({
                title: "Error",
                text: "An error occured. Please try again later!",
                icon: "error",

            })
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

</body>

</html>