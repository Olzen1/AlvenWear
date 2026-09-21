<?php
session_start();
session_destroy();

// Check if there is a redirect destination (e.g., to login page)
if (isset($_GET['redirect'])) {
    header("location: " . $_GET['redirect']);
} else {
    // Default behavior
    header("location: index.php");
}
exit();
