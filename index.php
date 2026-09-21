<?php
session_start();
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- INLINE CSS -->
    <style>
        main {
            padding: 0px;
        }

        /* Center the Product and Appreciation sections */
        .product .container,
        .appreciation .container {
            max-width: 1400px;
            margin: 0 auto;
            /* Centers the block in the middle of the screen */
            padding: 0 40px;
            /* Adds space on the left and right */
            box-sizing: border-box;
        }

        /* NAVBAR CONTAINER */
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

        /* LOGO */
        .logo a {
            display: block;
            flex-shrink: 0;
        }

        .logo a img {
            height: auto;
            width: 100px;
            display: block;
        }

        /* SEARCH BAR (MIDDLE) */
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

        /* ICON LIST (Right Side) */
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

        /* DROPDOWN MENU */
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

        /* HERO BANNER */
        .hero-banner {
            position: relative;
            margin: 0;
            padding: 0;
            height: 600px;
            width: 100%;
            background-color: #000;
            overflow: hidden;
        }

        .slideshow-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .slide {
            display: none;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Text Overlay on Slides */
        .slide-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 5;
            padding: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .slide-content h1 {
            font-size: 48px;
            font-weight: 700;
            margin: 0 0 15px 0;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 1px;
        }

        .slide-content p {
            font-size: 20px;
            margin: 0 0 25px 0;
            font-weight: 300;
        }

        .slide-content .cta-btn {
            display: inline-block;
            background: white;
            color: black;
            padding: 12px 35px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .slide-content .cta-btn:hover {
            background: #333;
            color: white;
            transform: translateY(-2px);
        }

        .slide-content .cta-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
            margin-left: 15px;
        }

        .slide-content .cta-outline:hover {
            background: white;
            color: black;
        }

        /* Teaser Badge */
        .teaser-badge {
            display: inline-block;
            background: #e53e3e;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        @keyframes fade {
            from {
                opacity: .4
            }

            to {
                opacity: 1
            }
        }

        .dot {
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
            cursor: pointer;
        }

        .active,
        .dot:hover {
            background-color: #fff;
        }

        /* PRODUCT GRID */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 34px;
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
            padding: 10px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        .price {
            font-family: 'Courier New', Courier, monospace;
        }

        .section-title {
            margin-bottom: 20px;
            margin-top: 20px;
            border-left: 5px solid black;
            padding-left: 10px;
            font-size: 1.8rem;
            color: #000000;
            font-family: 'Courier New', Courier, monospace;
        }

        /* CATEGORY FILTER BUTTONS */
        .category-filter {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .category-filter label {
            font-weight: bold;
            font-size: 14px;
            margin-right: 10px;
        }

        .filter-btn {
            padding: 8px 20px;
            border: 1px solid #333;
            background: white;
            color: #333;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .filter-btn:hover {
            background: #333;
            color: white;
        }

        .filter-btn.active {
            background: #333;
            color: white;
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
            <div class="logo"><a href="index.php"><img src="/alvenwear/assets/ALVÉN.png" alt="Alvenwear Logo"></a></div>
            <div class="nav-search">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search products..." class="search-input" required>
                    <button type="submit" class="search-btn"><img src="/alvenwear/assets/search ilmans.jpg" alt="Search"></button>
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
                                    <a href="profile.php">My Profile</a>
                                    <a href="logout.php">Log Out</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="Login.php"><img src="/alvenwear/assets/log in ilman.png" alt="Login"></a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-banner">
            <div class="slideshow-container">

                <!-- Slide 1 More Than Just Clothing -->
                <div class="slide fade">

                    <div class="slide-content">
                        <h1>Follow Us On Social Media</h1>
                        <p>to get early update</p>
                        <a href="#footer" class="cta-btn cta-outline">Join Community</a>
                    </div>
                </div>
                 <!-- Slide 4 Upcoming Shirt Teaser -->
                <div class="slide fade">
                    <img src="/alvenwear/assets/Pink Jersey Coming Soon.png" style="width:100%; filter: brightness(0.7);">
                    <div class="slide-content">
                        <span class="teaser-badge">Coming Soon</span>
                        <h1>New Drop Alert</h1>
                        <p>Exclusive Alvenwear shirt collection launching soon</p>
                        <a href="Login.php" class="cta-btn">Get Notified</a>
                    </div>
                </div>


                <!-- Slide 2 Connect & Create -->
                <div class="slide fade">
                    <div class="slide-content">
                        <h1>Check Out Our Product</h1>
                        <p>Don't forget to Log In</p>
                        <a href="profile.php" class="cta-btn">My Profile</a>
                        <a href="#product" class="cta-btn cta-outline">Explore</a>
                    </div>
                </div>

                <!-- Slide 3 Limited Editions -->
                <div class="slide fade">
                    <img src="/alvenwear/assets/LIMITED EDITION.png" style="width:100%;">
                    <div class="slide-content">
                        <h1>Limited Editions</h1>
                        <p>Exclusive designs</p>
                        <a href="#product" class="cta-btn">View Collection</a>
                    </div>
                </div>

               
                <!-- Navigation Dots (Updated for 4 slides) -->
                <div style="text-align:center; position:absolute; bottom:20px; width:100%; z-index:10;">
                    <span class="dot" onclick="currentSlide(0)"></span>
                    <span class="dot" onclick="currentSlide(1)"></span>
                    <span class="dot" onclick="currentSlide(2)"></span>
                    <span class="dot" onclick="currentSlide(3)"></span>
                </div>
            </div>
        </section>

        <section class="product" id="product">
            <div class="container">
                <h2 class="section-title">Our Product</h2>

                <!-- Category Filter Buttons -->
                <div class="category-filter">
                    <label>Filter by:</label>
                    <a href="index.php" class="filter-btn <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">All</a>
                    <?php
                    include "db_connect.php";
                    $categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
                    while ($cat = $categories->fetch_assoc()) {
                        $isActive = (isset($_GET['category']) && $_GET['category'] == $cat['category']) ? 'active' : '';
                        echo "<a href='index.php?category=" . urlencode($cat['category']) . "' class='filter-btn $isActive'>" . htmlspecialchars($cat['category']) . "</a>";
                    }
                    ?>
                </div>

                <div class="product-grid">
                    <?php
                    $search_query = isset($_GET['q']) ? $_GET['q'] : '';
                    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
                    $sql = "SELECT * FROM products WHERE 1=1";
                    if (!empty($search_query)) {
                        $sql .= " AND name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
                    }
                    if (!empty($category_filter)) {
                        $sql .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
                    }
                    $sql .= " ORDER BY id DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                    ?>
                            <div class="product-card">
                                <a href="productDetails.php?id=<?php echo $row["id"]; ?>" style="display:block;">
                                    <img src="/alvenwear/assets/<?php echo $row["image"]; ?>" alt="<?php echo $row["name"]; ?>">
                                </a>
                                <div class="product-details">
                                    <a href="productDetails.php?id=<?php echo $row["id"]; ?>" style="color:black; text-decoration:none;">
                                        <?php echo $row["name"]; ?>
                                    </a>
                                    <div class="product-bottom">
                                        <span class="price">RM <?php echo number_format($row["price"], 2); ?></span><br>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p style='grid-column: 1/-1; text-align:center; padding: 40px; color: #666;'>No products found" . (!empty($category_filter) ? " in " . htmlspecialchars($category_filter) : "") . ".</p>";
                    }
                    ?>
                </div>
            </div>
        </section>

        <section class="appreciation">
            <div class="container appreciation-flex">
                <div class="appreciation-img">
                    <img src="/alvenwear/assets/clove_colors_embroide-removebg-preview.png" alt="Thank you flower">
                </div>
                <div class="appreciation-text">
                    <h2>Thank you for<br>supporting us!!</h2>
                    <p>we're really appreciate your support!</p>
                </div>
            </div>
        </section>
    </main>

    <hr>
    <footer>
        <div class="footer-container" id="footer">
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
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            let i;
            let slides = document.getElementsByClassName("slide");
            let dots = document.getElementsByClassName("dot");
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex > slides.length) {
                slideIndex = 1
            }
            for (i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex - 1].style.display = "block";
            dots[slideIndex - 1].className += " active";
            setTimeout(showSlides, 8000);
        }

        function currentSlide(n) {
            slideIndex = n;
            showSlides();
        }
    </script>
</body>

</html>