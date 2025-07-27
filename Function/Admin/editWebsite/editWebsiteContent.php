<?php
require '../../../Config/dbcon.php'; // adjust path as needed

// $data = json_decode(file_get_contents("php://input"), true);

file_put_contents('debug_backend.log', print_r($data, true));


if (!$data || !isset($data['sectionName'])) {
    http_response_code(400);
    echo "Invalid input";
    exit;
}

$sectionName = $data['sectionName'];
unset($data['sectionName']);

foreach ($data as $title => $content) {
    $stmt = $conn->prepare("UPDATE websiteContents SET content = ?, lastUpdated = NOW() WHERE sectionName = ? AND title = ?");
    $stmt->bind_param("sss", $content, $sectionName, $title);
    $stmt->execute();
}

echo "Update successful!";
