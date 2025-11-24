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


switch ($userRole) {
    case 3:
        $role = "Admin";
        break;
    default:
        $_SESSION['error'] = "Unauthorized Access eh!";
        session_destroy();
        header("Location: ../register.php");
        exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Website</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Admin/editWebsite/editWebsite.css" />
    <!-- icon libraries from font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>
    <div class="container-fluid">
        <div class="backButtonContainer">
            <a href="../../../Function/Admin/editWebsite/unsetEditMode.php" id="backBtn"><i class="fa-solid fa-arrow-left backButton" style="color: #121212;" id="emailBackArrow" alt="Back Button"></i></a>
        </div>

        <div class="titleContainer">
            <h2 class="pageTitle" id="title">Edit Website</h2>
            <i class="fa-sharp fa-regular fa-circle-question" style="color: #559cd3;display: none;cursor: pointer;"
                id="help-circle"></i>
        </div>


        <div class="pagesContainer" id="pagesContainer">
            <button class="btn btn-info" id="landingPage"><img
                    src="../../../Assets/Images/Icon/landing-page.png" alt="Landing Page" class="buttonIcon">Landing
                Page</button>

            <div class="dropdown">
                <button class="btn btn-info dropdown-toggle" type="button" id="amenitiesDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../../../Assets/Images/Icon/amenities.png" alt="Amenities" class="buttonIcon"> Amenities
                </button>
                <ul class="dropdown-menu" aria-labelledby="amenitiesDropdown">
                    <li><a class="dropdown-item" href="#" id="amenities">Resort Amenities</a></li>
                    <li><a class="dropdown-item" href="#" id="rates">Rates and Hotel Rooms</a></li>
                    <li><a class="dropdown-item" href="#" id="events">Events</a></li>
                </ul>
            </div>

            <button class="btn btn-info" id="blog"><img src="../../../Assets/Images/Icon/blog.png"
                    alt="Blog" class="buttonIcon">Blog</button>

            <button class="btn btn-info" id="about"><img src="../../../Assets/Images/Icon/about.png"
                    alt="About" class="buttonIcon">About</button>


            <div class="dropdown">
                <button class="btn btn-info dropdown-toggle" type="button" id="bookNowDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../../../Assets/Images/Icon/bookNow.png" alt="Book Now" class="buttonIcon"> Book Now
                </button>
                <ul class="dropdown-menu" aria-labelledby="amenitiesDropdown">
                    <li><a class="dropdown-item" href="#" id="resortBtn">Book Now - Resort</a></li>
                    <li><a class="dropdown-item" href="#" id="hotelBtn">Book Now - Hotel</a></li>
                    <li><a class="dropdown-item" href="#" id="eventBtn">Book Now - Events</a></li>
                </ul>
            </div>

            <button class="btn btn-info" id="footer"><img
                    src="../../../Assets/Images/Icon/footer.png" alt="Footer" class="buttonIcon">Footer</button>
        </div>
    </div>
    <!-- //* Landing Page iFrame -->
    <div class="container-fluid landingPage" id="landingPageContainer">
        <iframe src="../../../index.php?" class="editFrame" style="width: 100%; height: 100vh; display:none"></iframe>
    </div>
    <!-- //* About Page iFrame -->
    <div class="container-fluid aboutPage" id="aboutContainer">
        <iframe src="../../about.php" class="editFrame" style="width: 100%;  height: 100vh; display:none"></iframe>
    </div>

    <!-- //* Resort Book Now Page iFrame -->
    <div class="container-fluid resortPage" id="resortContainer">
        <iframe src="../../Customer/resortBooking.php" class="editFrame" style="width: 100%;  height: 100vh; display:none"></iframe>
    </div>
    <!-- //* Amenities Page iFrame -->
    <div class="container-fluid amenitiesPage" id="amenitiesContainer">
        <iframe src="../../amenities.php?" class="editFrame" style="width: 100%; height: 100vh; display:none"></iframe>
    </div>
    <!-- //* Rates and Hotel Rooms Page iFrame -->
    <div class="container-fluid ratesAndHotelsPage" id="ratesAndHotelContainer">
        <iframe src="../../ratesAndHotelRooms.php" class="editFrame" style="width: 100%; height: 100vh; display:none"></iframe>
    </div>
    <!-- //* Events Page iFrame -->
    <div class="container-fluid eventsPage" id="eventsContainer">
        <iframe src="../../events.php" class="editFrame" style="width: 100%; height: 100vh; display:none"></iframe>
    </div>
    <!-- //* Blog Page iFrame -->
    <div class="container-fluid blogPage" id="blogContainer">
        <button type="button" class="btn btn-primary" id="newBlogBtn" data-bs-toggle="modal" style="display:none"
            data-bs-target="#NewBlogPost">Add a New Blog Post</button>
        <iframe src="../../blog.php" class="editFrame" style="width: 100%; height: 100vh;  display:none"></iframe>
    </div>
    <!-- //* Footer Page iFrame -->
    <div class="container-fluid footerPage" id="footerContainer">
        <iframe src="../../footer.php" class="editFrame" style="width: 100%;  height: 100vh; display:none"></iframe>
    </div>


    <!-- //! MODAL FOR ADDING A NEW BLOG POST -->
    <!-- Modal -->
    <div class="modal fade" id="NewBlogPost" tabindex="-1" aria-labelledby="newBlogPost" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add a New Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="blogPostForm" enctype="multipart/form-data">
                        <div class="input-container mb-2">
                            <label for="eventName">Type of Event</label>
                            <input type="text" class="form-control" id="eventName" name="eventName" required>
                        </div>
                        <div class="input-container mb-2">
                            <label for="eventDate">Date</label>
                            <input type="date" class="form-control" id="eventDate" name="eventDate" required>
                        </div>
                        <div class="input-container mb-2">
                            <label for="eventTitle">Title/Header</label>
                            <input type="text" class="form-control" id="eventTitle" name="eventTitle" required>
                        </div>
                        <div class="input-container mb-2">
                            <label for="eventDesc">Event Description</label>
                            <textarea class="form-control" name="eventDesc" id="eventDesc" required></textarea>
                        </div>
                        <div class="input-container mb-2">
                            <label>Event Images</label>

                            <div id="imageInputs">
                                <input type="file" class="form-control mb-2" name="eventImages[]" accept="image/*" required>
                            </div>

                            <button type="button" id="addImageBtn" class="btn btn-outline-primary btn-sm mt-1">
                                + Add another image
                            </button>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- </form> -->

    <!-- Bootstrap Link -->
    <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script>
    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pagesContainer = document.getElementById("pagesContainer");
            const landingPageContainer = document.getElementById("landingPageContainer");
            const icon = document.getElementById("help-circle");
            const aboutContainer = document.getElementById("aboutContainer");
            const amenitiesContainer = document.getElementById("amenitiesContainer");
            const ratesAndHotelContainer = document.getElementById("ratesAndHotelContainer");
            const eventsContainer = document.getElementById("eventsContainer");
            const newBlogBtn = document.getElementById("newBlogBtn");
            const footerContainer = document.getElementById("footerContainer");
            const landingPageBtn = document.getElementById("landingPage");
            const aboutPageBtn = document.getElementById("about");
            const blogPageBtn = document.getElementById("blog");
            const bookNowPageBtn = document.getElementById("bookNow");
            const footerPageBtn = document.getElementById("footer");
            const amenitiesPageBtn = document.getElementById("amenities");
            const ratesPageBtn = document.getElementById("rates");
            const eventsPageBtn = document.getElementById("events");
            const resortPageBtn = document.getElementById("resortBtn");
            const resortContainer = document.getElementById("resortContainer");
            const hotelPageBtn = document.getElementById("hotelBtn");
            const eventPageBtn = document.getElementById("eventBtn");


            landingPageBtn.addEventListener('click', function() {
                hideAllContainers();
                landingPageContainer.style.display = "block";
                landingPageContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "Landing Page";
            });

            aboutPageBtn.addEventListener('click', function() {
                hideAllContainers();
                aboutContainer.style.display = "block";
                aboutContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "About Page";
            });

            amenitiesPageBtn.addEventListener('click', function() {
                hideAllContainers();
                amenitiesContainer.style.display = "block";
                amenitiesContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "Amenities Page";
            });

            ratesPageBtn.addEventListener('click', function() {
                hideAllContainers();
                ratesAndHotelContainer.style.display = "block";
                ratesAndHotelContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "Rates and Hotel Rooms Page";
            });

            eventsPageBtn.addEventListener('click', function() {
                hideAllContainers();
                eventsContainer.style.display = "block";
                eventsContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "Events Page";
            });

            blogPageBtn.addEventListener('click', function() {
                hideAllContainers();
                blogContainer.style.display = "block";
                blogContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "Blog Page";
                newBlogBtn.style.display = "block";
                newBlogBtn.style.position = "absolute";
            });

            footerPageBtn.addEventListener('click', function() {
                hideAllContainers();
                footerContainer.style.display = "block";
                footerContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "Website Footer";
            });
            resortPageBtn.addEventListener('click', function() {
                hideAllContainers();
                resortContainer.style.display = "block";
                resortContainer.querySelector("iframe").style.display = "block";
                icon.style.display = "block";
                document.getElementById("backBtn").href = "editWebsite.php";
                document.getElementById("title").innerHTML = "Book Now - Resort";
                newBlogBtn.style.position = "absolute";
            });


            function hideAllContainers() {
                landingPageContainer.style.display = "none";
                aboutContainer.style.display = "none";
                amenitiesContainer.style.display = "none";
                blogContainer.style.display = "none";
                pagesContainer.style.display = "none";
            }
        });
    </script>

    <!-- Sweetalert Popup -->
    <script>
        const icon = document.getElementById("help-circle");
        icon.addEventListener("click", function() {
            Swal.fire({
                title: "How it works",
                text: "Texts and images with red borders can be edited. Please click 'Save Changes' once you're satisfied with your edits.",
                icon: "info",
                confirmButtonText: "Got it!"
            });
        });
    </script>


    <script>
        document.getElementById("addImageBtn").addEventListener("click", function() {
            const container = document.getElementById("imageInputs");

            const input = document.createElement("input");
            input.type = "file";
            input.name = "eventImages[]";
            input.accept = "image/*";
            input.className = "form-control mb-2";
            container.appendChild(input);
        });

        document.getElementById("blogPostForm").addEventListener("submit", function(e) {
            e.preventDefault();

            let form = e.target;
            let formData = new FormData(form);

            fetch("../../../Function/Admin/editWebsite/addBlogPost.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    console.log("Raw response from PHP:", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                        console.log("Parsed JSON:", data);
                    } catch (e) {
                        console.error("JSON parse error:", e);
                        alert("PHP returned invalid JSON. Check console for full output.");
                        return;
                    }

                    if (data.success) {
                        const modalEl = document.getElementById("NewBlogPost");
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        // Clear form
                        form.reset();

                        // SweetAlert confirmation
                        Swal.fire({
                            title: "Post Successful",
                            text: "You have successfully added a new blog post!",
                            icon: "success",
                            confirmButtonText: "Okay"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // 🔁 Refresh the page after clicking "Okay"
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.message || "An unknown error occurred.",
                            icon: "error",
                            confirmButtonText: "Close"
                        });
                    }
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    alert("An unexpected error occurred while uploading the post.");
                });
        });
    </script>

</body>

</html>