<?php
require '../Config/dbcon.php';
session_start();
$userID = 1;
$_SESSION['userID'] =  $userID;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/account.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top">

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <img src="../Assets/Images/Icon/home2.png" alt="home icon">
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <img src="../Assets/Images/Icon/notification.png" alt="home icon">
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <img src="../Assets/Images/Icon/setting.png" alt="home icon">
                    </a>
                </li>
                </li>
            </ul>
        </div>
    </nav>

    <aside>
        <div class="headerText">
            <h2>My Account</h2>
        </div>
        <?php
        if (isset($userID)) {
            $query = "SELECT * FROM users WHERE userID= '$userID'";
            $run_query = mysqli_query($conn, $query);
            if (mysqli_num_rows($run_query) > 0) {
                $user = mysqli_fetch_array($run_query);

                // Convert birthday to ISO format
                $dateStr = $user['birthDate']; // e.g., "04/07/2021"
                $dateObj = DateTime::createFromFormat('d/m/Y', $dateStr);
                $isoDate = $dateObj ? $dateObj->format('Y-m-d') : '';
        ?>
                <form name="form" id="myForm" class="userInfo" action="../Function/editAccount.php" method="POST"
                    enctype="multipart/form-data">
                    <div class="contents">
                        <input type="hidden" value="<?= htmlspecialchars($user['userTypeID']) ?>" name="userType">
                        <input type="hidden" value="<?= htmlspecialchars($user['userID'])  ?>" name="userID">

                        <div class="profile-image">
                            <?php
                            $imgSrc = '../Assets/Images/userProfile/no pfp.png';
                            if (!empty($user['userProfile'])) {
                                $imgData = base64_encode($user['userProfile']);
                                $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                            }
                            ?>
                            <img src="<?= $imgSrc ?>" alt="User Image" class="user-image" id="displayPhoto">


                            <button class="add-image hidden" id="btn" type="button">
                                <img src="../Assets/Images/Icon/camera.png" alt="Camera" class="camera" id="camera">
                            </button>
                            <input type="file" id="fileInput" style="display: none;" class="text-field"
                                accept=".jpg, .jpeg, .png" name="userProfile">
                            <div class="details">
                                <input type="text" id="nameBox" name="name" class="text-field"
                                    value="<?= $user['firstName'] . ' ' . $user['middleInitial'] . '. ' . $user['lastName'] ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="information">
                            <div class="details">
                                <img src="../Assets/Images/Icon/email.png" alt="email icon">
                                <input type="email" name="email" class="text-field" value="<?= $user['email'] ?>" readonly>
                            </div>
                            <div class="details">
                                <img src="../Assets/Images/Icon/phone.png" alt="phone icon">
                                <input type="tel" name="phoneNumber" class="text-field" value="<?= $user['phoneNumber'] ?>"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'');" pattern="[0-9]{11}"
                                    placeholder="Click 'Edit' to add (optional)" readonly>
                            </div>
                            <div class="details">
                                <img src="../Assets/Images/Icon/address.png" alt="address icon">
                                <input type="text" name="userAddress" class="text-field" value="<?= $user['userAddress'] ?>"
                                    readonly>
                            </div>
                            <div class="details">
                                <img src="../Assets/Images/Icon/birthday.png" alt="birthday icon">
                                <input type="date" name="birthDate" id="birthDate" class="text-field"
                                    value="<?= htmlspecialchars($user['birthDate'])  ?>" readonly>
                            </div>
                        </div>

                        <div class="editBtn">
                            <button class="btn btn-primary" id="editBtn" type="submit" onclick="enableEdit(event)"
                                name="edit">Edit your information</button>
                        </div>
                    </div>
                </form>

                <script>
                    function enableEdit(event) {
                        const form = document.getElementById('myForm');
                        const inputs = form.querySelectorAll('.text-field');
                        const editButton = document.getElementById('editBtn');
                        const imageBtn = document.getElementById('btn'); // Your image edit button
                        const fileInput = document.getElementById('fileInput');
                        const displayPhoto = document.getElementById('displayPhoto');

                        const isEditing = editButton.textContent === "Edit your information";

                        if (isEditing) {
                            // Enable editing
                            inputs.forEach(input => {
                                input.removeAttribute('readonly');
                                input.classList.add('edit-mode');
                            });

                            // Show the add-image button
                            imageBtn.classList.remove('hidden'); // instead of setting style

                            // Show the file input and handle image changes
                            imageBtn.addEventListener('click', function(e) {
                                e.preventDefault(); // Prevent any default behavior from clicking the button
                                fileInput.click();
                            });

                            fileInput.addEventListener('change', (event) => {
                                const file = event.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        displayPhoto.src = e.target.result; // Preview the uploaded image
                                    };
                                    reader.readAsDataURL(file);
                                }
                            });

                            // Change button text to "Save Changes"
                            editButton.textContent = "Save Changes";
                            event.preventDefault();
                        } else {
                            // If we're not in editing mode, submit the form
                            form.submit();
                        }
                    }
                </script>


        <?php
            } else {
                echo "<h4>Invalid ID</h4>";
            }
        } // End of isset($userID)
        ?>

    </aside>



    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>


</body>

</html>