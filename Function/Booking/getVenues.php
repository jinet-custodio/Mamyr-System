<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require '../../Config/dbcon.php';
session_start();

$categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0;

$sql = "
    SELECT DISTINCT rs.*
    FROM resortServices rs
    INNER JOIN packages p ON rs.resortServiceID = p.resortServiceID
    WHERE p.PcategoryID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryID);
$stmt->execute();
$result = $stmt->get_result();

$venues = [];
while ($row = $result->fetch_assoc()) {
    $venues[] = $row;
}

echo json_encode($venues);
