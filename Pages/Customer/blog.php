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
//for setting image paths in 'include' statements
$baseURL = '../..';

switch ($userRole) {
    case 1: //customer
        $role = "Customer";
        break;
    case 2:
        $role = "Business Partner";
        break;
    case 4:
        $role = "Partnership Applicant";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

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
require '../../Function/notification.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Blog</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../../Assets/CSS/blog.css">
    <link rel="stylesheet" href="../../Assets/CSS/navbar.css">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <!-- icon libraries from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>


<body>
    <div class="wrapper">
        <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2" style="background-color: white;">
            <!-- Account Icon on the Left -->
            <ul class="navbar-nav navbar-nav d-flex flex-row align-items-center" id="profileAndNotif">
                <?php
                $getProfile = $conn->prepare("SELECT userProfile FROM user WHERE userID = ? AND userRole = ?");
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
                    <a href="../Account/account.php">
                        <img src="<?= htmlspecialchars($image) ?>" alt="User Profile" class="profile-pic">
                    </a>
                </li>



                <!-- Get notification -->
                <?php

                if ($userRole === 1 || $userRole === 4) {
                    $receiver = 'Customer';
                } elseif ($userRole === 2) {
                    $receiver = 'Partner';
                }

                $notifications = getNotification($conn, $userID, $receiver);
                $counter = $notifications['count'];
                $notificationsArray = $notifications['messages'];
                $color = $notifications['colors'];
                $notificationIDs = $notifications['ids'];
                ?>

                <div class="notification-container position-relative">
                    <button type="button" class="btn position-relative" data-bs-toggle="modal"
                        data-bs-target="#notificationModal">
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
                <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                    <li class="nav-item">
                        <?php if ($userRole !== 2): ?>
                        <a class="nav-link" href="dashboard.php"> Home</a>
                        <?php else: ?>
                        <a class="nav-link" href="../BusinessPartner/bpDashboard.php"> Home</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Amenities
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="amenities.php">Resort Amenities</a></li>
                            <li><a class="dropdown-item" href="ratesAndHotelRooms.php">Rates and Hotel Rooms</a></li>
                            <li><a class="dropdown-item" href="events.php">Events</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Blog</a>
                    </li>
                    <?php if ($userRole !== 2): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="beOurPartner.php">Be Our Partner</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookNow.php">Book Now</a>
                    </li>
                    <li class="nav-item">
                        <a href="../../Function/logout.php" class="btn btn-outline-danger" id="logOutBtn">Log Out</a>
                    </li>
                </ul>
            </div>
        </nav>


        <!-- Notification Modal -->
        <?php include '../notificationModal.php' ?>

        <main>
            <?php
            $getWebContent = "SELECT * FROM websitecontent WHERE sectionName = 'Blog'";
            $result = mysqli_query($conn, $getWebContent);

            $contentMap = [];
            $blogPosts = [];
            $imageMap = [];

            $getImagesQuery = "SELECT contentID, imageData, altText FROM websitecontentimage ORDER BY imageOrder ASC";
            $imageResult = mysqli_query($conn, $getImagesQuery);

            if ($imageResult && mysqli_num_rows($imageResult) > 0) {
                while ($imgRow = mysqli_fetch_assoc($imageResult)) {
                    $imageMap[$imgRow['contentID']] = $imgRow;
                }
            }

            while ($row = mysqli_fetch_assoc($result)) {
                $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));
                $contentMap[$cleanTitle] = $row['content'];

                if (preg_match('/^BlogPost(\d+)-(.*)$/', $cleanTitle, $matches)) {
                    $postNumber = 'BlogPost' . $matches[1];
                    $field = $matches[2];
                    $blogPosts[$postNumber][$field] = $row['content'];
                    $blogPosts[$postNumber]['contentID'] = $row['contentID'];
                }
            }

            // Sort posts by date (newest first)
            uasort($blogPosts, function ($a, $b) {
                $dateA = isset($a['EventDate']) ? $a['EventDate'] : '0000-00-00';
                $dateB = isset($b['EventDate']) ? $b['EventDate'] : '0000-00-00';
                return strcmp($dateB, $dateA);
            });


            $defaultImage = ".../../Assets/Images/no-picture.jpg";
            ?>

            <div class="titleContainer">
                <h4 class="titlemain" id="maintext">
                    <?= htmlspecialchars($contentMap['MainTitle'] ?? 'Main Title Not Found') ?>
                </h4>
                <h4><?= htmlspecialchars($contentMap['Sub-title'] ?? '') ?></h4>
            </div>

            <div class="blogmain">
                <div class="title">
                    <h5>Recent Blog Posts</h5>
                </div>

                <div class="posts">
                    <?php
                    $defaultImage = "../../Assets/Images/no-picture.jpg";
                    $index = 0;
                    ?>

                    <?php foreach ($blogPosts as $postID => $post): ?>
                    <?php
                        $contentID = $post['contentID'] ?? null;
                        $imagePath = $defaultImage;
                        $altText = 'Blog image';

                        $lookupKey = null;
                        if (!empty($post['contentID']) && isset($imageMap[$post['contentID']])) {
                            $lookupKey = $post['contentID'];
                        } elseif (isset($imageMap[$postID])) {
                            $lookupKey = $postID;
                        }

                        if ($lookupKey !== null) {
                            $imageFile = $imageMap[$lookupKey]['imageData'];
                            $tempPath = "../../Assets/Images/blogposts/" . $imageFile;
                            if (file_exists($tempPath)) {
                                $imagePath = $tempPath;
                            }
                            $altText = $imageMap[$lookupKey]['altText'] ?? 'Blog image';
                        }
                        ?>

                    <?php if ($index === 0): ?>
                    <div class="featured">
                        <div class="featuredpost">
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($altText) ?>"
                                class="img-fluid" />

                            <div class="desc">
                                <?php if (!empty($post['EventType']) && !empty($post['EventDate'])): ?>
                                <p class="eventType text-muted">
                                    <?= htmlspecialchars($post['EventType']) ?> •
                                    <?= htmlspecialchars(date("j F Y", strtotime($post['EventDate']))) ?>
                                </p>
                                <?php endif; ?>

                                <div class="blogHeading">
                                    <h4><?= htmlspecialchars($post['EventHeader'] ?? '') ?></h4>
                                </div>
                                <div class="blogDescription">
                                    <p><?= htmlspecialchars($post['Content'] ?? '') ?></p>
                                </div>

                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modal<?= htmlspecialchars($postID) ?>">
                                    Read More
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php if ($index === 1): ?>
                    <div class="others">
                        <?php endif; ?>

                        <div class="post row align-items-start mb-3">
                            <div class="col-md-5 othersImg">
                                <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($altText) ?>"
                                    class="img-fluid" />
                            </div>
                            <div class="col-md-7 othersDesc">
                                <?php if (!empty($post['EventType']) && !empty($post['EventDate'])): ?>
                                <p class="othersEventType text-muted">
                                    <?= htmlspecialchars($post['EventType']) ?> •
                                    <?= htmlspecialchars(date("j F Y", strtotime($post['EventDate']))) ?>
                                </p>
                                <?php endif; ?>

                                <div class="othersHeading">
                                    <h4><?= htmlspecialchars($post['EventHeader'] ?? '') ?></h4>
                                </div>
                                <div class="othersDescription">
                                    <p><?= htmlspecialchars($post['Content'] ?? '') ?></p>
                                </div>

                                <button class="btn btn-primary mb-3 othersReadmore" data-bs-toggle="modal"
                                    data-bs-target="#modal<?= htmlspecialchars($postID) ?>">
                                    Read More
                                </button>
                            </div>
                        </div>

                        <?php if ($index === count($blogPosts) - 1): ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- MODAL for each post -->
                    <div class="modal fade" id="modal<?= htmlspecialchars($postID) ?>" tabindex="-1"
                        aria-labelledby="modalLabel<?= htmlspecialchars($postID) ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel<?= htmlspecialchars($postID) ?>">
                                        <?= htmlspecialchars($post['EventHeader'] ?? 'Blog Post') ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <img src="<?= htmlspecialchars($imagePath) ?>"
                                        alt="<?= htmlspecialchars($altText) ?>" class="img-fluid mb-3" />

                                    <?php if (!empty($post['EventType']) && !empty($post['EventDate'])): ?>
                                    <p class="text-muted">
                                        <?= htmlspecialchars($post['EventType']) ?> •
                                        <?= htmlspecialchars(date("j F Y", strtotime($post['EventDate']))) ?>
                                    </p>
                                    <?php endif; ?>

                                    <div class="blog-full-content">
                                        <?= nl2br(htmlspecialchars($post['Content'] ?? '')) ?>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary bookNowBtn">Book Now</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php $index++; ?>
                    <?php endforeach; ?>
                </div>
            </div>

        </main>

        <?php include 'footer.php';
        include 'loader.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
    <script src="../../Assets/JS/scrollNavbg.js"></script>



    <!-- Notification Ajax -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const badge = document.querySelector('.notification-container .badge');

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

                        this.style.transition = 'background-color 0.3s ease';
                        this.style.backgroundColor = 'white';


                        if (badge) {
                            let currentCount = parseInt(badge.textContent, 10);

                            if (currentCount > 1) {
                                badge.textContent = currentCount - 1;
                            } else {
                                badge.remove();
                            }
                        }
                    });
            });
        });
    });
    </script>


</body>

</html>