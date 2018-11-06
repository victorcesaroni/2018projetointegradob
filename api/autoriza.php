<?php

require_once 'database.php';

$conn = connect();

if (isset($_GET['usuario'])) {
    // validacao por parte do usuario

    $name = $_GET['name'];
    $password = $_GET['password'];
    $porta = $_GET['porta'];
    $porta_id = -1;

    {
        // descobre o id da porta pelo nome
        $sql = "SELECT id, name FROM doors WHERE name='$porta'";
        $result = $conn->query($sql);
       
        if (!$result) {
            echo "PORTA_NAO_EXISTE";
        } else { 
            if ($result->num_rows > 0) {
                if ($row = $result->fetch_assoc()) {    
                    $porta_id = $row['id'];       
                }
            }
        }       
    }

    // verifica credenciais do usuario
    $sql = "SELECT id, name, password FROM users WHERE name='$name' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {           
            // atualiza o ultimo login do usuario
            $sql = "UPDATE users SET last_login=NOW() WHERE id=$row[id]";
            $result = $conn->query($sql);

            if (!$result) {
                echo "ERRO_INTERNO";
            } else { 
                // insere um novo token para o usuario
                $token = md5(rand());
                $sql = "INSERT INTO tokens (id, user_id, door_id, token, creation_date, valid) VALUES (NULL, $row[id], $porta_id, '$token', NOW(), 1)";
                $result = $conn->query($sql);
                if (!$result) {
                    echo "ERRO_INTERNO";
                } else {                    
                    $dateTime = new DateTime(date('Y-m-d H:i:s'));
                    $minutesToAdd = 10;
                    $dateTime->modify("+{$minutesToAdd} minutes");
                    echo "OK|$token|" . $dateTime->format('Y-m-d H:i:s');;
                }
            }                        
        }
    } else {
        echo "ACESSO_NEGADO";
    }
} else if (isset($_GET['porta'])) {
    // validacao por parte de porta

    $name = $_GET['name'];
    $token = $_GET['token'];
    $porta = $_GET['porta'];

    {
        // invalida tokens com mais de 10min desde o ultimo uso
        $sql = "UPDATE tokens SET valid=0 WHERE (SELECT TIMESTAMPDIFF(MINUTE,use_date,NOW()) > 10)";
        $result = $conn->query($sql);
    }

    // verifica se o token passado pelo usuario nao esta expirado e se existe para a porta determinada
    $sql = "SELECT users.id, users.name, tokens.id AS _token_id, tokens.token AS _token FROM users,tokens,doors 
        WHERE users.id = tokens.user_id  AND tokens.door_id = doors.id
        AND users.name='$name' 
        AND tokens.valid=1 
        AND doors.name='$porta'
        AND tokens.token='$token'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {
            $token_id = $row['_token_id'];

            // atualiza o ultimo uso do token
            $sql = "UPDATE tokens SET use_date=NOW(), valid=0 WHERE id=$token_id";
            $result = $conn->query($sql);
    
            if (!$result) {
                echo "ERRO_INTERNO";
            } else { 
               echo "OK";
            }
        }
    } else {
        echo "ACESSO_NEGADO";
    }
}
$conn->close();
 
?> 
