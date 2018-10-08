<?php

require_once 'database.php';

$conn = connect();

if (isset($_GET['usuario'])) {
    $name = $_GET['name'];
    $password = $_GET['password'];
    $porta = $_GET['porta'];
    $porta_id = -1;

    {
        $sql = "SELECT id, name FROM doors WHERE name='$porta'";
        $result = $conn->query($sql);
       
        if (!$result) {
            echo "PORTA NAO EXISTE<br>";
        } else { 
            if ($result->num_rows > 0) {
                if ($row = $result->fetch_assoc()) {    
                    $porta_id = $row['id'];       
                }
            }
        }       
    }

    $sql = "SELECT id, name, password FROM users WHERE name='$name' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {           
            $sql = "UPDATE users SET last_login=NOW() WHERE id=$row[id]";
            $result = $conn->query($sql);

            if (!$result) {
                echo "ERRO INTERNO<br>";
            } else { 
                $token = md5(rand());
                $sql = "INSERT INTO tokens (id, user_id, door_id, token, creation_date, valid) VALUES (NULL, $row[id], $porta_id, '$token', NOW(), 1)";
                $result = $conn->query($sql);
                if (!$result) {
                    echo "ERRO INTERNO<br>";
                } else {
                    echo "OK: $token<br>";
                }
            }                        
        }
    } else {
        echo "ACESSO NEGADO<br>";
    }
} else if (isset($_GET['porta'])) {
    $name = $_GET['name'];
    $token = $_GET['token'];
    $porta = $_GET['porta'];

    $sql = "SELECT users.id, users.name, tokens.id AS _token_id, tokens.token AS _token FROM users,tokens,doors 
        WHERE users.id = tokens.user_id  AND tokens.door_id = doors.id
        AND users.name='$name' 
        AND tokens.valid=1 
        AND doors.name='$porta'
        AND tokens.token='$token'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {
            $sql = "UPDATE tokens SET use_date=NOW(), valid=0 WHERE id=$row[_token_id]";
            $result = $conn->query($sql);
    
            if (!$result) {
                echo "ERRO INTERNO<br>";
            } else { 
               echo "OK<br>";
            }      
        }
    } else {
        echo "ACESSO NEGADO<br>";
    }
}
$conn->close();
 
?> 
