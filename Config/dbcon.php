<?php


define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'mamyr');


$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if (!$conn) {
    die('Connection failed:' . mysqli_connect_error());
}


date_default_timezone_set('Asia/Manila');

$conn->query("SET time_zone = '+08:00'");
