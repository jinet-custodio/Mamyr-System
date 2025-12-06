<?php

require '../Config/dbcon.php';

require_once 'emailSenderFunction.php';

$env = parse_ini_file(__DIR__ . '/../.env');
require '../vendor/autoload.php';

require_once 'Helpers/emailFunctions.php';


$reminderBookingReviews = reminderBookingReview($conn, $env);
$reminderPaymentReview = reminderPaymentReview($conn, $env);
$paymentReminder = paymentReminder($conn, $env);
$logMessage =
    "Scheduled Date & Time: " . date('F. d, Y g:i A') .
    "Reminders: \n " .
    $reminderBookingReviews . "\n" .
    $reminderPaymentReview . "\n" .
    $paymentReminder . "\n";


echo '<pre>';
print_r($logMessage);
echo '</pre>';


file_put_contents(__DIR__ . '/cron_log.txt', $logMessage, FILE_APPEND);
