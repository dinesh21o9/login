<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once 'database.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "Dinesh_Work";
$alg = 'HS256';


$jsonData = file_get_contents('php://input');


$data = json_decode($jsonData);

$method = $_SERVER['REQUEST_METHOD'];

$mysqli = require __DIR__ . "/database.php";

// print_r($data);
// print_r($method);


switch ($method) {

    case 'POST':
        if ($data->page == "signup") {
            $password = $data->password;
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email , password_hash)
                    VALUES (?, ?, ?)";

            $stmt = $mysqli->stmt_init();

            if (!$stmt->prepare($sql)) {
                die("SQL error: " . $mysqli->error);
            }

            $stmt->bind_param(
                "sss",
                $data->name,
                $data->email,
                $password_hash
            );


            if ($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Record created successfully.'];
            } else {
                if ($mysqli->errno === 1062) {
                    $response = ['status' => 0, 'message' => 'Email already taken'];
                } else if ($mysqli->error) {
                    $response = ['status' => $mysqli->error, 'message' => 'Error in mysqli! open index.php'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];
                }
            }
        } else if ($data->page == "adminLogin") {
            //Admin login
            $sql = sprintf(
                "SELECT * FROM admins 
                            WHERE email = '%s'",
                $mysqli->real_escape_string($data->email)
            );

            $result = $mysqli->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch the user data

                if (password_verify($data->password, $user['password_hash'])) {

                    $payload = array(
                        "user" => 'admin',
                        "user_id" => $user['admin_id'],
                        "user_name" => $user['name']
                    );

                    $jwt = JWT::encode($payload, $key, $alg);

                    $response = ['status' => 1, 'message' => 'Logged in successfully', 'token' => $jwt];
                } else {
                    // Incorrect password
                    $response = ['status' => 0, 'message' => 'Incorrect password'];
                }
            }
        } else if ($data->page == "userLogin") {
            // User Login 
            $sql = sprintf(
                "SELECT * FROM users 
                                WHERE email = '%s'",
                $mysqli->real_escape_string($data->email)
            );

            $result = $mysqli->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc(); // Fetch the user data

                if (password_verify($data->password, $user['password_hash'])) {

                    $payload = array(
                        "user" => 'user',
                        "user_id" => $user['id'],
                        "user_name" => $user['name']
                    );

                    $jwt = JWT::encode($payload, $key, $alg);

                    $response = ['status' => 1, 'message' => 'Logged in successfully', 'token' => $jwt];
                } else {
                    // Incorrect password
                    $response = ['status' => 0, 'message' => 'Incorrect password'];
                }
            } else {
                // User not found
                $response = ['status' => 0, 'message' => 'User not found'];
            }
        } else if ($data->page == 'viewProp') {
            $target = $data->prop_id;
            if ($data->offer == 'sale') {
                $stmt = $mysqli->prepare("SELECT * FROM properties_db_sale WHERE prop_id = ?");
            } else if ($data->offer == 'rent') {
                $stmt = $mysqli->prepare("SELECT * FROM properties_db_rent WHERE prop_id = ?");
            }
            $stmt->bind_param("i", $target);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            $mysqli->close();
            echo json_encode($data);
            break;
        } else if ($data->page == 'post') {
            $jwt = $data->cookie;
            try {
                $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                $user_id = $decoded->user_id;
                $user_name = $decoded->user_name;
            } catch (Exception $e) {

                // Token verification failed, handle error
                $response = ['status' => 0, 'message' => 'Token verification failed. Please log in again.', 'error' => $e->getMessage()];
                echo json_encode($response);
                exit;
            }
            // Posting Sale properties:
            if ($data->offer === "sale") {
                $sql = "INSERT INTO properties_db_sale (
                        user_id,
                        user_name,
                        address,
                        age,
                        balcony,
                        bathroom,
                        bedroom,
                        bhk,
                        carpet,
                        deposit,
                        description,
                        furnished,
                        garden,
                        gym,
                        hospital,
                        lift,
                        loan,
                        market_area,
                        status,
                        total_floors,
                        type,
                        water_supply,
                        offer,
                        parking_area,
                        play_ground,
                        power_backup,
                        price,
                        property_name,
                        room_floor,
                        school,
                        security_guard,
                        shopping_mall
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $mysqli->stmt_init();
                if (!$stmt->prepare($sql)) {
                    die("SQL error: " . $mysqli->error);
                }

                $stmt->bind_param(
                    "isssiiiiisssssssssssssssssssssss",
                    $user_id,
                    $user_name,
                    $data->address,
                    $data->age,
                    $data->balcony,
                    $data->bathroom,
                    $data->bedroom,
                    $data->bhk,
                    $data->carpet,
                    $data->deposit,
                    $data->description,
                    $data->furnished,
                    $data->garden,
                    $data->gym,
                    $data->hospital,
                    $data->lift,
                    $data->loan,
                    $data->market_area,
                    $data->status,
                    $data->total_floors,
                    $data->type,
                    $data->water_supply,
                    $data->offer,
                    $data->parking_area,
                    $data->play_ground,
                    $data->power_backup,
                    $data->price,
                    $data->property_name,
                    $data->room_floor,
                    $data->school,
                    $data->security_guard,
                    $data->shopping_mall
                );

                if ($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Uploaded property successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];
                }
            }
            //Posting Rent properties:
            if ($data->offer === "rent") {
                $sql = "INSERT INTO properties_db_rent (
                        user_id,
                        user_name,
                        address,
                        age,
                        balcony,
                        bathroom,
                        bedroom,
                        bhk,
                        carpet,
                        deposit,  
                        description,
                        furnished,
                        garden,
                        gym,
                        hospital,
                        lift,
                        market_area,
                        status,
                        total_floors,
                        type,
                        water_supply,
                        offer,
                        parking_area,
                        play_ground,
                        power_backup,
                        rent,         
                        property_name,
                        room_floor,
                        school,
                        security_guard,
                        shopping_mall
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $mysqli->stmt_init();
                if (!$stmt->prepare($sql)) {
                    die("SQL error: " . $mysqli->error);
                }

                $stmt->bind_param(
                    "isssiiiiissssssssssssssssssssss",
                    $user_id,
                    $user_name,
                    $data->address,
                    $data->age,
                    $data->balcony,
                    $data->bathroom,
                    $data->bedroom,
                    $data->bhk,
                    $data->carpet,
                    $data->deposit,
                    $data->description,
                    $data->furnished,
                    $data->garden,
                    $data->gym,
                    $data->hospital,
                    $data->lift,
                    $data->market_area,
                    $data->status,
                    $data->total_floors,
                    $data->type,
                    $data->water_supply,
                    $data->offer,
                    $data->parking_area,
                    $data->play_ground,
                    $data->power_backup,
                    $data->rent,
                    $data->property_name,
                    $data->room_floor,
                    $data->school,
                    $data->security_guard,
                    $data->shopping_mall
                );

                if ($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Uploaded property successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];
                }
            }
        } else if ($data->page == 'dashboard') {
            if ($data->cookie) {
                $jwt = $data->cookie;
                try {
                    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                    $user_id = $decoded->user_id;
                    $user_name = $decoded->user_name;
                } catch (Exception $e) {
                    // Token verification failed, handle error
                    $response = ['status' => 0, 'message' => 'Token verification failed. Please log in again.', 'error' => $e->getMessage()];
                    echo json_encode($response);
                    exit;
                }
            }else{
                $user_id = -1;
            }
            
            $response = [];

            $result = $mysqli->query("SELECT * FROM properties_db_sale WHERE admin_id != 0");
            while ($row = $result->fetch_assoc()) {
                if ($user_id === $row['user_id']) {
                    $row['editAccess'] = true;
                } else {
                    $row['editAccess'] = false;
                }
                $response[] = $row;
            }

            $result = $mysqli->query("SELECT * FROM properties_db_rent WHERE admin_id != 0");
            while ($row = $result->fetch_assoc()) {
                if ($user_id === $row['user_id']) {
                    $row['editAccess'] = true;
                } else {
                    $row['editAccess'] = false;
                }
                $response[] = $row;
            }
        } else if ($data->page == 'header') {
            $jwt = $data->cookie;
            try {
                $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
                $user_id = $decoded->user_id;
                $user_name = $decoded->user_name;
                $response = ['status' => 1, 'userName' => $user_name, 'message' => 'Fetched user name on JWT token'];
            } catch (Exception $e) {
                $response = ['status' => 0, 'message' => 'Token verification failed. Please log in again.', 'error' => $e->getMessage()];
                echo json_encode($response);
                exit;
            }
        }
        echo json_encode($response);
        $mysqli->close();
        break;

    case 'GET':

        $data = [];

        $result = $mysqli->query("SELECT * FROM properties_db_sale WHERE admin_id != 0");
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result = $mysqli->query("SELECT * FROM properties_db_rent WHERE admin_id != 0");
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        $mysqli->close();
        break;

    case 'DELETE':
        $prop_id = $_GET['prop_id'];

        $sql_sale = "DELETE FROM properties_db_sale WHERE prop_id = $prop_id";
        $sql_rent = "DELETE FROM properties_db_rent WHERE prop_id = $prop_id";

        if ($mysqli->query($sql_rent) | $mysqli->query($sql_sale) === TRUE) {
            $response = ['status' => 1, 'message' => 'Property deleted successfully'];
        } else {
            $response = ['status' => 0, 'message' => 'Error deleting property: ' . $mysqli->error];
        }

        echo json_encode($response);
        $mysqli->close();
        break;
}
