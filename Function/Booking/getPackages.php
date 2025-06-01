<?php
header('Content-Type: application/json');
require '../../Config/dbcon.php';
session_start();

$categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0;

$sql = "SELECT packageID, packageName, packageDescription, Pduration, Pcapacity, Pprice FROM packages WHERE PcategoryID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryID);
$stmt->execute();
$result = $stmt->get_result();

$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}

echo json_encode($packages);
