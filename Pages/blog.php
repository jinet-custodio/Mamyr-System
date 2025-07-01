<?php
require '../Config/dbcon.php'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Blog</title>
    <link rel="icon" type="image/x-icon" href="../assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/blog.css">
    <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>


<body>
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2">
        <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-10">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"> Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link  dropdown-toggle " href=" ../Pages/amenities.php" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        AMENITIES
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="../Pages/amenities.php">RESORT AMENITIES</a></li>
                        <li><a class="dropdown-item" href="ratesAndHotelRooms.php">RATES AND HOTEL ROOMS</a></li>
                        <li><a class="dropdown-item" href="../Pages/events.php">EVENTS</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">BLOG</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Pages/busPartnerRegister.php" id="bopNav">BE OUR PARTNER</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="./about.php">ABOUT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">BOOK NOW</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Sign Up</a>
                </li>
            </ul>
        </div>
    </nav>

    <main>
        <?php
        $getWebContent = "SELECT * FROM websiteContents WHERE sectionName = 'Blog'";
        $result = mysqli_query($conn, $getWebContent);

        $contentMap = [];
        $blogPosts = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cleanTitle = trim(preg_replace('/\s+/', '', $row['title']));

                $contentMap[$cleanTitle] = $row['content'];

                if (preg_match('/^BlogPost(\d+)-(.*)$/', $cleanTitle, $matches)) {
                    $postNumber = 'BlogPost' . $matches[1];
                    $field = $matches[2];
                    $blogPosts[$postNumber][$field] = $row['content'];
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
                            <img src="../Assets/Images/blogposts/image.png" alt="">
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
                    ?>
                        <div class="post row">
                            <div class="othersImg col-md-5">
                                <img src="../Assets/Images/blogposts/img2.png" alt="<?= htmlspecialchars($post['EventHeader'] ?? '') ?>">
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
                <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
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
    <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
    <script src="../Assets/JS/scrollNavbg.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sweet Alert -->
    <!-- <script>
        const bookButtons = document.querySelectorAll('#bopNav');

        bookButtons.forEach(button => {
            button.addEventListener('click', function() {
                Swal.fire({
                    title: 'Want to Become Our Business Partner?',
                    text: 'You must have an existing account before becoming a business partner.',
                    icon: 'info',
                    confirmButtonText: 'Sign Up'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'register.php';
                    }
                });
            });
        });
    </script> -->
</body>

</html>