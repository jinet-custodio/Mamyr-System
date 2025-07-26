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

     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
         integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
         crossorigin="anonymous" referrerpolicy="no-referrer" />
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
             <?php
                $availsql = "SELECT RSAvailabilityID, RServiceName, RSduration
            FROM resortAmenities 
            WHERE RScategoryID = '1'";

                $result = mysqli_query($conn, $availsql);
                ?>
             <div class="container-fluid" id="hotelContainerFluid">
                 <div class="hotelIconsContainer">
                     <div class="availabilityIcons">
                         <div class="availabilityIcon" id="allRooms" onclick="filterRooms('all')">
                             <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 1"
                                 class="avail" id="allrooms">
                             <p>All Rooms</p>
                         </div>
                         <div class="availabilityIcon" id="availableRooms" onclick="filterRooms('available')">
                             <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon1.png" alt="Rate Picture 2"
                                 class="avail">
                             <p>Available</p>
                         </div>
                         <div class="availabilityIcon" id="unavailableRooms" onclick="filterRooms('unavailable')">
                             <img src="../../Assets/Images/BookNowPhotos/hotelIcons/icon2.png" alt="Rate Picture 3"
                                 class="avail">
                             <p>Not Available</p>
                         </div>
                     </div>


                     <div class="hotelIconContainer mt-3">
                         <?php
                            if ($result->num_rows > 0) {
                                $i = 1;
                                while ($row = $result->fetch_assoc()) {
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
                             <h5 class="numberOfHours">Number of Hours</h5>
                             <div class="input-group">
                                 <select class="form-select" name="hoursSelected" id="hoursSelected" required>
                                     <option value="" disabled selected>Choose...</option>
                                     <option value="11 hours">11 Hours</option>
                                     <option value="22 hours">22 Hours</option>
                                 </select>
                             </div>
                         </div>
                         <div class="roomNumbers">
                             <h5 class="roomNumber-title">Room Number</h5>
                             <div class="input-group">
                                 <select class="form-select" name="selectedHotel" id="selectedHotel" required>
                                     <option value="" disabled selected>Choose a room</option>
                                     <?php
                                        $category = 'Hotel';
                                        $availableID = 1;
                                        $selectHotel = $conn->prepare("SELECT rs.*, rsc.categoryName FROM resortAmenities rs
                            JOIN resortservicescategories rsc ON rs.RScategoryID = rsc.categoryID  
                            WHERE rsc.categoryName = ? AND RSAvailabilityID = ?");
                                        $selectHotel->bind_param("si", $category, $availableID);
                                        $selectHotel->execute();
                                        $result = $selectHotel->get_result();
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                        ?>
                                             <option value="<?= $row['RServiceName'] ?>"
                                                 data-duration="<?= $row['RSduration'] ?>"><?= $row['RServiceName'] ?> - Max. of
                                                 <?= $row['RScapacity'] ?> pax - ₱<?= $row['RSprice'] ?></option>
                                     <?php
                                            }
                                        }
                                        ?>
                                 </select>
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


                     <div class="paymentMethod">
                         <h5 class="payment-title">Payment Method</h5>
                         <div class="input-group">
                             <select class="form-select" name="PaymentMethod" id="paymentMethod" required>
                                 <option value="" disabled selected>Choose...</option>
                                 <option value="GCash">GCash</option>
                                 <option value="Cash">Cash</option>
                             </select>
                         </div>
                     </div>

                     <div class="additional-info-container">
                         <ul>
                             <li><img src="../../Assets/Images/Icon/info.png" alt="Info Icon"
                                     class="info-icon">&nbsp;&nbsp;If the maximum pax exceeded, extra guest is charged
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
             dateFormat: "Y-m-d H:i"
         });

         hotelCheckinIcon.addEventListener('click', function(event) {
             checkInDate.click()
         });


         flatpickr('#checkOutDate', {
             enableTime: true,
             minDate: minDate,
             dateFormat: "Y-m-d H:i"
         });

         hotelCheckoutIcon.addEventListener('click', function(event) {
             checkOutDate.click()
         });
     </script>


     <!-- Hotel check-in check-out  -->
     <script>
         const hoursSelected = document.getElementById('hoursSelected');
         const checkInInput = document.getElementById('checkInDate');
         const checkOutInput = document.getElementById('checkOutDate');
         const selectedHotel = document.getElementById('selectedHotel');
         const hotelDivs = document.querySelectorAll('.hotelIconWithCaption')

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


             selectedHotel.setAttribute('data-duration', selectedValue);
             Array.from(selectedHotel.options).forEach(option => {
                 if (!option.value) {
                     option.hidden = false;
                     return;
                 }
                 const roomDuration = option.getAttribute('data-duration')?.trim().toLowerCase() || '';
                 option.hidden = roomDuration !== selectedValue;
             });
             selectedHotel.selectedIndex = 0;


             hotelDivs.forEach(div => {
                 const aTag = div.querySelector('a[data-duration]');
                 if (!aTag) return;

                 const duration = aTag.getAttribute('data-duration')?.trim().toLowerCase() || '';
                 div.style.display = duration === selectedValue ? 'inline-block' : 'none';
             });
         });
     </script>


     <!-- Get the available amenities depends on the customer selected date -->
     <script>
         document.addEventListener("DOMContentLoaded", function() {

             Swal.fire({
                 icon: 'info',
                 title: 'Select your choice of date',
                 text: 'Please pick a booking date to continue',
                 confirmButtonText: 'OK'
             }).then(() => {

                 const dateInput = document.getElementById('checkInDate');
                 dateInput.style.border = '2px solid red';

                 //  dateInput.focus();
             });
         });



         document.getElementById('checkInDate').addEventListener('change', function() {
             const checkInDate = this.value;

             if (checkInDate !== "") {
                 this.style.border = '2px solid rgb(235, 237, 240)';
             }

             fetch(`../../Function/Booking/getAvailableAmenities.php?date=${encodeURIComponent(checkInDate)}`)
                 .then(response => {
                     if (!response.ok) throw new Error("Network error");
                     return response.json();
                 })
                 .then(data => {
                     if (data.error) {
                         alert("Error: " + data.error);
                         return;
                     }

                     const cottageSelection = document.getElementById('cottageSelections');
                     cottageSelection.setAttribute("multiple", "");
                     cottageSelection.innerHTML = "";


                     const defaultCottageOption = document.createElement('option');
                     defaultCottageOption.value = "";
                     defaultCottageOption.disabled = true;
                     defaultCottageOption.selected = true;
                     defaultCottageOption.textContent = "Please select a cottage";
                     cottageSelection.appendChild(defaultCottageOption);

                     data.cottages.forEach(cottage => {
                         const cottageOption = document.createElement('option');
                         cottageOption.value = cottage.RServiceName;
                         cottageOption.textContent = `${cottage.RServiceName} for ₱${Number(cottage.RSprice).toLocaleString()}.00 - Good for ${cottage.RScapacity} pax`;
                         cottageSelection.appendChild(cottageOption);
                     });

                     const roomSelection = document.getElementById('roomSelect');
                     roomSelection.innerHTML = "";

                     const defaultRoomOption = document.createElement('option');
                     defaultRoomOption.value = "";
                     defaultRoomOption.disabled = true;
                     defaultRoomOption.selected = true;
                     defaultRoomOption.textContent = "Please select a room";
                     roomSelection.appendChild(defaultRoomOption);

                     data.rooms.forEach(room => {
                         const roomOption = document.createElement('option');
                         roomOption.value = room.RServiceName;
                         roomOption.textContent = `${room.RServiceName} for ₱ ${Number(room.RSprice).toLocaleString()}.00 - Good for ${room.RScapacity} pax`;
                         roomSelection.appendChild(roomOption);
                     });


                     const entertainmentSelection = document.getElementById('entertainmentContainer');
                     entertainmentSelection.innerHTML = "";
                     const entertainmentFormLabel = document.getElementById('entertainmentFormLabel');

                     entertainmentFormLabel.innerHTML = "Additional Services";

                     data.entertainments.forEach(entertainment => {
                         const wrapper = document.createElement('div');
                         wrapper.classList.add('checkbox-item');

                         const checkbox = document.createElement('input');
                         checkbox.type = 'checkbox';
                         checkbox.name = 'entertainmentOptions[]';
                         checkbox.value = entertainment.RServiceName;
                         checkbox.id = `ent-${entertainment.RServiceName}`;

                         const label = document.createElement('label');
                         label.setAttribute('for', checkbox.id);
                         label.innerHTML = `${entertainment.RServiceName} for ₱${Number(entertainment.RSprice).toLocaleString()}.00`;

                         wrapper.appendChild(checkbox);
                         wrapper.appendChild(label);
                         entertainmentSelection.appendChild(wrapper);
                     });
                 })
                 .catch(error => console.error(
                     Swal.fire({
                         icon: 'error',
                         text: 'Please select date first',
                         title: 'Error',
                         confirmButtonOkay: 'Okay'
                     })
                 ));

         });
     </script>


 </body>

 </html>