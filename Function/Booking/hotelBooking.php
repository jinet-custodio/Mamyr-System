<?php

require '../../Config/dbcon.php';


session_start();
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);
$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);

if (isset($_POST['hotelBooking'])) {
}
