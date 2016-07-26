<?php
date_default_timezone_set('Asia/Kolkata');
try
{
    $sqlite = new PDO('sqlite:C:\Users\gaurav.khambhala\Desktop\ZKTimeNet.db');
	$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
  echo 'Connection failed: ' . $e->getMessage();
}

// create and open log file for writing logs

$filename = "daily_att_sync_".date('d-m-Y_H_i_s').'.log';
$myfile = fopen($filename, "w") or die("Unable to open file!");

// write data to the file

$date = date('Y-m-d',strtotime("-1 days"));

$sql = "select A.id, A.emp_ssn, A.emp_firstname, A.emp_lastname, min(B.punch_time), max(B.punch_time) FROM hr_employee A Join att_punches B on A.id = B.employee_id where A.emp_ssn is NOT NULL AND A.emp_ssn != '' AND B.punch_time BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' GROUP BY A.emp_ssn";
$statement = $sqlite->prepare($sql);

try
{
   $statement->execute();
}
catch(PDOException $e)
{
   echo "Statement failed: " . $e->getMessage();
   return false;
}
// result from database
$result = $statement->fetchAll();
$service_url = 'http://attendance-dev.knowarth.com/dailyreport';
$total = 0;
$curl_post_data = array();
foreach($result as $row){
	$datetime1 = new DateTime($row['min(B.punch_time)']);
	$datetime2 = new DateTime($row['max(B.punch_time)']);
	$interval = $datetime1->diff($datetime2);
	
	$worked = $date.' '.$interval->format("%H:%i:%s");
	
	$curl_post_data[] = array(
			'name' => $row['emp_firstname'].' '.$row['emp_lastname'],
			'emp_code' => $row['emp_ssn'],
			'att_date' => $date,
			'checkin' => $row['min(B.punch_time)'],
			'checkout' => $row['max(B.punch_time)'],
			'worked' => $worked,
		  //  'hrms_request_id' => ''
	);
	//
}
$response = CallAPI('POST',$service_url, $curl_post_data);
// write response to the file
fwrite($myfile, $response);
// close file after writing
fclose($myfile);
exit;

function CallAPI($method, $url, $data = array())
  {
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( $data ));
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
    echo $result ;
    curl_close($curl);

    return $result;
  }
?>