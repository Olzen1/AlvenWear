<?php
session_start();
include "db_connect.php";

$msg = "";
$total_price = 0;

if (empty($_SESSION['cart'])) {
    header("location: index.php");
    exit();
}

// Calculate Total Price
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'];
}

// Get the logged-in username
$logged_in_username = isset($_SESSION["login_user"]) ? $_SESSION["login_user"] : "";

// Fetch user's actual email from the users table
$user_email = "";
if (!empty($logged_in_username)) {
    $user_query = $conn->query("SELECT email FROM users WHERE username='$logged_in_username'");
    if ($user_query && $user_query->num_rows > 0) {
        $user_data = $user_query->fetch_assoc();
        $user_email = $user_data['email'];
    }
}

// Fetch user's saved addresses using their actual email
$saved_addresses = [];
if (!empty($user_email)) {
    $addresses_result = $conn->query("SELECT * FROM addresses WHERE user_email='$user_email' ORDER BY is_default DESC, id DESC");
    if ($addresses_result) {
        while ($addr = $addresses_result->fetch_assoc()) {
            $saved_addresses[] = $addr;
        }
    }
}

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['fullname'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // FILE UPLOAD
    $receipt_path = "";
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $target_dir = "assets/receipts/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["receipt"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file)) {
            $receipt_path = $filename;
        }
    }

    // Insert orders (WITHOUT order_group_id)
    foreach ($_SESSION['cart'] as $item) {
        $sql = "INSERT INTO orders (user_email, product_name, price, size, status, customer_name, customer_phone, customer_address, receipt_image) 
                VALUES ('$user_email', '" . $item['name'] . "', '" . $item['price'] . "', '" . $item['size'] . "', 'Pending', '$name', '$phone', '$address', '$receipt_path')";

        $conn->query($sql);
    }

    unset($_SESSION['cart']);
    echo "<script>alert('Order placed successfully! Waiting for admin approval.'); window.location.href='index.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            background-color: #f4f4f4;
        }

        .checkout-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .checkout-container,
        .payment-info {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        /* Saved Address Section */
        .saved-address-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .saved-address-section h4 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333;
        }

        .address-option {
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .address-option:hover {
            border-color: #999;
        }

        .address-option input[type="radio"] {
            margin-right: 10px;
        }

        .address-option.selected {
            border-color: #000;
            background: #f0f0f0;
        }

        .address-option label {
            cursor: pointer;
            font-weight: 500;
        }

        .address-details {
            margin-left: 25px;
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        .badge-default {
            display: inline-block;
            background: #000;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 8px;
            font-weight: bold;
        }

        .payment-info {
            text-align: center;
        }

        .payment-info h3 {
            margin-top: 0;
        }

        .qr-display {
            margin: 20px 0;
            border: 1px solid #eee;
            padding: 15px;
            display: inline-block;
            border-radius: 8px;
        }

        .qr-display img {
            max-width: 200px;
            height: auto;
        }

        .total-price {
            font-size: 28px;
            color: #e74c3c;
            font-weight: bold;
            margin: 15px 0;
            font-family: 'Courier New', Courier, monospace;
        }

        .btn-checkout {
            width: 100%;
            background: #000;
            color: white;
            padding: 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            margin-top: 10px;
        }

        .btn-checkout:hover {
            background: #333;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /*account dropdown */
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
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: bold;
            padding: 0 10px;
            line-height: 30px;
        }

        /* NAVBAR */
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
        }

        .search-input {
            flex: 1;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            outline: none;
        }

        .search-btn {
            background: none;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
        }

        .search-btn img {
            width: 20px;
            height: 20px;
        }

        .nav-scl ul {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 25px;
        }

        .nav-scl li img {
            height: 24px;
            transition: transform 0.2s;
        }

        .nav-scl li a:hover img {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .checkout-wrapper {
                grid-template-columns: 1fr;
            }

            .navbar .container {
                flex-wrap: wrap;
            }

            .nav-search {
                order: 3;
                width: 100%;
                margin: 10px 0 0 0;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <div class="logo"><a href="index.php"><img src="/alvenwear/assets/ALVÉN.png"></a></div>
            <div class="nav-search">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search products..." class="search-input" required>
                    <button type="submit" class="search-btn"><img src="/alvenwear/assets/search ilmans.jpg" alt="Search"></button>
                </form>
            </div>
            <div class="nav-scl">
                <ul>
                    <li><a href="cart.php"><img src="/alvenwear/assets/cart.png" alt=""></a></li>
                    <li>
                        <?php if (isset($_SESSION["login_user"])): ?>
                            <div class="account-menu">
                                <span class="user-greeting">
                                    <img src="/alvenwear/assets/log in ilman.png" alt="User" style="width:20px;">
                                    <?php echo ($_SESSION["login_user"]); ?>
                                </span>
                                <div class="account-menu-content">
                                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                                        <a href="adminMenu.php" style="color: red; font-weight: bold;">Admin Dashboard</a>
                                        <div style="border-top: 1px solid #eee; margin: 5px 0;"></div>
                                    <?php endif; ?>
                                    <a href="myOrders.php">View Order Status</a>
                                    <a href="profile.php">My Profile</a>
                                    <a href="logout.php?redirect=Login.php">Switch user</a>
                                    <a href="logout.php">Log Out</a>
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

    <div class="checkout-wrapper">
        <div class="checkout-container">
            <div class="section-title">📦 Shipping Details</div>

            <?php echo $msg; ?>

            <form action="checkout.php" method="POST" enctype="multipart/form-data" id="checkoutForm">

                <!-- Saved Addresses Section (if user is logged in and has saved addresses) -->
                <?php if (!empty($saved_addresses)): ?>
                    <div class="saved-address-section">
                        <h4>📍 Choose Saved Address</h4>
                        <?php foreach ($saved_addresses as $index => $addr): ?>
                            <div class="address-option" onclick="selectAddress(this)">
                                <input type="radio" name="address_selection" value="saved"
                                    data-name="<?php echo htmlspecialchars($addr['recipient_name']); ?>"
                                    data-phone="<?php echo htmlspecialchars($addr['phone']); ?>"
                                    data-address="<?php echo htmlspecialchars($addr['address']); ?>"
                                    id="addr_<?php echo $addr['id']; ?>"
                                    <?php echo ($index === 0) ? 'checked' : ''; ?>
                                    onchange="updateFormFromSelection()">
                                <label for="addr_<?php echo $addr['id']; ?>" style="cursor: pointer; font-weight: 600;">
                                    <?php echo htmlspecialchars($addr['recipient_name']); ?>
                                    <?php if ($addr['is_default']): ?>
                                        <span class="badge-default">DEFAULT</span>
                                    <?php endif; ?>
                                </label>
                                <div class="address-details">
                                    <?php echo htmlspecialchars($addr['phone']); ?><br>
                                    <?php echo nl2br(htmlspecialchars($addr['address'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="address-option" onclick="selectNewAddress()">
                            <input type="radio" name="address_selection" value="new" id="addr_new" onchange="updateFormFromSelection()">
                            <label for="addr_new" style="cursor: pointer;">+ Use Different Address</label>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Manual Address Entry -->
                <div id="manualAddressSection" style="<?php echo !empty($saved_addresses) ? 'display:none;' : ''; ?>">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" id="fullname" required placeholder="Recipient name">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" id="phone" required placeholder="012-345 6789">
                    </div>
                    <div class="form-group">
                        <label>Delivery Address</label>
                        <textarea name="address" id="address" rows="3" required placeholder="Street, City, State, Postal Code"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label>📄 Upload Bank Receipt</label>
                    <input type="file" name="receipt" accept="image/*,.pdf" required>
                    <small style="color:#666; font-size:12px;">Supported: JPG, PNG, PDF (Max 5MB)</small>
                </div>

                <button type="submit" class="btn-checkout">✓ Confirm Order - RM <?php echo number_format($total_price, 2); ?></button>
                <a href="cart.php" class="back-link">← Back to Cart</a>
            </form>
        </div>

        <div class="payment-info">
            <div class="section-title" style="justify-content: center;">💳 Payment Instruction</div>
            <p style="color:#666; margin-bottom: 20px;">Please transfer the total amount below.</p>
            <div class="total-price">RM <?php echo number_format($total_price, 2); ?></div>
            <div class="qr-display">
                <img src="/alvenwear/assets/qr.jpeg" alt="Payment QR Code">
            </div>
            <p style="font-size: 14px; color: #555; margin: 15px 0;">
                <strong>Bank:</strong> Maybank<br>
                <strong>Account:</strong> 5941 3512 4321
            </p>
            <p style="font-size: 12px; color: #999; margin-top: 20px;">
                ⚠️ After payment, upload your receipt above.<br>
                Orders are processed after admin verification.
            </p>
        </div>
    </div>

    <script>
        function selectAddress(element) {
            // Hide manual address section
            document.getElementById('manualAddressSection').style.display = 'none';

            // Update visual selection
            document.querySelectorAll('.address-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');

            // Update form fields
            updateFormFromSelection();
        }

        function selectNewAddress() {
            // Show manual address section
            document.getElementById('manualAddressSection').style.display = 'block';

            // Clear form
            document.getElementById('fullname').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('address').value = '';

            // Update visual selection
            document.querySelectorAll('.address-option').forEach(el => el.classList.remove('selected'));
        }

        function updateFormFromSelection() {
            const selected = document.querySelector('input[name="address_selection"]:checked');
            if (selected && selected.value === 'saved') {
                document.getElementById('fullname').value = selected.dataset.name;
                document.getElementById('phone').value = selected.dataset.phone;
                document.getElementById('address').value = selected.dataset.address;
            }
        }

        // Initialize - select first address by default if exists
        window.onload = function() {
            const firstAddress = document.querySelector('input[name="address_selection"][value="saved"]');
            if (firstAddress) {
                firstAddress.checked = true;
                firstAddress.parentElement.classList.add('selected');
                updateFormFromSelection();
            } else {
                // If no saved addresses, show manual form
                document.getElementById('addr_new').checked = true;
            }
        };
    </script>

</body>

</html>