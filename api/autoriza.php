<?php

require_once 'database.php';

$conn = connect();

if (isset($_GET['usuario'])) {
    $name = $_GET['name'];
    $password = $_GET['password'];
    $porta = $_GET['porta'];

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
                $sql = "INSERT INTO tokens (id, user_id, door_id, token, creation_date, valid) VALUES (NULL, $row[id], $porta, '$token', NOW(), 1)";
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
    $username = $_GET['username'];
    $token = $_GET['token'];
    $porta = $_GET['porta'];

    $sql = "SELECT users.id, users.name, tokens.id AS _token_id, tokens.token AS _token FROM users,tokens 
        WHERE users.id = tokens.user_id 
        AND users.name='$username' 
        AND tokens.valid=1 
        AND tokens.door_id=$porta
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
