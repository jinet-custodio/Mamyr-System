<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place </title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/Customer/account.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <!-- <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css"> -->
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top">

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <img src="../../Assets/Images/Icon/home2.png" alt="home icon">
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <img src="../../Assets/Images/Icon/notification.png" alt="home icon">
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <img src="../../Assets/Images/Icon/setting.png" alt="home icon">
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
                <form name="form" id="myForm" class="userInfo" action="../../Function/editAccount.php" method="POST"
                    enctype="multipart/form-data">
                    <div class="contents">
                        <input type="hidden" value="<?= htmlspecialchars($user['userRole']) ?>" name="userRole">
                        <input type="hidden" value="<?= htmlspecialchars($user['userID'])  ?>" name="userID">

                        <div class="profile-image">
                            <?php
                            $imgSrc = '../../Assets/Images/userProfile/no pfp.png';
                            if (!empty($user['userProfile'])) {
                                $imgData = base64_encode($user['userProfile']);
                                $imgSrc = 'data:image/jpeg;base64,' . $imgData;
                            }
                            ?>
                            <img src="<?= $imgSrc ?>" alt="User Image" class="user-image" id="displayPhoto">


                            <button class="add-image hidden" id="btn" type="button">
                                <img src="../../Assets/Images/Icon/camera.png" alt="Camera" class="camera" id="camera">
                            </button>
                            <input type="file" id="fileInput" style="display: none;" class="text-field"
                                accept=".jpg, .jpeg, .png" name="userProfile">
                            <div class="details">
                                <input type="text" id="nameBox" name="name" class="text-field"
                                    value="<?= $user['firstName'] . ' ' . $user['middleInitial'] . ' ' . $user['lastName'] ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="information">
                            <div class="details">
                                <img src="../../Assets/Images/Icon/email.png" alt="email icon">
                                <input type="email" name="email" class="text-field" value="<?= $user['email'] ?>" readonly>
                            </div>
                            <div class="details">
                                <img src="../../Assets/Images/Icon/phone.png" alt="phone icon">
                                <input type="tel" name="phoneNumber" class="text-field" value="<?= $user['phoneNumber'] ?>"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'');" pattern="[0-9]{11}"
                                    placeholder="Click 'Edit' to add (optional)" readonly>
                            </div>
                            <div class="details">
                                <img src="../../Assets/Images/Icon/address.png" alt="address icon">
                                <input type="text" name="userAddress" class="text-field" value="<?= $user['userAddress'] ?>"
                                    readonly>
                            </div>
                            <div class="details">
                                <img src="../../Assets/Images/Icon/birthday.png" alt="birthday icon">
                                <input type="date" name="birthDate" id="birthDate" class="text-field"
                                    value="<?= htmlspecialchars($user['birthDate'])  ?>" readonly>
                            </div>
                        </div>

                        <div class="editBtn">
                            <button class="btn btn-primary w-75" id="editBtn" type="submit" onclick="enableEdit(event)"
                                name="edit">Edit</button>
                        </div>
                    </div>
                </form>


        <?php
            } else {
                echo "<h4>Invalid ID</h4>";
            }
        }
        ?>

    </aside>
    <main>
        <div class="bookings">
            <table class="table table-striped " id="bookingTable">

                <thead>
                    <th scope="col">Check In Date</th>
                    <th scope="col">Check Out Date</th>
                    <th scope="col">Booking Type</th>
                    <th scope="col">Status</th>
                    <th scope="col">Payment</th>
                    <th scope="col">Review</th>
                </thead>
                <tbody>
                    <!-- Select booking info -->
                    <?php
                    $selectQuery = "SELECT ps.PBName, rs.RScategoryID , ec.categoryName, b.* 
                FROM bookings b
                LEFT JOIN allservices a ON b.packageServiceID = a.packageServiceID
                LEFT JOIN packages p ON a.packageID = p.packageID
                LEFT JOIN eventcategories ec ON p.PcategoryID = ec.categoryID
                LEFT JOIN services s ON a.serviceID = s.serviceID
                LEFT JOIN resortservices rs ON s.resortServiceID = rs.resortServiceID
                LEFT JOIN partnershipservices ps ON s.partnershipServiceID = ps.partnershipServiceID
                WHERE userID = '$userID'";
                    $result = mysqli_query($conn, $selectQuery);
                    if (mysqli_num_rows($result) > 0) {
                        foreach ($result as $bookings) {
                            $bookingID = $bookings['bookingID'];
                    ?>
                            <tr>
                                <td><?= $bookings['startDate'] ?></td>
                                <td><?= $bookings['endDate'] ?></td>
                                <?php
                                if ($bookings['serviceID'] != "") {
                                ?>
                                    <td><?= $bookings['category'] ?></td>
                                <?php
                                } elseif ($bookings['packageID'] != "") {
                                ?>
                                    <td><?= $bookings['categoryName'] ?></td>
                                <?php
                                } elseif ($bookings['customePackageID'] != "") {
                                ?>
                                    <td>Customized Package</td>
                                <?php
                                }
                                ?>

                                <td>
                                    <?php
                                    if ($bookings['status'] == "Pending") {
                                    ?>
                                        <button class="btn btn-warning w-75">
                                            <?= $bookings['status'] ?>
                                        </button>
                                    <?php
                                    } elseif ($bookings['status'] == "Approved") {
                                    ?>
                                        <button class="btn btn-success w-75">
                                            <?= $bookings['status'] ?>
                                        </button>
                                    <?php
                                    } elseif ($bookings['status'] == "Cancelled") {
                                    ?>
                                        <button class="btn btn-danger w-75">
                                            <?= $bookings['status'] ?>
                                        </button>
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="" class="btn btn-primary">Pay</a>
                                </td>
                                <td>
                                    <a href="" class="btn btn-outline-primary"> Rate</a>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    <?php
                    } else {
                    ?>
                        <td colspan="6">
                            <h5 scope="row" class="text-center">No record Found!</h5>
                        </td>
                    <?php
                    } ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- <script src="../../Assets/JS/datatables.min.js"></script> -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <script>
        function enableEdit(event) {
            const form = document.getElementById('myForm');
            const inputs = form.querySelectorAll('.text-field');
            const editButton = document.getElementById('editBtn');
            const imageBtn = document.getElementById('btn'); // Your image edit button
            const fileInput = document.getElementById('fileInput');
            const displayPhoto = document.getElementById('displayPhoto');

            const isEditing = editButton.textContent === "Edit";

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
    <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('#bookingTable').DataTable();
        });
    </script> -->

</body>

</html>