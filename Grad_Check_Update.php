<?php include("../onlinetraining/includes/config.php")?>
<?php include("../onlinetraining/includes/functions.php")?>
<?php 
$conn = Connect();
$pmiConnect = pmimain_connect();

//first get customers in db. 
$custSelect = "SELECT ID, Fname, Lname FROM customers";

$custResult = $conn->query($custSelect);

while($row = $custResult->fetch_assoc()){
    $id = $row['ID'];
    echo $id;
    $GradSelect = "SELECT GradID FROM Current_Grad_Check 
                    WHERE GradFirstName = '".$row['Fname']."' 
                    AND GradLastName = '".$row['Lname']."' 
                    GROUP BY GradID";
    
    $gradResult = $pmiConnect->query($GradSelect);
    if($gradResult->num_rows > 0){
        //update record with ID as key
       while($rows = $gradResult->fetch_assoc()){
            echo "Grad Rec Found<br>";
            $GradID = $rows['GradID'];
            $update = "UPDATE customers SET Cert = ".$GradID." WHERE ID = '".$id."'";
            
            $conn->query($update);
        }
             }
    else{
        //no data found and thus enter a 0
        echo "No Grad Rec Found<br>";
        $update = "UPDATE customers SET Cert = 0 WHERE ID = '".$id."'";
        $conn->query($update);
    }
}
?>