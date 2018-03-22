<?php 
include("../onlinetraining/includes/config.php");

$conn = log_db();

$insert = "INSERT INTO cron_scripts (file) VALUES ('Test')";
$conn->query($insert);
?>