<?php

require '../../Config/dbcon.php';

require_once __DIR__ . '/../../vendor/autoload.php';
session_start();
$mpdf = new \Mpdf\Mpdf([
    'format' => [150, 200],
    'orientation' => 'L',
    'margin_left' => 5,
    'margin_right' => 5,
    'margin_top' => 5,
    'margin_bottom' => 5,
]);



$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);

//Function for turning the number format to word format

//Paki enable na lang yung extension na intl sa php.ini to mga bes
function convertToWords($number)
{
    $formatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $pesos = floor($number);
    $centavos = round(($number - $pesos) * 100);

    $pesoWords = ucfirst($formatter->format($pesos)) . " pesos";
    $centavoWords = $centavos > 0 ? " and " . $formatter->format($centavos) . " centavos" : " and zero centavos";

    return $pesoWords . $centavoWords;
}

if (isset($_POST['downloadReceiptBtn'])) {
    $bookingID = str_pad($_POST['bookingID'], 4, '0', STR_PAD_LEFT);
    $totalBill = (float) $_POST['totalCost'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $adminName = !empty($_POST['adminName']) ? mysqli_real_escape_string($conn, $_POST['adminName']) : "Ms. Diane";
    $mamyrAddress = 'Gabihan, San Ildefonso, Bulacan 3010';
    $businessName = "Mamyr Resort & Event Place";
    $resortOwner = "Myrna C. Dela Cruz - Prop.";
    $date = date('F d, Y g:i a');


    $amountInWords = convertToWords($totalBill);


    ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>


    <style>
    .logo {
        height: 35px;
        margin-top: -5px;

    }

    h3 {
        margin-top: -10px;
    }
    </style>
</head>

<body>

    <header style="margin-bottom:20px">
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">
        <h3 style="text-align: center;">Mamyr Resort</h3>
        <h5 class="mamyrAddress" style="text-align: center;"><?= $mamyrAddress ?></h5>
        <h4 style="text-align: center;"><?= $resortOwner ?></h4>
    </header>
    <div class="receiptNo">
        <h3>Official Receipt No. <?= $bookingID ?></h3>
    </div>
    <p style="text-align:right; margin-top: -40px;"><strong>Date:</strong> <?= $date ?></p>

    <div class="contents" style="margin-top: 25px; padding: 15px">
        <p style="text-align: justify; font-size: 15px"><strong>Received from </strong> <?= $adminName ?> <strong>with
                Address at
            </strong> <?= $mamyrAddress ?>
            <strong>Bus. Style/Name </strong><?= $businessName ?>
            <strong>the sum of </strong> <?= $amountInWords ?> (₱
            <?= number_format($totalBill, 2) ?>)

            <strong>In partial/full payment for</strong> <?= $bookingType ?> Booking.
        </p>

    </div>

    <p style="text-align: center; font-size: 15px"><strong>Requested by:</strong> <?= $name ?></p>
</body>


</html>


<?php

    $html = ob_get_clean();

    $mpdf->WriteHTML($html);
    $mpdf->Output('Receipt.pdf', 'I');
    exit;
} else {
    echo 'Eh';
}
?>