<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once 'database.php';
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

$key = "Dinesh_Work";
$alg ='HS256';

// Retrieve the raw JSON data from the request
$jsonData = file_get_contents('php://input');

// Decode the JSON data into a PHP object or array
$data = json_decode($jsonData);

$method = $_SERVER['REQUEST_METHOD'];

// print_r($data);
// print_r($method);


if ($method == "POST") {

    if(isset($data->name)){

        $password = $data->password;
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $mysqli = require __DIR__ . "/database.php";

        $sql = "INSERT INTO users (name, email , password_hash)
                VALUES (?, ?, ?)";
                
        $stmt = $mysqli->stmt_init();

        if ( ! $stmt->prepare($sql)) {
            die("SQL error: " . $mysqli->error);
        }

        $stmt->bind_param("sss",
                        $data->name,
                        $data->email,
                        $password_hash);
                
                        
        if ($stmt->execute()) {

            $response = ['status' => 1, 'message' => 'Record created successfully.'];
            
        } else {
            
            // if ($mysqli->errno === 1062) {
            //     $response = ['status'=> 0,'message'=> 'Email already taken'];
            // } 
            // else if($mysqli->error) {
            //     $response= ['status' => 0, 'error' => $mysqli->error , 'message'=> 'Error in mysqli! open index.php'];
            // }
            // else{
                $response = ['status' => 0, 'message' => 'Failed to create record.'];
            // }
        }
        echo json_encode($response);
        $mysqli->close();
    } 
    else{

        $mysqli = require __DIR__ . "/database.php"; // Connecting to the database

        if($data->user == 'admin'){

            $sql = sprintf("SELECT * FROM admins 
                            WHERE email = '%s'",
                            $mysqli->real_escape_string($data->email));

            $result = $mysqli->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch the user data

                if (password_verify($data->password, $user['password_hash'])) {
                    
                    $payload = array(
                        "user" => 'admin',
                        "user_id" => $user['admin_id'],
                        "user_name" => $user['name']
                    );
                    
                    $jwt = JWT::encode($payload, $key,$alg);

                    $response = ['status' => 1, 'message' => 'Logged in successfully', 'token'=> $jwt ];

                } else {
                    // Incorrect password
                    $response = ['status' => 0, 'message' => 'Incorrect password'];
                }
            }
        }
        else{

            $sql = sprintf("SELECT * FROM users 
                            WHERE email = '%s'",
                            $mysqli->real_escape_string($data->email));

            $result = $mysqli->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch the user data

                if (password_verify($data->password, $user['password_hash'])) {
                    
                    $payload = array(
                        "user" => 'user',
                        "user_id" => $user['id'],
                        "user_name" => $user['name']
                    );
                    
                    $jwt = JWT::encode($payload, $key,$alg);

                    // setcookie('jwtUserToken', $jwt, time()+3600, '/', 'http://localhost://3000');

                    // echo $_COOKIE['jwtUserToken'];

                    // echo json_encode(array("jwt" => $jwt));

                    $response = ['status' => 1, 'message' => 'Logged in successfully', 'token'=>$jwt];

                } else {
                    // Incorrect password
                    $response = ['status' => 0, 'message' => 'Incorrect password'];
                }
            } else {
                // User not found
                $response = ['status' => 0, 'message' => 'User not found'];
            }
        }
        echo json_encode($response);
        $mysqli->close();

    }
} 
else {
    echo "No JSON data received";
}
?>
