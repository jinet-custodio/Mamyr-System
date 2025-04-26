<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .carousel-container {
            position: relative;
            width: 80%;
            margin: auto;
            overflow: hidden;
        }

        .carousel-track-container {
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .carousel-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .carousel-img {
            width: 100%;
            min-width: 100%;
            object-fit: cover;
        }

        /* Buttons */
        .prev-btn,
        .next-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
        }

        .prev-btn {
            left: 10px;
        }

        .next-btn {
            right: 10px;
        }
    </style>


</head>

<body>
    <div class="pavilion" style="background-color: #7dcbf2; height: 155vh;">
        <div class="pavilionTitleContainer" style="padding-top: 2vw;">
            <hr class="pavilionLine">
            <h4 class="pavilionTitle">Pavilion Hall</h4>
            <p class="pavilionDescription">
                Our Pavilion Hall offers the perfect space for events, gatherings, and special occasions...
            </p>
        </div>

        <div class="carousel-container">
            <button class="btn btn-primary prev-btn" id="prevBtn">&#10094;</button>

            <div class="carousel-track-container">
                <div class="carousel-track" id="carouselTrack">
                    <img src="../Assets/Images/amenities/pavilionPics/pav1.jpg" alt="Pavilion Picture 1" class="carousel-img">
                    <img src="../Assets/Images/amenities/pavilionPics/pav2.jpg" alt="Pavilion Picture 2" class="carousel-img">
                    <img src="../Assets/Images/amenities/pavilionPics/pav3.jpg" alt="Pavilion Picture 3" class="carousel-img">
                    <img src="../Assets/Images/amenities/pavilionPics/pav4.jpg" alt="Pavilion Picture 4" class="carousel-img">
                    <img src="../Assets/Images/amenities/pavilionPics/pav5.jpg" alt="Pavilion Picture 5" class="carousel-img">
                </div>
            </div>

            <button class="btn btn-primary next-btn" id="nextBtn">&#10095;</button>
        </div>
    </div>



    <script>
        const track = document.getElementById('carouselTrack');
        const prevButton = document.getElementById('prevBtn');
        const nextButton = document.getElementById('nextBtn');

        const images = document.querySelectorAll('.carousel-img');
        const imageWidth = images[0].clientWidth;

        let currentIndex = 0;

        nextButton.addEventListener('click', () => {
            if (currentIndex < images.length - 1) {
                currentIndex++;
                track.style.transform = `translateX(-${imageWidth * currentIndex}px)`;
            }
        });

        prevButton.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                track.style.transform = `translateX(-${imageWidth * currentIndex}px)`;
            }
        });
    </script>

</body>

</html>