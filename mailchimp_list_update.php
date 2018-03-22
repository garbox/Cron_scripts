<?php
//this function update mailchimp email with the database we have. This allows for marketing and production emails to be in sync.
//This is one of thr first projects iv worked on before really utlizing objectes on my own. You might noticed i bounch between json object and assoc arrays some times with in the same function group.

class Mailchimp{
	//Connection for the API, Paramters Needed, APIkey, URL, Request as POST PUT GET etc....)
	private function APIConnection($url, $Request, $data){

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . 'user_name');
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

        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . 'user_name');
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

    public function Get_List_data($list_id){
        $url = 'https://us12.api.mailchimp.com/3.0/lists/'.$list_id;
        $APIConnect = new Mailchimp();
        return $APIConnect->APIConnection_no_input($url, "GET");

    }
}
function Connect(){
            // Info to login to server
            $servername = "localhost";
            $username = "username";
            $password = "password";
            $dbname = "dbname";

            // Create and check connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            return $conn;
        }
function pmimain_connect(){
            // Info to login to server
            $servername = "localhost";
            $username = "username";
            $password = "password";
            $dbname = "dbname";

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
function assesment_db(){
            // Info to login to server
            $servername = "localhost";
            $username = "username";
            $password = "password";
            $dbname = "dbname";

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
function log_db(){
            // Info to login to server
            $servername = "localhost";
            $username = "username";
            $password = "password";
            $dbname = "dbname";

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

    //set up connections
    $conn = pmimain_connect();
    $olc_conn = Connect();
    $quiz_conn = assesment_db();
    $log_update = log_db();

    //set mailchimp obj
    $mail = new Mailchimp();

    $list_data = $mail->Get_List_data('cc2535b0e1');
    $data = json_decode($list_data['data'], TRUE);
    $pre_count = $data['stats']['member_count'];

    //get memeber list from PMI server
    $mem_list_query = "SELECT MemFirstName, MemLastName, MemEmail, MemPhone, MemAddr1, MemState, MemCity, MemZip FROM Members WHERE MemEmail != '' ORDER BY MemID DESC LIMIT 200";
    $olc_memeber_query = "SELECT Fname, Lname, City, State, Zip, Phone, Email FROM  customers ORDER BY ID DESC";
    $quiz_members_query = "SELECT first_name, last_name, email FROM savsoft_users ORDER BY uid DESC";

    //run query for data
    $mem_list_data = $conn->query($mem_list_query);
    $olc_memeber_data = $olc_conn->query($olc_memeber_query);
    $quiz_members_data = $quiz_conn->query($quiz_members_query);

    //loop through data PMI_Main DB
    while($data_obj = $mem_list_data->fetch_assoc()){
        if($data_obj['MemZip'] ==""){
            $data_obj['MemZip'] = 0;
        }
        $hash_email = md5(strtolower($data_obj['MemEmail']));
        $data = array(
            'email' => $data_obj['MemEmail'],
            'fname' => $data_obj['MemFirstName'],
            'lname' => $data_obj['MemLastName'],
            'address' => $data_obj['MemAddr1'],
            'state' => $data_obj['MemState'],
            'zip' => $data_obj['MemZip'],
            'city' => $data_obj['MemCity'],
            'phone' => $data_obj['MemPhone']
        );
       $mail->MC_update_list($data);
    }

    //loop through data OLC database
    while($data_obj = $olc_memeber_data->fetch_assoc()){
        if($data_obj['Zip'] ==""){
            $data_obj['Zip'] = 0;
        }
        $hash_email = md5(strtolower($data_obj['Email']));
        $data = array(
            'email' => $data_obj['Email'],
            'fname' => $data_obj['Fname'],
            'lname' => $data_obj['Lname'],
            'address' => $data_obj['Address'],
            'state' => $data_obj['State'],
            'zip' => $data_obj['Zip'],
            'city' => $data_obj['City'],
            'phone' => $data_obj['Phone']
        );
        $mail->MC_update_list($data);
    }

    //loop through data quiz DB
    while($data_obj = $quiz_members_data->fetch_assoc()){
        $hash_email = md5(strtolower($data_obj['email']));
        $data = array(
            'email' => $data_obj['email'],
            'fname' => $data_obj['first_name'],
            'lname' => $data_obj['last_name']
        );
        $mail->MC_update_list($data);
    }

    // get list data and return memeber count to see changes.
    $list_data = $mail->Get_List_data('cc2535b0e1');
    $data = json_decode($list_data['data'], TRUE);
    $post_count = $data['stats']['member_count'];

    //set up quries for log reports.
    $cron_script_log = "INSERT INTO cron_scripts (file) VALUES('".$_SERVER['SCRIPT_FILENAME']."')";
    $mailchimp_update_log = "INSERT INTO mailchimp_update (pre_update_count, post_update_count) VALUES('".$pre_count."', '".$post_count."')";

    //figure out the difference bettwen pre and post update
    $total_count_change = $post_count - $pre_count;

    //insert data into log DB
    $log_update->query($cron_script_log);
    $log_update->query($mailchimp_update_log);
?>
