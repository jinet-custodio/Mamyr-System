<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- Data Table Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/services.css" />

</head>

<body id="servicesBody">
    <div class="container-fluid">

        <div class="headerContainer">
            <div class="backArrowContainer">
                <a href="adminDashboard.php" id="backButton"><img src="../../Assets/Images/icon/back-button.png"
                        alt="Back Arrow" class="backArrow"></a>
            </div>
            <h2 class="header text-center" id="headerText">Services</h2>
        </div>

        <section id="serviceCategories">
            <a href="#" id="resort-link" class="categoryLink" onclick="resort()">
                <div class="card category-card resort-category" style="width: 18rem; ">
                    <img class="card-img-top" src="../../Assets/images/amenities/poolPics/poolPic3.jpg"
                        alt="Wedding Event">

                    <div class="category-body">
                        <h5 class="category-title">RESORT</h5>
                    </div>
                </div>
            </a>

            <a href="#" id="hotel-link" class="categoryLink" onclick="hotel()">
                <div class="card category-card hotel-category" style="width: 18rem; ">
                    <img class="card-img-top" src="../../Assets/images/amenities/hotelPics/hotel1.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">HOTEL</h5>
                    </div>
                </div>
            </a>

            <a href="#" id="event-link" class="categoryLink" onclick="eventCategory()">
                <div class="card category-card event-category" style="width: 18rem; ">
                    <img class="card-img-top" src="../../Assets/images/amenities/pavilionPics/pav4.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">EVENT</h5>
                    </div>
                </div>
            </a>

            <a href="#" id="catering-link" class="categoryLink" onclick="catering()">
                <div class="card category-card event-category" style="width: 18rem; ">
                    <img class="card-img-top" src="../../Assets/images//BookNowPhotos/foodCoverImg2.jpg"
                        alt="Wedding Event">
                    <div class="category-body">
                        <h5 class="category-title">CATERING</h5>
                    </div>
                </div>
            </a>
        </section>

        <div class="resortContainer" id="resortContainer">
            <table class=" table table-striped" id="resortServices">
                <thead>
                    <th scope="col">Service Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td> <select id="resortAvailability" name="resortAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserrved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>
                        </td>
                        <td class="d-flex">
                            <button class="btn btn-primary">Edit</button>
                            <button class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="hotelContainer" id="hotelContainer">
            <table class=" table table-striped" id="resortServices">
                <thead>
                    <th scope="col">Service Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td> <select id="resortAvailability" name="resortAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserrved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>
                        </td>
                        <td class="d-flex">
                            <button class="btn btn-primary">Edit</button>
                            <button class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="eventContainer" id="eventContainer">
            <table class=" table table-striped" id="resortServices">
                <thead>
                    <th scope="col">Service Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Capacity</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Description</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td> <select id="resortAvailability" name="resortAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="occupied" id="available">Occupied</option>
                                <option value="reserved" id="reserrved">Reserved</option>
                                <option value="maintenance" id="maintenance">Maintenance</option>
                            </select>
                        </td>
                        <td class="d-flex">
                            <button class="btn btn-primary">Edit</button>
                            <button class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="cateringContainer" id="cateringContainer">
            <table class=" table table-striped" id="resortServices">
                <thead>
                    <th scope="col">Food Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Category</th>
                    <th scope="col">Description</th>
                    <th scope="col">Availability</th>
                    <th scope="col">Action</th>

                </thead>

                <tbody>
                    <tr>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>

                        <td> <select id="resortAvailability" name="resortAvailability" class="form-select" required>
                                <option value="" disabled selected>Select Availability</option>
                                <option value="available" id="available">Available</option>
                                <option value="unavailable" id="unavailable">Unavailable</option>

                            </select>
                        </td>
                        <td class="d-flex">
                            <button class="btn btn-primary">Edit</button>
                            <button class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>





    </div>






















    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
    </script>

    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Data Table Link -->
    <script src="../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
        $(document).ready(function() {
            $('#resortServices').DataTable({
                language: {
                    emptyTable: "No Services"
                }
            });
        });
    </script>

    <script>
        const serviceCategories = document.getElementById("serviceCategories")
        const resortContainer = document.getElementById("resortContainer")
        const hotelContainer = document.getElementById("hotelContainer")
        const eventContainer = document.getElementById("eventContainer")
        const cateringContainer = document.getElementById("cateringContainer")
        const backButton = document.getElementById("backButton")
        const servicesBody = document.getElementById("servicesBody")

        resortContainer.style.display = "none"
        hotelContainer.style.display = "none"
        eventContainer.style.display = "none"
        cateringContainer.style.display = "none"

        function resort() {
            if (resortContainer.style.display == "none") {

                resortContainer.style.display = "block";
                serviceCategories.style.display = "none"
                hotelContainer.style.display = "none"
                eventContainer.style.display = "none"
                cateringContainer.style.display = "none"
                document.getElementById("headerText").innerHTML = "Resort"
                document.body.setAttribute("style", "background-color: white;");

            } else {
                resortContainer.style.display = "none"
                backButton.href = "adminDashboard.php";
            }
        }

        function hotel() {
            if (hotelContainer.style.display == "none") {

                hotelContainer.style.display = "block";
                serviceCategories.style.display = "none"
                resortContainer.style.display = "none"
                eventContainer.style.display = "none"
                cateringContainer.style.display = "none"
                document.getElementById("headerText").innerHTML = "Hotel"
                document.body.setAttribute("style", "background-color: white;");

            } else {
                hotelContainer.style.display = "none"
                backButton.href = "adminDashboard.php";
            }
        }

        function eventCategory() {
            if (eventContainer.style.display == "none") {

                eventContainer.style.display = "block";
                serviceCategories.style.display = "none"
                resortContainer.style.display = "none"
                hotelContainer.style.display = "none"
                cateringContainer.style.display = "none"
                document.getElementById("headerText").innerHTML = "Event"
                document.body.setAttribute("style", "background-color: white;");

            } else {
                eventContainer.style.display = "none"
                backButton.href = "adminDashboard.php";
            }
        }

        function catering() {
            if (cateringContainer.style.display == "none") {

                cateringContainer.style.display = "block";
                serviceCategories.style.display = "none"
                resortContainer.style.display = "none"
                hotelContainer.style.display = "none"
                eventContainer.style.display = "none"
                document.getElementById("headerText").innerHTML = "Catering"
                document.body.setAttribute("style", "background-color: white;");

            } else {
                cateringContainer.style.display = "none"
                backButton.href = "adminDashboard.php";
            }
        }
    </script>
</body>

</html>