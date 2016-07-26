<?php
  ini_set('display_errors', 'On');
  error_reporting(E_ALL);
  // Set default timezone
  date_default_timezone_set('UTC');
$dir = 'sqlite:ZKTimeNet.db';
$dbh  = new PDO($dir) or die("cannot open the database");
$query =  "SELECT count(Id) from att_punches";

$result = $dbh->query($query);

foreach ($dbh->query($query) as $row)
{
	echo $row[0];
}
exit;
 
 
 
  try {
    /**************************************
    * Create databases and                *
    * open connections                    *
    **************************************/
 
    // Create (connect to) SQLite database in file
	$file_db = new PDO('sqlite:ZKTimeNet.db');
    //$file_db = new PDO('sqlite:C:\Users\gaurav.khambhala\Desktop\ZKTimeNet(1).db');
    // Set errormode to exceptions
  //  $file_db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 

 
 


    /**************************************
    * Play with databases and tables      *
    **************************************/
 

    // Select all data from file db messages table 
    $result = $file_db->query('SELECT * FROM att_punches');
 
    print_r($result);
 
    
 
    /**************************************
    * Close db connections                *
    **************************************/
 
    // Close file db connection
    $file_db = null;

  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }
?>