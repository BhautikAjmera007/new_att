<?php
global $sqlite;
try
{
    $sqlite = new PDO('sqlite:ZKTimeNet.db');

}
catch (PDOException $e)
{
  echo 'Connection failed: ' . $e->getMessage();
}

if(file_exists('date.txt')) { 
	$myfile = fopen("date.txt", "r");

	$checkin = fread($myfile,filesize("date.txt"));
	$statement = $sqlite->prepare("select A.id, A.emp_ssn, A.emp_firstname, A.emp_lastname, B.att_date, B.checkin, B.checkout, B.worked FROM hr_employee A Join att_day_details B on A.id = B.employee_id where B.checkin not null  And B.checkin = :checkin ORDER BY A.emp_firstname ASC ");
		$statement->bindParam(':checkin', $checkin);

} else {
	$statement = $sqlite->prepare('select A.id, A.emp_ssn, A.emp_firstname, A.emp_lastname, B.att_date, B.checkin, B.checkout, B.worked FROM hr_employee A Join att_day_details B on A.id = B.employee_id where B.checkin not null ORDER BY A.emp_firstname ASC ');
	
}
 
try{
	
    $statement->execute();
} catch(PDOException $e) {
     echo "Statement failed: " . $e->getMessage();
     return false;
}

$result = $statement->fetchAll();
$i = 1;
foreach($result as $row){
	$service_url = 'http://localhost/attendace/public/attendancereport';
	$curl_post_data[] = array(
		'name' => $row['emp_firstname'].' '.$row['emp_lastname'],
        'emp_code' => $row['emp_ssn'],
        'att_date' => $row['att_date'],
        'checkin' => $row['checkin'],
        'checkout' => $row['checkout'],
        'worked' => $row['worked'],
        'hrms_request_id' => ''
	);
  
	if(($i % 200) == 0){
		 $result = CallAPI('POST',$service_url, $curl_post_data);  
	}
	
	$i++;
}
 
if($curl_post_data){
	$result = CallAPI('POST',$service_url, $curl_post_data);  

}
 
$myfile = fopen("date.txt","w");
$current_date = date("Y/m/d");
fwrite($myfile,$current_date);
  
function CallAPI($method, $url, $data = false)
  {
	  
    $curl = curl_init();
	
    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data){
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
			}
            break;
          case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }


    // Optional Authentication:
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
	 
	 if(curl_errno($curl)){
		echo 'Curl error: ' . curl_error($curl);
	}	
	curl_close($curl);
	
	return $result;
	
  }
?>