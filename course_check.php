<!-- This script will check the orderplaced table for any order that have expired and remove them from the customer portal and archived tables in the database. 
Orders will still be in place for viewing of accounting and such.
Class will be removed after 1 year of purchase date.
This will not remove it from transactions reports!-->

<!--
Fequency: Daily @12am

-->
<?php 
function Connect(){
            // Info to login to server
            $servername = "localhost";
            $username = "pmimd_Master";
            $password = "D~(8oTNkRzP9";
            $dbname = "pmimd_prodcenter";

            // Create and check connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            return $conn;
        }
function log_db(){
            // Info to login to server
            $servername = "localhost";
            $username = "pmimd_Master";
            $password = "D~(8oTNkRzP9";
            $dbname = "pmimd_log";

            // Create and check connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            // Check connection
            if ($conn->connect_error) {
                return $conn->connect_error;
            } 
            else{
                return $conn;
            }
        }
?>
<?php

    $conn = Connect();
    $log_update = log_db();
    $select = "SELECT CustomerID, ProdID FROM orderplaced WHERE DATE(DateOrdered) < DATE(DATE_SUB(NOW(),INTERVAL 1 YEAR))";
    $query_data = $conn->query($select);

    while($order_data = $query_data->fetch_assoc()){
        $portal_data = "DELETE FROM custportal WHERE custID = '".$order_data['CustomerID']."' AND prodID = '".$order_data['ProdID']."'";
        $archive_data = "DELETE FROM custarchive WHERE custID = '".$order_data['CustomerID']."' AND prodID = '".$order_data['ProdID']."'";
        $conn->query($portal_data);
        $conn->query($archive_data);
    }
$cron_script_log = "INSERT INTO cron_scripts (file) VALUES('".$_SERVER['SCRIPT_FILENAME']."')";
$log_update->query($cron_script_log);
?>
