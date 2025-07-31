<!DOCTYPE html>
<html>

<head>
    <title>Sales Report</title>

    <style>
    body::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 570px;
        height: 570px;
        background: url('../../Assets/Images/MamyrLogo.png');
        background-position: center;
        background-repeat: no-repeat;
        background-size: contain;
        opacity: 0.08;
        z-index: -1;
        pointer-events: none;
    }

    .logo {
        height: 40px;
        position: absolute;
        top: 5%;
        left: 7%;
    }

    .header-title {
        font-size: 20px;

    }

    .headerTextContainer {
        margin-top: -10px;
    }

    h4 {
        font-family: "Poppins Light";
        font-size: 12px;
    }

    .section-title {
        text-align: center;
        font-size: 22px;
    }

    hr {
        width: 90%;
        height: 2px;
        background-color: black;
    }

    .request {
        float: right;
        margin-top: -60px;
    }

    .contents {
        margin: 5px 96px 5px 95px;
    }

    .table {
        width: 100%;
        margin-top: 75px;
        border: 1px solid black;
        border-collapse: collapse;
        text-align: center;

    }

    tr tr,
    td,
    th {
        border: 1px solid black;
        padding: 10px;

    }

    .no-data-text {
        font-weight: bold;
        color: red;
    }

    .signatories {
        display: flex;
        justify-content: center;
        margin-top: 70px;
        gap: 100px;
    }
    </style>
</head>

<body>

    <header>
        <img src="../../Assets/Images/MamyrLogo.png" alt="Mamyr Resort and Events Place" class="logo">

        <h1 class="header-title" style="text-align: center;">Mamyr Resort & Events Place</h2>

            <div class="headerTextContainer">
                <h4 style="text-align:center;">Gabihan, San Ildefonso, Bulacan <br> mamyresort@gmail.com | (0998) 962
                    4697
                </h4>

            </div>
    </header>
    <hr>
    <main>
        <section class="contents">
            <h2 class="section-title">Sales Report</h2>
            <p><strong>Report Generated: </strong> Wednesday, July 30, 2025 (1:29 PM)
            </p>
            <p><strong>Date Range:</strong> July 01, 2025 to July 30, 2025</p>
            <p class="request"><strong>Requested By:</strong>Louise Anne S. Bartolome</p>
        </section>

        <section class="contents">
            <table class="table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Booking Type</th>
                        <th>Total Guest</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Payment Method</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td>001</td>
                        <td>Louise Anne Bartolome</td>
                        <td>Resort Booking</td>
                        <td>5</td>
                        <td>July 01, 2025</td>
                        <td>July 01, 2025</td>
                        <td>Cash</td>
                        <td>₱2,500</td>
                    </tr>

                    <tr>
                        <td>002</td>
                        <td>Shantal Gregorio</td>
                        <td>Hotel Booking</td>
                        <td>6</td>
                        <td>July 28, 2025</td>
                        <td>July 29, 2025</td>
                        <td>Gcash</td>
                        <td>₱3,500</td>
                    </tr>


                    <tr>
                        <td colspan="8" class="no-data-text">No bookings found for selected dates</td>
                    </tr>

                </tbody>
            </table>
        </section>

        <section class="contents">
            <h2 class="section-title" style="margin-top: 60px;">Report Summary</h2>
            <p><strong>Total Bookings:</strong> 2</p>
            <p><strong>Total Cost:</strong> ₱6,000</p>
        </section>

        <section class="signatories">
            <p><strong>Signed By:</strong> ________________________</p>
            <p><strong>Submitted By:</strong> ______________________</p>
        </section>
    </main>

</body>

</html>