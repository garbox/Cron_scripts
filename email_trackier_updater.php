<?php include("../onlinetraining/includes/config.php")?>
<?php
// This function/application will take info from AFCollateral_Distribution table and get the campaign ID
// Then get campaign info from mailchimp: 
// if email has been sent. get info to update into database: sent_amount, sent_date, 
// repeat untill fetch_assoc is at eof.
    class Mailchimp{
	//Connection for the API, Paramters Needed, APIkey, URL, Request as POST PUT GET etc....)
	private function APIConnection($url, $Request, $data){
		
		$ch = curl_init($url);
	
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . '2c230f72ec2cf86b57a2f8d36d60194d-us12');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $Request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);   
		
		// Results 
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
        $return_data = array(
            'http_code' => $httpCode,
            'data'      => $result
        );
		
		return $return_data;
	}
    
    private function APIConnection_no_input($url, $Request){
		
		$ch = curl_init($url);
	
		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . '2c230f72ec2cf86b57a2f8d36d60194d-us12');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $Request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
		
		// Results 
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
        $return_data = array(
            'http_code' => $httpCode,
            'data'      => $result
        );
		
		return $return_data;
	}
	
	// List ID c843b5346c SP-Survery
	// List ID b68b12eba5 PMCC List
	// List ID 72bade8760 MLM List
	public function MCAddMember($listId) {
				
		$memberID = md5(strtolower($_SESSION['Profile']['email']));
        $url = 'https://us12.api.mailchimp.com/3.0/lists/' . $listId . '/members/' .$memberID;
		
		
		$json = json_encode([
			'email_address' => $_SESSION['Profile']['email'],
			'status'        => "subscribed", // "subscribed","unsubscribed","cleaned","pending"
			'merge_fields'  => [
				'FNAME'     => $_SESSION['Profile']['fname'],
				'LNAME'     => $_SESSION['Profile']['lname'],
				'ZIP' 		=> $_SESSION['Profile']['zip']
			]
		]);
		
		// Connection for API. 
		$APIConnect = new Mailchimp();
		$APIConnect->APIConnection($url, "PUT", $json);                                                                    
	}
    
    public function MCAddMemberToList($listId, $custData, $lead_Code) {
				
		$memberID = md5(strtolower($custData['email']));
        $url = 'https://us12.api.mailchimp.com/3.0/lists/' . $listId . '/members/' .$memberID;
		
		
		$json = json_encode([
			'email_address' => $custData['email'],
			'status'        => "subscribed", // "subscribed","unsubscribed","cleaned","pending"
			'merge_fields'  => [
				"FNAME"=> $custData['fname'],
                "LNAME" => $custData['lname'],
                "LEAD_CODE"=> $_SESSION['lead_code'],
                "ZIP"=> $custData['zip'],
                "JOB_TITLE" => $custData['jobtitle']
			]
		]);
		
		// Connection for API. 
		$APIConnect = new Mailchimp();
		$APIConnect->APIConnection($url, "PUT", $json);                                                                    
	}
    
    public function MC_update_list($data) {
				
		$memberID = md5(strtolower($data['email']));
		$url = 'https://us12.api.mailchimp.com/3.0/lists/cc2535b0e1/members/' .$memberID;
		
		$json = json_encode([
			'email_address' => $data['email'],
			'status_if_new' => "subscribed", // "subscribed","unsubscribed","cleaned","pending"
			'merge_fields'  => [
				'FNAME'     => $data['fname'],
				'LNAME'     => $data['lname'],
                'ADDRESS'  => $data['address'],
                'CITY'      => $data['city'],
				'ZIP' 		=> $data['zip']
			]
		]);
		
		// Connection for API. 
		$APIConnect = new Mailchimp();
		return $APIConnect->APIConnection($url, "PUT", $json);                                                                    
	}
    
    public function MCEcommerce_disabled($StoreID, $OrderID, $TotalSale) {
		
            $memberID = "4548cef640e47fed66ff5b97680a45dc";
            $campignID = "2ac9af9ed7";
            $url = 'https://us12.api.mailchimp.com/3.0/ecommerce/stores/'.$StoreID.'/orders';
            
            $json = json_encode([
                  'id' => $OrderID,
                  'customer' => [
                    'id' => $memberID
                  ],
                  'campaign_id' => $campignID,
                  'checkout_url' => 'https://www.pmimd.com/onlinetraining/payment/confirmation.php',
                  'currency_code' => 'USD',
                  'order_total' => $TotalSale,
            ]);
            // Connection for API. 
            $APIConnect = new Mailchimp();
            echo $APIConnect->APIConnection($apiKey, $url, "PUT", $json);  
                                                                  
	}	
    
    public function Get_List_data($list_id){
        $url = 'https://us12.api.mailchimp.com/3.0/lists/'.$list_id;
        $APIConnect = new Mailchimp();
        return $APIConnect->APIConnection_no_input($url, "GET");  
        
    }
    
    public function Get_campaign_info($campign_id){
        $url = 'https://us12.api.mailchimp.com/3.0/campaigns/'.$campign_id;
        $APIConnect = new Mailchimp();
        return $APIConnect->ApiConnection_no_input($url, "GET");
    }
}
?>

<?php 
    //set connection
    //set query for getting campaign ID where date is = to current date
    $conn = pmimain_connect();
    $query = "SELECT DISTINCT mc_campaign_id FROM AFCollateral_Distribution WHERE DATE(CURRENT_DATE()-1) = cdDateToSend";
    $result = $conn->query($query);

    while($campign_id = $result->fetch_assoc()){
        
        $campign_info = new Mailchimp();
        $campign_id = $campign_id["mc_campaign_id"];

        $data = (object)$campign_info->Get_campaign_info($campign_id);
        
        $data = json_decode($data->data);
        
        if($data->status == 'sent'){

            $update = "UPDATE AFCollateral_Distribution 
            SET cdQty = '".$data->emails_sent."', 
            cdQtySent = '".$data->emails_sent."', 
            cdDateSent = '".date("Y-m-j", strtotime($data->send_time))."'
            WHERE mc_campaign_id = '".$campign_id."'";
            
            echo "Email is ".$data->status ."<br>";
            $conn->query($update);
            echo $conn->error;
        }
        else{
            echo "Email is ".$data->status ."<br>";
        }
    }
?>

