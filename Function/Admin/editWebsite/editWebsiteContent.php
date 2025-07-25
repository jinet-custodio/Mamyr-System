<?php
require '../../../Config/dbcon.php'; // adjust path as needed

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['sectionName'])) {
    http_response_code(400);
    echo "Invalid input";
    exit;
}

$sectionName = $data['sectionName'];
unset($data['sectionName']); // remove this key to only keep field data

foreach ($data as $title => $content) {
    $stmt = $conn->prepare("UPDATE websiteContents SET content = ?, lastUpdated = NOW() WHERE sectionName = ? AND title = ?");
    $stmt->bind_param("sss", $content, $sectionName, $title);
    $stmt->execute();
}

echo "Update successful!";
