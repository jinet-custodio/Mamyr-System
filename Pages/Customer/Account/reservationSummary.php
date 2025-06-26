<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Summary - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/reservationSummary.css" />
</head>

<body>
    <div class="container">
        <div class="backButtonContainer">
            <a href="bookingHistory.php"><img src="../../../Assets/Images/Icon/arrow.png" alt="Back Button"
                    class="backButton"></a>
        </div>

        <div class="PendingContainer">

            <div class="leftPendingContainer">

                <img src="../../../Assets/Images/Icon/pending.png" alt="Pending Icon" class="PendingIcon">
                <h4 class="pendingTitle">Your reservation is pending for approval </h4>
                <h6 class="pendingSubtitle">Your request has been sent to the admin. Please wait for the approval of
                    your reservation.</h6>

                <!-- <button type="button" class="btn btn-success w-100 mt-3" data-bs-toggle="modal"
                    data-bs-target="#modeofPaymentModal">Make a Down Payment</button> -->
                <a href="../bookNow.php" class="btn btn-primary w-100">Make Another Reservation</a>
            </div>
            <div class="rightPendingContainer">
                <h3 class="rightContainerTitle">Reservation Summary
                </h3>

                <div class="firstRow">
                    <div class="clientContainer">
                        <h6 class="header">Client</h6>
                        <h6 class="content" id="clientName">Juan Dela Cruz</h6>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Contact Number</h6>
                        <h6 class="content" id="contactNumber">0912-345-6789</h6>
                    </div>
                </div>

                <div class="secondRow">
                    <div class="reservationTypeContainer">
                        <h6 class="header">Reservation Type</h6>
                        <h6 class="content" id="reservation">Event</h6>
                    </div>

                    <div class="contactNumContainer">
                        <h6 class="header">Address</h6>
                        <h6 class="content" id="address">Poblacion, San Idefonso, Bulacan</h6>
                    </div>
                </div>

                <div class="card" id="summaryDetails" style="width: 25.6rem;">
                    <ul class="list-group list-group-flush">
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Event Date</h6>
                            <h6 class="cardContent" id="eventDate">20 May 2025</h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Venue</h6>
                            <h6 class="cardContent" id="venue">Pavilion Hall</h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Event Duration</h6>
                            <h6 class="cardContent" id="eventDuration">4 Hours</h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Number of Guests</h6>
                            <h6 class="cardContent" id="guestNo">200</h6>
                        </li>
                        <li class=" list-group-item">
                            <h6 class="cardHeader">Package Type</h6>
                            <h6 class="cardContent" id="packageType">Wedding <img
                                    src="../../../Assets/Images/Icon/information.png" alt="More Details"
                                    class="infoIcon">
                            </h6>
                        </li>
                        <li class=" list-group-item" id="totalAmountSection">
                            <h6 class="cardHeader">Total Amount:</h6>
                            <h6 class="cardContent" id="totalAmount">145,000 Php
                            </h6>
                        </li>
                        <li class=" list-group-item" id="promoSection">
                            <h6 class="cardHeader">Promo/Discount:</h6>
                            <h6 class="cardContent" id="promoDiscount">- 5,000 Php
                            </h6>
                        </li>
                        <li class=" list-group-item" id="totalBillSection">
                            <h6 class="cardHeader">Total Bill:</h6>
                            <h6 class="cardContent" id="totalBill">140,000 Php
                            </h6>
                        </li>
                    </ul>
                </div>

                <div class="downPaymentContainer">
                    <h6 class="header">Down Payment Amount (30%):</h6>
                    <h6 class="content" id="downPaymentAmount">42,000 Php</h6>
                </div>
                <div class="noteContainer">
                    <h6 class="note">Note: Please pay for the down payment amount for the approval of your booking
                        within
                        seven (7) business days.</h6>
                </div>
            </div>
        </div>


        <!-- modal
        <div class="modal fade" id="modeofPaymentModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Mode of Payment</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="modeofPaymentModalBody">
                        <button class="btn btn-primary w-75 m-auto" data-bs-target="#gcashPaymentModal"
                            data-bs-toggle="modal">Gcash
                            Down Payment</button>
                        <button class="btn btn-info w-75 m-auto">On-site Down Payment</button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="gcashPaymentModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2"
            tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Upload Your Screenshot</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>


                    <div class="modal-body" id="gcashModalBody">
                        Please upload a screenshot of your Gcash down payment below.

                        <div class="form-group">
                            <input type="file" class="form-control-file " id="fileInput"
                                accept=".jpeg, .png, image/jpeg, image/png">
                        </div>
                    </div>



                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-target="#modeofPaymentModal"
                            data-bs-toggle="modal">Back</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
                    </div>
                </div>
            </div>
        </div> -->


    </div>

























    <!-- Bootstrap Link -->
    <!-- <script src="../../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>

</html>