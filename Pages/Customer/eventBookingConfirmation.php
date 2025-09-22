<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../Config/dbcon.php';
date_default_timezone_set('Asia/Manila');

session_start();
require_once '../../Function/sessionFunction.php';
checkSessionTimeout($timeout = 3600);

$userID = $_SESSION['userID'];
$userRole = $_SESSION['userRole'];

if (isset($_SESSION['userID'])) {
    $stmt = $conn->prepare("SELECT userID FROM user WHERE userID = ?");
    $stmt->bind_param('i', $_SESSION['userID']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }

    if (!$user) {
        $_SESSION['error'] = 'Account no longer exists';
        session_unset();
        session_destroy();
        header("Location: ../register.php");
        exit();
    }
}

if (!isset($_SESSION['userID']) || !isset($_SESSION['userRole'])) {
    header("Location: ../register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <?php
    $query = $conn->prepare("SELECT firstName, middleInitial, lastName FROM user WHERE userID = ? AND userRole = ?");
    $query->bind_param('ii', $userID, $userRole);
    $query->execute();
    $result =  $query->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $firstName = trim(ucfirst($data['firstName']));
        $lastName = trim(ucfirst($data['lastName']));
        $middleInitial = trim(ucfirst($data['middleInitial'])) ?? '';

        $name = ucfirst($firstName) . " " .
            ucfirst($middleInitial) . " " .
            ucfirst($lastName);
    }
    ?>


    <?php


    function getMenuItem($items, $query)
    {
        $menuItems = [];
        foreach ($items as $item) {
            $itemName = trim($item);
            $query->bind_param('s',  $itemName);
            if ($query->execute()) {
                $result = $query->get_result();
                if ($result->num_rows > 0) {
                    while ($data = $result->fetch_assoc()) {
                        $menuItems[] = [
                            'foodItemID' => $data['foodItemID'],
                            'foodName'   => $data['foodName']
                            // 'foodPrice' => $data['foodPrice']
                        ];
                    }
                } else {
                    error_log("No data");
                }
            } else {
                error_log("Error: " . $query->error);
            }
        }

        return $menuItems;
    }

    if (isset($_POST['eventBN'])) {

        $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
        $guestNo = intval($_POST['guestNo']);
        $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
        $additionalRequest = !empty($_POST['additionalRequest'])
            ? mysqli_real_escape_string($conn, $_POST['additionalRequest'])
            : 'N/A';


        //Date and time
        $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
        $eventStartTime = mysqli_real_escape_string($conn, $_POST['eventStartTime']);

        $startDateObj = new DateTime("$eventDate $eventStartTime");


        $endDateObj = clone $startDateObj;


        $endDateObj->modify('+5 hours');


        $startDate = $startDateObj->format('Y-m-d H:i:s');
        $endDate = $endDateObj->format('Y-m-d H:i:s');

        $formattedEventDate = $startDateObj->format('F d, Y');
        $formattedEventTime = $startDateObj->format('g:i A') . " to " . $endDateObj->format('g:i A');

        //Food 
        $chickenSelected = !empty($_POST['chickenSelections']) ? array_map('trim',  $_POST['chickenSelections']) : [];
        $porkSelected = !empty($_POST['porkSelections']) ? array_map('trim',  $_POST['porkSelections']) : [];
        $pastaSelected = !empty($_POST['pastaSelections']) ? array_map('trim',  $_POST['pastaSelections']) : [];
        $beefSelected = !empty($_POST['beefSelections']) ? array_map('trim',  $_POST['beefSelections']) : [];
        $vegieSelected = !empty($_POST['vegieSelections']) ? array_map('trim',  $_POST['vegieSelections']) : [];
        $seafoodSelected = !empty($_POST['seafoodSelections']) ? array_map('trim',  $_POST['seafoodSelections']) : [];
        $drinkSelected = !empty($_POST['drinkSelections']) ? array_map('trim',  $_POST['drinkSelections']) : [];
        $dessertSelected = !empty($_POST['dessertSelections']) ? array_map('trim',  $_POST['dessertSelections']) : [];

        $getMenuItemQuery = $conn->prepare("SELECT * FROM `menuitem` WHERE foodName = ?");
        $allMenus = [];
        $allMenus['chicken'] = $chickenItems = getMenuItem($chickenSelected, $getMenuItemQuery);
        $allMenus['pork'] =  $porkItems = getMenuItem($porkSelected, $getMenuItemQuery);
        $allMenus['pasta'] =  $pastaItems = getMenuItem($pastaSelected, $getMenuItemQuery);
        $allMenus['beef'] =  $beefItems = getMenuItem($beefSelected, $getMenuItemQuery);
        $allMenus['vegie'] =  $vegieItems = getMenuItem($vegieSelected, $getMenuItemQuery);
        $allMenus['seafood'] =  $seafoodItems = getMenuItem($seafoodSelected, $getMenuItemQuery);
        $allMenus['drink'] =  $drinkItems = getMenuItem($drinkSelected, $getMenuItemQuery);
        $allMenus['dessert'] =  $dessertItems = getMenuItem($dessertSelected, $getMenuItemQuery);

        //Venue
        $eventVenue = mysqli_real_escape_string($conn, $_POST['eventVenue']);

        $getVenuePrice = $conn->prepare('SELECT * FROM `resortamenity` WHERE RServiceName = ?');
        $getVenuePrice->bind_param('s', $eventVenue);
        if ($getVenuePrice->execute()) {
            $result = $getVenuePrice->get_result();
            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $venueID = $data['resortServiceID'];
                $venuePrice = $data['RSprice'];
                $venueCapacity = $data['RScapacity'];
                $venueDescription = $data['RSdescription'];
            }
        }

        //Business Partner Service
        $additionalServiceSelected = [];

        if (!empty($_POST['additionalServiceSelected'])) {
            foreach ($_POST['additionalServiceSelected'] as $id => $service) {
                // Only keep if checkbox was actually selected
                if (isset($service['selected'])) {
                    $additionalServiceSelected[$id] = [
                        'selected' => trim($service['selected']),
                        'PBName' => trim($service['PBName'] ?? ''),
                        'PBPrice' => trim($service['PBPrice'] ?? ''),
                        'partnershipServiceID' => trim($service['partnershipServiceID'] ?? ''),
                    ];
                }
            }
        }
        $_SESSION['eventFormData'] = $_POST;

        // var_dump($additionalServiceSelected);
    }

    // echo '<pre>';
    // print_r($allMenus);
    // echo '</pre>';
    ?>

    <header>
        <div class="backToSelection" id="backToSelection">
            <a href="eventBooking.php" class="btn"><img src="../../Assets/Images/Icon/arrowBtnBlue.png"
                    alt="Back Button Image"></a>
        </div>
        <div class="titleContainer">
            <h1 class="eventTitle" id="eventTitle">EVENT BOOKING</h1>
        </div>
    </header>
    <form action="../../Function/Booking/eventBooking.php" method="POST">
        <main class="card">
            <section class="event-details-container">
                <h4>Event Details</h4>
                <div class="input-container">
                    <label for="customerName">Customer Name: </label>
                    <input type="text" name="customerName" value="<?= htmlspecialchars($name) ?>" readonly>
                </div>
                <div class="input-container">
                    <label for="eventType">Type of Event</label>
                    <input type="text" name="eventType" value="<?= htmlspecialchars($eventType) ?>">
                </div>

                <div class="input-container">
                    <label for="paxNumber">Number of People</label>
                    <input type="text" name="paxNumber" value="<?= htmlspecialchars($guestNo) ?> people">
                    <input type="hidden" name="guestNo" value="<?= $guestNo ?>">
                </div>
            </section>

            <section class="venue-details">

                <div class="input-container">
                    <label for="eventVenue">Venue of Event</label>
                    <input type="text" name="eventVenue" value="<?= htmlspecialchars($eventVenue) ?>">
                </div>
                <div class="input-container">
                    <p>Venue Description</p>
                    <textarea cols="30" rows="5" name="venueDescription" id="venueDescription" readonly><?= !empty($venueDescription) ? htmlspecialchars(ucfirst(strtolower($venueDescription))) : 'N/A' ?></textarea>
                </div>
            </section>


            <div class="input-container">
                <p>Additional Request</p>
                <textarea cols="30" rows="5" name="additionalRequest" id="additionalRequest"><?= !empty($additionalRequest) ? htmlspecialchars($additionalRequest) : '' ?></textarea>
            </div>

            <section class="date-container">
                <h4> Date & Time</h4>
                <div class="input-container">
                    <label for="formattedEventDate">Event Date: </label>
                    <input type="text" name="formattedEventDate" value="<?= htmlspecialchars($formattedEventDate) ?>">
                </div>
                <div class="input-container">
                    <label for="eventTime">Event Time: </label>
                    <input type="text" name="eventTime" value="<?= htmlspecialchars($formattedEventTime) ?>">

                </div>
            </section>

            <?php if (!empty($allMenus)) { ?>
                <section class="food-container">
                    <h4>Food, Drinks & Dessert</h4>

                    <?php
                    $selectedFoodCount = 0;
                    $hasMenuItems = false;
                    foreach ($allMenus as $items) {
                        if (!empty($items)) {
                            $hasMenuItems = true;
                            break;
                        }
                    }
                    ?>

                    <?php if ($hasMenuItems) { ?>
                        <?php foreach ($allMenus as $category => $items) { ?>
                            <?php if (!empty($items)) {
                                $selectedFoodCount++ ?>
                                <h3><?= htmlspecialchars(ucfirst($category)) ?></h3>
                                <?php foreach ($items as $item) {
                                ?>
                                    <label><?= $item['foodName'] ?></label>
                                    <input type="hidden" name="foodIDs[]" value="<?= $item['foodItemID'] ?>">
                                <?php } ?>

                            <?php } ?>
                        <?php } ?>

                    <?php } else { ?>
                        <p>No menu selected.</p>
                    <?php } ?>
                </section>
            <?php } ?>

            <section class="additional-container">
                <h4>Additional Services</h4>
                <?php
                $additionalServicePrice = 0;
                if (!empty($additionalServiceSelected)) { ?>


                    <?php foreach ($additionalServiceSelected as $serviceID => $service) {
                        $additionalServicePrice += $service['PBPrice'] ?>
                        <div class="form-group">
                            <label><?= htmlspecialchars($service['PBName']) ?> &mdash; ₱<?= number_format($service['PBPrice'], 2) ?></label>
                            <input type="hidden"
                                name="additionalServiceSelected[]"
                                value="<?= htmlspecialchars($serviceID) ?>"
                                class="form-control">
                        </div>
                    <?php } ?>
            </section>
        <?php } else { ?>
            <p>No additional service selected.</p>
        <?php } ?>


        <?php
        $chargeType = 'Food';
        $pricingType = 'Per Head';
        $getPricingID = $conn->prepare("SELECT pricingID, price FROM `servicepricing` WHERE chargeType = ? AND pricingType = ?");
        $getPricingID->bind_param('ss',  $chargeType, $pricingType);
        if ($getPricingID->execute()) {
            $result =  $getPricingID->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                $pricingID = intval($row['pricingID']);
                $pricePerHead = (float) $row['price'];
            }
        }

        $totalFoodPrice = 0;
        if ($selectedFoodCount > 0) {
            $totalFoodPrice = $guestNo * $pricePerHead;
        }
        $totalCost = $totalFoodPrice + $venuePrice + $additionalServicePrice;
        $downpaymentPrice = $totalCost * 0.3;
        ?>


        <section class="additionalServices">

        </section>

        <section class="payment-container">
            <h4>Payment Details</h4>
            <div class="input-container">
                <label for="paymentMethod"> Payment Method</label>
                <input type="text" name="paymentMethod" value="<?= htmlspecialchars($paymentMethod) ?>">
            </div>
            <div class="input-container">
                <label for="eventVenuePrice">Venue Price</label>
                <input type="text" name="eventVenuePrice" value="<?= !empty($venuePrice) ? '₱' . htmlspecialchars(number_format($venuePrice, 2)) : 'None' ?>">
            </div>
            <div class="input-container">
                <label for="totalFoodPrice">Menu Price</label>
                <input type="text" name="totalFoodPrice" value="<?= !empty($totalFoodPrice) ? '₱' . htmlspecialchars(number_format($totalFoodPrice, 2)) : 'None' ?>">
            </div>
            <div class="input-container">
                <label for="additionalServicePrice">Additional Service Price</label>
                <input type="text" name="additionalServicePrice" value="<?= !empty($additionalServicePrice) ? '₱' . htmlspecialchars(number_format($additionalServicePrice, 2)) : 'None' ?>">
            </div>
            <div class="input-container">
                <label for="downpayment">(Tentative) Downpayment (30%): </label>
                <input type="text" name="downpayment" value="₱<?= htmlspecialchars(number_format($downpaymentPrice, 2)) ?>">
            </div>
            <div class="input-container">
                <label for="totalCost">Total Cost (Tentative)</label>
                <input type="text" name="totalCost" value="₱<?= htmlspecialchars(number_format($totalCost, 2)) ?>">
            </div>
        </section>


        <section class="notes-container">

            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>The price shown is for the event venue only.</p>
            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>The admin will verify the number of guests and the selected menu to calculate the total cost.</p>
            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>There will be a changes in downpayment amount once the total bill is computed</p>
            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>Full payment must be given to the admin before the start of the event.</p>
            <p><i class="fa-solid fa-circle-info fa-flip" style="color: #74C0FC;"></i>Cooking and sticking decoration that will damage the venue are prohibited.</p>
        </section>

        <div class="hidden-inputs">

            <input type="hidden" name="eventDate" value="<?= $eventDate ?>">
            <input type="hidden" name="eventStartTime" value="<?= $eventStartTime ?>">
            <input type="hidden" name="startDate" value="<?= $startDate ?>">
            <input type="hidden" name="endDate" value="<?= $endDate ?>">
            <input type="hidden" name="menuIDs" value="<? $menuIDs ?>">
            <input type="hidden" name="venueID" value="<?= $venueID ?>">
            <input type="hidden" name="pricingID" value="<?= $pricingID ?>">
        </div>

        <div class="button-container">
            <button type="submit" class="btn" name="eventBook">Book Now</button>
        </div>
        </main>
    </form>
</body>

</html>