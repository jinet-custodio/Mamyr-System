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
    <title>Mamyr - Events</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/events.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">
        <!-- Account Icon on the Left -->
        <ul class="navbar-nav">
            <?php
            $query = "SELECT userProfile FROM users WHERE userID = '$userID' AND userRole = '$userRole'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
                $imageData = $data['userProfile'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageData);
                finfo_close($finfo);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            ?>
            <li class="nav-item account-nav">
                <a href="Account/account.php">
                    <img src="<?= htmlspecialchars($image) ?>" alt="User Profile">
                </a>
            </li>


            <!-- Get notification -->
            <?php
            $getNotifications = $conn->prepare("SELECT * FROM notifications WHERE userID = ? AND is_read = 0");
            $getNotifications->bind_param("i", $userID);
            $getNotifications->execute();
            $getNotificationsResult = $getNotifications->get_result();
            if ($getNotificationsResult->num_rows > 0) {
                $counter = 0;
                $notificationsArray = [];
                $color = [];
                $notificationIDs = [];
                while ($notifications = $getNotificationsResult->fetch_assoc()) {
                    $is_readValue = $notifications['is_read'];
                    $notificationIDs[] = $notifications['notificationID'];
                    if ($is_readValue === 0) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "rgb(247, 213, 176, .5)";
                    } elseif ($is_readValue === 1) {
                        $notificationsArray[] = $notifications['message'];
                        $counter++;
                        $color[] = "white";
                    }
                }
            }
            ?>

            <div class="notification-container position-relative">
                <button type="button" class="btn position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <img src="../../Assets/Images/Icon/bell.png" alt="Notification Icon" class="notificationIcon">
                    <?php if (!empty($counter)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= htmlspecialchars($counter) ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>

        </ul>


        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"> -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"> HOME</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="amenities.php" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item active" href="#">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookNow.php">BOOK NOW</a>
                </li>
                <li class="nav-item">
                    <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">LOG OUT</a>
                </li>
            </ul>
        </div>
    </nav>


    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <?php if (!empty($notificationsArray)): ?>
                        <ul class="list-group list-group-flush ">
                            <?php foreach ($notificationsArray as $index => $message):
                                $bgColor = $color[$index];
                                $notificationID = $notificationIDs[$index];
                            ?>
                                <li class="list-group-item mb-2 notification-item" data-id="<?= htmlspecialchars($notificationID) ?>" style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgb(84, 87, 92, .5)">
                                    <?= htmlspecialchars($message) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-3 text-muted">No new notifications.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="titleContainer">
        <h4 class="title">EVENTS</h4>
        <p class="description">At Mamyr Resort and Events Place, we celebrate life’s most meaningful moments—weddings,
            birthdays, reunions, corporate events, and more—that can be celebrated in our Pavilion, which can occupy
            up to 350 guests, and our Mini Pavilion, perfect for more intimate gatherings of up to 50
            guests. Whether grand or small, each event is made memorable in a beautiful and comfortable setting
            designed to suit your occasion.
        </p>
    </div>

    <div class="categories">
        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/Images/amenities/pavilionPics/pav4.jpg" alt="Wedding Event">
            <div class="card-body">
                <h5 class="card-title">Wedding</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Celebrating love and lifelong memories at Mamyr Resort—where every wedding is a
                        dream come true!</p>
                </div>

                <button type="button" class="btn btn-primary" id="bookBtn" style="margin-top: auto;">BOOK NOW</button>
            </div>
        </div>

        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/images//EventsPhotos/debut.jpg" alt="Debut Event">
            <div class="card-body">
                <h5 class="card-title">Debut</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Celebrating a milestone at Mamyr Resort and Events Place—where every debut
                        marks a new chapter of unforgettable memories!</p>
                </div>
                <button type="button" class="btn btn-primary" style="margin-top: auto;">BOOK NOW</button>
            </div>
        </div>

        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/images//EventsPhotos/kidsParty.jpg" alt="Kids Party">
            <div class="card-body">
                <h5 class="card-title">Kids Party</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Creating magical moments at Mamyr Resort and Events Place—where every kids'
                        party is filled with joy, laughter, and unforgettable memories!</p>
                </div>
                <button type="button" class="btn btn-primary" style="margin-top: auto;">BOOK NOW</button>
            </div>
        </div>


        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/Images/EventsPhotos/birthday.jpg" alt="Birthday Event">
            <div class="card-body">
                <h5 class="card-title">Birthday</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Celebrating the joy of life at Mamyr Resort and Events Place—where every year
                        brings new moments to cherish!</p>
                </div>
                <button type="button" class="btn btn-primary" id="bookBtn" style="margin-top: auto;">BOOK NOW</button>


            </div>
        </div>

        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/images/EventsPhotos/christening.jpg" alt="Christening Event">
            <div class="card-body">
                <h5 class="card-title">Christening/Dedication</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Make lasting memories at Mamyr Resort where every celebration, from
                        christenings to dedications, is a moment to treasure.</p>
                </div>

                <button type="button" class="btn btn-primary" id="bookBtn" style="margin-top: auto;">BOOK NOW</button>


            </div>
        </div>

        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/images/EventsPhotos/teamBuilding.jpg" alt="Team Building Event">
            <div class="card-body">
                <h5 class="card-title">Team Building</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Creating great ideas and strong bonds at Mamyr Resort—where teamwork and
                        leadership thrive in inspiring surroundings!
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="bookBtn" style="margin-top: auto;">BOOK NOW</button>


            </div>
        </div>

        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/images/EventsPhotos/thanksgiving.jpg" alt="Thanksgiving Event">
            <div class="card-body">
                <h5 class="card-title">Thanksgiving Party</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Celebrating gratitude and togetherness at Mamyr Resort—where good food and
                        great company make every moment unforgettable!</p>
                </div>
                <button type="button" class="btn btn-primary" id="bookBtn" style="margin-top: auto;">BOOK NOW</button>


            </div>
        </div>

        <div class="card" style="width: 18rem; display: flex; flex-direction: column; height: 100%;">
            <img class="card-img-top" src="../../Assets/images/EventsPhotos/xmas.jpg" alt="Birthday Event">
            <div class="card-body">
                <h5 class="card-title">Christmas Party</h5>
                <div class="eventDescription">
                    <p class="eventDesc">Embracing the magic of the holidays at Mamyr Resort—where grand feasts and
                        unforgettable moments bring joy to all!</p>
                </div>
                <button type="button" class="btn btn-primary" id="bookBtn" style="margin-top: auto;">BOOK NOW</button>
            </div>
        </div>


    </div>



    <footer class="py-1" style="margin-top: 5vh;">
        <div class=" pb-1 mb-1 d-flex align-items-center justify-content-start">
            <a href="../index.php">
                <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
            </a>
            <h3 class="mb-0">MAMYR RESORT AND EVENTS PLACE</h3>
        </div>

        <div class="info">
            <div class="reservation">
                <h4 class="reservationTitle">Reservation</h4>
                <h4 class="numberFooter">(0998) 962 4697 </h4>
                <h4 class="emailAddressTextFooter">mamyr@gmail.com</h4>
            </div>
            <div class="locationFooter">
                <h4 class="locationTitle">Location</h4>
                <h4 class="addressTextFooter">Sitio Colonia, Gabihan, San Ildefonso, Bulacan</h4>

            </div>
        </div>
        <hr class="footerLine">
        <div class="socialIcons">
            <a href="https://www.facebook.com/p/Mamyr-Resort-Restaurant-Events-Place-100083298304476/"><i
                    class='bx bxl-facebook-circle'></i></a>
            <a href="https://workspace.google.com/intl/en-US/gmail/"><i class='bx bxl-gmail'></i></a>
            <a href="tel:+09989624697">
                <i class='bx bxs-phone'></i>
            </a>
        </div>

    </footer>
    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    <!-- Notification Ajax -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notificationID = this.dataset.id;

                    fetch('../../Function/notificationFunction.php', {
                            method: 'POST',
                            headers: {
                                'Content-type': 'application/x-www-form-urlencoded'
                            },
                            body: 'notificationID=' + encodeURIComponent(notificationID)
                        })
                        .then(response => response.text())
                        .then(data => {
                            this.style.backgroundColor = 'white';
                        });
                });
            });
        });
    </script>


    <!-- Scroll Nav BG -->
    <script src="../../Assets/JS/scrollNavbg.js"></script>
</body>

</html>