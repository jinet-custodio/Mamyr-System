<?php
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Admin/createBooking.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <div class="backBtnContainer">
            <a href="booking.php"><img src="../../Assets/Images/Icon/arrow.png" alt="Back Button" class="backButton">
            </a>
        </div>
        <!-- <div class="resortContainer">
            <a class="btn btn-info w-100 ">Resort</a>
        </div>
        <div class=" hotelContainer">
            <a class="btn btn-primary w-100 h-100">Hotel</a>
        </div>
        <div class="eventContainer">
            <a class="btn btn-warning w-100 h-100">Event</a>
        </div> -->
    </nav>

    <div class="titleContainer">
        <h1 class="title" id="title">Create a Booking</h1>
    </div>

    <!-- resort booking -->
    <div class="container">
        <div class="row">
            <div class="col pb-2" id="resortInfoContainer">
                <div class="labelContainer">
                    <h4 class="resortLabel">Resort</h4>
                </div>
                <form action="#" method="POST" id="resortBookingPage">
                    <div class="inputs">
                        <div class="resortBooking formContainer">
                            <div class="row">
                                <h5 class="schedLabel inputLabel mt-2">Schedule</h5>
                                <div class="d-flex align-items-center justify-content-center my-2 gap-1">
                                    <input type="date" class="form-control w-100" id="resortBookingDate"
                                        name="resortBookingDate" required>
                                    <select id="tourSelections" name="tourSelections" class="form-select" required>
                                        <option value="" disabled selected>Tour Type</option>
                                        <option value="Day" id="dayTour">Day Tour</option>
                                        <option value="Night" id="nightTour">Night Tour</option>
                                        <option value="Overnight" id="overnightTour">Overnight Tour</option>
                                    </select>
                                </div>
                            </div>

                            <h5 class="noOfPeopleLabel inputLabel">Number of People</h5>
                            <div class="peopleForm d-flex align-items-center justify-content-center my-3 gap-1">
                                <input type="number" class="form-control" placeholder="Adults" name="adultCount">
                                <input type="number" class="form-control" placeholder="Children" name="childrenCount">
                                <input type="number" class="form-control" placeholder="Toddlers" name="toddlerCount">

                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="row">
                                        <h5 class="videokeRentalLabel inputLabel">Cottages</h5>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-info mx-auto" name="cottageBtn" id="cottageBtn" data-bs-toggle="modal" data-bs-target="#cottageModal">Choose Here</button>
                                            <!-- Modal for cottages -->
                                            <div class="modal" id="cottageModal">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Available Cottage/s</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body" id="cottageModalBody">
                                                            <p class="modal-text"> <i class="fa-solid fa-circle-info" style="color: rgb(15, 127, 255);"></i> You can select more than one cottage</p>
                                                            <div id="cottagesContainer"></div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Okay</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Modal for hotel rooms -->
                                            <div class="modal" id="hotelRoomModal">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Available Hotels</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="modal-text"> <i class="fa-solid fa-circle-info" style="color: rgb(15, 127, 255);"></i> You can select more than one cottage</p>
                                                            <div id="roomsContainer"> </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Okay</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <h5 class="videokeRentalLabel inputLabel">Additional Services</h5>
                                        <div class="input-group">
                                            <button class="btn btn-info  mx-auto" name="entertainmentBtn" id="entertainmentBtn" data-bs-toggle="modal" data-bs-target="#entertainmentModal">Choose Here</button>
                                            <!-- Modal for hotel rooms -->
                                            <div class="modal modal-fullscreen-sm-down" id="entertainmentModal">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Available Additional Services</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="entertainmentContainer"></div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Okay</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="purposeLabel inputLabel">Additional Notes</h5>
                                    <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest"
                                        rows="5" placeholder="Optional"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=" button-container ">
                        <button type="submit" class="btn btn-primary btn-md w-100" name="bookRates">Create
                            Booking</button>
                    </div>

                </form>
            </div>
            <!-- resort booking -->

            <!-- hotel booking -->
            <div class="col pb-2" id="hotelInfoContainer">
                <h4 class="hotelLabel text-white">Hotel</h4>

                <div class="hotelInfoFormContainer formContainer">
                    <form action="#" method="POST" id="hotel-page">
                        <!--purpose of this div: going to put margin top para pumantay sa left and right column-->


                        <div class="firstRow row d-flex justify-content-around align-items-center">
                            <!-- <div class="hourContainer">
                                <h5 class="numberOfHours">Number of Hours</h5>
                                <select class="form-select" name="hoursSelected" id="hoursSelected"
                                    style="width: 169px;" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="11 hours">11 Hours</option>
                                    <option value="22 hours">22 Hours</option>
                                </select>
                            </div> -->

                            <div class="roomContainer col-md-5">
                                <h5 class="roomNumber-title inputLabel text-white">Room Number</h5>
                                <select class="form-select" name="selectedHotel"
                                    id="selectedHotel" required>
                                    <option value="" disabled selected>Choose a room</option>
                                </select>
                            </div>
                            <div class="checkIn-container col-md-5">
                                <h5 class="containerLabel inputLabel text-white">Check-In Date</h5>
                                <input type="datetime-local" class="form-control" name="checkInDate" id="checkInDate" required>
                            </div>

                        </div>

                        <!-- <div class="checkInOut">
                            <div class="checkIn-container">
                                <h5 class="containerLabel">Check-In Date</h5>
                                <input type="datetime-local" class="form-control" style="width: 169px;"
                                    name="checkInDate" id="checkInDate" required>
                            </div>
                            <div class="checkOut-container">
                                <h5 class="containerLabel">Check-Out Date</h5>
                                <input type="datetime-local" class="form-control" style="width: 169px;"
                                    name="checkOutDate" id="checkOutDate" required>
                            </div>
                        </div> -->

                        <div class="hotelPax">
                            <h5 class="noOfPeopleHotelLabel inputLabel text-white">Number of People</h5>
                            <div class="hotelPeopleForm row d-flex justify-content-around align-items-center my-3">
                                <div class="col">
                                    <input type="number" class="form-control" name="adultCount" placeholder="Adults"
                                        required>
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="childrenCount" placeholder="Children"
                                        required>
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="toddlerCount" placeholder="Toddlers"
                                        required>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="paymentMethod">
                            <h5 class="payment-title">Payment Method</h5>
                            <div class="input-group">
                                <select class="form-select" name="PaymentMethod" id="paymentMethod" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>
                            <input type="hidden" name="eventPax" id="hiddenGuestValue">
                </div> -->

                        <div class="additional-info-container">

                            <h5 class="purposeLabel inputLabel">Additional Notes</h5>
                            <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest" rows="4"
                                placeholder="Optional"></textarea>
                            <img src="../../Assets/Images/Icon/info.png" alt="Info Icon"
                                class="info-icon">&nbsp;&nbsp;If the maximum pax exceeded, extra guest is
                            charged ₱250 per head</li>
                            </ul>
                        </div>


                        <div class="button-container">
                            <button type="submit" class="btn custom-btn warning btn-md w-100" name="hotelBooking"
                                id="hotelBooking">Create Booking</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- hotel booking -->

            <!-- event booking -->
            <div class="col pb-3" id="eventContainer">
                <h4 class="eventLabel">Event</h4>
                <form action="#" method="POST" id="event-page">
                    <div class="eventFormContainer formContainer">
                        <div class="eventForm d-flex justify-content-around">
                            <div class="eventType ">
                                <h5 class="eventTypeLabel inputLabel">Type of Event</h5>
                                <select class="form-select" style="width: 169px;" name="eventType" id="eventType" required>
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                            <div class="dateContainer">
                                <h5 class="dateLabel inputLabel">Event Schedule</h5>
                                <input type="date" class="form-control w-100" name="eventDate" id="eventtBookingDate"
                                    disabled required>
                            </div>
                        </div>


                        <div class="timeOfEventContainer d-flex justify-content-around">
                            <div class="startTime w-50">
                                <h5 class="startLabel inputLabel"> Start Time</h5>
                                <input type="time" class="form-control w-100" name="eventTime"
                                    id="eventStartTime" required>
                            </div>
                            <div class="guestContainer">
                                <h5 class="guestLabel inputLabel">Number of Guests</h5>
                                <input type="text" class="form-control w-100" name="guestNo" id="guestNo"
                                    placeholder="Estimated Guests">
                            </div>
                        </div>

                        <div class="peopleVenueContainer row">
                            <div class="venueContainer col-md-5">
                                <h5 class="venueLabel inputLabel">Venue</h5>
                                <select class="form-select" style="width: 169px;" name="venue" id="venue" required>
                                    <option value="" disabled selected>Choose...</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <h5 class="purposeLabel inputLabel">Additional Notes</h5>
                                <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest" rows="2"
                                    placeholder="Optional"></textarea>
                            </div>
                        </div>

                        <!-- <h5 class="packageLabel">Package</h5>
                    <select class="form-select" name="eventType" id="eventType" required>
                        <option value="" disabled selected>Choose...</option>
                    </select> -->
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <h5 class="menuLabel inputLabel">Food Items</h5>
                                <div class="input-group">
                                    <button type="button" class="btn btn-info mx-auto w-75 my-2" name="menuBtn" id="menuBtn" data-bs-toggle="modal" data-bs-target="#dishModal">Choose Here</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="menuLabel inputLabel">Additional Services</h5>
                                <div class="input-group">
                                    <button type="button" class="btn btn-info mx-auto w-75 my-2" name="menuBtn" id="menuBtn" data-bs-toggle="modal" data-bs-target="#additionalServicesModal">Choose Here</button>
                                </div>
                            </div>
                        </div>
                        <!-- Dish Modal -->
                        <div class="modal fade modal-lg" id="dishModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-4 fw-bold" id="dishModalLabel">Select Dishes</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="note-container">
                                        <ul>
                                            <li>You can select a maximum of 4 dishes.</li>
                                            <li>You may only select 1 drink.</li>
                                            <li>You can select up to 2 kinds of dessert.</li>
                                        </ul>
                                    </div>
                                    <div class="modal-body dishMenu" id="dishMenu">
                                        <div class="chicken">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Chicken</h6>
                                            </div>
                                            <div class="dishListContainer" id="chickenContainerA"></div>
                                        </div>
                                        <div class="pasta">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Pasta</h6>
                                            </div>
                                            <div class="dishListContainer" id="pastaContainerA"></div>
                                        </div>
                                        <div class="pork">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Pork</h6>
                                            </div>
                                            <div class="dishListContainer" id="porkContainerA"></div>
                                        </div>
                                        <div class="veg">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Vegetables</h6>
                                            </div>
                                            <div class="dishListContainer" id="vegieContainerA"></div>
                                        </div>
                                        <div class="beef">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Beef</h6>
                                            </div>
                                            <div class="dishListContainer" id="beefContainerA"></div>
                                        </div>
                                        <div class="seafood">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Seafood</h6>
                                            </div>
                                            <div class="dishListContainer" id="seafoodContainerA"> </div>
                                        </div>
                                        <div class="drinks">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Drinks</h6>
                                            </div>
                                            <div class="dishListContainer" id="drinkContainer"></div>
                                        </div>
                                        <div class="dessert">
                                            <div class="dishTypeContainer">
                                                <h6 class="dishType fw-bold">Desserts</h6>
                                            </div>
                                            <div class="dishListContainer" id="dessertContainer"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="confirmDishBtn" data-bs-dismiss="modal">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- BP Modal -->
                        <div class="modal fade modal-lg" id="additionalServicesModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-4 fw-bold" id="additionalServicesModalLabel">Business Partners</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body additionalService" id="additionalService">
                                        <div class="photography">
                                            <div class="bpTypeContainer">
                                                <h6 class="bpCategory fw-bold">Photography/Videography</h6>
                                            </div>
                                            <div class="partnerListContainer">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" name="additionalServiceSelected[]" id="sw">
                                                    <label class="form-check-label" for="sw">
                                                        Shutter Wonders
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="2" name="additionalServiceSelected[]" id="cc">
                                                    <label class="form-check-label" for="cc">
                                                        Captured Creativity
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="foodCart">
                                            <div class="bpTypeContainer">
                                                <h6 class="bpCategory fw-bold">Food Cart</h6>
                                            </div>
                                            <div class="partnerListContainer">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="3" name="additionalServiceSelected[]" id="im">
                                                    <label class="form-check-label" for="im">
                                                        Issang Macchiato
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="4" name="additionalServiceSelected[]" id="mr">
                                                    <label class="form-check-label" for="mr">
                                                        Mango Royal
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="photoBooth">
                                            <div class="bpTypeContainer">
                                                <h6 class="bpCategory fw-bold">Photo Booth</h6>
                                            </div>
                                            <div class="partnerListContainer">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="5" name="additionalServiceSelected[]" id="ss">
                                                    <label class="form-check-label" for="ss">
                                                        Studios Studio
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="6" name="additionalServiceSelected[]" id="tri">
                                                    <label class="form-check-label" for="tri">
                                                        The Right Image
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="classicSnacks">
                                            <div class="bpTypeContainer">
                                                <h6 class="bpCategory fw-bold">Classic Snacks</h6>
                                            </div>
                                            <div class="partnerListContainer">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="7" name="additionalServiceSelected[]" id="di">
                                                    <label class="form-check-label" for="di">
                                                        Dirty Ice Cream (Sorbetes)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="8" name="additionalServiceSelected[]" id="sf">
                                                    <label class="form-check-label" for="sf">
                                                        Street Food
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="host">
                                            <div class="bpTypeContainer">
                                                <h6 class="bpCategory fw-bold">Host</h6>
                                            </div>
                                            <div class="partnerListContainer">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="9" name="additionalServiceSelected[]" id="hh">
                                                    <label class="form-check-label" for="hh">
                                                        The Hosting Hub
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="10" name="additionalServiceSelected[]" id="sh">
                                                    <label class="form-check-label" for="sh">
                                                        Stellar Hosts
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="lightsSounds">
                                            <div class="bpTypeContainer">
                                                <h6 class="bpCategory fw-bold">Lights and Sounds</h6>
                                            </div>
                                            <div class="partnerListContainer">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="11" name="additionalServiceSelected[]" id="lp">
                                                    <label class="form-check-label" for="lp">
                                                        Lightwave Productions
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="12" name="additionalServiceSelected[]" id="st">
                                                    <label class="form-check-label" for="st">
                                                        SoundBeam Technologies
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class=" button-container ">
                            <button type="submit" class="btn btn-primary btn-md w-100" name="bookRates">Create
                                Booking</button>
                        </div>
                </form>

            </div>
            <!-- event booking -->
        </div>


        <!-- Bootstrap Link -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
        </script>
</body>

</html>