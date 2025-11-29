<?php

date_default_timezone_set('Asia/Manila');

function getEventCategory($conn, $eventTypeID)
{
    $getEventTypeQuery = $conn->prepare("SELECT `categoryID`, `categoryName` FROM `eventcategory` WHERE categoryID = ?");
    $getEventTypeQuery->bind_param("i", $eventTypeID);

    $result = $getEventTypeQuery->get_result();
    if ($result->num_rows === 0) {
        return null;
    }
    $row = $result->fetch_assoc();
    return [
        'eventCategoryID' => $row['categoryID'],
        'categoryName' => $row['categoryName']
    ];
}

function getPartnerType($conn)
{
    $getPartnerTypeQuery = $conn->prepare("SELECT * FROM partnershiptype");
    $getPartnerTypeQuery->execute();
    $getPartnerTypeResult = $getPartnerTypeQuery->get_result();
    $partnerTypes = [];
    if ($getPartnerTypeResult->num_rows > 0) {
        while ($row = $getPartnerTypeResult->fetch_assoc()) {
            $partnerTypes[] = $row;
        }
    }

    return  $partnerTypes;
}
