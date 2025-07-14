<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Partner Services - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/BusinessPartner/bpServices.css">
    <!-- DataTables Link -->
    <link rel="stylesheet" href="../../Assets/CSS/datatables.min.css" />
</head>

<body>
    <!-- Side Bar -->
    <div class="sidebar">

        <div class="profileContainer">
            <a href="#"><img src="../../Assets/Images/defaultProfile.png" alt="home icon" class="profilePic"></a>
        </div>

        <ul class="list-group">
            <li>
                <a href="bpDashboard.php" class="list-group-item ">
                    <img src="../../Assets/Images/Icon/Dashboard.png" alt="Dashboard" class="sidebar-icon">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="bpBookings.php" class="list-group-item">
                    <img src="../../Assets/Images/Icon/booking.png" alt="Bookings" class="sidebar-icon">
                    Bookings
                </a>
            </li>

            <a href="bpServices.php" class="list-group-item active">
                <img src="../../Assets/Images/Icon/services.png" alt="Services" class="sidebar-icon">
                Services
            </a>

            <li>
                <a href="bpRevenue.php" class="list-group-item">
                    <img src="../../Assets/Images/Icon/Profits.png" alt="Revenue" class="sidebar-icon">
                    Revenue
                </a>
            </li>



            </li>
            <li>
                <button type="button" class="btn btn-outline-danger" id="logoutBtn"> <img
                        src="../../../Assets/Images/Icon/logout.png" alt="Log Out" class="sidebar-icon">
                    Logout</button>
            </li>
        </ul>
    </div>

    <div class="container">
        <h3 class="welcomeText" id="title">Services</h3>

        <div class="home" id="homeBtnContainer">
            <a href="#"><img src="../../../Assets/Images/Icon/home2.png" alt="home icon" class="homeIcon"></a>
            <!-- this will lead to dashboard -->
        </div>

        <div class="btnContainer" id="addServiceButtonContainer">
            <button class="btn btn-primary" id="addServiceButton" onclick="addService()">Add
                Service</button>
        </div>

        <div class="tableContainer" id="servicesTable">
            <table class=" table table-striped" id="services">
                <thead>
                    <th scope="col">Service</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </thead>

                <tbody>
                    <tr>
                        <td>Videoke Rental</td>
                        <td>₱800</td>
                        <td><span class="btn btn-warning w-75" id="maintenance">Maintenance</span>
                        </td>
                        <td><a href="#" class="btn btn-primary w-75">View</a></td>

                    </tr>

                    <tr>
                        <td>Catering</td>
                        <td>₱50,000</td>
                        <td><span class="btn btn-success w-75" id="available">Available</span>
                        </td>
                        <td><a href="#" class="btn btn-primary w-75">View</a></td>

                    </tr>

                    <tr>
                        <td>Performer</td>
                        <td>₱2,500</td>
                        <td><span class="btn btn-danger w-75" id="unavailable">Unavailable</span>
                        </td>
                        <td><a href="#" class="btn btn-primary w-75">View</a></td>

                    </tr>

                    <tr>
                        <td>Photography</td>
                        <td>₱30,000</td>
                        <td><span class="btn btn-info w-75">Booked</span>
                        </td>
                        <td><a href="#" class="btn btn-primary w-75">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="addServiceContainer" id="addServiceContainer">
            <div class="backArrowContainer" id="backArrowContainer">
                <a href="bpServices.php"><img src="../../Assets/Images/Icon/arrow.png" alt="Back Button"
                        class="backArrow">
                </a>
            </div>
            <form action="#" method="POST">
                <div class="serviceInputContainer">
                    <div class="serviceNameContainer">
                        <label for="serviceName" id="addServiceLabel">Service Name</label>
                        <input type="text" class="form-control" id="serviceName" name="serviceName">
                    </div>
                    <div class="AvailabilityContainer">
                        <label for="availability" id="addServiceLabel">Availability</label>
                        <select class="form-select" name="availability" id="availability" required>
                            <option value="" disabled selected>Select Availability</option>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="booked">Booked</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="priceContainer">
                        <label for="price" id="addServiceLabel">Price</label>
                        <input type="text" class="form-control" id="price" name="price">

                        <label for="description" class="description" id="addServiceLabel">Description</label>
                        <textarea class="form-control" id="description" name="description"
                            placeholder="Service information/description (Optional)"></textarea>
                    </div>
                    <div class="imageContainer">
                        <label for="serviceImage" id="addServiceLabel">Upload Image</label>
                        <img src="../../Assets/Images/no-picture.jpg" alt="Service Picture" id="preview"
                            class="serviceImage" name="serviceImage">
                        <input type="file" id="servicePicture" name="servicePicture" hidden>
                        <label for="servicePicture" id="choose" class="custom-file-button btn btn-primary w-50 mt-2"
                            name="choose">Choose
                            Image</label>
                    </div>
                </div>
                <div class="submitBtnContainer ">
                    <button type="submit" class="btn btn-success w-25">Add Service</button>
                </div>
            </form>



        </div>

    </div>



    <script>
    const servicesTable = document.getElementById("servicesTable")
    const addServiceContainer = document.getElementById("addServiceContainer")
    const addServiceButtonContainer = document.getElementById("addServiceButtonContainer")
    const homeBtnContainer = document.getElementById("homeBtnContainer")

    addServiceContainer.style.display = "none"

    function addService() {
        if (addServiceContainer.style.display == "none") {
            addServiceContainer.style.display = "block";
            servicesTable.style.display = "none";
            addServiceButtonContainer.style.display = "none";
            homeBtnContainer.style.display = "none";
            document.getElementById("title").innerHTML = "Add Service"

        } else {
            addServiceContainer.style.display = "block";
        }
    }
    </script>



    <!-- Jquery Link -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- DataTables Link -->
    <script src="../../../Assets/JS/datatables.min.js"></script>
    <!-- Table JS -->
    <script>
    $(document).ready(function() {
        $('#services').DataTable({
            language: {
                emptyTable: "No Services"
            },
            columnDefs: [{
                width: '15%',
                target: 0

            }]
        });
    });
    </script>

    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <!-- Sweetalert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Show -->
    <script>
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');

    logoutBtn.addEventListener("click", function() {
        Swal.fire({
            title: "Are you sure you want to log out?",
            text: "You will need to log in again to access your account.",
            icon: "warning",
            showCancelButton: true,
            // confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, logout!",
            customClass: {
                title: 'swal-custom-title',
                htmlContainer: 'swal-custom-text'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../../../Function/logout.php";
            }
        });
    })
    </script>

    <script>
    document.querySelector("input[type='file']").addEventListener("change", function(event) {
        let reader = new FileReader();
        reader.onload = function() {
            let preview = document.getElementById("preview");
            preview.src = reader.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(event.target.files[0]);
    });
    </script>





</body>

</html>