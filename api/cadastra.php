<?php

require_once 'database.php';

$conn = connect();

$name = $_GET['name'];
$password = $_GET['password'];

$sql = "INSERT INTO users (id, name, password, last_login) VALUES (NULL, '$name', '$password', NOW())";
$result = $conn->query($sql);

if (!$result) {
  echo "ERRO<br>";
} else {  
  echo "OK<br>";
}

$conn->close();
 
?> 
