<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Type - Sign Up</title>
    <link rel="shortcut icon" href="../Assets/Images/Icon/favicon.png" type="image/x-icon">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../Assets/CSS/userType.css">
    <!-- online stylesheet link for bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- icon libraries for font-awesome and box icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

</head>

<body>


    <div class="backArrowContainer" id="backArrowContainer">
        <a href="register.php"><img src="../Assets/Images/Icon/arrowBtnBlue.png" alt="Back Button" class="backArrow"> </a>
    </div>

    <div class="titleContainer">
        <h3 class="title">Welcome to Mamyr Resort and Events Place!</h3>
        <h5 class="subtitle">I am signing up as:</h5>
    </div>

    <div class="container">



        <a href="register.php?page=register" id="partner-link" class="categoryLink">
            <div class="card category-card ">
                <img class="card-img-top" src="../Assets/Images/UserTypePhotos/customer.png" alt="Partners">

                <div class="category-body m-auto">
                    <h5 class="category-title m-auto">Customer</h5>
                    <p class="card-text">I am interested in making bookings and viewing the amenities of the resort.
                    </p>
                </div>
            </div>
        </a>

        <a href="busPartnerRegister.php" id="request-link" class="categoryLink">
            <div class="card category-card ">
                <img class="card-img-top" src="../Assets/Images/UserTypePhotos/businessPartner.png"
                    alt="Business Partner">

                <div class="category-body m-auto">
                    <h5 class="category-title m-auto">Business Partner</h5>
                    <p class="card-text"> I want to request for a partnership to offer my services to customers of
                        the resort.</p>
                </div>
            </div>
        </a>




    </div>

    <!-- Div for loader -->
    <div id="loaderOverlay" style="display: none;">
        <div class="loader"></div>
    </div>

    </script>
    <!-- Bootstrap JS -->
    <!-- <script src="../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>


    <!-- Script for loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderOverlay = document.getElementById('loaderOverlay');
            const currentPath = window.location.pathname.replace(/\/+$/, '').toLowerCase(); // Normalize

            const categoryLinks = document.querySelectorAll('.categoryLink');

            categoryLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = link.getAttribute('href');

                    if (href && !href.startsWith('#')) {
                        // Create a temporary anchor to parse the href
                        const tempAnchor = document.createElement('a');
                        tempAnchor.href = href;
                        const targetPath = tempAnchor.pathname.replace(/\/+$/, '').toLowerCase();

                        // If the target is different from the current path, show loader
                        if (targetPath !== currentPath) {
                            loaderOverlay.style.display = 'flex';
                        }
                    }
                });
            });
        });

        function hideLoader() {
            const overlay = document.getElementById('loaderOverlay');
            if (overlay) overlay.style.display = 'none';
        }

        // Hide loader on normal load
        window.addEventListener('load', hideLoader);

        // Hide loader on back/forward navigation (from browser cache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });
    </script>

</body>

</html>