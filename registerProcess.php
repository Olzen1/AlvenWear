<?php
include 'db_connect.php';

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

// Check if email already exists
$check = $conn->query("SELECT * FROM users WHERE email='$email'");
if ($check->num_rows > 0) {
    echo "Email already exists. <a href='Login.php'>Try Login</a>";
    exit();
}

// create the new user and insert it to user details
$sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

// exception
if ($conn->query($sql)) {
    echo "Registration Successful! <a href='Login.php'>Click here to Login</a>";
} else {
    echo "Error: " . $conn->error;
}
