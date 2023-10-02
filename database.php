<?php

// Render DB
$host = "dpg-ckdh784gonuc73bu1usg-a";
$dbname = "homeseekrdb";
$username = "homeseekrdb_user";
$password = "RyI6dsrMpCSKUbN5PZ4bCqHEIYIMguOf";
// $host = "localhost";
// $dbname = "growth_craft";
// $username = "root";
// $password = "";

$mysqli = new mysqli(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname);
                     
if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;
?>