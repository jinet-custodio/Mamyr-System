<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/new_account.css" />

</head>

<body>
    <!-- Side Bar -->
    <div class="sidebar">

        <div class="home">
            <a href="../dashboard.php"><img src="../../../Assets/Images/Icon/home2.png" alt="home icon"
                    class="homeIcon"></a>
        </div>
        <div class="sidebar-header">
            <h5>User Account</h5>
        </div>
        <ul class="list-group">
            <li>
                <a href="account.php" class="list-group-item active">
                    <img src="../../../Assets/Images/Icon/user.png" alt="Profile Information" class="sidebar-icon">
                    Profile Information
                </a>
            </li>

            <li>
                <a href="userManagement.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/bookingHistory.png" alt="Booking History"
                        class="sidebar-icon">
                    Booking History
                </a>
            </li>

            <li>
                <a href="loginSecurity.php" class="list-group-item">
                    <img src="../../../Assets/Images/Icon/login_security.png" alt="Login Security" class="sidebar-icon">
                    Login & Security
                </a>
            </li>


            <a href="deleteAccount.php" class="list-group-item">
                <img src="../../../Assets/Images/Icon/delete-user.png" alt="Delete Account" class="sidebar-icon">
                Delete Account
            </a>
            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>
    <!-- End Side Bar -->


    <!-- Customer Information Container -->
    <div class="customer-account-container">
        <form action="#" method="POST">
            <div class="card">
                <div class="account-info">
                    <!-- <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
                    <input type="hidden" name="userRole" value="<?= htmlspecialchars($userRole) ?>"> -->
                    <div class="profile-image">
                        <img src="../../../Assets/Images/defaultProfile.png" alt="Default Profile Picture"
                            class="profile-pic">
                        <button type="button" class="changePfpBtn btn btn-primary" id="changePfp" onclick="changePfp()">
                            Change Profile
                        </button>
                        <!-- Profile Picture Modal -->
                        <div class="modal" id="picModal" tabindex="-1" aria-labelledby="picModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="picModalLabel">Change Profile Picture</h5>
                                        <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="../../../Assets/Images/defaultProfile.png"
                                            alt="Default Profile Picture" id="preview" class="profile-pic">
                                        <input type="file" name="profilePic" id="profilePic" hidden>
                                        <label for="profilePic"
                                            class="custom-file-button btn btn-outline-primary">Choose Image</label>
                                    </div>
                                    <div class="modal-button">
                                        <button type="submit" class="btn btn-danger" name="cancelPfp">Cancel</button>
                                        <button type="submit" class="btn btn-success" name="changePfpBtn">Save
                                            Changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-info">
                        <h5 class="account-name">Louise</h5>
                        <h6 class="account-contact"> 09238220044</h6>
                        <h6 class="roleName">Customer</h6>
                    </div>
                </div>

        </form>
        <form action="#" method="POST">
            <div class="customer-details">
                <!-- <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>">
                <input type="hidden" name="userRole" value="<?= htmlspecialchars($userRole) ?>"> -->
                <div class="info">
                    <input type="text" name="fullName" id="fullName" value="Louise Anne S. Bartolome" disabled required>
                    <label for="fullName">Full Name</label>
                </div>
                <div class="info">
                    <input type="<?= htmlspecialchars($type) ?>" name="birthday" id="birthday" value="January 27, 2004"
                        disabled>
                    <label for="birthday">Birthday</label>
                </div>
                <div class="info">
                    <input type="text" name="address" id="address"
                        value="243 E. Viudez St. Poblacion, San Ildefonso, Bulacan" disabled required>
                    <label for="address">Address</label>
                </div>
                <div class="info">
                    <input type="text" name="phoneNumber" id="phoneNumber" value="09238220044" disabled required>
                    <label for="phoneNumber">Phone Number</label>
                </div>
            </div>
            <div class="button-container">
                <button type="button" class="edit btn btn-primary" name="changeDetails" id="editBtn"
                    onclick="enableEditing()">Edit</button>
                <button type="submit" name="cancelChanges" id="cancelBtn" class="change-info btn btn-danger"
                    style="display: none;">Cancel</button>
                <button type="submit" name="saveChanges" id="saveBtn" class="change-info btn btn-primary"
                    style="display: none;">Save</button>
            </div>
        </form>
    </div>


    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>
    //Show the image preview
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
    //Show Modal
    document.addEventListener("DOMContentLoaded", function() {
        const changeBtn = document.getElementById("changePfp");
        const modalElement = document.getElementById("picModal");

        changeBtn.addEventListener("click", function() {
            const myModal = new bootstrap.Modal(modalElement);
            myModal.show();
        });
    });
    </script>

</body>

</html>