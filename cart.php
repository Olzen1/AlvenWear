<?php
session_start();
include "db_connect.php";

// Handle Add to Cart (if redirected from product page)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {
    $product_id = $_POST["product_id"];
    $sql_product = "SELECT * FROM products WHERE id='$product_id'";
    $result_product = $conn->query($sql_product);
    if ($result_product->num_rows > 0) {
        $row = $result_product->fetch_assoc();
        $selected_size = isset($_POST['selected_size']) ? $_POST['selected_size'] : 'N/A';

        $_SESSION['cart'][] = array(
            "id" => $row['id'],
            "name" => $row['name'],
            "price" => $row['price'],
            "image" => $row['image'],
            "size" => $selected_size
        );
        header("Location: cart.php");
        exit();
    }
}

// Handle Remove Item
if (isset($_GET['remove'])) {
    $key = $_GET['remove'];
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
    header("location: cart.php");
    exit();
}

// Handle Clear Cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("location: cart.php");
    exit();
}

// Calculate Total
$total = 0;
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
foreach ($cart_items as $item) {
    $total += $item['price'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* NAVBAR  */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

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

        /* CART */
        main {
            flex: 1;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .cart-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
        }

        .cart-count {
            background: #333;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .cart-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            align-items: center;
            transition: transform 0.2s;
        }

        .cart-item:hover {
            transform: translateY(-2px);
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 5px 0;
            font-family: 'Courier New', Courier, monospace;
        }

        .item-size {
            display: inline-block;
            background: #edf2f7;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .item-price {
            font-size: 20px;
            font-weight: 700;
            color: #e53e3e;
            font-family: 'Courier New', Courier, monospace;
        }

        .btn-remove {
            color: #e53e3e;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 12px;
            border: 1px solid #fed7d7;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .btn-remove:hover {
            background: #fff5f5;
        }

        .cart-summary {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            text-align: right;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 15px;
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-top: 15px;
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
        }

        .cart-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            display: inline-block;
            font-family: 'Courier New', Courier, monospace;
        }

        .btn-outline {
            background: white;
            color: #333;
            border: 2px solid #333;
        }

        .btn-outline:hover {
            background: #333;
            color: white;
        }

        .btn-primary {
            background: #333;
            color: white;
        }

        .btn-primary:hover {
            background: #1a1a1a;
        }

        .btn-danger {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .btn-danger:hover {
            background: #fed7d7;
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .empty-cart svg {
            width: 80px;
            height: 80px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-cart p {
            color: #718096;
            font-size: 16px;
            margin-bottom: 25px;
        }


        hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 0;
        }

        .Policy ul {
            list-style: none;
        }

        .Policy li {
            display: inline-block;
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

            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .cart-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!--NAVBAR-->
    <nav class="navbar">
        <div class="container">
            <!-- Logo -->
            <div class="logo">
                <a href="index.php">
                    <img src="/alvenwear/assets/ALVÉN.png" alt="Alvenwear Logo">
                </a>
            </div>

            <!-- Search Bar (Middle) -->
            <div class="nav-search">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search products..." class="search-input" required>
                    <button type="submit" class="search-btn">
                        <img src="/alvenwear/assets/search ilmans.jpg" alt="Search">
                    </button>
                </form>
            </div>

            <!-- Icons (Right Side) -->
            <div class="nav-scl">
                <ul>
                    <!-- Cart Icon -->
                    <li><a href="cart.php"><img src="/alvenwear/assets/cart.png" alt="Cart"></a></li>
                    <!-- User / Dropdown -->
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
                                    <a href="profile.php"> My Profile</a>
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
        <div class="cart-header">
            <h1>🛒 Your Cart</h1>
            <span class="cart-count"><?php echo count($cart_items); ?> items</span>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
                <p>Your cart is empty. Start shopping to add items!</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <?php foreach ($cart_items as $key => $item): ?>
                    <div class="cart-item">
                        <img src="/alvenwear/assets/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-info">
                            <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <span class="item-size">Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?></span>
                            <div class="item-price">RM <?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <a href="cart.php?remove=<?php echo $key; ?>" class="btn-remove" onclick="return confirm('Remove this item?')">Remove</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal (<?php echo count($cart_items); ?> items)</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-row">
                    <span style="font-weight:700;">Total</span>
                    <span style="font-weight:700; color:#e53e3e;">RM <?php echo number_format($total, 2); ?></span>
                </div>

                <div class="cart-actions">
                    <a href="index.php" class="btn btn-outline">← Continue Shopping</a>
                    <?php if (count($cart_items) > 1): ?>
                        <a href="cart.php?clear=1" class="btn btn-danger" onclick="return confirm('Clear entire cart?');">Clear Cart</a>
                    <?php endif; ?>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout →</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!--FOOTER-->
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