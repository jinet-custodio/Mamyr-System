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
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

     <!-- Flatpickr calendar -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
     <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

     <!-- Font Awesome -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
         integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
         crossorigin="anonymous" referrerpolicy="no-referrer" />
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

     <!-- BoxIcon -->
     <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
 </head>

 <body id="hotel-page">

     <!-- Hotel Booking -->
     <form action="confirmBooking.php" method="POST">
         <div class="hotel" id="hotel">
             <div class="backToSelection" id="backToSelection">
                 <img src="../../Assets/Images/Icon/arrow.png" alt="back button" onclick="backToSelection()">
             </div>
             <div class="titleContainer">
                 <h4 class="hotelTitle" id="hotelTitle">HOTEL BOOKING</h4>
             </div>
             <div class="container-fluid" id="hotelContainerFluid">
                 <div class="hotelIconsContainer">
                     <div class="availabilityIcons">
                         <div class="availabilityIcon" id="availableRooms">
                             <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 2"
                                 class="avail">
                             <p>Available</p>
                         </div>
                         <div class="availabilityIcon" id="unavailableRooms">
                             <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 3"
                                 class="avail">
                             <p>Not Available</p>
                         </div>
                     </div>

                     <div class="hotelIconContainer mt-3">
                         <?php
                            $hotelCategoryID = 1;
                            $getAllHotelQuery = $conn->prepare("SELECT RServiceName, RSduration, RSAvailabilityID FROM resortAmenities 
                                                                WHERE RScategoryID = ? 
                                                                GROUP BY RServiceName
                                                                ORDER BY CAST(SUBSTRING(RServiceName, LOCATE(' ', RServiceName) + 1) AS UNSIGNED)");
                            $getAllHotelQuery->bind_param("i", $hotelCategoryID);
                            $getAllHotelQuery->execute();
                            $getAllHotelResult = $getAllHotelQuery->get_result();
                            if ($getAllHotelResult->num_rows > 0) {
                                $i = 1;
                                while ($row = $getAllHotelResult->fetch_assoc()) {
                                    $isAvailable = ($row['RSAvailabilityID'] == 1);
                                    $iconPath = $isAvailable
                                        ? "../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png"
                                        : "../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png";
                                    $roomName = htmlspecialchars($row['RServiceName']);
                                    $availabilityStatus = $isAvailable ? 'available' : 'unavailable';
                            ?>
                                 <div class="hotelIconWithCaption" style="display: inline-block; text-align: center;"
                                     data-availability="<?= $availabilityStatus ?>">
                                     <a href="#<?= trim($row['RServiceName']) ?>"
                                         data-duration="<?= htmlspecialchars($row['RSduration']) ?>">
                                         <img src="<?= $iconPath ?>" alt="<?= $roomName ?>" class="hotelIcon"
                                             id="hotelIcon<?= $i ?>">
                                     </a>
                                     <p class="roomCaption"> <?= $roomName ?></p>
                                 </div>
                             <?php
                                    $i++;
                                }
                            } else {
                                ?>
                             <p class="text-center m-auto">No room services found.</p>
                         <?php
                            }
                            ?>
                     </div>

                     <div>
                         <a href="ratesAndHotelRooms.php" class="btn btn-primary btn-md w-100" id="amenitiesHR"> Take me
                             to Hotel Rooms and
                             Rates</a>
                     </div>
                 </div>


                 <div class="card hotel-card" id="hotelBookingCard" style="width: 40rem; flex-shrink: 0; ">
                     <div class="hoursRoom">
                         <div class="NumberOfHours">
                             <h5 class="numberOfHoursLabel">Number of Hours</h5>
                             <div class="input-group">
                                 <select class="form-select" name="hoursSelected" id="hoursSelected" required>
                                     <option value="" disabled selected>Choose...</option>
                                     <option value="11 hours">11 Hours</option>
                                     <option value="22 hours">22 Hours</option>
                                 </select>
                             </div>
                         </div>
                         <div class="arrivalTime">
                             <h5 class="arrivalTimeLabel">Time arrival</h5>
                             <div class="input-group">
                                 <input type="time" name="arrivalTime" id="arrivalTime">
                             </div>
                         </div>
                     </div>

                     <div class="checkInOut">
                         <div class="checkIn-container">
                             <h5 class="containerLabel">Check-In Date</h5>
                             <div style="display: flex;align-items:center;width:100%">
                                 <input type="text" class="form-control" name="checkInDate" id="checkInDate" required
                                     placeholder="Select Date and Time">
                                 <i class="fa-solid fa-calendar" id="hotelCheckinIcon"
                                     style="margin-left: -2vw;font-size:1.2vw;"> </i>
                             </div>
                         </div>
                         <div class="checkOut-container">
                             <h5 class="containerLabel">Check-Out Date</h5>
                             <div style="display: flex;align-items:center;">
                                 <input type="text" class="form-control" name="checkOutDate" id="checkOutDate" required
                                     placeholder="Select Date and Time">
                                 <i class="fa-solid fa-calendar" id="hotelCheckoutIcon"
                                     style="margin-left: -2vw;font-size:1.2vw;"> </i>
                             </div>
                         </div>
                     </div>

                     <div class="hotelPax">
                         <h5 class="noOfPeopleHotelLabel">Number of People</h5>
                         <div class="hotelPeopleForm">
                             <input type="number" class="form-control" name="adultCount" placeholder="Adults" required>
                             <input type="number" class="form-control" name="childrenCount" placeholder="Children"
                                 required>
                         </div>
                     </div>

                     <div class="hotelRooms">
                         <h5 class="hotelRooms-title">Room Number</h5>
                         <button type="button" class="btn btn-outline-info text-black w-100" name="selectedHotel" id="selectedHotel" data-bs-toggle="modal" data-bs-target="#hotelRoomModal"> Choose your room</button>

                         <!-- Modal for hotel rooms -->
                         <div class="modal" id="hotelRoomModal" tabindex="-1">
                             <div class="modal-dialog">
                                 <div class="modal-content">
                                     <div class="modal-header">
                                         <h5 class="modal-title">Available Hotels</h5>
                                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                     </div>
                                     <div class="modal-body" id="hotelDisplaySelection">
                                     </div>
                                     <div class="modal-footer">
                                         <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Okay</button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <div class="paymentMethod">
                         <h5 class="payment-title">Payment Method</h5>
                         <div class="input-group">
                             <select class="form-select" name="paymentMethod" id="paymentMethod" required>
                                 <option value="" disabled selected>Choose...</option>
                                 <option value="GCash">GCash</option>
                                 <option value="Cash">Cash</option>
                             </select>
                         </div>
                     </div>

                     <div class="additional-info-container">
                         <ul>
                             <li style="color: #0076d1ff;"><i class="fa-solid fa-circle-info" style="color: #37a5fff1;"></i>&nbsp;If the maximum pax exceeded, extra guest is charged
                                 ₱250 per head</li>
                         </ul>
                     </div>
                     <button type="submit" class="btn btn-success" name="hotelBooking" id="hotelBooking">Book
                         Now</button>
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


     <!-- Hotel check-in check-out  -->
     <script>
         const hoursSelected = document.getElementById('hoursSelected');
         const checkInInput = document.getElementById('checkInDate');
         const checkOutInput = document.getElementById('checkOutDate');

         checkInInput.addEventListener('change', () => {
             const selectedValue = hoursSelected.value;
             const checkInDate = new Date(checkInInput.value);
             const addHours = parseInt(selectedValue);
             if (!isNaN(checkInDate.getTime()) && !isNaN(addHours)) {
                 const checkOutDate = new Date(checkInDate.getTime() + addHours * 60 * 60 * 1000);

                 const year = checkOutDate.getFullYear();
                 const month = String(checkOutDate.getMonth() + 1).padStart(2, '0');
                 const day = String(checkOutDate.getDate()).padStart(2, '0');
                 const hours = String(checkOutDate.getHours()).padStart(2, '0');
                 const minutes = String(checkOutDate.getMinutes()).padStart(2, '0');

                 const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;
                 checkOutInput.value = formattedDate;

             }
         });


         hoursSelected.addEventListener('change', () => {
             const selectedValue = hoursSelected.value.trim().toLowerCase();


             if (checkInInput.value) {
                 checkInInput.dispatchEvent(new Event('change'));
             }

         });
     </script>


     <!-- Get the available hotel/room depends on the customer selected date -->
     <script>
         document.addEventListener("DOMContentLoaded", function() {
             const checkInDate = document.getElementById('checkInDate');
             const hoursSelected = document.getElementById('hoursSelected');
             const checkOutDate = document.getElementById('checkOutDate');
             Swal.fire({
                 icon: 'info',
                 title: 'Select your choice of date',
                 text: 'Please pick a booking date to continue',
                 confirmButtonText: 'OK'
             }).then(() => {
                 checkInDate.style.border = '2px solid red';
                 hoursSelected.style.border = '2px solid red';
                 //  dateInput.focus();
             });

         });

         function fetchAvailableRooms() {
             const checkInDateValue = checkInDate.value;
             const checkOutDateValue = checkOutDate.value;
             const hoursSelectedValue = hoursSelected.value;

             if (!checkInDateValue || !hoursSelectedValue) return;

             fetch(`../../Function/Booking/getAvailableAmenities.php?hotelCheckInDate=${encodeURIComponent(checkInDateValue)}&hotelCheckOutDate=${encodeURIComponent(checkOutDateValue)}&duration=${encodeURIComponent(hoursSelectedValue)}`)
                 .then(response => {
                     if (!response.ok) throw new Error('Network Problem');
                     return response.json();
                 })
                 .then(data => {
                     if (data.error) {
                         alert("Error: " + data.error);
                         return;
                     }

                     const hotelRoomContainer = document.getElementById("hotelDisplaySelection");
                     hotelRoomContainer.innerHTML = '';

                     data.hotels.forEach(hotel => {
                         const wrapper = document.createElement('div');
                         wrapper.classList.add('checkbox-item');

                         const checkbox = document.createElement('input');
                         checkbox.type = 'checkbox';
                         checkbox.name = 'hotelSelections[]';
                         checkbox.value = hotel.RServiceName;
                         checkbox.id = hotel.RServiceName;
                         checkbox.dataset.capacity = hotel.RScapacity;

                         const label = document.createElement('label');
                         label.setAttribute('for', checkbox.id);
                         label.textContent = `${hotel.RServiceName} good for ${hotel.RScapacity} pax (₱${Number(hotel.RSprice).toLocaleString()}.00)`;

                         wrapper.appendChild(checkbox);
                         wrapper.appendChild(label);
                         hotelRoomContainer.appendChild(wrapper);
                     })

                 }).catch(error => {
                     console.error(error);
                     Swal.fire({
                         icon: 'error',
                         title: 'Error',
                         text: 'Failed to fetch hotels. Please try again.',
                     });
                 });
         }

         checkInDate.addEventListener("change", () => {
             fetchAvailableRooms();
             if (!checkInDate.value) {
                 checkInDate.style.border = '2px solid red';
             } else {
                 checkInDate.style.border = '';
             }
         });

         checkOutDate.addEventListener("change", fetchAvailableRooms);

         hoursSelected.addEventListener("change", () => {
             fetchAvailableRooms();
             checkInDate.addEventListener("change", fetchAvailableRooms);
             checkOutDate.addEventListener("change", fetchAvailableRooms);
             hoursSelected.addEventListener("change", fetchAvailableRooms);




             if (!hoursSelected.value) {
                 hoursSelected.style.border = '2px solid red';
             } else {
                 hoursSelected.style.border = '';
             }

         });
     </script>


 </body>

 </html>