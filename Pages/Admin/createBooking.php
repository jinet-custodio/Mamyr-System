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
    <div class="container ">
        <div class="row">
            <div class="col" id="resortInfoContainer">
                <h4 class="resortLabel">Resort</h4>

                <form action="#" method="POST" id="resortBookingPage">

                    <div class="resortBooking">
                        <h5 class="schedLabel">Schedule</h5>
                        <div class="scheduleForm">
                            <input type="date" class="form-control w-100" id="resortBookingDate"
                                name="resortBookingDate" required>
                            <select id="tourSelections" name="tourSelections" class="form-select" required>
                                <option value="" disabled selected>Tour Type</option>
                                <option value="Day" id="dayTour">Day Tour</option>
                                <option value="Night" id="nightTour">Night Tour</option>
                                <option value="Overnight" id="overnightTour">Overnight Tour</option>
                            </select>
                        </div>

                        <h5 class="noOfPeopleLabel">Number of People</h5>
                        <div class="peopleForm">
                            <input type="number" class="form-control" placeholder="Adults" name="adultCount">
                            <input type="number" class="form-control" placeholder="Children" name="childrenCount">

                        </div>

                        <h5 class="videokeRentalLabel">Videoke Rental</h5>
                        <div class="input-group">
                            <select id="booleanSelections" name="videokeChoice" class="form-select" required>
                                <option value="" selected disabled>Choose...</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <h5 class="purposeLabel">Additional Notes</h5>
                        <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest"
                            rows="5" placeholder="Optional"></textarea>

                        <div class=" button-container ">
                            <button type="submit" class="btn btn-primary btn-md w-100" name="bookRates">Create
                                Booking</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- resort booking -->

            <!-- hotel booking -->
            <div class="col" id="hotelInfoContainer">
                <h4 class="hotelLabel">Hotel</h4>

                <div class="hotelInfoFormContainer">
                    <form action="#" method="POST" id="hotel-page">
                        <!--purpose of this div: going to put margin top para pumantay sa left and right column-->


                        <div class="firstRow">
                            <div class="hourContainer">
                                <h5 class="numberOfHours">Number of Hours</h5>
                                <select class="form-select" name="hoursSelected" id="hoursSelected"
                                    style="width: 169px;" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="11 hours">11 Hours</option>
                                    <option value="22 hours">22 Hours</option>
                                </select>
                            </div>

                            <div class="roomContainer">
                                <h5 class="roomNumber-title">Room Number</h5>
                                <select class="form-select" style="width: 169px;" name="selectedHotel"
                                    id="selectedHotel" required>
                                    <option value="" disabled selected>Choose a room</option>
                                </select>
                            </div>

                        </div>

                        <div class="checkInOut">
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
                        </div>

                        <div class="hotelPax">
                            <h5 class="noOfPeopleHotelLabel">Number of People</h5>
                            <div class="hotelPeopleForm">
                                <input type="number" class="form-control" name="adultCount" placeholder="Adults"
                                    required>
                                <input type="number" class="form-control" name="childrenCount" placeholder="Children"
                                    required>
                            </div>
                        </div>

                        <div class="paymentMethod">
                            <h5 class="payment-title">Payment Method</h5>
                            <div class="input-group">
                                <select class="form-select" name="PaymentMethod" id="paymentMethod" required>
                                    <option value="" disabled selected>Choose...</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>
                            <!-- <input type="hidden" name="eventPax" id="hiddenGuestValue"> -->
                        </div>

                        <div class="additional-info-container">
                            <ul>
                                <!-- <li><img src="../../Assets/Images/Icon/info.png" alt="Info Icon" class="info-icon">&nbsp;&nbsp;The ₱2,500(22hours)/₱2,000(11hours) room accommodates a maximum of 4 pax</li>
                            <li><img src="../../Assets/Images/Icon/info.png" alt="Info Icon" class="info-icon">&nbsp;&nbsp;The ₱3,500 room acommodates a maximum of 6 pax</li> -->
                                <li><img src="../../Assets/Images/Icon/info.png" alt="Info Icon"
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
            <div class="col" id="eventContainer">
                <h4 class="eventLabel">Event</h4>
                <form action="#" method="POST" id="event-page">

                    <div class="eventForm">
                        <div class="eventType">
                            <h5 class="eventTypeLabel">Type of Event</h5>
                            <select class="form-select" style="width: 169px;" name="eventType" id="eventType" required>
                                <option value="" disabled selected>Choose...</option>
                            </select>
                        </div>
                        <div class="dateContainer">
                            <h5 class="dateLabel">Date</h5>
                            <input type="date" class="form-control w-100" name="eventDate" id="eventtBookingDate"
                                disabled required>
                        </div>
                    </div>


                    <div class="timeOfEventContainer">
                        <div class="startTime">
                            <h5 class="startLabel">From</h5>
                            <input type="time" class="form-control" style="width: 169px;" name="eventTime"
                                id="eventStartTime" required>
                        </div>
                        <div class="endTime">
                            <h5 class="endLabel">To</h5>
                            <input type="time" class="form-control" style="width: 169px;" name="eventTime"
                                id="eventEndTime" required>
                        </div>
                    </div>

                    <div class="peopleVenueContainer">
                        <div class="guestContainer">
                            <h5 class="guestLabel">Number of Guests</h5>
                            <input type="text" class="form-control w-100" name="guestNo" id="guestNo"
                                placeholder="Estimated Guests">
                        </div>
                        <div class="venueContainer">
                            <h5 class="venueLabel">Venue</h5>
                            <select class="form-select" style="width: 169px;" name="venue" id="venue" required>
                                <option value="" disabled selected>Choose...</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="packageLabel">Package</h5>
                    <select class="form-select" name="eventType" id="eventType" required>
                        <option value="" disabled selected>Choose...</option>
                    </select>

                    <h5 class="purposeLabel">Additional Notes</h5>
                    <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest" rows="5"
                        placeholder="Optional"></textarea>

                    <div class=" button-container ">
                        <button type="submit" class="btn btn-primary btn-md w-100" name="bookRates">Create
                            Booking</button>
                    </div>
                </form>
            </div>
            <!-- event booking -->
        </div>
    </div>




















































    </div>




</body>

</html>