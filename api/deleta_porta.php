<?php

require_once 'database.php';

$id = $_GET['id'];

$conn = connect();

$sql = "DELETE FROM doors WHERE id=$id";
$result = $conn->query($sql);

if (!$result) {
  echo "ERRO<br>";
} else {  
  echo "OK<br>";
}

$conn->close();
 
?> 
