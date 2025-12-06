<?php

use Mpdf\Tag\Em;
//Reminder for bookings not yet reviewed (admin)
function reminderBookingReview($conn, $env)
{
    try {
        $pendingID = 1;
        $getUnreviewedBooking = $conn->prepare("SELECT bookingID, bookingCode FROM booking WHERE bookingStatus = ?");
        $getUnreviewedBooking->bind_param('i', $pendingID);

        if (!$getUnreviewedBooking->execute()) {
            throw new Exception("Executing the query fails. Error: " . $getUnreviewedBooking->error);
        }

        $result = $getUnreviewedBooking->get_result();
        $bookings = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row['bookingCode'];
            }
        }

        $getUnreviewedBooking->close();

        if (!empty($bookings)) {
            $bookingListHtml = '<ul>';
            foreach ($bookings as $booking) {
                $bookingListHtml .= '<li> <strong> ' . htmlspecialchars($booking) . '</strong></li>';
            }
            $bookingListHtml .= '</ul>';


            $getAdmins = $conn->prepare("SELECT a.userID, a.adminID, u.email FROM admin a
                    LEFT JOIN user u ON a.userID = u.userID");
            if (!$getAdmins->execute()) {
                throw new Exception("Executing the query fails. Error: " . $getAdmins->error);
            }

            $result = $getAdmins->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $email = $row['email'];
                    $userID = $row['userID'];
                    $name = 'Admin';
                    $subject = "Booking Pending Review";
                    $message = '<body style="font-family: Arial, sans-serif;         background-color: #f4f4f4; padding: 20px; margin: 0;">

                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <tr style="background-color:#365CCE;">
                                    <td style="text-align:center; ">
                                        <h4 style="font-family:Poppins Light; color:#ffffff; font-size: 18px;  margin-top: 25px">Pending Booking/s Needing Review!</h4>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: 10px 0 10px;">Hello, Admin</p>
                                        <p style="font-size: 12px; margin: 8px 0;">The system detects pending customer booking request/s. The following booking code/s require your review:
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;"> ' . $bookingListHtml . '
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;">Please log in to the system to review them at your earliest convenience.
                                        </p>
                                        <br>
                                        <p style="font-size: 14px;">Warm regards,</p>
                                        <p style="font-size: 14px; font-weight: bold;">Mamyr Resort and Events Place Website.</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
            ';
                    if (!sendEmail($email, $name, $subject, $message, $env)) {
                        throw new Exception("Failed Sending Email to $email. Error: ");
                    }



                    $receiver = "Admin";
                    $message = "You have " . count($bookings) . " pending booking request(s) to review!";
                    $insertNotification = $conn->prepare("INSERT INTO notification(receiverID, message, receiver) VALUES(?, ?, ?)");
                    $insertNotification->bind_param("iss", $userID, $message, $receiver);
                    if (!$insertNotification->execute()) {
                        $conn->rollback();
                        throw new Exception("Failed to insert notification");
                    }
                }
            }

            return "You have " . count($bookings) . " pending booking request(s) to review!";
        } else {
            return "You don't have booking to review!";
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        return "You don't have booking to review!";
    }
}

//Reminder for payments not yet reviewed (admin)
function reminderPaymentReview($conn, $env)
{
    try {
        $paymentSentID = 5;
        $getUnreviewedPayment = $conn->prepare("SELECT b.bookingID, b.bookingCode FROM confirmedbooking cb
            LEFT JOIN booking b ON cb.bookingID = b.bookingID 
            WHERE paymentStatus = ?");
        $getUnreviewedPayment->bind_param('i', $paymentSentID);

        if (!$getUnreviewedPayment->execute()) {
            throw new Exception("Executing the query fails. Error: " . $getUnreviewedPayment->error);
        }

        $result = $getUnreviewedPayment->get_result();
        $payments = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row['bookingCode'];
            }
        }

        $getUnreviewedPayment->close();

        if (!empty($payments)) {
            $bookingListHtml = '<ul>';
            foreach ($payments as $payment) {
                $bookingListHtml .= '<li> <strong> ' . htmlspecialchars($payment) . '</strong></li>';
            }
            $bookingListHtml .= '</ul>';


            $getAdmins = $conn->prepare("SELECT a.userID, a.adminID, u.email FROM admin a
                    LEFT JOIN user u ON a.userID = u.userID");
            if (!$getAdmins->execute()) {
                throw new Exception("Executing the query fails. Error: " . $getAdmins->error);
            }

            $result = $getAdmins->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $email = $row['email'];
                    $userID = $row['userID'];
                    $name = 'Admin';
                    $subject = "Payment Pending Review";
                    $message = '<body style="font-family: Arial, sans-serif;         background-color: #f4f4f4; padding: 20px; margin: 0;">

                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <tr style="background-color:#365CCE;">
                                    <td style="text-align:center; ">
                                        <h4 style="font-family:Poppins Light; color:#ffffff; font-size: 18px;  margin-top: 25px">Pending Payment/s Needing Review!</h4>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: 10px 0 10px;">Hello, Admin</p>
                                        <p style="font-size: 12px; margin: 8px 0;">The system detects unreviewed payment/s. The following booking code/s require your review:
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;"> ' . $bookingListHtml . '
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;">Please log in to the system to review them at your earliest convenience.
                                        </p>
                                        <br>
                                        <p style="font-size: 14px;">Warm regards,</p>
                                        <p style="font-size: 14px; font-weight: bold;">Mamyr Resort and Events Place Website.</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
            ';
                    if (!sendEmail($email, $name, $subject, $message, $env)) {
                        throw new Exception("Failed Sending Email to $email. Error: ");
                    }



                    $receiver = "Admin";
                    $message = "You have " . count($payments) . " pending booking request(s) to review!";
                    $insertNotification = $conn->prepare("INSERT INTO notification(receiverID, message, receiver) VALUES(?, ?, ?)");
                    $insertNotification->bind_param("iss", $userID, $message, $receiver);
                    if (!$insertNotification->execute()) {
                        $conn->rollback();
                        throw new Exception("Failed to insert notification");
                    }
                }
            }

            return "You have " . count($payments) . " pending payment/s to review!";
        } else {
            return "You don't have payment to review!";
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        return "You don't have payment to review!";
    }
}


//Reminder for customers not yet sending payment (customer)
function paymentReminder($conn, $env)
{
    try {
        $unpaidID = 1;
        $approvedBooking = 2;
        $getUnpaidBooking = $conn->prepare("SELECT u.firstName, u.email,  b.bookingID, b.bookingCode, cb.createdAt FROM confirmedbooking cb
            LEFT JOIN booking b ON cb.bookingID = b.bookingID 
            LEFT JOIN user u ON b.userID = u.userID
            WHERE b.bookingStatus = ? AND cb.paymentStatus = ? AND cb.createdAt BETWEEN NOW() - INTERVAL 14 HOUR AND NOW() - INTERVAL 12 HOUR");
        $getUnpaidBooking->bind_param('ii', $approvedBooking, $unpaidID);

        if (!$getUnpaidBooking->execute()) {
            throw new Exception("Executing the query fails. Error: " . $getUnpaidBooking->error);
        }

        $result = $getUnpaidBooking->get_result();
        $count = 0;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $count++;
                $bookingCode = $row['bookingCode'];
                $email = $row['email'];
                $name = $row['firstName'];
                $subject = "Payment Reminder";
                $message = '<body style="font-family: Arial, sans-serif;         background-color: #f4f4f4; padding: 20px; margin: 0;">

                            <table align="center" width="100%" cellpadding="0" cellspacing="0"
                                style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

                                <tr style="background-color:#365CCE;">
                                    <td style="text-align:center; ">
                                        <h4 style="font-family:Poppins Light; color:#ffffff; font-size: 18px;  margin-top: 25px">Complete Your Booking to Secure Your Spot!</h4>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 30px; text-align: left; color: #333333;">
                                        <p style="font-size: 12px; margin: 10px 0 10px;">Hello, ' . $name . '</p>
                                        <p style="font-size: 12px; margin: 8px 0;">We noticed you created a booking (' . $bookingCode . ') 12 hours ago, but you haven’t sent the down payment yet.
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;"> To secure your slot, please complete your payment and send the receipt in the website.
                                        </p>
                                        <p style="font-size: 12px; margin: 8px 0;">If you have any questions, feel free to contact us.
                                        </p>
                                        <p style="margin: 10px 0 0;"> You can contact us directly here: <a
                                                href="https://www.facebook.com/messages/t/100888189251567"
                                                style="color: #007bff; text-decoration: none;"> Message us on Facebook</a> </p>

                                        <p style=" font-size: 14px; margin: 20px 0 0;">We look forward to welcoming you soon!</p>
                                        <br>
                                        <p style="font-size: 14px;">Thank you,</p>
                                        <p style="font-size: 14px; font-weight: bold;">Mamyr Resort and Events Place Website.</p>
                                    </td>
                                </tr>
                            </table>
                        </body>
            ';
                if (!sendEmail($email, $name, $subject, $message, $env)) {
                    throw new Exception("Failed Sending Email to $email. Error: ");
                }
            }
        }

        return 'Payment reminder sent to ' . $count . ' customer/s';

        $getUnpaidBooking->close();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        return "You don't have payment to review!";
    }
}
