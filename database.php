<?php

$dotenvPath = __DIR__ . "/.env";

if (!file_exists($dotenvPath)) {
    die("Error: .env file not found at " . $dotenvPath);
}

$dotenv = parse_ini_file($dotenvPath);

if ($dotenv === false) {
    die("Error: Failed to parse .env file. Check syntax.");
}

$host = $dotenv['DB_HOST'] ?? 'localhost';
$dbname = $dotenv['DB_NAME'] ?? '';
$user = $dotenv['DB_USER'] ?? '';
$password = $dotenv['DB_PASSWORD'] ?? '';
$port = $dotenv['DB_PORT'] ?? 3306;


$mysqli = new mysqli($host, $user, $password, $dbname, $port);

if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;

?>