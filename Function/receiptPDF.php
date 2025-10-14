<?php

require '../Config/dbcon.php';
date_default_timezone_set('Asia/Manila'); //Set default time zone 
require_once __DIR__ . '/../vendor/autoload.php';
session_start();
$mpdf = new \Mpdf\Mpdf([
    'format' => [140, 216],
    'orientation' => 'L',
    'margin_left' => 5,
    'margin_right' => 5,
    'margin_top' => 5,
    'margin_bottom' => 5,
]);



$userID = mysqli_real_escape_string($conn, $_SESSION['userID']);
$userRole = mysqli_real_escape_string($conn, $_SESSION['userRole']);

//Function for turning the number format to word format
// TODO: Paki enable na lang yung extension na intl sa php.ini 
function convertToWords($number)
{
    $formatter = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);

    $pesos = floor($number);
    $centavos = round(($number - $pesos) * 100);

    $pesoWords = ucfirst($formatter->format($pesos)) . " pesos";
    $centavoWords = $centavos > 0 ? " and " . $formatter->format($centavos) . " centavos" : " and zero centavos";

    return $pesoWords . $centavoWords;
}

if (isset($_POST['downloadReceiptBtn'])) {
    $bookingID = intval($_POST['bookingID']);
    $formattedBookingID = str_pad($bookingID, 4, '0', STR_PAD_LEFT);
    $totalBill = (float) $_POST['totalCost'];
    $name = !empty($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : mysqli_real_escape_string($conn, $_POST['guestName']);
    $bookingType = mysqli_real_escape_string($conn, $_POST['bookingType']);
    $adminName = !empty($_POST['adminName']) ? mysqli_real_escape_string($conn, $_POST['adminName']) : mysqli_real_escape_string($conn, $_POST['name']);
    $mamyrAddress = 'Gabihan, San Ildefonso, Bulacan 3010';
    $businessName = "Mamyr Resort & Event Place";
    $resortOwner = "Myrna C. Dela Cruz - Prop.";
    $date = date('F d, Y g:i A');
    $services =  $_POST['services'] ?? [];
    $amountPaid = 0;
    $isFullPayment = false;

    $selectBookingQuery = $conn->prepare("SELECT bookingID,finalBill, amountPaid, userBalance FROM confirmedbooking
    WHERE bookingID = ? ");
    $selectBookingQuery->bind_param('s', $bookingID);
    if (!$selectBookingQuery->execute()) {
        error_log('Failed Executing query for booking: ' . $selectBookingQuery->error);
    }

    $result = $selectBookingQuery->get_result();

    if ($result->num_rows === 0) {
        $amountPaid = 'No payment yet';
        $storedAmountPaid = 0.00;
        $storedUserBalance = 0.00;
        $isPaid = false;
        $isFullPayment = false;
        $payment = '0.00';
    } else {
        $row = $result->fetch_assoc();

        $storedAmountPaid = floatval($row['amountPaid']) ?? 0.00;
        $storedUserBalance = floatval($row['userBalance']) ?? 0.00;
    }

    // $confirmedFinalBill = floatval($row['confirmedFinalBill']);
    if ($storedAmountPaid == 0.00) {
        $isPaid = false;
        $isFullPayment = false;
        $payment = '0.00';
    } else {
        $isPaid = true;
        $isFullPayment = ($storedAmountPaid >= $totalBill);
        $payment = '₱ ' . number_format($storedAmountPaid, 2) . ($isFullPayment ? ' (Fully Paid)' : ' (Partially Paid)');
    }



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
            .contents p {
                line-height: 1rem;
            }

            .logo {
                height: 35px;
                margin-top: 5px;
                margin-left: 20px;

            }

            h3 {
                margin-top: -30px;
                margin-right: 20px;
            }

            .underlined {
                display: inline-block;
                border-bottom: 1px solid black;
                padding-bottom: 3px;
                min-width: 200px;
            }
        </style>
    </head>

    <body>

        <header style="margin-bottom:20px">
            <img src="' . __DIR__ . '/../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">

            <div>
                <h3 style="text-align: right;">Receipt No. <?= $formattedBookingID ?></h3>
                <h4 style="text-align: left; margin-top:20px; margin-left:5px"><?= $resortOwner ?></h4>
            </div>
            <p style="text-align:right; margin-top: -35px; margin-right:21px;"><strong>Date:</strong> <?= $date ?></p>

        </header>

        <hr style="height:5px; color: #09a7eb; margin-top:-20px">
        <div class="contents" style="margin-top: 25px; padding: 15px; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.6;">
            <h3 style="text-align: center; margin-top: -40px;"><?= htmlspecialchars($businessName)  ?></h3>

            <p><strong>Received from</strong> <span class="underlined"><?= htmlspecialchars($name) ?></span></p>

            <p><strong>Address </strong> <span class="underlined"><?= htmlspecialchars($mamyrAddress) ?></span></p>

            <p><strong>Business Name/Style </strong> <span class="underlined"><?= htmlspecialchars($businessName) ?></span></p>

            <p>
                <strong>Total Amount:</strong> <span class="underlined"><?= ucwords($amountInWords) ?>
                    (<strong>₱ <?= number_format($totalBill, 2) ?></strong>) </span>
            </p>

            <p>
                <strong>Amount Paid of </strong> <span class="underlined"> <?= $payment ?> </span> &nbsp;
                <strong>Payment For:</strong> <span class="underlined"><?= htmlspecialchars($bookingType) ?> Booking</span>
            </p>

            <?php if (!empty($services)): ?>
                <p><strong>Included Services:</strong>
                    <span class="underlined">
                        <?= htmlspecialchars(implode(', ', array_unique($services))) ?>
                    </span>
                </p>
            <?php endif; ?>


            <p><strong>Requested By:</strong> <span class="underlined"><?= htmlspecialchars($adminName) ?></span></p>

            <hr style="width: 200px; height: 1px; background-color: black; border: none; margin-top: 15px; margin-left: auto;" />

            <p style="text-align: right; margin-top: 3px; margin-right: 30px;">
                Authorized Signature
            </p>
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