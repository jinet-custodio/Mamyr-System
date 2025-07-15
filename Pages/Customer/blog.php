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
    <title>Mamyr - About</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/blog.css">
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
            $getProfile = $conn->prepare("SELECT userProfile FROM users WHERE userID = ? AND userRole = ?");
            $getProfile->bind_param("ii", $userID, $userRole);
            $getProfile->execute();
            $getProfileResult = $getProfile->get_result();
            if ($getProfileResult->num_rows > 0) {
                $data = $getProfileResult->fetch_assoc();
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

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item active" href="amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="partnerApplication.php">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="bookNow.php">BOOK NOW</a>
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

    <main>
        <?php
        $sectionName = 'Blog';
        $getWebContent = $conn->prepare("SELECT * FROM websiteContents WHERE sectionName = ?");
        $getWebContent->bind_param("s", $sectionName);
        $getWebContent->execute();
        $getWebContentResult = $getWebContent->get_result();

        $contentMap = [];
        $blogPosts = [];
        $imagesByContentID = [];

        $getImagesQuery = $conn->prepare("SELECT contentID, imageData, altText FROM websiteContentImages ORDER BY imageOrder ASC");
        $getImagesQuery->execute();
        $getImagesQueryResult = $getImagesQuery->get_result();
        if ($getImagesQueryResult && $getImagesQueryResult->num_rows > 0) {
            while ($imgRow = $getImagesQueryResult->fetch_assoc()) {
                $cid = $imgRow['contentID'];
                $imagesByContentID[$cid][] = $imgRow;
            }
        }

        while ($row = $getWebContentResult->fetch_assoc()) {
            $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
            $contentID = $row['contentID'];

            $contentMap[$cleanTitle] = $row['content'];

            if (preg_match('/^BlogPost(\d+)-(.*)$/', $cleanTitle, $matches)) {
                $postNumber = 'BlogPost' . $matches[1];
                $field = $matches[2];

                $blogPosts[$postNumber][$field] = $row['content'];

                if (!isset($blogPosts[$postNumber]['contentID'])) {
                    $blogPosts[$postNumber]['contentID'] = $contentID;
                }
            }
        }



        uasort($blogPosts, function ($a, $b) {
            $dateA = isset($a['EventDate']) ? $a['EventDate'] : '0000-00-00';
            $dateB = isset($b['EventDate']) ? $b['EventDate'] : '0000-00-00';
            return strcmp($dateB, $dateA);
        });


        $firstPost = reset($blogPosts);
        ?>


        <div class="titleContainer">
            <h4 class="title"><?= htmlspecialchars($contentMap['MainTitle'] ?? 'Main Title Not Found') ?></h4>
            <h4><?= htmlspecialchars($contentMap['Sub-title'] ?? '') ?></h4>
        </div>

        <div class="blogmain">
            <div class="title">
                <h5>Recent blog posts</h5>
            </div>

            <div class="posts">
                <!-- Featured Post -->
                <?php if (!empty($firstPost)): ?>
                    <div class="featured">
                        <div class="featuredpost">
                            <?php
                            $featuredContentID = $firstPost['contentID'] ?? null;

                            if ($featuredContentID && isset($imagesByContentID[$featuredContentID])) {
                                $imgData = $imagesByContentID[$featuredContentID][0]['imageData'];
                                $altText = $imagesByContentID[$featuredContentID][0]['altText'] ?? 'Blog image';
                                $finfo = finfo_open();
                                $mimeType = finfo_buffer($finfo, $imgData, FILEINFO_MIME_TYPE);
                                finfo_close($finfo);

                                $base64Image = base64_encode($imgData);
                                echo "<img src='data:$mimeType;base64,$base64Image' alt='" . htmlspecialchars($altText) . "' />";
                            } else {
                                echo "<img src='../../Assets/Images/no-picture.jpg' alt='Default blog image'>";
                            }
                            ?>


                            <div class="desc">
                                <div class="eventType">
                                    <?php if (isset($firstPost['EventType'], $firstPost['EventDate'])): ?>
                                        <p style="color: rgb(43, 43, 43);">
                                            <?= htmlspecialchars($firstPost['EventType']) ?> •
                                            <?= htmlspecialchars(date("j F Y", strtotime($firstPost['EventDate']))) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="blogHeading">
                                    <?php if (isset($firstPost['EventHeader'])): ?>
                                        <h4><?= htmlspecialchars($firstPost['EventHeader']) ?></h4>
                                    <?php endif; ?>
                                </div>
                                <div class="blogDescription">
                                    <?= htmlspecialchars($firstPost['Content'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Other Posts -->
                <div class="others container">
                    <?php
                    $isFirst = true;
                    foreach ($blogPosts as $postID => $post) {
                        if ($isFirst) {
                            $isFirst = false;
                            continue;
                        }

                        $contentID = $post['contentID'] ?? null;
                    ?>
                        <div class="post row">
                            <div class="othersImg col-md-5">
                                <?php
                                if ($contentID && isset($imagesByContentID[$contentID])) {
                                    $imgData = $imagesByContentID[$contentID][0]['imageData'];
                                    $finfo = finfo_open();
                                    $mimeType = finfo_buffer($finfo, $imgData, FILEINFO_MIME_TYPE);
                                    $base64Image = base64_encode($imgData);
                                    echo "<img src='data:$mimeType;base64,$base64Image' alt='" . htmlspecialchars($altText) . "' />";
                                } else {
                                    echo "<img src='../../Assets/Images/no-picture.jpg' alt='Default blog image'>";
                                }
                                ?>
                            </div>
                            <div class="othersDesc col-md-7">
                                <div class="othersEventType">
                                    <?php if (isset($post['EventType'], $post['EventDate'])): ?>
                                        <p style="color: rgb(43, 43, 43);">
                                            <?= htmlspecialchars($post['EventType']) ?> •
                                            <?= htmlspecialchars(date("j F Y", strtotime($post['EventDate']))) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="othersHeading">
                                    <?php if (isset($post['EventHeader'])): ?>
                                        <h4><?= htmlspecialchars($post['EventHeader']) ?></h4>
                                    <?php endif; ?>
                                </div>
                                <div class="othersDescription">
                                    <?= htmlspecialchars($post['Content'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-1" style="margin-top: 5vw !important;">
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
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../../Assets/JS/scrollNavbg.js"></script>



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

</body>

</html>