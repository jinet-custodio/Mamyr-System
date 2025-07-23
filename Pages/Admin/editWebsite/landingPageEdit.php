<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Website</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Admin/editWebsite/landingPageEdit.css" />

</head>

<body>
    <div class="container-fluid">
        <div class="backButtonContainer">
            <a href="../adminDashboard.php" id="backBtn"><img src="../../../Assets/Images/Icon/back-button.png"
                    alt="Back Button" class="backBtn"></a>
        </div>

        <div class="titleContainer">
            <h2 class="pageTitle" id="title">Edit Website</h2>

            <button type="submit" class="btn btn-primary" id="saveButton" style="display: none;">Save Changes</button>
        </div>


        <div class="pagesContainer" id="pagesContainer">
            <button class="btn btn-info" id="landingPage" onclick="landingPage()"><img
                    src="../../../Assets/Images/Icon/landing-page.png" alt="Landing Page" class="buttonIcon">Landing
                Page</button>

            <button class="btn btn-info" id="amenities" onclick="amenities()"><img
                    src="../../../Assets/Images/Icon/amenities.png" alt="Amenities"
                    class="buttonIcon">Amenities</button>

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
        <iframe src="../../../index.php" id="editFrame" style="width: 1280px; height:768px"></iframe>
    </div>








    <script>
    const pagesContainer = document.getElementById("pagesContainer")
    const landingPageContainer = document.getElementById("landingPageContainer")
    const saveButton = document.getElementById("saveButton")

    landingPageContainer.style.display = "none"

    function landingPage() {
        if (landingPageContainer.style.display == "none") {
            landingPageContainer.style.display = "block";
            saveButton.style.display = "block"
            pagesContainer.style.display = "none";
            document.getElementById("backBtn").href = "landingPageEdit.php?pages=pagesContainer"
            document.getElementById("title").innerHTML = "Landing Page"

        } else {
            landingPageContainer.style.display = "block";
        }
    }
    </script>




    <!-- <script>
    const iframe = document.getElementById('editFrame');

    iframe.onload = () => {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

        // Enable editing within the iframe
        iframeDoc.body.contentEditable = true;
        iframeDoc.designMode = "on";

        // Add a "Change Image" button for each image element
        const images = iframeDoc.getElementsByTagName('img');
        Array.from(images).forEach(img => {
            // Create a button element
            const button = iframeDoc.createElement('button');
            button.style.position = 'absolute';
            button.style.top = '50'
            button.style.left = '50'
            button.style.zIndex = '10';

            // Position the button on top of the image
            const rect = img.getBoundingClientRect();
            button.style.top = `${rect.top + window.scrollY}px`;
            button.style.left = `${rect.left + window.scrollX}px`;

            // Event listener to handle image change
            button.addEventListener('click', () => {
                const input = iframeDoc.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';

                input.addEventListener('change', (e) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(e.target.files[0]);
                });

                // Trigger file input click to allow image selection
                input.click();
            });

            // Append the button to the iframe document
            iframeDoc.body.appendChild(button);
        });
    };
    </script> -->





    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</body>

</html>