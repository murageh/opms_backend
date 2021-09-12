<?php
/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */

//Access-Control-Allow-Origin header with wildcard.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

$mysqli = new mysqli("localhost", "root", "", "opms");

// Check connection
if ($mysqli === false) {
    @die("ERROR: Could not connect. " . $mysqli->connect_error);
}

// Print host information
//echo "Connect Successfully. Host info: " . $mysqli->host_info;

