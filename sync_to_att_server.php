<?php
try
{
	$sqlite = new PDO('sqlite:ZKTimeNet.db');
	$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
  echo 'Connection failed: ' . $e->getMessage();
}


//$startData = "2016-04-02";
$startData = date("Y-m-d");

$current  = date("Y-m-d 59:00:00");

$sql = "select A.id, A.emp_ssn, A.emp_firstname, A.emp_lastname, min(B.punch_time) as checkin, max(B.punch_time) as checkout FROM hr_employee A Join att_punches B on A.id = B.employee_id where (A.emp_ssn is NOT NULL || A.emp_ssn != '')  AND B.punch_time BETWEEN '".$startData." 00:00:00' AND '".$startData." 23:59:59' GROUP BY A.emp_ssn";

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

$result = $statement->fetchAll();
$service_url = 'http://attendance-dev.knowarth.com/attendancereport';
$count = 1;
echo "<pre>";
print_R($result );
$curl_post_data = array();
	
	foreach($result as $row){
		$in = strtotime($row['checkin']);
		//$out = strtotime($row['checkout']);
		$time_one = new DateTime($row['checkin']);
		$time_two = new DateTime($row['checkout']);
		$difference = $time_one->diff($time_two);
		$hours = $difference->format('%h');
		$min = $difference->format('%i');	
		
		$att_date = date('Y-m-d 00:00:00',$in);
		
		$curl_post_data[] = array(
			'name' => $row['emp_firstname'].' '.$row['emp_lastname'],
			'emp_code' => $row['emp_ssn'],
			'att_date' => $att_date,
			'checkin' => $row['checkin'],
			'checkout' => $row['checkout'],
			'worked' => date('Y-m-d '.$hours.':'.$min.':00',$in),
		);
	
		if($count % 200 == 0){
			$response = CallAPI('POST',$service_url, $curl_post_data);
			$curl_post_data = array();
		}
		
		$count++;
	}

$response = CallAPI('POST',$service_url, $curl_post_data);
exit;


function CallAPI($method, $url, $data = array())
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
    
    curl_close($curl);

    return $result;
  }
?>