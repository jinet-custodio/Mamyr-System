<?php

require '../../Config/dbcon.php';

if (isset($_POST['bookRates'])) {
    $tourSelections = mysqli_real_escape_string($conn, $_POST['tourSelections']);
}
