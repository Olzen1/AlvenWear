<?php
session_start();
include "db_connect.php";

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$search_results = [];

if (!empty($search_query)) {
    $safe_query = $conn->real_escape_string($search_query);
    $sql = "SELECT * FROM products WHERE name LIKE '%$safe_query%' OR category LIKE '%$safe_query%' OR description LIKE '%$safe_query%'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* NAVBAR*/
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgFb(255, 255, 255);
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

        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            width: 100%;
        }

        /* Search Header */
        .search-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .search-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 10px;
        }

        .search-header p {
            color: #718096;
            font-size: 15px;
        }

        .search-query {
            color: #333;
            font-weight: 600;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: #fff;
            border-radius: 9px;
            overflow: hidden;
            border: 1px solid #eee;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
        }

        .product-card img {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
        }

        .product-details {
            color: black;
            padding: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        .product-details a {
            color: black;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .product-details a:hover {
            text-decoration: underline;
        }

        .price {
            font-family: 'Courier New', Courier, monospace;
            color: #e53e3e;
            font-size: 18px;
            font-weight: 700;
        }

        .category-tag {
            display: inline-block;
            background: #edf2f7;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            color: #4a5568;
            margin-top: 8px;
            font-family: sans-serif;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #718096;
            font-size: 16px;
            margin-bottom: 25px;
        }

        .btn-home {
            display: inline-block;
            background: #333;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-home:hover {
            background: #1a1a1a;
        }

        .Policy ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
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

            .footer-container {
                grid-template-columns: 1fr;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
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

            <div class="nav-search">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search products..." class="search-input"
                        value="<?php echo htmlspecialchars($search_query); ?>" required>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="search-header">
            <h1>Search Results</h1>
            <?php if (!empty($search_query)): ?>
                <p>Found <span class="search-query"><?php echo count($search_results); ?> product(s)</span> for "<span class="search-query"><?php echo htmlspecialchars($search_query); ?></span>"</p>
            <?php else: ?>
                <p>Please enter a search term above</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($search_query) && count($search_results) > 0): ?>
            <div class="product-grid">
                <?php foreach ($search_results as $product): ?>
                    <div class="product-card">
                        <a href="productDetails.php?id=<?php echo $product['id']; ?>" style="display:block;">
                            <img src="/alvenwear/assets/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                        <div class="product-details">
                            <a href="productDetails.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                            <span class="price">RM <?php echo number_format($product['price'], 2); ?></span>
                            <?php if (!empty($product['category'])): ?>
                                <div class="category-tag"><?php echo htmlspecialchars($product['category']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
                <p><?php echo empty($search_query) ? "Enter a search term to find products" : "No products found for your search. Try different keywords!"; ?></p>
                <a href="index.php" class="btn-home">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li><a href="#">Facebook: @Alvenwear</a></li>
                    <li><a href="#">TikTok: @Alvenwear</a></li>
                    <li><a href="#">Instagram: @Alvenwear</a></li>
                    <li><a href="#">Telegram: AlvenwearCommunity</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Payment Method</h3>
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
                    <li><a href="#">Terms of Service</a> |</li>
                    <li><a href="#">Privacy Policy</a> |</li>
                    <li><a href="#">Refund Policy</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>

</html>