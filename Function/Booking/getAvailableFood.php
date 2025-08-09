<?php
require '../../Config/dbcon.php';

$ageGroup = 'Adult';
$getFoodItemQuery = $conn->prepare("SELECT * FROM menuItems WHERE ageGroup = ? ORDER BY foodCategory ASC");
$getFoodItemQuery->bind_param("s", $ageGroup);
$getFoodItemQuery->execute();
$getFoodItemResult = $getFoodItemQuery->get_result();

if ($getFoodItemResult->num_rows > 0) {
    while ($row = $getFoodItemResult->fetch_assoc()) {
        $categoryName[] = $row['foodCategory'];
        $foodItem[] = $row;
    }
}
