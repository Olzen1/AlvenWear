<?php
include "db_connect.php";
session_start();

// 1. Get Product ID from URL
if (!isset($_GET['id'])) {
    header("location: index.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id='$id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* --- PRODUCT DETAIL LAYOUT --- */
        .detail-wrapper {
            max-width: 1100px;
            margin: 60px auto;
            display: flex;
            /* Side by side layout */
            gap: 50px;
            align-items: flex-start;
            padding: 0 20px;
        }

        /* Left Side Image */
        .detail-image {
            flex: 1;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .detail-image img {
            max-width: 100%;
            height: auto;
            max-height: 600px;
            /* Keep it tall */
            object-fit: contain;
            /* Ensure image isnt cropped */
        }

        /* Right Side details */
        .detail-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .detail-info h1 {
            font-family: 'Courier New', Courier, monospace;
            font-size: 2.5rem;
            margin-bottom: 10px;
            line-height: 1.1;
        }

        .detail-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            font-family: 'Courier New', Courier, monospace;
        }

        .detail-desc {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 1rem;
        }

        /* Size Selector */
        .size-selector {
            margin-bottom: 25px;
        }

        .size-label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
        }

        .size-options {
            display: flex;
            gap: 10px;
        }

        .size-option input {
            display: none;
            /* Hide radio buttons */
        }

        .size-option label {
            display: inline-block;
            padding: 10px 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            min-width: 50px;
            text-align: center;
            transition: all 0.2s;
        }

        /* Highlight selected size */
        .size-option input:checked+label {
            background-color: black;
            color: white;
            border-color: black;
        }

        /* Add to Cart Button */
        .btn-add-cart {
            background-color: black;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn-add-cart:hover {
            background-color: #444;
        }

        /* responsive for the mobile by making it fit    */
        @media (max-width: 768px) {

            /*  moves the image to the top, and the Details/Text stack neatly */
            .detail-wrapper {
                flex-direction: column;
            }

            .detail-image img {
                max-height: 400px;
            }
        }

        /* Navbar Dropdown Style */
        .account-menu {
            position: relative;
            display: inline-block;
        }

        .account-menu-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
            padding: 10px 0;
            border-radius: 4px;
            text-align: left;
        }

        .account-menu:hover .account-menu-content {
            display: block;
        }

        .account-menu-content a {
            color: black;
            padding: 10px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }

        .account-menu-content a:hover {
            background-color: #f1f1f1;
        }

        .user-greeting {
            font-size: 12px;
            font-weight: bold;
            display: block;
            padding: 0 10px;
            line-height: 30px;
        }

        /* NAVBAR CONTAINER*/
        .navbar {
            display: flex;
            /* Makes items sit side-by-side */
            align-items: center;
            /* Vertically centers items */
            justify-content: space-between;
            /* Pushes Logo to Left, Icons to Right */
            background-color: rgb(255, 255, 255);
            padding: 10px 20px;
            /* Adds breathing room */
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

        /* LOGO */
        .logo a {
            display: block;
            /* Makes the link clickable area the whole image */
        }

        .logo a img {
            height: auto;
            width: 100px;
            display: block;
        }

        /* ICON LIST */
        .nav-scl ul {
            display: flex;
            align-items: center;
            list-style: none;
            /* Removes bullet points */
            margin: 0;
            padding: 0;
            gap: 30px;
            /* Space between Cart, User */
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

        /* FOOTER GENERAL STYLING */
        footer {
            background-color: #f9f9f9;
            /* Light grey background */
            padding-top: 40px;
            /* Top spacing */
            margin-top: 20px;
            /* Space between content and footer */
        }

        /* FOOTER GRID */
        .footer-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 0 40px 40px 40px;
            /* Left/Right/Bottom padding */
            max-width: 1200px;
            margin: 0 auto;
            /* Centers the grid on wide screens */
        }

        .social-links ul,
        .footer-section ul,
        .Policy ul {
            list-style: none;
            /* REMOVES THE DOTS */
            padding: 0;
            margin: 0;
        }

        .FooterBottom {
            display: block;
            width: 100%;
            text-align: center;
            /* Centers the Copyright text */
            padding: 20px;
            border-top: 1px solid #ddd;
            background-color: #f9f9f9;
            clear: both;
            /* Ensures it breaks out of the grid above */
        }

        /*policy style */
        .Policy {
            display: inline-block;
            /* Wraps the list tightly so it centers correctly */
            margin-top: 10px;
        }

        .Policy li {
            display: inline-block;
            /* Puts links side-by-side */
            margin: 0 5px;
            /* Small gap between links */
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <!-- NAVBAR (Same as Index) -->
    <nav class="navbar">
        <div class="container">
            <div class="logo"><a href="index.php"><img src="/alvenwear/assets/ALVÉN.png"></a></div>
            <div class="nav-scl">
                <ul>
                    <li><a href="cart.php"><img src="/alvenwear/assets/cart.png" alt=""></a></li>

                    <li>
                        <?php if (isset($_SESSION["login_user"])): ?>
                            <div class="account-menu">
                                <span class="user-greeting">
                                    <img src="/alvenwear/assets/log in ilman.png" alt="User" style="vertical-align: middle; width:20px;">
                                    <?php echo ($_SESSION["login_user"]); ?>
                                </span>
                                <div class="account-menu-content">
                                    <!-- ADMIN DASHBOARD BUTTON (Only visible to Admins) -->
                                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                                        <a href="adminMenu.php" style="color: red; font-weight: bold;">Admin Dashboard</a>
                                        <div style="border-top: 1px solid #eee; margin: 5px 0;"></div>
                                    <?php endif; ?>
                                    <a href="myOrders.php">View Order Status</a>

                                    <?php if ($_SESSION['user_role'] != 'admin'): ?>
                                        <a href="logout.php?redirect=Login.php">Switch user</a>
                                    <?php endif; ?>
                                    <a href="logout.php">Log Out</a>
                                    <a href="profile.php">My Profile</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="Login.php"><img src="/alvenwear/assets/log in ilman.png" alt=""></a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="detail-wrapper">
        <!-- Left: Image -->
        <div class="detail-image">
            <img src="/alvenwear/assets/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        </div>

        <!-- Right: Details -->
        <div class="detail-info">
            <h1><?php echo $product['name']; ?></h1>
            <div class="detail-price">RM <?php echo number_format($product['price'], 2); ?></div>

            <div class="detail-desc">
                <?php
                // Show description if exists, otherwise placeholder
                echo !empty($product['description']) ? nl2br($product['description']) : "No description available for this product.";
                ?>
            </div>

            <!-- Add to Cart Form -->
            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                <!-- Size Selector -->
                <div class="size-selector">
                    <span class="size-label">Select Size:</span>
                    <div class="size-options">
                        <?php
                        // Convert sizes string into buttons
                        $sizes = explode(",", $product['sizes']);
                        $first = true;
                        foreach ($sizes as $size):
                            $size = trim($size);
                            $checked = $first ? 'checked' : ''; // Select first size by default
                            if ($first) $first = false;
                        ?>
                            <div class="size-option">
                                <input type="radio" name="selected_size" id="size-<?php echo $size; ?>" value="<?php echo $size; ?>" <?php echo $checked; ?> required>
                                <label for="size-<?php echo $size; ?>"><?php echo $size; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn-add-cart">Add To Cart</button>
            </form>

            <div style="margin-top: 20px; font-size: 0.9rem; color: #555;">
                <p>✓ Worldwide shipping</p>
                <p>✓ Secure payments</p>
            </div>
        </div>
    </div>
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

</body>

</html>