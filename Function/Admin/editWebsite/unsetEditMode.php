<?php
session_start();
unset($_SESSION['edit_mode']);
header("Location: ../../../../../Pages/Admin/adminDashboard.php");
exit();
