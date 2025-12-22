<?php

function canUserBook($conn, $userID)
{
    $pendingID = 1;
    $approvedID = 2;
    $reservedID = 3;

    $searchBookingQuery = $conn->prepare(
        "SELECT COUNT(bookingID) AS numberOfBooking 
        FROM booking 
        WHERE userID = ? AND bookingStatus IN (?,?,?)"
    );
    $searchBookingQuery->bind_param('iiii', $userID, $pendingID, $approvedID, $reservedID);
    $searchBookingQuery->execute();

    $result = $searchBookingQuery->get_result();
    $row = $result->fetch_assoc();
    $count = (int) $row['numberOfBooking'];

    return $count === 0;
}
