<!DOCTYPE html>
<html lang="en">

<head>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Summary</title>
        <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png ">
        <link rel="stylesheet" href="../../Assets/CSS/Customer/payment.css">
        <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
            integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />

        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    </head>
</head>

<body>

    <div class="backBtn">

        <a href="#" class="backBooking">
            <button class="btn btn-primary"><img src="../../Assets/Images/Icon/whiteArrow.png" alt=""
                    class="arrow"></button>
        </a>
    </div>
    <div class="container-fluid">
        <form action="#" method="$_POST"></form>
        <div class="card" style="width: 60rem; height:auto;">
            <div class="card-title">
                <h4 class="fw-bold">Booking Summary:</h4>
            </div>

            <h6 class="note">Reminder: Online bookings will only accept 30% of down payments through GCash for
                validation purposes, the rest of the bill shall be paid onsite.</h6>
            <div class="card-body">
                <div class="topSection">

                    <div class="eventSummary">
                        <h5 class="fw-bold">Booked Event:</h5>
                        <h6 class="eventType">Wedding Event</h6>

                        <div class="event-schedule text-muted">
                            <p id="eventDate" class="eventDate">May 7, 2025</p>
                            <p class="mx-1 mb-0">&bullet;</p>
                            <p id="eventDuration" class="eventDuration">5 hours</p>
                            <p class="mx-1 mb-0">&bullet;</p>
                            <p id="eventVenue" class="eventVenue">Main Function Hall</p>
                        </div>
                    </div>

                    <div class="price">
                        <h5 class="fw-bold">Billing Summary</h5>

                        <div class="totalBillContainer">
                            <h6 class="totalBillLabel text-muted">Total Bill:</h6>
                            <h6 class="totalBill text-muted">&#8369; 140,000</h6>
                        </div>

                        <div class="dpContainer">
                            <h6 class="dpLabel text-muted">Down Payment:</h6>
                            <h6 class="dp text-muted">- 30%</h6>
                        </div>

                        <div class="tdpContainer">
                            <h5 class="tdpLabel fw-bold">Your Down Payment is:</h5>
                            <h5 class="tdp ">&#8369; 42,000.00</h5>
                        </div>

                    </div>
                </div>

                <hr>
                <h4 class="fw-bold">Payment Procedure:</h4>
                <h6 class="note">Please make sure to make a down payment within THREE (3) DAYS to complete the booking
                    process.
                </h6>

                <div class="gcashContainer">
                    <div class="qr">
                        <h5 class="fw-bold">Pay via GCash QR Code</h5>
                        <img src="../../Assets/Images/PaymentImage/gcashQR.png" alt="GCash QR Code" class="qr-pic">
                    </div>
                    <div class="num">
                        <h5 class="fw-bold">Gcash Number </h5>
                        <h6 class="number">09050219888 </h6>
                        <h5 class="fw-bold">Account Name </h5>
                        <h6 class="name">Juan Dela Cruz</h6>
                    </div>
                </div>

                <div class="mb-5">
                    <h5 class="fw-bold">Proof of Down Payment: </h5>
                    <h6 class="text-muted">Please attach a screenshot of your payment.</h6>
                    <input class="form-control " type="file" id="proofUpload" accept="image/*">
                </div>

                <div class="mt-auto">
                    <button type="submit" class="btn btn-success btn-md w-100" name="bookRates">Pay Booking</button>
                </div>

            </div>
        </div>
        </form>
    </div>







    <!-- <div class="centered-wrapper">
        <div class="summary-box">
            <h5 class="fw-bold mb-3">Booking Summary:</h5>

            <div class="d-flex justify-content-between mb-3">

                <div class="left-info">
                    <h6 class="mb-1">Event:</h6>
                    <p class="mb-0">Wedding Event</p>
                    <div class="event-line text-muted">
                        <p id="event-date" class="mb-0">May 7, 2025</p>
                        <p class="mx-1 mb-0">&bullet;</p>
                        <p id="event-duration" class="mb-0">5 hours</p>
                        <p class="mx-1 mb-0">&bullet;</p>
                        <p id="event-venue" class="mb-0">Main Function Hall</p>
                    </div>
                </div>

                <div class="right-info text-end">
                    <small class="d-block">Subtotal:</small>
                    <strong>₱140,000.00</strong>
                    <div class="mb-2">
                        <small class="d-block">30% Downpayment:</small>
                        <strong id="downpayment">₱42,000.00</strong>
                    </div>
                    <p><strong>Total:</strong> ₱140,000.00</p>
                </div>
            </div>

            <p class="alert-msg">Please make a downpayment of 30% within 48 hours to proceed with the reservation.</p>

            <div class="payment-info">
                <div class="left-info">
                    <small>GCash Number:</small>
                    <p class="mb-0"><strong>0905-021-9888</strong></p>
                    <p><strong>Account Name:</strong> Juan Dela Cruz</p>
                </div>
                <div class="right-info">
                    <img src="../../Assets/Images/PaymentImage/gcashQR.png" alt="GCash QR Code" class="qr-pic mb-2">
                </div>
            </div>

            <div class="mb-2">
                <label for="proofUpload" class="form-label">Upload downpayment proof:</label>
                <input class="form-control form-control-sm" type="file" id="proofUpload" accept="image/*">
            </div>

            <button class="btn btn-success btn-sm w-100">Pay</button>
        </div>
    </div> -->


</body>


</html>