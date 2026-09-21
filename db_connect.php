<?php
// db_connect.php

$servername = "localhost"; // server name (default is localhost)
$username = "root";         // database username (default is root)
$password = "";             // database password (default is empty)
$dbName = "alvenwear";      // The name of the database 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
