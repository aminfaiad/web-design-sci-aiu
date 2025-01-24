<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php

// Database configuration
$host = 'localhost';
$dbname = 'sms';
$username = 'SMS';
$password = 'password';

// Create a connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful
//echo "Database connection established successfully.";

?>
