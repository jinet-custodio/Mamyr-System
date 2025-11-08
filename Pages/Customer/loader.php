<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//fetch Business Logo
$sectionName = 'Logo';
$getLogo = $conn->prepare("SELECT resortInfoName FROM resortinfo WHERE resortInfoTitle = ? LIMIT 1");
$getLogo->bind_param("s", $sectionName);
$getLogo->execute();
$getLogoResult = $getLogo->get_result();
$baseURL = '';
if ($row = $getLogoResult->fetch_assoc()) {
    $logoFileName = $row['resortInfoName'];
} else {
    $logoFileName = 'no-picture.jpg';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
    <style>
        .loader {
            display: grid;
            position: absolute;
            z-index: 15;
        }

        #loaderOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(243, 243, 243, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* From Uiverse.io by adamgiebl */
        .dots-container {
            display: flex;
            align-items: center;
            flex-direction: row !important;
            margin-top: 0 !important;
            justify-content: center;
            height: 100%;
            width: 100%;
        }

        .dot {
            height: 20px;
            width: 20px;
            margin-right: 10px;
            border-radius: 10px;
            background-color: #b3d4fc;
            animation: pulse 1.5s infinite ease-in-out;
        }

        .dot:last-child {
            margin-right: 0;
        }

        .dot:nth-child(1) {
            animation-delay: -0.3s;
        }

        .dot:nth-child(2) {
            animation-delay: -0.1s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.1s;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.8);
                background-color: #b3d4fc;
                box-shadow: 0 0 0 0 rgba(178, 212, 252, 0.7);
            }

            50% {
                transform: scale(1.2);
                background-color: #6793fb;
                box-shadow: 0 0 0 10px rgba(178, 212, 252, 0);
            }

            100% {
                transform: scale(0.8);
                background-color: #b3d4fc;
                box-shadow: 0 0 0 0 rgba(178, 212, 252, 0.7);
            }
        }
    </style>
</head>

<body>
    <div id="loaderOverlay">
        <div class="loader">
            <img src="<?= $baseURL ?>/Assets/Images/<?= htmlspecialchars($logoFileName) ?>" alt="Business Logo" class="w-25 mx-auto mb-1">
            <section class="dots-container">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </section>
        </div>
    </div>

    <!-- Script for loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loaderOverlay = document.getElementById('loaderOverlay');
            const currentPath = window.location.pathname.replace(/\/+$/, '').toLowerCase(); // Normalize

            if (document.querySelectorAll('.navbar a')) {
                const navbarLinks = document.querySelectorAll('.navbar a');
                navbarLinks.forEach(link => {
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
            }


            if (document.querySelectorAll('.sidebar a')) {
                const sidebarLinks = document.querySelectorAll('.sidebar a');
                sidebarLinks.forEach(link => {
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
            }

            if (document.querySelectorAll('.loaderTrigger')) {
                const triggers = document.querySelectorAll('.loaderTrigger');
                triggers.forEach(trigger => {
                    trigger.addEventListener('click', function(e) {
                        loaderOverlay.style.display = 'flex';
                    })
                })
            }
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