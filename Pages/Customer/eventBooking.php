<?php
require '../../Config/dbcon.php';

$session_timeout = 3600;

ini_set('session.gc_maxlifetime', $session_timeout);
session_set_cookie_params($session_timeout);
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    $_SESSION['error'] = 'Session Expired';

    session_unset();
    session_destroy();
    header("Location: ../register.php?session=expired");
    exit();
}

$_SESSION['last_activity'] = time();
$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr - Book Now</title>
    <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
    <!-- CSS Link -->
    <link rel="stylesheet" href="../../Assets/CSS/Customer/bookNow.css">
    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- flatpickr calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>

<body id="event-page">
    <!-- Event Booking -->
    <form action="../../Function/Booking/eventBooking.php" method="POST">
        <div class=" event" id="event">
            <div class="backToSelection" id="backToSelection">
                <img src="../../Assets/Images/Icon/arrow.png" alt="back button" onclick="backToSelection()">
            </div>
            <div class="titleContainer">
                <h4 class="eventTitle" id="eventTitle">EVENT BOOKING</h4>
            </div>

            <div class="container-fluid event-container" id="eventContainer">
                <div class="card event-card" id="eventBookingCard" style="width: 40rem; flex-shrink: 0; ">

                    <div class="eventTypeContainer">
                        <label for="eventType" class="eventInfoLabel"></label>
                        <select class="form-select" name="eventType" id="eventType" required>
                        </select>
                    </div>

                    <div class="guestInfo">
                        <label for="guestNo" class="eventInfoLabel">Number of Guests</label>
                        <input type="number" class="form-control" name="guestNo" id="guestNo"
                            placeholder="Estimated Number of Guests" required>
                    </div>

                    <div class="eventSched">
                        <label for="eventSched" class="eventInfoLabel">Event Schedule</label>
                        <input type="datetime-local" class="form-control">
                    </div>

                    <div class="eventVenue">
                        <label for="eventVenue" class="eventInfoLabel" id="venueInfoLabel">Venue</label>
                        <select class="form-select" name="eventVenue" id="eventVenue" required>
                        </select>
                    </div>

                    <div class="eventStartTime">
                        <label for="eventStartTime" class="eventInfoLabel">Start Time</label>
                        <input type="time" class="form-control" name="eventStartTime" id="eventStartTime" required>
                    </div>

                    <div class="eventEndTime">
                        <label for="eventEndTime" class="eventInfoLabel">End Time</label>
                        <input type="time" class="form-control" name="eventEndTime" id="eventStartTime" required>
                    </div>

                    <div class="paymentMethod">
                        <label for="paymentMethod" class="eventInfoLabel">Payment Method</label>
                        <select class="form-select" name="paymentMethod" id="paymentMethod" required>
                            <option value="" disabled selected>Choose...</option>
                            <option value="gcash">Gcash</option>
                            <option value="cash">Cash (Onsite Payment)</option>
                        </select>

                        <div class="noteContainer">
                            <p class="note">Note: For any concerns or details regarding food and other services, contact
                                us at
                                (0998) 962 4697.</p>
                        </div>
                    </div>

                    <div class="eventInfo">
                        <label for="additionalRequest" class="eventInfoLabel">Additional Notes</label>
                        <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest"
                            rows="5" placeholder="Optional"></textarea>

                    </div>
                </div>

                <div class="secondColumn">
                    <div id="calendar"></div>
                    <div class="packageDisplay" style="display: none;">
                        <div id="packageCardsContainer" class="container d-flex flex-wrap gap-3">
                        </div>
                    </div>
                </div>

                <!-- <div class="card foodSelection-card" id="foodSelectionCard">
                    <h1>testing</h1>
                </div> -->

                <div class="card foodSelection-card" id="foodSelectionCard" style="width:40rem;">
                    <div class="card-body">
                        <img src="../../Assets/Images/BookNowPhotos/foodCoverImg.jpg" class="card-img-top"
                            id="foodSelectionCover" alt="Food Selection Cover">
                    </div>
                    <div class="card-body ">
                        <h5 class="card-title fw-bold text-center">Dish Selection</h5>
                        <p class="card-text mt-3 text-center">Choose from a variety of catering options to suit your
                            event’s needs.
                            Select dishes that will delight your guests and complement your celebration.</p>
                        <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal"
                            data-bs-target="#dishModal">Open Menu</button>
                    </div>
                </div>

                <div class="card additionalServices-card" id="additionalServicesCard" style="width:40rem;">
                    <div class="card-body">
                        <img src="../../Assets/Images/BookNowPhotos/additionalServiceImg.jpg" class="card-img-top"
                            id="additionalServicesCover" alt="Additional Services Cover">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-center">Additional Services</h5>
                        <p class="card-text mt-3 text-center">Explore our range of additional services to elevate your
                            event. From
                            photography to hosting, choose what best suits your needs and adds a special touch to
                            your celebration.</p>

                        <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal"
                            data-bs-target="#additionalServicesModal">View Services</button>
                    </div>
                </div>
            </div>
            <!--end ng container div-->

            <div class=" eventButton-container">
                <button type="submit" class="btn btn-primary btn-md w-25" name="eventBN">Book Now</button>
            </div>

        </div>
        <!--end ng event div-->

        <!-- Dish Modal -->
        <div class="modal fade modal-lg" id="dishModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-4 fw-bold" id="dishModalLabel">Select Dishes</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body dishMenu" id="dishMenu">
                        <div class="chicken">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Chicken</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="cPastil"
                                        id="cPastil">
                                    <label class="form-check-label" for="cPastil">
                                        Chicken Pastil
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="cBbq" id="cBbq">
                                    <label class="form-check-label" for="cBbq">
                                        Chicken Barbecue Sauce
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="cCordonBleu"
                                        id="cCordonBleu">
                                    <label class="form-check-label" for="cCordonBleu">
                                        Chicken Cordon Bleu
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="cTeriyaki"
                                        id="cTeriyaki">
                                    <label class="form-check-label" for="cTeriyaki">
                                        Chicken Teriyaki
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="cButterGarlic"
                                        id="cButterGarlic">
                                    <label class="form-check-label" for="cButterGarlic">
                                        Butter Garlic Chicken
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="pasta">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Pasta</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="carbonara"
                                        id="carbonara">
                                    <label class="form-check-label" for="carbonara">
                                        Creamy Carbonara
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="spag" id="spag">
                                    <label class="form-check-label" for="spag">
                                        Filipino Style Spaghetti
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="pastaPesto"
                                        id="pastaPesto">
                                    <label class="form-check-label" for="pastaPesto">
                                        Pasta Pesto Sauce
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="cantonBihon"
                                        id="cantonBihon">
                                    <label class="form-check-label" for="cantonBihon">
                                        Canton and Bihon Guisado
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="japchae"
                                        id="japchae">
                                    <label class="form-check-label" for="japchae">
                                        Japchae Noodles
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="pork">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Pork</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="asado" id="asado">
                                    <label class="form-check-label" for="asado">
                                        Pork Asado
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="mechado"
                                        id="mechado">
                                    <label class="form-check-label" for="mechado">
                                        Pork Mechado Roll
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="caldereta"
                                        id="caldereta">
                                    <label class="form-check-label" for="caldereta">
                                        Pork Caldereta
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="hamonado"
                                        id="hamonado">
                                    <label class="form-check-label" for="hamonado">
                                        Pork Hamonado
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="roastedPork"
                                        id="roastedPork">
                                    <label class="form-check-label" for="roastedPork">
                                        Roasted Pork
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="veg">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Vegetables</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="mixedVeggie"
                                        id="mixedVeggie">
                                    <label class="form-check-label" for="mixedVeggie">
                                        Mixed Veggies Sauté
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="orientalMix"
                                        id="orientalMix">
                                    <label class="form-check-label" for="orientalMix">
                                        Oriental Mix Veggies
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="chopsuey"
                                        id="chopsuey">
                                    <label class="form-check-label" for="chopsuey">
                                        Chopsuey
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="chineseChopsuey"
                                        id="chineseChopsuey">
                                    <label class="form-check-label" for="chineseChopsuey">
                                        Chinese Chopsuey
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="butterCreamy"
                                        id="butterCreamy">
                                    <label class="form-check-label" for="butterCreamy">
                                        Butter Creamy Veggies
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="beef">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Beef</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="beefCaldereta"
                                        id="beefCaldereta">
                                    <label class="form-check-label" for="beefCaldereta">
                                        Beef Spicy Caldereta
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="beefBroccolli"
                                        id="beefBroccolli">
                                    <label class="form-check-label" for="beefBroccolli">
                                        Beef Broccolli
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="roastBeef"
                                        id="roastBeef">
                                    <label class="form-check-label" for="roastBeef">
                                        Roast Beef
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="beefTeriyaki"
                                        id="beefTeriyaki">
                                    <label class="form-check-label" for="beefTeriyaki">
                                        Beef Teriyaki
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="beefMushroom"
                                        id="beefMushroom">
                                    <label class="form-check-label" for="beefMushroom">
                                        Beef Creamy Mushroom Sauce
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="seafood">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Seafood</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="fishFillet"
                                        id="fishFillet">
                                    <label class="form-check-label" for="fishFillet">
                                        Crispy Fish Fillet with Creamy Mayo
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="shrimpTempura"
                                        id="shrimpTempura">
                                    <label class="form-check-label" for="shrimpTempura">
                                        Shrimp Tempura
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="garlicShrimp"
                                        id="garlicShrimp">
                                    <label class="form-check-label" for="garlicShrimp">
                                        Butter Garlic Shrimp
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="squidGambas"
                                        id="squidGambas">
                                    <label class="form-check-label" for="squidGambas">
                                        Squid Gambas
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="nutCrustFillet"
                                        id="nutCrustFillet">
                                    <label class="form-check-label" for="nutCrustFillet">
                                        Nut Crusted Fish Fillet with Sweet Chili Sauce
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="drinks">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Drinks</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="lemonIceTea"
                                        id="lemonIceTea">
                                    <label class="form-check-label" for="lemonIceTea">
                                        Lemon Iced Tea
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="orangeJuice"
                                        id="orangeJuice">
                                    <label class="form-check-label" for="orangeJuice">
                                        Orange Juice
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="pineappleJuice"
                                        id="pineappleJuice">
                                    <label class="form-check-label" for="pineappleJuice">
                                        Pineapple Juice
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="redIcedTea"
                                        id="redIcedTea">
                                    <label class="form-check-label" for="redIcedTea">
                                        Red Iced Tea
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="lemonade"
                                        id="lemonade">
                                    <label class="form-check-label" for="lemonade">
                                        Lemonade
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="dessert">
                            <div class="dishTypeContainer">
                                <h6 class="dishType fw-bold">Desserts</h6>
                            </div>
                            <div class="dishListContainer">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="fruitSalad"
                                        id="fruitSalad">
                                    <label class="form-check-label" for="fruitSalad">
                                        Fruit Salad
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="bukoSalad"
                                        id="bukoSalad">
                                    <label class="form-check-label" for="bukoSalad">
                                        Buko Salad
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="bukoPandan"
                                        id="bukoPandan">
                                    <label class="form-check-label" for="bukoPandan">
                                        Buko Pandan
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="coffeeJelly"
                                        id="coffeeJelly">
                                    <label class="form-check-label" for="coffeeJelly">
                                        Coffee Jelly
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
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
                                    <input class="form-check-input" type="checkbox" value="" name="sw" id="sw">
                                    <label class="form-check-label" for="sw">
                                        Shutter Wonders
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="cc" id="cc">
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
                                    <input class="form-check-input" type="checkbox" value="" name="im" id="im">
                                    <label class="form-check-label" for="im">
                                        Issang Macchiato
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="mr" id="mr">
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
                                    <input class="form-check-input" type="checkbox" value="" name="ss" id="ss">
                                    <label class="form-check-label" for="ss">
                                        Studios Studio
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="tri" id="tri">
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
                                    <input class="form-check-input" type="checkbox" value="" name="di" id="di">
                                    <label class="form-check-label" for="di">
                                        Dirty Ice Cream (Sorbetes)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="Sf" id="sf">
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
                                    <input class="form-check-input" type="checkbox" value="" name="hh" id="hh">
                                    <label class="form-check-label" for="hh">
                                        The Hosting Hub
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="sh" id="sh">
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
                                    <input class="form-check-input" type="checkbox" value="" name="lp" id="lp">
                                    <label class="form-check-label" for="lp">
                                        Lightwave Productions
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="st" id="st">
                                    <label class="form-check-label" for="st">
                                        SoundBeam Technologies
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- Bootstrap Link -->
    <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>

    <!-- Full Calendar for Date display -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="../../Assets/JS/fullCalendar.js"></script>

    <!-- Flatpickr for date input -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Sweetalert Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Functions -->
    <script>
    function backToSelection() {
        location.href = "bookNow.php"
    };
    </script>

    <!-- Calendar -->
    <script>
    const calIcon = document.getElementById("calendarIcon");

    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 3);

    //hotel calendar
    flatpickr('#checkInDate', {
        enableTime: true,
        minDate: minDate,
        dateFormat: "Y-m-d H:i ",
        minTime: '00:00'
    });

    flatpickr('#checkOutDate', {
        enableTime: true,
        minDate: minDate,
        dateFormat: "Y-m-d H:i ",
        minTime: '00:00'
    });
    </script>

    <!-- Event Category and Hall-->
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        fetch(`../../Function/Booking/getEventCategory.php`)
            .then(response => {
                if (!response.ok) throw new Error('Network Error');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert("Error: " + data.error);
                    return;
                }

                const eventInfoLabel = document.querySelector(".eventInfoLabel");
                const eventTypeSelect = document.getElementById("eventType");

                const venueInfoLabel = document.querySelector("#venueInfoLabel");
                const venueSelect = document.getElementById("eventVenue");

                eventTypeSelect.innerHTML = '';

                eventInfoLabel.innerHTML = 'Type of Event';

                const typeOption = document.createElement('option');
                typeOption.value = "";
                typeOption.disabled = true;
                typeOption.selected = true;
                typeOption.textContent = "Choose here...";
                eventTypeSelect.appendChild(typeOption);


                data.Categories.forEach(category => {
                    const typeOptions = document.createElement('option');
                    typeOptions.value = category.categoryName;
                    typeOptions.textContent = category.categoryName;
                    eventTypeSelect.appendChild(typeOptions);
                })

                venueSelect.innerHTML = '';

                venueInfoLabel.innerHTML = 'Venue';

                const venueOption = document.createElement('option')
                venueOption.value = "";
                venueOption.disabled = true;
                venueOption.selected = true;
                venueOption.textContent = "Choose...";
                venueSelect.appendChild(venueOption);

                data.Halls.forEach(hall => {
                    const venueOptions = document.createElement('option');
                    venueOptions.value = hall.RServiceName;
                    venueOptions.textContent = `${hall.RServiceName} - ${hall.RSmaxCapacity} pax`;
                    venueSelect.appendChild(venueOptions);
                })

            })
            .catch(error => {
                console.error('There was a problem with the fetch operation', error);
            })
    });
    </script>




</body>

</html>