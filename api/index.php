<?php

require_once 'database.php';

$conn = connect();

$sql = "SELECT id, name, password, last_login FROM users";
$result = $conn->query($sql);

echo "Users:<br>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "$row[id] $row[name] $row[password] $row[last_login]<br>";
    }
} else {
    echo "0 results<br>";
}

echo "Doors:<br>";

$sql = "SELECT id, name, last_heart_beat FROM doors";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "$row[id] $row[name] $row[last_heart_beat]<br>";
    }
} else {
    echo "0 results<br>";
}


echo "Tokens:<br>";

$sql = "SELECT * FROM tokens";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "$row[id] $row[token] $row[user_id] $row[door_id] $row[creation_date] $row[use_date] $row[valid]<br>";
    }
} else {
    echo "0 results<br>";
}
$conn->close();
 
?> 
