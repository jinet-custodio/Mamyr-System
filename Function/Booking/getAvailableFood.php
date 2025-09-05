<?php
require '../../Config/dbcon.php';

$adultFood = 'Adult';
$availableID = 1;
$getFoodItemQuery = $conn->prepare("SELECT * FROM menuitem WHERE ageGroup = ? AND availabilityID = ?");
$getFoodItemQuery->bind_param("si", $adultFood, $availableID);
$getFoodItemQuery->execute();
$getFoodItemResult = $getFoodItemQuery->get_result();


$chickenCategory = [];
$porkCategory = [];
$beefCategory = [];
$pastaCategory = [];
$vegetablesCategory = [];
$seafoodCategory = [];
$dessertCategory = [];
$drinkCategory = [];
if ($getFoodItemResult->num_rows > 0) {
    while ($row = $getFoodItemResult->fetch_assoc()) {
        $categoryName = $row['foodCategory'];
        if ($categoryName === 'Chicken') {
            $chickenCategory[] = $row;
        } elseif ($categoryName === 'Pork') {
            $porkCategory[] = $row;
        } elseif ($categoryName === 'Beef') {
            $beefCategory[] = $row;
        } elseif ($categoryName === 'Pasta') {
            $pastaCategory[] = $row;
        } elseif ($categoryName === 'Vegetables') {
            $vegetablesCategory[] = $row;
        } elseif ($categoryName === 'Seafood') {
            $seafoodCategory[] = $row;
        } elseif ($categoryName === 'Drink') {
            $drinkCategory[] = $row;
        } elseif ($categoryName === 'Dessert') {
            $dessertCategory[] = $row;
        }
    }
}

echo json_encode([
    'chickenCategory'  => $chickenCategory,
    'pastaCategory'  => $pastaCategory,
    'porkCategory'  => $porkCategory,
    'beefCategory'  => $beefCategory,
    'vegieCategory'  => $vegetablesCategory,
    'seafoodCategory'  => $seafoodCategory,
    'drinkCategory' => $drinkCategory,
    'dessertCategory' => $dessertCategory

]);
$getFoodItemResult->close();
