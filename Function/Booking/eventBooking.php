<?php

require '../../Config/dbcon.php';
session_start();

if (isset($_POST['eventBook'])) {
    $eventType = mysqli_real_escape_string($conn, $_POST['eventType']);
    $other_input = mysqli_real_escape_string($conn, $_POST['other_input']);
    $eventDate = mysqli_real_escape_string($conn, $_POST['eventDate']);
    $eventVenue = mysqli_real_escape_string($conn, $_POST['eventVenue']);
    $numberOfHours = mysqli_real_escape_string($conn, $_POST['numberOfHours']);
    $eventPax = mysqli_real_escape_string($conn, $_POST['eventPax']);
    $eventPackage = mysqli_real_escape_string($conn, $_POST['eventPackage']);
    $additionalNotes = mysqli_real_escape_string($conn, $_POST['additionalNotes']);
    $services = getServices($conn);
    $packages = getPackages($conn);
    $customPackages = getPackages($conn);

    if (($eventType !== '' && $other_input === '') || ($eventType === '' && $other_input !== '')) {
        if ($eventType === 'Birthday') {
        } elseif ($eventType === 'Wedding') {
        } elseif ($eventType === 'Team Building') {
        } elseif ($eventType === 'Christening/Dedication') {
        } elseif ($eventType === 'Thanksgiving Party') {
        } elseif ($eventType === 'Christmas Party') {
        } else {
        }
    }
}



//Get all the services
function getServices($conn)
{
    $selectServices = "SELECT * FROM services";
    $resultServices = mysqli_query($conn, $selectServices);
    if (mysqli_num_rows($resultServices) > 0) {
        $servicesData = mysqli_fetch_assoc($resultServices);
        while ($servicesData) {
            $services[] = $servicesData;
        }
    }

    return $services;
}

//Get all the packages
function getPackages($conn)
{
    $selectPackages = "SELECT * FROM packages";
    $resultPackages = mysqli_query($conn, $selectPackages);
    if (mysqli_num_rows($resultPackages) > 0) {
        $packagesData = mysqli_fetch_assoc($resultPackages);
        while ($packagesData) {
            $packages[] = $packagesData;
        }
    }

    return $packages;
}


//Get all the custom packages
function getCustomPackages($conn)
{
    $selectCustomPackages = "SELECT * FROM custompackages";
    $resultCustomPackages = mysqli_query($conn, $selectCustomPackages);
    if (mysqli_num_rows($resultCustomPackages) > 0) {
        $custompackagesData = mysqli_fetch_assoc($resultCustomPackages);
        while ($custompackagesData) {
            $customPackages[] = $custompackagesData;
        }
    }

    return $customPackages;
}
