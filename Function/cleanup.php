<?php

require_once __DIR__ . '/../Config/dbcon.php';


require_once __DIR__ . '/Helpers/userFunctions.php';
require_once __DIR__ . '/Helpers/statusFunctions.php';
require_once __DIR__ . '/Helpers/categoryFunctions.php';


$affectedRows = resetExpiredOTPs($conn);
$addedUser = addToAdminTable($conn);
autoChangeStatus($conn);
$expiredStatus = changeToExpiredStatus($conn);
$doneStatus = changeToDoneStatus($conn);
$deleted = noPayment24hrs($conn);
$rejectBookedRequest = partnerAutoReject($conn);


$logMessage =
    "Added to admin table: $addedUser\n" .
    "Deleted OTPs: $affectedRows\n" .
    "Expired Status: $expiredStatus\n" .
    "Done Status: $doneStatus\n" .
    "Deleted rows in service unavailable: $deleted\n" .
    "Reject partner booking request: $rejectBookedRequest\n";


echo $logMessage;
file_put_contents(__DIR__ . '/cron_log.txt', $logMessage, FILE_APPEND);
$conn->close();
