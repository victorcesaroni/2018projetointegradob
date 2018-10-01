<?php

require_once 'database.php';

$conn = connect();

$name = $_GET['name'];

$sql = "INSERT INTO doors (id, name) VALUES (NULL, '$name')";
$result = $conn->query($sql);

if (!$result) {
  echo "ERRO<br>";
} else {  
  echo "OK<br>";
}

$conn->close();
 
?> 
