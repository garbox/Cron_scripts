<?php include("../onlinetraining/includes/config.php")?>
<?php
// This function/application will take info from AFCollateral_Distribution table and get the campaign ID
// Then get campaign info from mailchimp:
// if email has been sent. get info to update into database: sent_amount, sent_date,

  class Mailchimp{
	//Connection for the API, Paramters Needed, APIkey, URL, Request as POST PUT GET etc....)

    private function APIConnection_no_input($url, $Request){

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . 'enter_username');
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

    while($campign_id = $result->fetch_object()){

        $campign_info = new Mailchimp();
        $campign_id = $campign_id->mc_campaign_id;

        $data = (object)$campign_info->Get_campaign_info($campign_id);

        $data = json_decode($data->data);

        if($data->status == 'sent'){

            $update = "UPDATE AFCollateral_Distribution
            SET cdQty = '".$data->emails_sent."',
            cdQtySent = '".$data->emails_sent."',
            cdDateSent = '".date("Y-m-j", strtotime($data->send_time))."'
            WHERE mc_campaign_id = '".$campign_id."'";
            $conn->query($update);
        }
    }
?>
