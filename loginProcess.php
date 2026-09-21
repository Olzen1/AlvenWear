<?php
session_start();
include 'db_connect.php';

// 1. Get data from the Login Form
// We use $_POST['email'] now
$mypassword = $_POST['password'];
$role = $_POST['role'];

// Check if it is an ADMIN login
if ($role == 'admin') {
    $username = $_POST['username'];
    $sql = "SELECT * FROM admin WHERE username='$username' AND password='$mypassword'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['login_user'] = $row['username'];
        $_SESSION['user_role'] = 'admin';

        // CHANGED: Send Admin to Index (Home) instead of Admin Menu
        header("location: index.php");
        exit();
    } else {
        echo "<p>Wrong Admin Username or Password. <a href='Login.php'>Try again</a></p>";
    }
}
// 3. Check if it is a CUSTOMER login 
else {
    $myemail = $_POST['email'];
    // check the database for the EMAIL
    $sql = "SELECT * FROM users WHERE email='$myemail' AND password='$mypassword'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['login_user'] = $row['username'];
        $_SESSION['user_role'] = 'customer';
        header("location: index.php"); // Go to Homepage
        exit();
    } else {
        echo "<p>Wrong Email or Password. <a href='Login.php'>Try again</a></p>";
    }
}

$conn->close();
