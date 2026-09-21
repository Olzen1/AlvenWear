<?php
session_start();
// If user is already logged in, send them to home
if (isset($_SESSION["login_user"])) {
    header("location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .hidden {
            display: none;
        }

        /* NAVBAR CONTAINER */
        .navbar {
            display: flex;
            /* Makes items sit side-by-side */
            align-items: center;
            /* Vertically centers items */
            justify-content: space-between;
            /* Pushes Logo to Left, Icons to Right */
            background-color: rgb(255, 255, 255);
            padding: 10px 20px;
            /* Adds room */
            position: sticky;
            /* Keeps nav at top when scrolling */
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            /* Ensures it sits on top of content */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            /* Subtle shadow */
        }

        .navbar .container {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /*  LOGO */
        .logo a {
            display: block;
            /* Makes the link clickable area the whole image */
        }

        .logo a img {
            height: auto;
            width: 100px;
            display: block;
        }

        /* ICON LIST (Right Side) */
        .nav-scl ul {
            display: flex;
            align-items: center;
            list-style: none;
            /* Removes bullet points */
            margin: 0;
            padding: 0;
            gap: 30px;
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
            /* Adjust icon size */
            width: auto;
            object-fit: contain;
            transition: transform 0.2s;
        }

        .nav-scl li a:hover img {
            transform: scale(1.1);
            /* Slight zoom on hover */
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="/alvenwear/assets/ALVÉN.png" alt="Alvenwear Logo">
                </a>
            </div>
            <div class="nav-scl">
                <ul>
                    <li><a href="cart.php"><img src="/alvenwear/assets/cart.png" alt="Cart"></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <div class="login-page-container">

            <!-- CUSTOMER LOGIN FORM -->
            <section class="login-box" id="customer-login">
                <h2>Log In</h2>
                <form action="loginProcess.php" method="POST">
                    <!-- Hidden field to identify this is a customer -->
                    <input type="hidden" name="role" value="customer">

                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <!-- NAME is required for PHP to catch the data -->
                        <input type="text" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-login">Log In</button>
                    </div>
                </form>

                <div class="switch-link">
                    <a href="#" id="link-to-admin">Log In as Admin</a>
                </div>
                <div class="create-account">
                    <h4>Create your account now!</h4>
                    <a href="#" class="btn-create" id="link-to-register">Create Account</a>
                </div>
            </section>


            <!-- ADMIN LOGIN FORM -->
            <section class="admin-login-box hidden" id="admin-login">
                <h2>Log In as Admin</h2>
                <form action="loginProcess.php" method="POST">
                    <!-- Hidden field to identify this is an admin -->
                    <input type="hidden" name="role" value="admin">


                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-admin-login">Admin Log In</button>
                    </div>
                </form>
                <div class="switch-link">
                    <a href="#" id="link-to-customer">Back to Customer Login</a>
                </div>
            </section>

            <!-- REGISTER FORM -->
            <section class="register-box hidden" id="register-section">
                <h2>Register</h2>
                <form action="registerProcess.php" method="POST">
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-register">Register</button>
                    </div>
                </form>
                <div class="switch-link">
                    <a href="#" id="link-to-login">Back to Log In</a>
                </div>
            </section>

        </div>
    </main>

    <script src="login.js"></script>
</body>

</html>