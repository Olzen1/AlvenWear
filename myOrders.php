<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["login_user"])) {
    header("location: Login.php");
    exit();
}

// FIX: Get the actual email from the users table (same as checkout.php)
$username = $_SESSION["login_user"];
$user_email = "";

$user_query = $conn->query("SELECT email FROM users WHERE username='$username'");
if ($user_query && $user_query->num_rows > 0) {
    $user_data = $user_query->fetch_assoc();
    $user_email = $user_data['email'];
}

// Now query orders using the actual email
$sql = "SELECT orders.*, products.image 
        FROM orders 
        JOIN products ON orders.product_name = products.name 
        WHERE orders.user_email='$user_email' 
        ORDER BY orders.id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Alvenwear</title>
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

        /* Search Bar */
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

        /* Icons */
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

        /* Account Dropdown */
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            width: 100%;
        }

        /* Page Header */
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 32px;
            color: #1a202c;
            margin-bottom: 10px;
            font-family: 'Courier New', Courier, monospace;
        }

        .page-header p {
            color: #718096;
            font-size: 15px;
        }

        /* Orders Table */
        .orders-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #333;
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
            font-size: 14px;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Product Info */
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-thumb {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
        }

        .product-name {
            font-weight: 600;
            color: #2d3748;
        }

        .product-size {
            display: inline-block;
            background: #edf2f7;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
            margin-top: 5px;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff7ed;
            color: #c05621;
        }

        .status-accepted {
            background: #f0fff4;
            color: #276749;
        }

        .status-cancelled {
            background: #fff5f5;
            color: #c53030;
        }

        /* Price */
        .price-tag {
            font-weight: 700;
            color: #e53e3e;
            font-size: 15px;
        }

        /* Receipt Link */
        .receipt-link {
            color: #333;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .receipt-link:hover {
            background: #f7fafc;
            border-color: #333;
        }

        /* Cancel Reason */
        .cancel-reason {
            background: #fff5f5;
            border-left: 3px solid #fc8181;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            color: #c53030;
            margin-top: 5px;
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

        .btn-shop {
            display: inline-block;
            background: #333;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-shop:hover {
            background: #1a1a1a;
        }

        /* Appreciation Section - RASKAL.MADE Style */
        .appreciation-section {
            background: white;
            padding: 60px 40px;
            margin: 40px auto;
            max-width: 1000px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 50px;
            flex-wrap: wrap;
        }

        .appreciation-logo {
            flex: 0 0 250px;
            text-align: center;
        }

        .appreciation-logo img {
            width: 100%;
            max-width: 250px;
            height: auto;
        }

        .appreciation-content {
            flex: 1;
            min-width: 300px;
        }

        .appreciation-content h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 15px;
            line-height: 1.4;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .appreciation-content p {
            font-size: 14px;
            color: #4a5568;
            line-height: 1.6;
            margin-top: 15px;
        }

        .appreciation-note {
            background: #f7fafc;
            border-left: 4px solid #333;
            padding: 12px 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 13px;
            color: #718096;
        }

        /* Footer - Copied from index.php */
        footer {
            background: #1a202c;
            color: #a0aec0;
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
            color: #fff;
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
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-section a:hover {
            color: #fff;
        }

        .FooterBottom {
            border-top: 1px solid #2d3748;
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
            color: #a0aec0;
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

            .footer-container {
                grid-template-columns: 1fr;
            }

            .appreciation-section {
                flex-direction: column;
                text-align: center;
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

            <!-- Search Bar -->
            <div class="nav-search">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search products..." class="search-input" required>
                    <button type="submit" class="search-btn">
                        <img src="/alvenwear/assets/search ilmans.jpg" alt="Search">
                    </button>
                </form>
            </div>

            <!-- Icons -->
            <div class="nav-scl">
                <ul>
                    <li><a href="cart.php"><img src="/alvenwear/assets/cart.png" alt="Cart"></a></li>
                    <li>
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
                                <a href="#" style="background:#f7fafc;font-weight:600;">View My Order</a>
                                <a href="logout.php?redirect=Login.php">Switch user</a>
                                <a href="profile.php"> My Profile</a>
                                <a href="logout.php">Log Out</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>My Orders</h1>
            <p>Track and manage your Alvenwear purchases</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="orders-table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:80px;">Order ID</th>
                            <th>Product</th>
                            <th>Size</th>
                            <th style="width:120px;">Price</th>
                            <th style="width:130px;">Status</th>
                            <th style="width:150px;">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):
                            $statusClass = 'status-pending';
                            $displayStatus = $row['status'];
                            if ($row['status'] == 'Accepted') {
                                $statusClass = 'status-accepted';
                            } elseif ($row['status'] == 'Cancelled') {
                                $statusClass = 'status-cancelled';
                                $displayStatus = "Cancelled";
                            }
                        ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td>
                                    <div class="product-info">
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="/alvenwear/assets/<?php echo htmlspecialchars($row['image']); ?>"
                                                alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                                class="product-thumb">
                                        <?php endif; ?>
                                        <div>
                                            <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="product-size"><?php echo htmlspecialchars($row['size']); ?></span></td>
                                <td><span class="price-tag">RM <?php echo number_format($row['price'], 2); ?></span></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $displayStatus; ?></span></td>
                                <td>
                                    

                                    <?php
                                    // Replace your existing <td> for receipt/details with this:
                                    
                                    if ($row['status'] == 'Accepted') {
                                        echo "<a href='receipt.php?id=" . $row['id'] . "' target='_blank' 
                                            style='display:inline-block; padding:6px 12px; background:#333; color:white; 
                                            text-decoration:none; border-radius:4px; font-size:13px; font-weight:500;'>
                                            🖨 View Receipt</a>";
                                    } elseif ($row['status'] == 'Cancelled' && !empty($row['cancel_reason'])) {
                                        echo "<div style='color:red; font-size:13px; margin-bottom:5px;'>
                                                <strong>Reason:</strong> " . htmlspecialchars($row['cancel_reason']) . "
                                            </div>";
                                    } else {
                                        echo "<span style='color:#999; font-size:12px;'>Pending approval</span>";
                                    }
                                    echo "</td>";
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M16 11V7a4 4 0 10-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="btn-shop">Start Shopping</a>
            </div>
        <?php endif; ?>


    </div>


</body>

</html>