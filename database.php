<?php

// CleverDB
$host = "bpnfyzupm3xulwt0nblm-mysql.services.clever-cloud.com";
$dbname = "bpnfyzupm3xulwt0nblm";
$username = "ukhpie7qrmaq9ems";
$password = "6OpPbrkaDqxCfrr1eefw";
$port = '3306';

// Render DB
// $host = "dpg-ckdh784gonuc73bu1usg-a";
// $dbname = "homeseekrdb";
// $username = "homeseekrdb_user";
// $password = "RyI6dsrMpCSKUbN5PZ4bCqHEIYIMguOf";
// $port = '5432';

//LocalHost DB
// $host = "localhost";
// $dbname = "growth_craft";
// $username = "root";
// $password = "";

$mysqli = new mysqli(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname,
                     port: $port);
                     
if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;
?>