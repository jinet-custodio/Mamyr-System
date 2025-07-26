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

     <!-- flatpickr calendar -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
     <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
         integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
         crossorigin="anonymous" referrerpolicy="no-referrer" />
     <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
 </head>

 <body id="resort-page">

     <!-- Resort Booking -->
     <form action="confirmBooking.php" method="POST">
         <div class="resort" id="resort">
             <button type="button" class="backToSelection btn btn-light" id="backToSelection">
                 <img src="../../Assets/Images/Icon/arrow.png" alt="back button" />
             </button>
             <div class="titleContainer">
                 <h4 class="resortTitle" id="resortTitle">RESORT BOOKING</h4>
             </div>

             <div class="container-fluid">
                 <div class="card resort-card" id="resortBookingCard" style="flex-shrink: 0;">
                     <h5 class="schedLabel">Schedule</h5>
                     <div class="scheduleForm">
                         <input type="text" class="form-control w-95" id="resortBookingDate" name="resortBookingDate"
                             placeholder="Select booking date" required />
                         <i class="fa-solid fa-calendar" id="calendarIcon" style="margin-left: -5vw;font-size:1.2vw;">
                         </i>
                         <select id="tourSelections" name="tourSelections" class="form-select" required>
                             <option value="" disabled selected>Select Preferred Tour</option>
                             <option value="Day" id="dayTour">Day Tour</option>
                             <option value="Night" id="nightTour">Night Tour</option>
                             <option value="Overnight" id="overnightTour">Overnight Tour</option>
                         </select>
                     </div>

                     <h5 class="noOfPeopleLabel">Number of People</h5>
                     <div class="peopleForm">
                         <div class="input-container ">
                             <input type="number" class="form-control" placeholder="Adults" id="adultCount" name="adultCount" />
                             <div class="info-container mt-1">
                                 <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                 <p>Adult aged 14 and up</p>
                             </div>
                         </div>
                         <div class="input-container">
                             <input type="number" class="form-control" placeholder="Kids" id="childrenCount" name="childrenCount" />
                             <div class="info-container mt-1">
                                 <i class="fa-solid fa-circle-info" style="color: #007BFF;"></i>
                                 <p>Children aged 13 and below</p>
                             </div>
                         </div>
                     </div>

                     <div class="cottageRoomForm">
                         <div class="cottagesForm">
                             <h5 class="cottagesFormLabel" id="cottagesFormLabel"></h5>
                             <div class="input-box">
                                 <div id="cottagesContainer"></div>
                             </div>
                         </div>

                         <div class="roomNumbers" style="display: none;" id="rooms">
                             <h5 class="roomLabel">Room</h5>
                             <select class="form-select" id="roomSelect" name="roomSelections">
                                 <option value="" selected disabled>Choose a room</option>
                             </select>
                         </div>
                     </div>


                     <div class="entertainmentForm">
                         <h5 class="entertainmentFormLabel" id="entertainmentFormLabel"></h5>
                         <div class="input-box">
                             <div id="entertainmentContainer"></div>
                         </div>
                     </div>

                     <h5 class="purposeLabel">Purpose for Booking/Additional Notes</h5>
                     <textarea class="form-control w-100" id="purpose-additionalNotes" name="additionalRequest" rows="3"
                         placeholder="Optional"></textarea>

                     <div class="mt-auto button-container">
                         <button type="submit" class="btn btn-primary btn-md w-100" name="bookRates">Book Now</button>
                     </div>
                 </div>

                 <div class="entrance-rates">
                     <div class="card rates">

                         <h1 class="text-center">Entrance Fee</h1>

                         <div class="card-body">
                             <?php
                                $getEntranceRates = $conn->prepare("SELECT er.*, etr.timeRangeID AS primaryID, etr.time_range AS timeRange FROM entranceRates er
                            JOIN entrancetimeranges etr ON er.timeRangeID = etr.timeRangeID
                            ORDER BY er.sessionType, etr.time_range, er.ERcategory");
                                $getEntranceRates->execute();
                                $getEntranceRatesResult = $getEntranceRates->get_result();

                                if ($getEntranceRatesResult->num_rows > 0) {
                                    $groupedData = [];
                                    while ($row = $getEntranceRatesResult->fetch_assoc()) {
                                        $sessionType = $row['sessionType'];
                                        $timeRange = $row['timeRange'];
                                        $category = $row['ERcategory'];
                                        $price = $row['ERprice'];

                                        $key = $sessionType . '|' . $timeRange;
                                        $groupedData[$key][] = [
                                            'category' => $category,
                                            'price' => $price
                                        ];
                                    }
                                    foreach ($groupedData as $key => $entries) {
                                        list($sessionType, $timeRange) = explode('|', $key);
                                ?>
                                     <div class="data-container">
                                         <h5><strong><?= htmlspecialchars($sessionType) ?></strong>|
                                             <?= htmlspecialchars($timeRange) ?> </h5>
                                         <?php
                                            foreach ($entries as $entry) {
                                            ?>
                                             <p><strong><?= htmlspecialchars($entry['category']) ?></strong> -
                                                 <?= htmlspecialchars($entry['price']) ?></p>
                                         <?php
                                            }
                                            ?>
                                     </div>
                             <?php
                                    }
                                }
                                ?>
                         </div>
                     </div>


                     <div class="card cottagesEntertainment">
                         <div class="cottagesNotes">
                             <div class="card-body cottage">
                                 <h1>Cottages</h1>
                                 <?php
                                    $cottageCategoryID = 2;

                                    $getCottageQuery = $conn->prepare("SELECT DISTINCT RScapacity, RSdescription, RSprice FROM resortAmenities WHERE RScategoryID = ?");
                                    $getCottageQuery->bind_param("i", $cottageCategoryID);
                                    $getCottageQuery->execute();
                                    $getCottageQueryResult =  $getCottageQuery->get_result();
                                    if ($getCottageQueryResult->num_rows > 0) {
                                        while ($row = $getCottageQueryResult->fetch_assoc()) {
                                            $description = $row['RSdescription'];
                                            $price = $row['RSprice'];
                                    ?>
                                         <p> ₱ <?= htmlspecialchars(number_format($price, 0)) ?> pesos
                                             <?= htmlspecialchars(strtolower($description)) ?> </p>
                                 <?php
                                        }
                                    }
                                    ?>
                             </div>

                             <div class="card-body note">
                                 <h1 class="card-title">NOTE:</h1>
                                 <ul>
                                     <li>Food is allowed except alcoholic drink and soft drinks. It`s available and
                                         affordable in our convenience store inside.</li>
                                     <li>Appropriate swimming attire is required.</li>
                                     <li>3ft below are free in entrance fee</li>
                                 </ul>
                             </div>
                         </div>

                         <div class="entertainmentContainer">
                             <div class="card-body videoke">
                                 <?php
                                    $entertainmentName = 'Videoke';
                                    $categoryID = 3;
                                    $getVideoke = $conn->prepare("SELECT * FROM resortAmenities WHERE RServiceName = ? AND RScategoryID = ?");
                                    $getVideoke->bind_param("si", $entertainmentName, $categoryID);
                                    $getVideoke->execute();
                                    $getVideokeResult =  $getVideoke->get_result();
                                    if ($getVideokeResult->num_rows > 0) {
                                        while ($row = $getVideokeResult->fetch_assoc()) {
                                            $price = $row['RSprice'];
                                    ?>
                                         <h1> Videoke</h1>
                                         <p><?= htmlspecialchars(number_format($price, 0)) ?> pesos per rent </p>
                                 <?php
                                        }
                                    }
                                    ?>
                             </div>

                             <div class="card-body massage">
                                 <?php
                                    $entertainmentName = 'Massage Chair';
                                    $categoryID = 3;
                                    $getVideoke = $conn->prepare("SELECT * FROM resortAmenities WHERE RServiceName = ? AND RScategoryID = ?");
                                    $getVideoke->bind_param("si", $entertainmentName, $categoryID);
                                    $getVideoke->execute();
                                    $getVideokeResult =  $getVideoke->get_result();
                                    if ($getVideokeResult->num_rows > 0) {
                                        while ($row = $getVideokeResult->fetch_assoc()) {
                                            $duration = $row['RSduration'];
                                            $price = $row['RSprice'];
                                    ?>
                                         <h1> Massage Chair</h1>
                                         <p><?= htmlspecialchars(number_format($price, 0)) ?> pesos for <?= htmlspecialchars($duration) ?></p>
                                 <?php
                                        }
                                    }
                                    ?>
                             </div>

                             <div class="card-body billiard">
                                 <?php
                                    $entertainmentName = 'Billiard';
                                    $categoryID = 3;
                                    $getVideoke = $conn->prepare("SELECT * FROM resortAmenities WHERE RServiceName = ? AND RScategoryID = ?");
                                    $getVideoke->bind_param("si", $entertainmentName, $categoryID);
                                    $getVideoke->execute();
                                    $getVideokeResult =  $getVideoke->get_result();
                                    if ($getVideokeResult->num_rows > 0) {
                                        while ($row = $getVideokeResult->fetch_assoc()) {
                                            $duration = $row['RSduration'];
                                            $price = $row['RSprice'];
                                    ?>
                                         <h1> Billiard</h1>
                                         <p><?= htmlspecialchars(number_format($price, 0)) ?> pesos for <?= htmlspecialchars($duration) ?> </p>
                                 <?php
                                        }
                                    }
                                    ?>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </form>


     <!-- Full Calendar for Date display -->
     <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
     <script src="../../Assets/JS/fullCalendar.js"></script>

     <!-- Flatpickr for date input -->
     <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

     <!-- Bootstrap Link -->
     <!-- <script src="../../Assets/JS/bootstrap.bundle.min.js"></script> -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
         integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
     </script>

     <!-- Sweetalert Link -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


     <!-- Functions -->
     <script>
         document.getElementById("backToSelection").addEventListener("click", function() {
             window.location.href = "bookNow.php";
         });
     </script>

     <!-- Calendar -->
     <script>
         const calIcon = document.getElementById("calendarIcon");

         const minDate = new Date();
         minDate.setDate(minDate.getDate() + 2);

         //resort calendar
         flatpickr('#resortBookingDate', {
             minDate: minDate,
             dateFormat: "Y-m-d"
         });
         calIcon.addEventListener('click', function(event) {
             resortBookingDate.click()
         });
     </script>


     <script>
         document.addEventListener("DOMContentLoaded", function() {
             Swal.fire({
                 icon: 'info',
                 title: 'Select your choice of date',
                 text: 'Please pick a booking date to continue',
                 confirmButtonText: 'OK'
             }).then(() => {

                 const dateInput = document.getElementById('resortBookingDate');
                 dateInput.style.border = '2px solid red';

                 dateInput.focus();
             });
         });




         const dateInput = document.getElementById('resortBookingDate');
         const tourSelect = document.getElementById('tourSelections');

         function fetchAmenities() {
             const selectedDate = dateInput.value;
             const selectedTour = tourSelect.value;

             if (!selectedDate || !selectedTour) return;

             fetch(`../../Function/Booking/getAvailableAmenities.php?date=${encodeURIComponent(selectedDate)}`)
                 .then(response => {
                     if (!response.ok) throw new Error("Network error");
                     return response.json();
                 })
                 .then(data => {
                     if (data.error) {
                         alert("Error: " + data.error);
                         return;
                     }

                     // Reset UI
                     const cottageContainer = document.getElementById('cottagesContainer');
                     const roomSection = document.getElementById('rooms');
                     const roomSelect = document.getElementById('roomSelect');
                     const cottageLabel = document.getElementById('cottagesFormLabel');
                     const entertainmentContainer = document.getElementById('entertainmentContainer');
                     const entertainmentLabel = document.getElementById('entertainmentFormLabel');

                     cottageContainer.innerHTML = '';
                     roomSelect.innerHTML = '';
                     entertainmentContainer.innerHTML = '';
                     roomSection.style.display = 'none';
                     cottageLabel.innerHTML = '';
                     entertainmentLabel.innerHTML = '';

                     // Show cottages for Day/Night
                     if (selectedTour === 'Day' || selectedTour === 'Night') {
                         cottageLabel.innerHTML = "Available Cottages";
                         data.cottages.forEach(cottage => {
                             const wrapper = document.createElement('div');
                             wrapper.classList.add('checkbox-item');

                             const checkbox = document.createElement('input');
                             checkbox.type = 'checkbox';
                             checkbox.name = 'cottageOptions[]';
                             checkbox.value = cottage.RServiceName;
                             checkbox.id = `cottage-${cottage.RServiceName}`;

                             const label = document.createElement('label');
                             label.setAttribute('for', checkbox.id);
                             label.textContent = `${cottage.RServiceName} - (${cottage.RScapacity} pax)`;

                             wrapper.appendChild(checkbox);
                             wrapper.appendChild(label);
                             cottageContainer.appendChild(wrapper);
                         });
                     }

                     // Show rooms for Overnight
                     if (selectedTour === 'Overnight') {
                         roomSection.style.display = 'block';
                         const defaultOption = document.createElement('option');
                         defaultOption.value = "";
                         defaultOption.disabled = true;
                         defaultOption.selected = true;
                         defaultOption.textContent = "Please select a room";
                         roomSelect.appendChild(defaultOption);

                         data.rooms.forEach(room => {
                             const option = document.createElement('option');
                             option.value = room.RServiceName;
                             option.textContent = `${room.RServiceName} for ₱${Number(room.RSprice).toLocaleString()}.00 - Good for ${room.RScapacity} pax`;
                             roomSelect.appendChild(option);
                         });
                     }

                     // Show entertainment for all
                     if (data.entertainments && data.entertainments.length > 0) {
                         entertainmentLabel.innerHTML = "Additional Services";
                         data.entertainments.forEach(ent => {
                             const wrapper = document.createElement('div');
                             wrapper.classList.add('checkbox-item');

                             const checkbox = document.createElement('input');
                             checkbox.type = 'checkbox';
                             checkbox.name = 'entertainmentOptions[]';
                             checkbox.value = ent.RServiceName;
                             checkbox.id = `ent-${ent.RServiceName}`;

                             const label = document.createElement('label');
                             label.setAttribute('for', checkbox.id);
                             label.textContent = `${ent.RServiceName} for ₱${Number(ent.RSprice).toLocaleString()}.00`;

                             wrapper.appendChild(checkbox);
                             wrapper.appendChild(label);
                             entertainmentContainer.appendChild(wrapper);
                         });
                     }
                 })
                 .catch(error => {
                     console.error(error);
                     Swal.fire({
                         icon: 'error',
                         title: 'Error',
                         text: 'Failed to fetch amenities. Please try again.',
                     });
                 });
         }

         // Attach event listeners to both fields
         dateInput.addEventListener('change', fetchAmenities);
         tourSelect.addEventListener('change', fetchAmenities);






         // document.getElementById('resortBookingDate').addEventListener('change', function() {
         // const resortBookingDate = this.value;
         // const tourSelect = document.getElementById("tourSelections");
         // tourSelect.addEventListener('change', function() {
         // const selectedTour = tourSelect.value;
         // })

         // if (resortBookingDate !== "") {
         // this.style.border = '2px solid rgb(235, 237, 240)';
         // // document.getElementById("openAddOns").disabled = false;
         // }

         // fetch(`../../Function/Booking/getAvailableAmenities.php?date=${encodeURIComponent(resortBookingDate)}`)
         // .then(response => {
         // if (!response.ok) throw new Error("Network error");
         // return response.json();
         // })
         // .then(data => {
         // if (data.error) {
         // alert("Error: " + data.error);
         // return;
         // }

         // if (selectedTour === 'Day' || selectedTour === 'Night') {
         // // COTTAGE
         // const cottageSelection = document.getElementById('cottagesContainer');
         // const cottagesFormLabel = document.getElementById('cottagesFormLabel');
         // cottagesFormLabel.innerHTML = "Available Cottages";
         // cottageSelection.innerHTML = "";

         // data.cottages.forEach(cottage => {
         // const wrapper = document.createElement('div');
         // wrapper.classList.add('checkbox-item');

         // const checkbox = document.createElement('input');
         // checkbox.type = 'checkbox';
         // checkbox.name = 'cottageOptions[]';
         // checkbox.value = cottage.RServiceName;
         // checkbox.id = `ent-${cottage.RServiceName}`;

         // const label = document.createElement('label');
         // label.setAttribute('for', checkbox.id);
         // label.innerHTML = `${cottage.RServiceName}-(${cottage.RScapacity} pax)`;

         // wrapper.appendChild(checkbox);
         // wrapper.appendChild(label);
         // cottageSelection.appendChild(wrapper);
         // });
         // }

         // if (selectedTour === 'Overnight') {
         // const roomSelection = document.getElementById('roomSelect');
         // roomSelection.innerHTML = "";

         // const defaultRoomOption = document.createElement('option');
         // defaultRoomOption.value = "";
         // defaultRoomOption.disabled = true;
         // defaultRoomOption.selected = true;
         // defaultRoomOption.textContent = "Please select a room";
         // roomSelection.appendChild(defaultRoomOption);

         // data.rooms.forEach(room => {
         // const roomOption = document.createElement('option');
         // roomOption.value = room.RServiceName;
         // roomOption.textContent = `${room.RServiceName} for ₱ ${Number(room.RSprice).toLocaleString()}.00 - Good for ${room.RScapacity} pax`;
         // roomSelection.appendChild(roomOption);
         // });
         // }


         // const entertainmentSelection = document.getElementById('entertainmentContainer');
         // entertainmentSelection.innerHTML = "";
         // const entertainmentFormLabel = document.getElementById('entertainmentFormLabel');

         // entertainmentFormLabel.innerHTML = "Additional Services";

         // data.entertainments.forEach(entertainment => {
         // const wrapper = document.createElement('div');
         // wrapper.classList.add('checkbox-item');

         // const checkbox = document.createElement('input');
         // checkbox.type = 'checkbox';
         // checkbox.name = 'entertainmentOptions[]';
         // checkbox.value = entertainment.RServiceName;
         // checkbox.id = `ent-${entertainment.RServiceName}`;

         // const label = document.createElement('label');
         // label.setAttribute('for', checkbox.id);
         // label.innerHTML = `${entertainment.RServiceName} for ₱${Number(entertainment.RSprice).toLocaleString()}.00`;

         // wrapper.appendChild(checkbox);
         // wrapper.appendChild(label);
         // entertainmentSelection.appendChild(wrapper);
         // });



         // })
         // .catch(error => console.error(
         // Swal.fire({
         // icon: 'error',
         // text: 'Please select date first',
         // title: 'Error'
         // })
         // ));

         // });
     </script>


 </body>

 </html>