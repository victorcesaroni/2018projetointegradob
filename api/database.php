<?php

date_default_timezone_set('America/Sao_Paulo');

function connect() {
	$servername = "localhost";
	$username = "root";
	$dbname = "pib";
	$password = "";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	
	return $conn;
}
 
?> 
