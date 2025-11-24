<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../Config/dbcon.php';
//for setting image paths in 'include' statements
$baseURL = '..';
session_start();

//for edit website, this will enable edit mode from the iframe
$editMode = isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Blog</title>
    <link rel="icon" type="image/x-icon" href="../Assets/Images/Icon/favicon.png ">
    <link rel="stylesheet" href="../Assets/CSS/blog.css">
    <link rel="stylesheet" href="../Assets/CSS/navbar.css">
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
        <?php if (!$editMode): ?>
            <nav class="navbar navbar-expand-lg fixed-top" id="navbar" style="background-color: white;">
                <a href="../index.php"><img src="../Assets/Images/MamyrLogo.png" alt="Mamyr Resort Logo" class="logoNav"></a>
                <button class=" navbar-toggler ms-auto collapsed" id="bg-nav-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto me-10" id="toggledNav">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php"> Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
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
                        <li class="nav-item">
                            <a class="nav-link" href="beOurPartnerNew.php" id="bopNav">Be Our Partner</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Book Now</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="signUpBtn" href="register.php">Log In</a>
                        </li>
                    </ul>
                </div>
            </nav>
        <?php endif; ?>
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
                    $imageMap[$imgRow['contentID']][] = $imgRow;
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


            $defaultImage = "../Assets/Images/no-picture.jpg";
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
                    $defaultImage = "../Assets/Images/no-picture.jpg";
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
                        if ($lookupKey !== null && isset($imageMap[$lookupKey][0])) {

                            $firstImage = $imageMap[$lookupKey][0];

                            $imageFile = $firstImage['imageData'];
                            $tempPath = "../Assets/Images/blogposts/" . $imageFile;

                            echo "<!-- Debug: imagePath = $tempPath -->";

                            if (file_exists($tempPath)) {
                                $imagePath = $tempPath;
                            }

                            $altText = $firstImage['altText'] ?? 'Blog image';
                        }

                        ?>

                        <?php if ($index === 0): ?>
                            <!-- FEATURED POST (LEFT SIDE) -->
                            <div class="featured">
                                <?php
                                $images = $imageMap[$post['contentID']] ?? [];
                                $imageCount = count($images);
                                ?>

                                <div class="fb-gallery">
                                    <?php if ($imageCount === 1): ?>

                                        <!-- 1 IMAGE -->
                                        <img src="../Assets/Images/blogposts/<?= htmlspecialchars($images[0]['imageData']) ?>"
                                            class="fb-img fb-img-1" />

                                    <?php elseif ($imageCount === 2): ?>

                                        <!-- 2 IMAGES -->
                                        <div class="fb-grid fb-grid-2">
                                            <?php foreach ($images as $img): ?>
                                                <img src="../Assets/Images/blogposts/<?= htmlspecialchars($img['imageData']) ?>"
                                                    class="fb-img img-fluid" />
                                            <?php endforeach; ?>
                                        </div>

                                    <?php elseif ($imageCount === 3): ?>

                                        <!-- 3 IMAGES -->
                                        <div class="fb-grid fb-grid-3">
                                            <?php foreach ($images as $img): ?>
                                                <img src="../Assets/Images/blogposts/<?= htmlspecialchars($img['imageData']) ?>"
                                                    class="fb-img" />
                                            <?php endforeach; ?>
                                        </div>

                                    <?php elseif ($imageCount >= 4): ?>

                                        <!-- 4+ IMAGES WITH OVERLAY -->
                                        <div class="fb-grid fb-grid-3">
                                            <!-- First 3 images -->
                                            <?php for ($i = 0; $i < 3; $i++): ?>
                                                <img src="../Assets/Images/blogposts/<?= htmlspecialchars($images[$i]['imageData']) ?>"
                                                    class="fb-img" />
                                            <?php endfor; ?>

                                            <!-- 4th tile with overlay -->
                                            <div class="fb-more-wrapper">
                                                <img src="../Assets/Images/blogposts/<?= htmlspecialchars($images[3]['imageData']) ?>"
                                                    class="fb-img fb-img-more" />
                                                <div class="fb-more-overlay">
                                                    +<?= $imageCount - 3 ?>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>
                                </div>


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

                                    <button class="btn btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal<?= htmlspecialchars($postID) ?>"
                                        id="featuredReadmore">
                                        Read More
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if ($index === 1): ?>
                                <div class="others">
                                <?php endif; ?>

                                <div class="post row align-items-start mb-3">
                                    <div class="col-md-5 othersImg">

                                        <?php
                                        $images = $imageMap[$post['contentID']] ?? [];
                                        $imageCount = count($images);
                                        ?>

                                        <div class="fb-gallery small-fb-gallery">

                                            <?php if ($imageCount === 1): ?>

                                                <!-- 1 IMAGE -->
                                                <img src="../Assets/Images/blogposts/<?= htmlspecialchars($images[0]['imageData']) ?>"
                                                    class="fb-img fb-img-1" />

                                            <?php elseif ($imageCount === 2): ?>

                                                <!-- 2 IMAGES -->
                                                <div class="fb-grid fb-grid-2">
                                                    <?php foreach ($images as $img): ?>
                                                        <img src="../Assets/Images/blogposts/<?= htmlspecialchars($img['imageData']) ?>"
                                                            class="fb-img img-fluid" />
                                                    <?php endforeach; ?>
                                                </div>

                                            <?php elseif ($imageCount === 3): ?>

                                                <!-- 3 IMAGES -->
                                                <div class="fb-grid fb-grid-3">
                                                    <?php foreach ($images as $img): ?>
                                                        <img src="../Assets/Images/blogposts/<?= htmlspecialchars($img['imageData']) ?>"
                                                            class="fb-img" />
                                                    <?php endforeach; ?>
                                                </div>

                                            <?php elseif ($imageCount >= 4): ?>

                                                <!-- 4+ IMAGES WITH OVERLAY -->
                                                <div class="fb-grid fb-grid-3">

                                                    <!-- First 3 images -->
                                                    <?php for ($i = 0; $i < 3; $i++): ?>
                                                        <img src="../Assets/Images/blogposts/<?= htmlspecialchars($images[$i]['imageData']) ?>"
                                                            class="fb-img" />
                                                    <?php endfor; ?>

                                                    <!-- Overflow tile -->
                                                    <div class="fb-more-wrapper">
                                                        <img src="../Assets/Images/blogposts/<?= htmlspecialchars($images[3]['imageData']) ?>"
                                                            class="fb-img fb-img-more" />
                                                        <div class="fb-more-overlay">
                                                            +<?= $imageCount - 3 ?>
                                                        </div>
                                                    </div>

                                                </div>

                                            <?php else: ?>

                                                <!-- NO IMAGES -->
                                                <img src="../Assets/Images/no-picture.jpg" class="img-fluid" />

                                            <?php endif; ?>

                                        </div>

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

                                        <button class="btn btn-primary mb-3 othersReadmore"
                                            data-bs-toggle="modal"
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
                                    </div>

                                    <div class="modal-body">
                                        <!-- EVENT TYPE + DATE -->
                                        <?php if (!empty($post['EventType']) && !empty($post['EventDate'])): ?>
                                            <p class="text-muted">
                                                <?= htmlspecialchars($post['EventType']) ?> •
                                                <?= htmlspecialchars(date("j F Y", strtotime($post['EventDate']))) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="blog-full-content mb-4">
                                            <?= nl2br(htmlspecialchars($post['Content'] ?? '')) ?>
                                        </div>
                                        <?php if (!empty($imageMap[$post['contentID']])): ?>
                                            <div class="additional-images">
                                                <?php foreach ($imageMap[$post['contentID']] as $img): ?>
                                                    <img src="../Assets/Images/blogposts/<?= htmlspecialchars($img['imageData']) ?>"
                                                        alt="<?= htmlspecialchars($img['altText']) ?>"
                                                        class="img-fluid mb-3" />
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                    </div>

                                    <div class="modal-footer">
                                        <?php if (!$editMode): ?>
                                            <button type="button" class="btn btn-primary bookNowBtn w-25">Book Now</button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php $index++; ?>
                    <?php endforeach; ?>
                </div>
            </div>

        </main>



        <?php if (!$editMode) {
            include 'footer.php';
            include '../Pages/Customer/loader.php';
        } else {
            include '../Pages/Customer/loader.php';
        } ?>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
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