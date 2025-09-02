<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: /Pages/register.php?");
    exit();
}

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

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: /Pages/register.php?session=expired");
    exit();
}


$_SESSION['edit_mode'] = true;
$_SESSION['last_activity'] = time();
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Website</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Admin/editWebsite/landingPageEdit.css" />
    <!-- icon libraries from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <div class="container-fluid">
        <div class="backButtonContainer">
            <a href="../adminDashboard.php" id="backBtn"><img src="../../../Assets/Images/Icon/back-button.png"
                    alt="Back Button" class="backBtn"></a>
        </div>

        <div class="titleContainer">
            <h2 class="pageTitle" id="title">Edit Website</h2>
            <i class="fa-sharp fa-regular fa-circle-question" style="color: #559cd3;display: none;cursor: pointer;"
                id="help-circle"></i>
        </div>


        <div class="pagesContainer" id="pagesContainer">
            <button class="btn btn-info" id="landingPage" onclick="landingPage()"><img
                    src="../../../Assets/Images/Icon/landing-page.png" alt="Landing Page" class="buttonIcon">Landing
                Page</button>

            <div class="dropdown">
                <button class="btn btn-info dropdown-toggle" type="button" id="amenitiesDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../../../Assets/Images/Icon/amenities.png" alt="Amenities" class="buttonIcon"> Amenities
                </button>
                <ul class="dropdown-menu" aria-labelledby="amenitiesDropdown">
                    <li><a class="dropdown-item" href="#" onclick="amenities()">Resort Amenities</a></li>
                    <li><a class="dropdown-item" href="#" onclick="ratesAndHotel()">Rates and Hotel Rooms</a></li>
                    <li><a class="dropdown-item" href="#" onclick="events()">Events</a></li>
                </ul>
            </div>

            <button class="btn btn-info" id="blog" onclick="blog()"><img src="../../../Assets/Images/Icon/blog.png"
                    alt="Blog" class="buttonIcon">Blog</button>

            <button class="btn btn-info" id="about" onclick="about()"><img src="../../../Assets/Images/Icon/about.png"
                    alt="About" class="buttonIcon">About</button>

            <button class="btn btn-info" id="bookNow" onclick="bookNow()"><img
                    src="../../../Assets/Images/Icon/bookNow.png" alt="Book Now" class="buttonIcon">Book Now</button>

            <button class="btn btn-info" id="footer" onclick="footer()"><img
                    src="../../../Assets/Images/Icon/footer.png" alt="Footer" class="buttonIcon">Footer</button>
        </div>
    </div>

    <div class="container-fluid landingPage" id="landingPageContainer">
        <iframe src="../../../index.php?" class="editFrame" style="width: 100%; height: 100vh; "></iframe>
    </div>
    <div class="container-fluid aboutPage" id="aboutContainer">
        <iframe src="../../about.php" class="editFrame" style="width: 100%;  height: 100vh; "></iframe>
    </div>
    <div class="container-fluid amenitiesPage" id="amenitiesContainer">
        <iframe src="../../amenities.php?" class="editFrame" style="width: 100%; height: 100vh;"></iframe>
    </div>
    <div class="container-fluid blogPage" id="blogContainer">
        <iframe src="../../blog.php" class="editFrame" style="width: 100%; height: 100vh;"></iframe>
    </div>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const pagesContainer = document.getElementById("pagesContainer");
    const landingPageContainer = document.getElementById("landingPageContainer");
    const icon = document.getElementById("help-circle");
    const aboutContainer = document.getElementById("aboutContainer");
    const amenitiesContainer = document.getElementById("amenitiesContainer");

    landingPageContainer.style.display = "none";
    aboutContainer.style.display = "none";
    amenitiesContainer.style.display = "none";
    blogContainer.style.display = "none";

    function landingPage() {
        hideAllContainers();
        landingPageContainer.style.display = "block";
        landingPageContainer.querySelector("iframe").style.display = "block";
        icon.style.display = "block";
        pagesContainer.style.display = "none";
        document.getElementById("backBtn").href = "editWebsite.php?pages=pagesContainer";
        document.getElementById("title").innerHTML = "Landing Page";
    }

    function about() {
        hideAllContainers();
        aboutContainer.style.display = "block";
        aboutContainer.querySelector("iframe").style.display = "block";
        icon.style.display = "block";
        pagesContainer.style.display = "none";
        document.getElementById("backBtn").href = "editWebsite.php?pages=pagesContainer";
        document.getElementById("title").innerHTML = "About Page";
    }

    function amenities() {
        hideAllContainers();
        amenitiesContainer.style.display = "block";
        amenitiesContainer.querySelector("iframe").style.display = "block";
        icon.style.display = "block";
        pagesContainer.style.display = "none";
        document.getElementById("backBtn").href = "editWebsite.php?pages=pagesContainer";
        document.getElementById("title").innerHTML = "Amenities Page";
    }

    function blog() {
        hideAllContainers();
        blogContainer.style.display = "block";
        blogContainer.querySelector("iframe").style.display = "block";
        icon.style.display = "block";
        pagesContainer.style.display = "none";
        document.getElementById("backBtn").href = "editWebsite.php?pages=pagesContainer";
        document.getElementById("title").innerHTML = "Blog Page";
    }


    function hideAllContainers() {
        landingPageContainer.style.display = "none";
        aboutContainer.style.display = "none";
        amenitiesContainer.style.display = "none";
        blogContainer.style.display = "none";

        landingPageContainer.querySelector("iframe").style.display = "none";
        aboutContainer.querySelector("iframe").style.display = "none";
        amenitiesContainer.querySelector("iframe").style.display = "none";
        blogContainer.querySelector("iframe").style.display = "none";
    }
    </script>

    <!-- Sweetalert Popup -->
    <script>
    icon.addEventListener("click", function() {
        Swal.fire({
            title: "How it works",
            text: "Texts iand images with red borders can be edited. Please click 'Save Changes' once you're satisfied with your edits.",
            icon: "info",
            confirmButtonText: "Got it!"
        });
    });
    </script>

</body>

</html>