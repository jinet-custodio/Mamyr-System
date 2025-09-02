<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
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

</head>

<body>
    <a href="userManagement.php" class="back-button btn"><img src="../../Assets/Images/Icon/arrow.png" alt=""></a>
    <div class="wrapper">
        <form action="../../Function/Account/addUser.php" method="POST" enctype="multipart/form-data">
            <div class="card">
                <h5 class="card-title">Create New Account</h5>
                <div class="card-body">
                    <div class="profile-pic">
                        <img src="../../Assets/Images/defaultProfile.png" alt="" class="profile-image" id="preview">
                        <input type="file" name="profile-image" id="profile-image" hidden>
                        <label for="profile-image" class="uploadPfpBtn btn btn-primary">Upload Profile</label>
                    </div>
                    <div class="information-container">
                        <div class="input-container form-floating fullwidth">
                            <select class="form-select" id="roleSelect" name="roleSelect" aria-label="Floating label select example" required>
                                <option value="" selected disabled>Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="partner">Business Partner</option>
                                <option value="customer">Customer</option>
                            </select>
                            <label for="roleSelect">Role</label>
                        </div>
                        <div class="name-container fullwidth">
                            <div class="input-container form-floating">
                                <input type="text" class="form-control" required name="firstName" id="firstName">
                                <label for="firstName">First Name</label>
                            </div>
                            <div class="input-container form-floating">
                                <input type="text" class="form-control" name="middleInitial" id="middleInitial">
                                <label for="middleInitial">M.I.</label>
                            </div>
                            <div class="input-container form-floating">
                                <input type="text" class="form-control" required name="lastName" id="lastName">
                                <label for="lastName">Last Name</label>
                            </div>
                        </div>
                        <div class="input-container form-floating fullwidth">
                            <input type="text" class="form-control" name="address" id="address">
                            <label for="address">Address</label>
                        </div>
                        <div class="input-container form-floating">
                            <input type="text" class="form-control" name="phoneNumber" id="phoneNumber" required>
                            <label for="phoneNumber">Phone Number</label>
                        </div>
                        <div class="input-container form-floating">
                            <input type="date" class="form-control" name="birthday" id="birthday">
                            <label for="birthday">Birthday</label>
                        </div>
                        <div class="input-container form-floating">
                            <input type="email" class="form-control" name="email" id="email" required>
                            <label for="email">Email</label>
                        </div>
                        <div class="input-container form-floating">
                            <input type="password" class="form-control" name="password" id="password" oninput="heckCreateAccountPassword();" required>
                            <label for="password">Password</label>
                            <div class="errorMsg">
                                <div class="confirmErrorMsg" id="passwordValidation"></div>
                            </div>
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
        const paramValue = params.get('status');

        if (paramValue === "invalidRole") {
            Swal.fire({
                title: "Error",
                text: "Please Select a Role.",
                icon: "info"
            });
        }

        if (paramValue) {
            const url = new URL(window.location);
            url.search = '';
            history.replaceState({}, document.title, url.toString());
        }
    </script>

</body>

</html>