<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Summary - Mamyr Resort and Events Place</title>
    <link rel="icon" type="image/x-icon" href="../../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <!-- <link rel="stylesheet" href="../../../Assets/CSS/bootstrap.min.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <!-- CSS Link -->
    <link rel="stylesheet" href="../../../Assets/CSS/Customer/Account/reservationSummary.css" />
</head>

<body>
    <div class="container">
        <div class="backButtonContainer">
            <a href="bookingHistory.php"><img src="../../../Assets/Images/Icon/arrow.png" alt="Back Button"
                    class="backButton"></a>
        </div>

        <div class="PendingContainer">

            <div class="leftPendingContainer">

                <img src="../../../Assets/Images/Icon/pending.png" alt="Pending Icon" class="PendingIcon">
                <h4 class="pendingTitle">Your reservation is pending for approval </h4>
                <h6 class="pendingSubtitle">Your request has been sent to the admin. Please wait for the approval of
                    your reservation.</h6>

                <button class="btn btn-success w-100 mt-3">Make a Down Payment</button>
                <button class="btn btn-secondary w-100">Make Another Reservation</button>
            </div>
            <div class="rightPendingContainer">
                <h3 class="rightContainerTitle">Reservation Summary
                </h3>
            </div>


        </div>




    </div>
</body>

</html>