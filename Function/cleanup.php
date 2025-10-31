<?php

require_once __DIR__ . '/../Config/dbcon.php';


require_once __DIR__ . '/Helpers/userFunctions.php';
require_once __DIR__ . '/Helpers/statusFunctions.php';
require_once __DIR__ . '/Helpers/categoryFunctions.php';

$affectedRows = resetExpiredOTPs($conn);

echo $affectedRows;


$conn->close();
