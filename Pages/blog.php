<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../Config/dbcon.php'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Blog</title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/blog.css">
    <!-- <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css"> -->
    <!-- link for online bootstrap CDN  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- icon libraries from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>


<body>
    <div class="wrapper">
        <nav class="navbar navbar-expand-lg fixed-top" id="navbar-half2" style="background-color: white;">
            <button class=" navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-10">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"> Home</a>
                    </li>
                    <li class="nav-item dropdown text-center">
                        <a class="nav-link  dropdown-toggle " href="#" id="navbarDropdown"
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
            $getWebContent = "SELECT * FROM websitecontents WHERE sectionName = 'Blog'";
            $result = mysqli_query($conn, $getWebContent);

            $contentMap = [];
            $blogPosts = [];
            $imagesByContentID = [];

            $getImagesQuery = "SELECT contentID, imageData, altText FROM websitecontentimages ORDER BY imageOrder ASC";
            $imageResult = mysqli_query($conn, $getImagesQuery);

            if ($imageResult && mysqli_num_rows($imageResult) > 0) {
                while ($imgRow = mysqli_fetch_assoc($imageResult)) {
                    $cid = $imgRow['contentID'];
                    $imagesByContentID[$cid][] = $imgRow;
                }
            }

            while ($row = mysqli_fetch_assoc($result)) {
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
                <h4 class="title" id="maintext"><?= htmlspecialchars($contentMap['MainTitle'] ?? 'Main Title Not Found') ?></h4>
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
                                    $featuredAlt = $imagesByContentID[$featuredContentID][0]['altText'] ?? 'Blog image';
                                    $finfo = finfo_open();
                                    $mimeType = finfo_buffer($finfo, $imgData, FILEINFO_MIME_TYPE);
                                    finfo_close($finfo);

                                    $featuredImage = base64_encode($imgData);
                                    echo "<img src='data:$mimeType;base64,$featuredImage' alt='" . htmlspecialchars($featuredAlt) . "' />";
                                } else {
                                    echo "<img src='../Assets/Images/no-picture.jpg' alt='Default blog image'>";
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
                                        <p> <?= htmlspecialchars($firstPost['Content'] ?? '') ?> </p>
                                    </div>
                                    <button id="featuredReadmore" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalFeatured">
                                        Read More
                                    </button>

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
                                        echo "<img src='../Assets/Images/no-picture.jpg' alt='Default blog image'>";
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
                                    <button class="btn btn-primary mt-3 othersReadmore" style="display:flex;align-self:flex-end;text-align:center" data-bs-toggle="modal" data-bs-target="#modal<?= htmlspecialchars($postID) ?>">
                                        Read More
                                    </button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Modal for featured post -->
                    <div class="modal fade" id="modalFeatured" tabindex="-1" aria-labelledby="modalFeaturedLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header py-4">
                                    <h5 class="modal-title" id="modalFeaturedLabel">
                                        <?= htmlspecialchars($firstPost['EventHeader'] ?? 'Blog Post') ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">

                                    <?php

                                    $featuredContentID = $firstPost['contentID'] ?? null;
                                    $featuredImage = '../../Assets/Images/no-picture.jpg'; // default fallback
                                    $featuredAlt = 'Blog image';

                                    if ($featuredContentID && isset($imagesByContentID[$featuredContentID][0])) {
                                        $imgData = $imagesByContentID[$featuredContentID][0]['imageData'];
                                        $featuredAlt = $imagesByContentID[$featuredContentID][0]['altText'] ?? 'Blog image';
                                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                        $mimeType = finfo_buffer($finfo, $imgData);
                                        finfo_close($finfo);
                                        $featuredImage = 'data:' . $mimeType . ';base64,' . base64_encode($imgData);
                                    }
                                    ?>

                                    <img src="<?= htmlspecialchars($featuredImage) ?>" alt="<?= htmlspecialchars($featuredAlt) ?>" class="img-fluid mb-3" />
                                    <?php if (isset($firstPost['EventType'], $firstPost['EventDate'])): ?>
                                        <p class="text-muted">
                                            <?= htmlspecialchars($firstPost['EventType']) ?> • <?= htmlspecialchars(date("j F Y", strtotime($firstPost['EventDate']))) ?>
                                        </p>

                                    <?php endif; ?>
                                    <div class="blog-full-content">
                                        <?= nl2br(htmlspecialchars($firstPost['Content'] ?? '')) ?>
                                    </div>
                                         <div class="modal-footer">
                                            <button type="button" class="btn btn-primary bookNowBtn">Book Now</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Modal for other posts -->
                    <?php foreach ($blogPosts as $postID => $post): ?>
                        <?php
                        $contentID = $post['contentID'] ?? null;
                        $image = null;
                        $alt = 'Blog image';

                        if ($contentID && isset($imagesByContentID[$contentID][0])) {
                            $imgData = $imagesByContentID[$contentID][0]['imageData'];
                            $alt = $imagesByContentID[$contentID][0]['altText'] ?? 'Blog image';
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mimeType = finfo_buffer($finfo, $imgData);
                            finfo_close($finfo);
                            $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($imgData);
                        } else {
                            $base64Image = '../../Assets/Images/no-picture.jpg'; // fallback
                        }
                        ?>

                        <div class="modal fade" id="modal<?= htmlspecialchars($postID) ?>" tabindex="-1" aria-labelledby="modalLabel<?= htmlspecialchars($postID) ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel<?= htmlspecialchars($postID) ?>">
                                            <?= htmlspecialchars($post['EventHeader'] ?? 'Blog Post') ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <!-- Blog Image -->
                                        <img src="<?= htmlspecialchars($base64Image) ?>" alt="<?= htmlspecialchars($alt) ?>" class="img-fluid mb-3" />
                                        <!-- Event Info -->
                                        <?php if (isset($post['EventType'], $post['EventDate'])): ?>
                                            <p class="text-muted">
                                                <?= htmlspecialchars($post['EventType']) ?> • <?= htmlspecialchars(date("j F Y", strtotime($post['EventDate']))) ?>
                                            </p>
                                        <?php endif; ?>
                                        <!-- Full Content -->
                                        <div class="blog-full-content">
                                            <?= nl2br(htmlspecialchars($post['Content'] ?? '')) ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary bookNowBtn">Book Now</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

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
    </div>
    <!-- <script src="../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../Assets/JS/scrollNavbg.js"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Redirects User to Book Now -->
    <script>
        const bookNowBtns = document.querySelectorAll('.bookNowBtn');
        
        bookNowBtns.forEach(bookNowBtn => {
            bookNowBtn.addEventListener("click", function(e) {
                window.location.href = "/Pages/register.php"
            });
        });     
    </script>
</body>

</html>