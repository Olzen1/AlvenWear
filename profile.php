<?php
session_start();
include "db_connect.php";

// Security Check
if (!isset($_SESSION["login_user"])) {
    header("location: Login.php");
    exit();
}

$username = $_SESSION["login_user"];
$msg = "";

// Function to fetch user data with user_type
function fetchUserData($conn, $username)
{
    $user = null;

    // Check Users Table
    $user_result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user['user_type'] = 'customer';
    }
    // Check Admin Table
    else {
        $admin_result = $conn->query("SELECT * FROM admin WHERE username='$username'");
        if ($admin_result && $admin_result->num_rows > 0) {
            $admin_data = $admin_result->fetch_assoc();
            $user = array(
                'id' => 0,
                'username' => $admin_data['username'],
                'email' => '',
                'password' => $admin_data['password'],
                'first_name' => '',
                'last_name' => '',
                'phone' => '',
                'gender' => '',
                'dob' => NULL,
                'user_type' => 'admin'
            );
        }
    }

    return $user;
}

// Fetch User Data
$user = fetchUserData($conn, $username);

if (!$user) {
    die("User not found. Please <a href='logout.php'>logout and login again</a>.");
}

$user_email = $user['email'];

// Handle Form Submission (Account Details)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_account'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];

    if ($user['user_type'] == 'admin') {
        // ADMIN UPDATE: Only update username in 'admin' table
    } else {
        // CUSTOMER UPDATE: Update all fields including first_name
        $sql = "UPDATE users SET 
                first_name='$first_name',
                last_name='$last_name', 
                phone='$phone', 
                gender='$gender',
                dob='$dob'
                WHERE username='$username'";

        if ($conn->query($sql)) {
            $msg = "<div class='success-msg'>✓ Profile updated successfully!</div>";
            // Refresh user data WITH user_type
            $user = fetchUserData($conn, $username);
        } else {
            $msg = "<div class='error-msg'>✗ Error updating profile: " . $conn->error . "</div>";
        }
    }
}

// Handle Password Update - For both users and admins
if (isset($_POST['update_password'])) {
    $current_pwd = $_POST['current_password'];
    $new_pwd = $_POST['new_password'];
    $confirm_pwd = $_POST['confirm_password'];

    if ($current_pwd == $user['password']) {
        if ($new_pwd === $confirm_pwd && strlen($new_pwd) >= 6) {
            $table = ($user['user_type'] == 'admin') ? 'admin' : 'users';
            $conn->query("UPDATE $table SET password='$new_pwd' WHERE username='$username'");
            $msg = "<div class='success-msg'>✓ Password updated successfully!</div>";
        } else {
            $msg = "<div class='error-msg'>✗ New passwords don't match or too short (min 6 chars)</div>";
        }
    } else {
        $msg = "<div class='error-msg'>✗ Current password is incorrect</div>";
    }
}

// Handle Add New Address (Customers Only)
if (isset($_POST['add_address']) && $user['user_type'] == 'customer') {
    $name = $_POST['recipient_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if ($is_default) {
        $conn->query("UPDATE addresses SET is_default=0 WHERE user_email='$user_email'");
    }

    $conn->query("INSERT INTO addresses (user_email, recipient_name, phone, address, is_default) 
                  VALUES ('$user_email', '$name', '$phone', '$address', $is_default)");
    header("Location: profile.php");
    exit();
}

// Handle Delete Address (Customers Only)
if (isset($_GET['delete_address']) && $user['user_type'] == 'customer') {
    $addr_id = intval($_GET['delete_address']);
    $conn->query("DELETE FROM addresses WHERE id=$addr_id AND user_email='$user_email'");
    header("Location: profile.php");
    exit();
}

// Handle Set Default Address (Customers Only)
if (isset($_GET['set_default']) && $user['user_type'] == 'customer') {
    $addr_id = intval($_GET['set_default']);
    $conn->query("UPDATE addresses SET is_default=0 WHERE user_email='$user_email'");
    $conn->query("UPDATE addresses SET is_default=1 WHERE id=$addr_id AND user_email='$user_email'");
    header("Location: profile.php");
    exit();
}

// Fetch addresses (Customers Only)
$addresses = null;
$default_address = null;
if ($user['user_type'] == 'customer') {
    $addresses = $conn->query("SELECT * FROM addresses WHERE user_email='$user_email' ORDER BY is_default DESC, created_at DESC");
    $default_address = $conn->query("SELECT * FROM addresses WHERE user_email='$user_email' AND is_default=1")->fetch_assoc();
}

// Handle Update Address
if (isset($_POST['update_address'])) {
    $addr_id = intval($_POST['edit_address_id']);
    $name = $_POST['edit_recipient_name'];
    $phone = $_POST['edit_phone'];
    $address = $_POST['edit_address'];
    $is_default = isset($_POST['edit_is_default']) ? 1 : 0;

    if ($is_default) {
        $conn->query("UPDATE addresses SET is_default=0 WHERE user_email='$user_email'");
    }

    $conn->query("UPDATE addresses SET 
                  recipient_name='$name', 
                  phone='$phone', 
                  address='$address', 
                  is_default=$is_default 
                  WHERE id=$addr_id AND user_email='$user_email'");

    header("Location: profile.php?updated=1");
    exit();
} // Handle Username Update
if (isset($_POST['update_username']) && $user['user_type'] == 'customer') {
    $new_username = trim($_POST['new_username']);

    // Validate username
    if (strlen($new_username) >= 3 && preg_match('/^[A-Za-z0-9_]+$/', $new_username)) {
        // Check if username already exists
        $check = $conn->query("SELECT id FROM users WHERE username='$new_username'");
        if ($check->num_rows == 0) {
            // Update username in database
            $conn->query("UPDATE users SET username='$new_username' WHERE username='$username'");
            // Update session
            $_SESSION["login_user"] = $new_username;
            $msg = "<div class='success-msg'>✓ Username updated to: " . htmlspecialchars($new_username) . "</div>";
            // Refresh user data
            $user = fetchUserData($conn, $new_username);
        } else {
            $msg = "<div class='error-msg'>✗ Username already taken</div>";
        }
    } else {
        $msg = "<div class='error-msg'>✗ Username must be 3+ characters, letters/numbers/underscores only</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* NAVBAR STYLES */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgb(255, 255, 255);
            padding: 10px 20px;
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .navbar .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .logo a {
            display: block;
            flex-shrink: 0;
        }

        .logo a img {
            height: auto;
            width: 100px;
            display: block;
        }

        .nav-search {
            flex: 1;
            max-width: 500px;
            margin: 0 20px;
        }

        .search-form {
            display: flex;
            align-items: center;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            overflow: hidden;
            background: #fff;
            transition: all 0.3s ease;
        }

        .search-form:focus-within {
            border-color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .search-input {
            flex: 1;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            outline: none;
            background: transparent;
        }

        .search-btn {
            background: none;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }

        .search-btn:hover {
            background-color: #f5f5f5;
        }

        .search-btn img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        .nav-scl ul {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 25px;
            flex-shrink: 0;
        }

        .nav-scl li {
            display: inline-block;
        }

        .nav-scl li a {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-scl li img {
            height: 24px;
            width: auto;
            object-fit: contain;
            transition: transform 0.2s;
        }

        .nav-scl li a:hover img {
            transform: scale(1.1);
        }

        .account-menu {
            position: relative;
            display: inline-block;
        }

        .account-menu-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #fff;
            min-width: 180px;
            box-shadow: 0 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            padding: 10px 0;
            border-radius: 4px;
            text-align: left;
            border: 1px solid #eee;
        }

        .account-menu:hover .account-menu-content {
            display: block;
        }

        .account-menu-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }

        .account-menu-content a:hover {
            background-color: #f1f1f1;
        }

        .user-greeting {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .user-greeting:hover {
            background-color: #f9f9f9;
        }

        /* PROFILE PAGE STYLES*/
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #fff;
            color: #000;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        .profile-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 36px;
            font-weight: 700;
            margin: 0 0 10px 0;
            letter-spacing: -0.5px;
        }

        .back-link {
            color: #000;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .section-title {
            font-size: 22px;
            font-weight: 600;
            margin: 40px 0 20px 0;
        }

        /* Form Grid for Account Details */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 40px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 500;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            padding: 12px 20px;
            border: 1px solid #000;
            border-radius: 50px;
            font-size: 15px;
            outline: none;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
        }

        .form-group input[disabled] {
            background-color: #f5f5f5;
            color: #888;
            cursor: not-allowed;
            border-color: #ccc;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 40px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cancel {
            background: #fff;
            border: 1px solid #000;
            color: #000;
        }

        .btn-cancel:hover {
            background: #f5f5f5;
        }

        .btn-submit {
            background: #000;
            border: 1px solid #000;
            color: #fff;
        }

        .btn-submit:hover {
            background: #333;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 20px;
        }

        .btn-danger {
            background: #fff5f5;
            border: 1px solid #fc8181;
            color: #c53030;
        }

        .btn-danger:hover {
            background: #fed7d7;
        }

        /* Address Box Style */
        .address-box {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 24px;
            background: #fff;
        }

        .address-content p {
            margin: 5px 0;
            font-size: 15px;
            color: #333;
        }

        .address-content strong {
            font-weight: 600;
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }

        .view-link {
            color: #000;
            text-decoration: underline;
            font-size: 14px;
            cursor: pointer;
            display: inline-block;
            margin-top: 15px;
            font-weight: 500;
        }

        .view-link:hover {
            color: #555;
        }

        /* Auth Details Grid */
        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .auth-box {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-box-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .auth-box-header svg {
            width: 20px;
            height: 20px;
            color: #555;
        }

        .auth-box h4 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .auth-box p {
            font-size: 14px;
            color: #666;
            margin: 4px 0;
        }

        .auth-box a {
            color: #000;
            text-decoration: underline;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .auth-box a:hover {
            text-decoration: none;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 32px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .modal-header h3 {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #000;
        }

        /* Address List in Modal */
        .address-item {
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            position: relative;
        }

        .address-item.default {
            border-color: #000;
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            background: #000;
            color: #fff;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 5px;
            vertical-align: middle;
        }

        .address-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 5px;
        }

        /* Messages */
        .success-msg {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error-msg {
            background: #ffebee;
            color: #c62828;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Footer Styles */
        hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 0;
        }

        footer {
            background: #ffffff;
            color: #000000;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 50px 20px 30px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .footer-section h3 {
            color: #000000;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section li {
            margin-bottom: 8px;
        }

        .footer-section a {
            color: #000000;
            text-decoration: none;
        }

        .footer-section a:hover {
            color: #fff;
        }

        .FooterBottom {
            border-top: 1px solid #000000;
            padding: 20px;
            text-align: center;
            font-size: 13px;
        }

        .FooterBottom p {
            margin-bottom: 10px;
        }

        .Policy ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .Policy li {
            display: inline-block;
        }

        .Policy a {
            color: #000000;
            text-decoration: none;
        }

        .Policy a:hover {
            color: #fff;
        }

        @media (max-width: 768px) {
            .navbar .container {
                flex-wrap: wrap;
            }

            .nav-search {
                order: 3;
                width: 100%;
                margin: 10px 0 0 0;
            }

            .form-grid,
            .auth-grid {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="/alvenwear/assets/ALVÉN.png" alt="Alvenwear Logo">
                </a>
            </div>
            <div class="nav-search">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search products..." class="search-input" required>
                    <button type="submit" class="search-btn">
                        <img src="/alvenwear/assets/search ilmans.jpg" alt="Search">
                    </button>
                </form>
            </div>
            <div class="nav-scl">
                <ul>
                    <li><a href="cart.php"><img src="/alvenwear/assets/cart.png" alt="Cart"></a></li>
                    <li>
                        <?php if (isset($_SESSION["login_user"])): ?>
                            <div class="account-menu">
                                <span class="user-greeting">
                                    <img src="/alvenwear/assets/log in ilman.png" alt="User" style="vertical-align: middle; width:20px;">
                                    <?php echo htmlspecialchars($_SESSION["login_user"]); ?>
                                </span>
                                <div class="account-menu-content">
                                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                                        <a href="adminMenu.php" style="color: red; font-weight: bold;">Admin Dashboard</a>
                                        <div style="border-top: 1px solid #eee; margin: 5px 0;"></div>
                                    <?php endif; ?>
                                    <a href="myOrders.php">View Order Status</a>

                                    <a href="logout.php?redirect=Login.php">Switch user</a>
                                    <a href="profile.php" style="background:#f7fafc;font-weight:600;"> My Profile</a>
                                    <a href="logout.php">Log Out</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="Login.php">
                                <img src="/alvenwear/assets/log in ilman.png" alt="Login">
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <div class="profile-container">
            <div class="page-header">
                <h1>My Account</h1>
                <a href="index.php" class="back-link">← Back</a>
            </div>

            <?php
            if (isset($_GET['updated'])) {
                echo "<div class='success-msg'>✓ Profile updated successfully!</div>";
            }
            echo $msg;
            ?>

            <!-- 1. Account Details Section -->
            <!-- Change Username Section -->
            <div class="section">
                <h2 class="section-title">Change Username</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background:#f5f5f5;">
                    </div>
                    <div class="form-group">
                        <label>New Username</label>
                        <input type="text" name="new_username" required minlength="3" placeholder="Enter new username"
                            pattern="[A-Za-z0-9_]+" title="Only letters, numbers, and underscores allowed">
                        <small style="color:#666; font-size:12px;">3-20 characters, letters/numbers/underscores only</small>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="update_username" class="btn btn-submit">Update Username</button>
                    </div>
                </form>
            </div>
            <div class="section">
                <h2 class="section-title">Account Details</h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" placeholder="First Name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" placeholder="Last Name" <?php echo ($user['user_type'] == 'admin') ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" <?php echo ($user['user_type'] == 'admin') ? 'disabled' : ''; ?>>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo (isset($user['gender']) && $user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($user['gender']) && $user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (isset($user['gender']) && $user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" <?php echo ($user['user_type'] == 'admin') ? 'disabled' : ''; ?>>
                        </div>

                        <!-- EMAIL FIELD: Only show for customers -->
                        <?php if ($user['user_type'] != 'admin'): ?>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Phone Number" <?php echo ($user['user_type'] == 'admin') ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                    <div class="btn-group">
                        <a href="index.php" class="btn btn-cancel">Cancel</a>
                        <button type="submit" name="update_account" class="btn btn-submit">Submit</button>
                    </div>
                </form>
            </div>
            <!-- Primary Address Section (Customers Only) -->
            <?php if ($user['user_type'] == 'customer'): ?>
                <div class="section">
                    <h2 class="section-title">Primary Address</h2>
                    <?php if ($default_address): ?>
                        <div class="address-box">
                            <div class="address-content">
                                <!-- htmls -->
                                <strong><?php echo htmlspecialchars($default_address['recipient_name']); ?></strong>
                                <p><?php echo htmlspecialchars($default_address['phone']); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($default_address['address'])); ?></p>
                            </div>
                            <span class="view-link" onclick="openAddressModal()">View Addresses (<?php echo $addresses->num_rows; ?>)</span>
                        </div>
                    <?php else: ?>
                        <div class="address-box">
                            <p style="color:#666;">No address saved yet.</p>
                            <span class="view-link" onclick="openAddressModal()">Add Address</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Authentication Details Section -->
            <div class="section">
                <h2 class="section-title">Change Password</h2>
                <div class="auth-grid">
                    <!-- Password Box -->
                    <div class="auth-box">
                        <div class="auth-box-header">
                            <!-- Lock Icon -->
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h4>Password</h4>
                        </div>
                        <a onclick="openPasswordModal()">Change password</a>
                    </div>

                    <!-- Email Box Only for Customers -->
                    <?php if ($user['user_type'] == 'customer'): ?>
                        <div class="auth-box">
                            <div class="auth-box-header">
                                <!-- Envelope Icon -->
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <h4>Email</h4>
                            </div>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- MODALS (Hidden by default)-->

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Password</h3>
                <button class="close-modal" onclick="closePasswordModal()">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-cancel" onclick="closePasswordModal()">Cancel</button>
                    <button type="submit" name="update_password" class="btn btn-submit">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Address Management Modal (Customers Only) -->
    <?php if ($user['user_type'] == 'customer'): ?>
        <!-- Address Management Modal -->
        <div id="addressModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Manage Addresses</h3>
                    <button class="close-modal" onclick="closeAddressModal()">&times;</button>
                </div>

                <!-- Add New Address Section -->
                <div id="addAddressSection">
                    <details style="margin-bottom:24px;">
                        <summary style="cursor:pointer;font-weight:600;">+ Add New Address</summary>
                        <form method="POST" style="margin-top:15px;padding-top:15px;border-top:1px dashed #ccc;">
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Recipient Name</label>
                                <input type="text" name="recipient_name" required>
                            </div>
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" required>
                            </div>
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Full Address</label>
                                <textarea name="address" rows="3" required style="width:100%;padding:10px;border:1px solid #000;border-radius:8px;font-size:14px;"></textarea>
                            </div>
                            <div class="form-group" style="margin-bottom:20px;">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                    <input type="checkbox" name="is_default" value="1">
                                    <span style="font-size:14px;">Set as default address</span>
                                </label>
                            </div>
                            <button type="submit" name="add_address" class="btn btn-submit" style="width:100%;">Save Address</button>
                        </form>
                    </details>
                </div>

                <!-- Edit Address Form (Hidden by default) -->
                <div id="editAddressForm" style="display:none;">
                    <h4 style="margin-bottom:15px;">Edit Address</h4>
                    <form method="POST">
                        <input type="hidden" name="edit_address_id" id="edit_address_id">
                        <div class="form-group" style="margin-bottom:15px;">
                            <label>Recipient Name</label>
                            <input type="text" name="edit_recipient_name" id="edit_recipient_name" required>
                        </div>
                        <div class="form-group" style="margin-bottom:15px;">
                            <label>Phone Number</label>
                            <input type="tel" name="edit_phone" id="edit_phone" required>
                        </div>
                        <div class="form-group" style="margin-bottom:15px;">
                            <label>Full Address</label>
                            <textarea name="edit_address" id="edit_address" rows="3" required style="width:100%;padding:10px;border:1px solid #000;border-radius:8px;font-size:14px;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:20px;">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="edit_is_default" id="edit_is_default" value="1">
                                <span style="font-size:14px;">Set as default address</span>
                            </label>
                        </div>
                        <div style="display:flex;gap:10px;">
                            <button type="submit" name="update_address" class="btn btn-submit" style="flex:1;">Update Address</button>
                            <button type="button" onclick="cancelEdit()" class="btn btn-cancel" style="flex:1;">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Saved Addresses List -->
                <div id="addressList">
                    <h4 style="margin-bottom:15px;">Saved Addresses</h4>
                    <?php if ($addresses && $addresses->num_rows > 0): ?>
                        <?php while ($addr = $addresses->fetch_assoc()): ?>
                            <div class="address-item <?php echo $addr['is_default'] ? 'default' : ''; ?>">
                                <?php if ($addr['is_default']): ?>
                                    <span class="badge">DEFAULT</span>
                                <?php endif; ?>
                                <div class="address-actions">
                                    <?php if (!$addr['is_default']): ?>
                                        <button onclick="window.location.href='profile.php?set_default=<?php echo $addr['id']; ?>'" class="btn btn-sm" style="background:#eee;color:#000;">Set Default</button>
                                    <?php endif; ?>
                                    <!--addslashes can convert ' to \ to keep the JS syntax is valid -->
                                    <button onclick="editAddress(<?php echo $addr['id']; ?>, '<?php echo addslashes($addr['recipient_name']); ?>', '<?php echo addslashes($addr['phone']); ?>', '<?php echo addslashes($addr['address']); ?>', <?php echo $addr['is_default']; ?>)" class="btn btn-sm" style="background:#333;color:#fff;">Edit</button>
                                    <button onclick="if(confirm('Delete this address?')) window.location.href='profile.php?delete_address=<?php echo $addr['id']; ?>'" class="btn btn-sm btn-danger">Delete</button>
                                </div>
                                <div class="address-content" style="padding-right: 100px;">
                                    <strong><?php echo htmlspecialchars($addr['recipient_name']); ?></strong>
                                    <p><?php echo htmlspecialchars($addr['phone']); ?></p>
                                    <!-- n12br is formatting measure for the address to look properly -->
                                    <p><?php echo nl2br(htmlspecialchars($addr['address'])); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#666; text-align:center; padding:20px;">No saved addresses.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!--FOOTER -->
    <hr>
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <div class="social-links">
                    <ul>
                        <li><a href="#">Facebook: @Alvenwear</a></li>
                        <li><a href="#">TikTok: @Alvenwear</a></li>
                        <li><a href="#">Instagram: @Alvenwear</a></li>
                        <li><a href="">Telegram: AlvenwearCommunity</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-section">
                <h3>Payment method</h3>
                <p>QR Pay | DuitNow | Touch 'n Go | Bank Transfer</p>
            </div>
            <div class="footer-section">
                <h3>Alvenwear</h3>
                <p>Everything Alven.</p>
            </div>
        </div>
        <div class="FooterBottom">
            <p>&copy; 2026 Alvenwear. All rights reserved.</p>
            <div class="Policy">
                <ul>
                    <li><a href="#">Terms of Service </a>|</li>
                    <li><a href="#">Privacy Policy </a>|</li>
                    <li><a href="#">Refund Policy </a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        // Opens the Password Change Modal
        function openPasswordModal() {
            // Find the modal element by ID and set its display to 'block' (visible)
            document.getElementById('passwordModal').style.display = 'block';
        }

        // Closes the Password Change Modal
        function closePasswordModal() {
            // Find the modal element and hide it by setting display to 'none'
            document.getElementById('passwordModal').style.display = 'none';
        }

        // Opens the Address Management Modal
        function openAddressModal() {
            document.getElementById('addressModal').style.display = 'block';
        }

        // Closes the Address Management Modal
        function closeAddressModal() {
            document.getElementById('addressModal').style.display = 'none';
        }
        // Event listener is When user clicks anywhere on the window
        window.onclick = function(event) {
            // Check if the click target IS the password modal background
            if (event.target == document.getElementById('passwordModal')) {
                closePasswordModal(); // Close it
            }
            // Check if the click target IS the address modal background
            if (event.target == document.getElementById('addressModal')) {
                closeAddressModal(); // Close it
            }
        }

        function editAddress(id, name, phone, address, isDefault) {
            // Fill form fields with the passed data
            document.getElementById('edit_recipient_name').value = name; // Set name input
            document.getElementById('edit_phone').value = phone; // Set phone input
            document.getElementById('edit_address').value = address; // Set address textarea
            document.getElementById('edit_is_default').checked = isDefault == 1; // Check/uncheck default checkbox
            document.getElementById('edit_address_id').value = id; // Store ID in hidden input

            // Toggle visibility: Hide the address list, show the edit form
            document.getElementById('addressList').style.display = 'none'; // Hide list view
            document.getElementById('editAddressForm').style.display = 'block'; // Show edit form
        }

        /* cancelEdit() - Cancels editing and returns to address list view */
        function cancelEdit() {
            //Toggle visibility back: Show list, hide form
            document.getElementById('editAddressForm').style.display = 'none'; // Hide edit form
            document.getElementById('addressList').style.display = 'block'; // Show list view

            // Clear all form fields (optional but good UX)
            document.getElementById('edit_recipient_name').value = ''; // Clear name
            document.getElementById('edit_phone').value = ''; // Clear phone
            document.getElementById('edit_address').value = ''; // Clear address
            document.getElementById('edit_is_default').checked = false; // Uncheck default
            // Note: We don't clear edit_address_id as it's hidden and not user-facing
        }
    </script>

</body>

</html>