<?php

require '../../Config/dbcon.php';

require_once __DIR__ . '/../../vendor/autoload.php';
session_start();
$mpdf = new \Mpdf\Mpdf([
    'format' => [180, 250],
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
        margin-top: 5px;
        margin-left: 20px;

    }

    h3 {
        margin-top: -25px;
        margin-right: 20px;
    }
    </style>
</head>

<body>

    <header style="margin-bottom:20px">
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">

        <div>
            <h3 style="text-align: right;">Official Receipt No. <?= $bookingID ?></h3>
            <h4 style="text-align: left; margin-top:20px; margin-left:5px"><?= $resortOwner ?></h4>
        </div>
        <p style="text-align:right; margin-top: -35px; margin-right:21px;"><strong>Date:</strong> <?= $date ?></p>

    </header>

    <hr style="height:5px; color: #09a7eb; margin-top:-20px">
    <div class="contents" style="margin-top: 35px; padding: 15px">
        <h3 style="text-align: center; margin-top:-40px">Mamyr Resort and Events Place</h3>
        <h4 style="text-align: center;">Official Receipt</h4>

        <p style="text-align: justify; font-size: 15px; margin-top: 35px"><strong>Received from </strong>
            <?= $adminName ?> Dela Cruz</p>

        <p><strong>
                Address
            </strong> <?= $mamyrAddress ?></p>

        <p><strong>Bus. Style/Name </strong><?= $businessName ?></p>

        <p><strong>Amount</strong> ₱
            <?= number_format($totalBill, 2) ?></p>

        <p><strong>In words </strong> <?= $amountInWords ?></p>

        <p><strong>In partial/full payment for</strong> <?= $bookingType ?> Booking
        </p>

        <p><strong>Service/s</strong> Videoke, Massage Chair
        </p>
        <p><strong>Requested by:</strong> <?= $name ?></p>
        <hr style="width:200px; height:1px; color:black; text-align:right; margin-top:20px">
        <p style="text-align: right; margin-top:1px; margin-right: 30px">Authorized Signature</p>
    </div>


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