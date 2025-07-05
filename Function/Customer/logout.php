<?php
session_start();
require '../Config/dbcon.php';

session_destroy();
header('Location: ../index.php');
exit();
