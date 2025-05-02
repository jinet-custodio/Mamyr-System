<?php
header('Content-Type: application/json');
require '../../Config/dbcon.php';
session_start();

$packageID = isset($_GET['packageID']) ? intval($_GET['packageID']) : 0;

$sql = "SELECT duration, capacity, resortServiceID FROM packages WHERE packageID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $packageID);
$stmt->execute();
$result = $stmt->get_result();

$package = $result->fetch_assoc();
echo json_encode($package);
