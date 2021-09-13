<?php
   $dbhost = 'localhost';
   $dbuser = 'root';
   $dbpass = '';
   $dbname = 'test';
   $mysqli = new mysqli($dbhost, $dbuser, $dbpass);
   
   if(! $mysqli ) {
      die('Could not connect: ' . mysqli_error($mysqli));
   }
   
   echo 'Connected successfully';
   
   $sql = 'CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;';
   $retval = mysqli_query($mysqli, $sql);
   
   if(!$retval) {
      die('Could not create database: ' . mysqli_error($mysqli));
   }
   
   echo "Database created successfully\n";
   
   $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
   
   if(! $mysqli ) {
      die('Could not reconnect: ' . mysqli_error($mysqli));
   }
   
   echo 'Reconnected successfully';
   
   
   
   $query = file_get_contents("initiate.sql");

	if ($stmt = $mysqli->prepare($query)) {
		if ($stmt->execute()) {
			echo "Success";
		} else { 
			echo "Fail";
		}
	}else{
		echo "oops" . mysqli_error($mysqli);
	}

    // Close connection
    $mysqli->close();
?>