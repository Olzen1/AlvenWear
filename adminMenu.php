<?php
include 'db_connect.php';
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    die("Access Denied");
}

// Handle Product Delete
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: adminMenu.php#products");
    exit();
}

// Handle Single Order Actions
if (isset($_POST['order_action'])) {
    $order_id = $_POST['order_id'];
    $action = $_POST['order_action'];
    if ($action == 'accept') {
        $conn->query("UPDATE orders SET status='Accepted' WHERE id='$order_id'");
    } elseif ($action == 'cancel') {
        $reason = $_POST['cancel_reason'];
        $conn->query("UPDATE orders SET status='Cancelled', cancel_reason='$reason' WHERE id='$order_id'");
    } elseif ($action == 'delete_order') {
        $conn->query("DELETE FROM orders WHERE id='$order_id'");
    }
    header("Location: adminMenu.php#orders");
    exit();
}

// Handle Bulk Order Actions
if (isset($_POST['bulk_action']) && isset($_POST['order_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['order_ids']));
    if ($_POST['bulk_action'] == 'bulk_accept') {
        $conn->query("UPDATE orders SET status='Accepted' WHERE id IN ($ids)");
    } elseif ($_POST['bulk_action'] == 'bulk_cancel') {
        $conn->query("UPDATE orders SET status='Cancelled' WHERE id IN ($ids)");
    } elseif ($_POST['bulk_action'] == 'bulk_delete') {
        $conn->query("DELETE FROM orders WHERE id IN ($ids)");
    }
    header("Location: adminMenu.php#orders");
    exit();
}

// Handle Note Update
if (isset($_POST['update_note'])) {
    $order_id = intval($_POST['note_order_id']);
    $note = $conn->real_escape_string($_POST['note_content']);
    $conn->query("UPDATE orders SET notes='$note' WHERE id=$order_id");
    header("Location: adminMenu.php#orders");
    exit();
}

// Handle Add Product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $sizes = $_POST['sizes'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $image = $_POST['image'];
    $sql = "INSERT INTO products (name, price, image, description, sizes, category) VALUES ('$name', '$price', '$image', '$description', '$sizes', '$category')";
    $conn->query($sql);
    header("Location: adminMenu.php#products");
    exit();
}

// Handle Category Actions
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $slug = strtolower(str_replace(' ', '-', $category_name));
    $conn->query("INSERT INTO categories (name, slug) VALUES ('$category_name', '$slug')");
    header("Location: adminMenu.php#categories");
    exit();
}
if (isset($_GET['delete_category'])) {
    $id = $_GET['delete_category'];
    $conn->query("DELETE FROM categories WHERE id=$id");
    header("Location: adminMenu.php#categories");
    exit();
}

// Handle User Delete
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: adminMenu.php#users");
    exit();
}

// Fetch Statistics
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Pending'")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$revenue_result = $conn->query("SELECT SUM(price) as total_revenue FROM orders WHERE status='Accepted'");
$total_revenue = $revenue_result->fetch_assoc()['total_revenue'] ?? 0;

// Handle Order Filters
$where_clauses = [];
if (isset($_GET['filter_status']) && $_GET['filter_status'] != '') {
    $where_clauses[] = "status = '" . $conn->real_escape_string($_GET['filter_status']) . "'";
}
if (isset($_GET['filter_customer']) && $_GET['filter_customer'] != '') {
    $where_clauses[] = "customer_name LIKE '%" . $conn->real_escape_string($_GET['filter_customer']) . "%'";
}
if (isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != '') {
    $where_clauses[] = "DATE(created_at) >= '" . $conn->real_escape_string($_GET['filter_date_from']) . "'";
}
if (isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != '') {
    $where_clauses[] = "DATE(created_at) <= '" . $conn->real_escape_string($_GET['filter_date_to']) . "'";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : "";
$orders_result = $conn->query("SELECT * FROM orders $where_sql ORDER BY id DESC");

// Fetch Other Data
$products_result = $conn->query("SELECT * FROM products ORDER BY id DESC");
$categories_result = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.name = p.category GROUP BY c.id ORDER BY c.name");
$users_result = $conn->query("SELECT u.*, COUNT(o.id) as order_count FROM users u LEFT JOIN orders o ON u.email = o.user_email GROUP BY u.id ORDER BY u.id DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard - Alvenwear</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            padding: 20px;
            font-family: sans-serif;
            background-color: #f5f5f5;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-number.revenue {
            color: #4CAF50;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .tabs {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            text-align: center;
            /* Center the tabs */
            display: flex;
            /* Use flexbox */
            justify-content: center;
            /* Center horizontally */
            flex-wrap: wrap;
            /* Allow wrapping on smaller screens */
            gap: 5px;
            /* Space between tabs */
        }

        .tab-btn {
            background: #333;
            color: white;
            border: none;
            padding: 12px 25px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
        }

        .tab-btn:hover {
            background: #555;
        }

        .tab-btn.active {
            background: #000;
        }

        .section {
            display: none;
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .section.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #333;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }

        .form-group textarea {
            height: 100px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #333;
            color: white;
        }

        .btn-primary:hover {
            background-color: #555;
        }

        .btn-success {
            background-color: #4CAF50;
            color: white;
        }

        .btn-danger {
            background-color: #f44336;
            color: white;
        }

        .btn-warning {
            background-color: #ff9800;
            color: white;
        }

        .btn-accept {
            background-color: green;
            color: white;
        }

        .btn-cancel {
            background-color: red;
            color: white;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .status-accepted {
            color: green;
            font-weight: bold;
        }

        .status-cancelled {
            color: red;
            font-weight: bold;
        }

        .receipt-img {
            max-width: 100px;
            height: auto;
            border: 1px solid #ccc;
            cursor: pointer;
        }

        .welcome-header {
            background: #333;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .welcome-header h1 {
            margin: 0 0 10px 0;
        }

        .add-product-form {
            max-width: 800px;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 3px;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }

        .cancel-form {
            margin-top: 10px;
            padding: 10px;
            border: 1px dashed #ccc;
            border-radius: 3px;
        }

        .cancel-input {
            width: 100%;
            padding: 5px;
            margin: 5px 0;
            box-sizing: border-box;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .revenue-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            border-left: 4px solid #4CAF50;
        }

        .revenue-info small {
            color: #666;
            display: block;
            margin-top: 5px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            margin: 0;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 5px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #000;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-primary {
            background: #333;
            color: white;
        }

        .badge-success {
            background: #4CAF50;
            color: white;
        }

        /* Advanced Order Features CSS */
        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
            align-items: flex-end;
        }

        .filter-bar .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 150px;
        }

        .filter-bar label {
            font-size: 12px;
            font-weight: 600;
            color: #555;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 6px 10px;
            font-size: 13px;
        }

        .bulk-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
        }

        .bulk-bar select {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .view-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }

        .print-btn {
            background: #6b7280;
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }

        .order-notes-area {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 60px;
            margin: 10px 0;
            font-family: inherit;
        }

        /* Print Invoice Styles */
        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
                background: white;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="welcome-header">
        <h1>Admin Dashboard - Alvenwear</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['login_user']); ?> | <a href="logout.php" style="color: #ff6b6b;">Logout</a> | <a href="index.php" style="color: #fff;">Back to Site</a></p>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-number"><?php echo $pending_orders; ?></div>
            <div class="stat-label">Pending Order</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_users; ?></div>
            <div class="stat-label">Total User</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $active_products; ?></div>
            <div class="stat-label">Active Product</div>
        </div>
        <div class="stat-card">
            <div class="stat-number revenue">RM <?php echo number_format($total_revenue, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('dashboard')">Dashboard</button>
        <button class="tab-btn" onclick="showTab('orders')">Orders (<?php echo $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c']; ?>)</button>
        <button class="tab-btn" onclick="showTab('products')">Products (<?php echo $active_products; ?>)</button>
        <button class="tab-btn" onclick="showTab('categories')">Categories</button>
        <button class="tab-btn" onclick="showTab('users')">Users (<?php echo $total_users; ?>)</button>
    </div>

    <div id="dashboard" class="section active">
        <h2>Dashboard Overview</h2>
        <p>Use the tabs above to manage your store.</p>
        <div style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
            <h3>Quick Stats:</h3>
            <ul style="line-height: 2;">
                <li><strong>Pending Orders:</strong> <?php echo $pending_orders; ?> orders awaiting approval</li>
                <li><strong>Total Users:</strong> <?php echo $total_users; ?> registered users</li>
                <li><strong>Active Products:</strong> <?php echo $active_products; ?> products in catalog</li>
                <li><strong>Total Revenue:</strong> <span style="color: #4CAF50; font-weight: bold;">RM <?php echo number_format($total_revenue, 2); ?></span> from completed orders</li>
            </ul>
        </div>
    </div>

    <!-- Orders Section -->
    <div id="orders" class="section">
        <h2>Customer Orders</h2>

        <!-- Filter Bar -->
        <form method="GET" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
            <input type="hidden" name="tab" value="orders">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Status</label>
                    <select name="filter_status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="">All Status</option>
                        <option value="Pending" <?php echo isset($_GET['filter_status']) && $_GET['filter_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Accepted" <?php echo isset($_GET['filter_status']) && $_GET['filter_status'] == 'Accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="Cancelled" <?php echo isset($_GET['filter_status']) && $_GET['filter_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Customer Name</label>
                    <input type="text" name="filter_customer" placeholder="Search customer..."
                        value="<?php echo isset($_GET['filter_customer']) ? htmlspecialchars($_GET['filter_customer']) : ''; ?>"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Category</label>
                    <select name="filter_category" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="">All Categories</option>
                        <?php
                        $categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
                        while ($cat = $categories->fetch_assoc()) {
                            $selected = (isset($_GET['filter_category']) && $_GET['filter_category'] == $cat['category']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($cat['category']) . "' $selected>" . htmlspecialchars($cat['category']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Filter</button>
                    <a href="adminMenu.php#orders" class="btn" style="flex: 1; background: #ddd; color: #333; text-align: center;">Clear</a>
                </div>
            </div>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Product Info</th>
                <th>Customer Details</th>
                <th>Receipt</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            // Build filter query
            $where_clauses = [];

            if (isset($_GET['filter_status']) && $_GET['filter_status'] != '') {
                $where_clauses[] = "status = '" . $conn->real_escape_string($_GET['filter_status']) . "'";
            }

            if (isset($_GET['filter_customer']) && $_GET['filter_customer'] != '') {
                $where_clauses[] = "customer_name LIKE '%" . $conn->real_escape_string($_GET['filter_customer']) . "%'";
            }

            if (isset($_GET['filter_category']) && $_GET['filter_category'] != '') {
                $filter_category = $conn->real_escape_string($_GET['filter_category']);
                $where_clauses[] = "product_name IN (SELECT name FROM products WHERE category = '$filter_category')";
            }

            $where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : "";
            $orders_result = $conn->query("SELECT * FROM orders $where_sql ORDER BY id DESC");

            if ($orders_result->num_rows > 0) {
                while ($row = $orders_result->fetch_assoc()) {
                    $statusClass = 'status-pending';
                    if ($row['status'] == 'Accepted') $statusClass = 'status-accepted';
                    if ($row['status'] == 'Cancelled') $statusClass = 'status-cancelled';

                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>
                        <strong>Product:</strong> " . htmlspecialchars($row['product_name']) . "<br>
                        <strong>Size:</strong> " . htmlspecialchars($row['size']) . "<br>
                        <strong>Price:</strong> RM " . $row['price'] . "<br>
                        <strong>Email:</strong> " . htmlspecialchars($row['user_email']) . "
                      </td>";
                    echo "<td>
                        <strong>Name:</strong> " . htmlspecialchars($row['customer_name']) . "<br>
                        <strong>Phone:</strong> " . htmlspecialchars($row['customer_phone']) . "<br>
                        <strong>Address:</strong> " . htmlspecialchars($row['customer_address']) . "
                      </td>";

                    $receiptPath = "assets/receipts/" . $row['receipt_image'];
                    if (!empty($row['receipt_image']) && file_exists($receiptPath)) {
                        echo "<td><a href='$receiptPath' target='_blank'><img src='$receiptPath' class='receipt-img' alt='Receipt'></a></td>";
                    } else {
                        echo "<td>No Receipt</td>";
                    }

                    echo "<td class='$statusClass'>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>";

                    if ($row['status'] == 'Pending') {
                        echo "<form method='POST' style='margin:2px 0;'>
                            <input type='hidden' name='order_id' value='" . $row['id'] . "'>
                            <input type='hidden' name='order_action' value='accept'>
                            <button type='submit' class='action-btn btn-accept'>Accept</button>
                          </form>";
                        echo "<form method='POST' class='cancel-form'>
                            <input type='hidden' name='order_id' value='" . $row['id'] . "'>
                            <input type='hidden' name='order_action' value='cancel'>
                            <input type='text' name='cancel_reason' class='cancel-input' placeholder='Cancel reason...' required>
                            <button type='submit' class='action-btn btn-cancel'>Cancel</button>
                          </form>";
                    } elseif ($row['status'] == 'Accepted') {
                        echo "<span style='color:green; font-weight:bold;'>✓ Completed</span>";
                    } elseif ($row['status'] == 'Cancelled') {
                        echo "<div style='color:red; font-weight:bold;'>✗ Cancelled</div>";
                        if (!empty($row['cancel_reason'])) {
                            echo "<small style='color:#666;'>Reason: " . htmlspecialchars($row['cancel_reason']) . "</small>";
                        }
                    }

                    echo "<form method='POST' style='margin-top:5px;'>
                        <input type='hidden' name='order_id' value='" . $row['id'] . "'>
                        <input type='hidden' name='order_action' value='delete_order'>
                        <button type='submit' class='action-btn btn-danger' onclick=\"return confirm('Delete this order?');\">Delete</button>
                      </form>";
                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No orders found matching your filters.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- 3. Order Details Modal + 5. Order Notes -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeOrderModal()">&times;</span>
            <h2 style="margin-top:0; border-bottom:2px solid #333; padding-bottom:10px;">Order #<span id="modalOrderId"></span> Details</h2>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <h4 style="margin-bottom:5px;">Product Info</h4>
                    <p><strong>Name:</strong> <span id="modalProduct"></span></p>
                    <p><strong>Size:</strong> <span id="modalSize"></span></p>
                    <p><strong>Price:</strong> RM <span id="modalPrice"></span></p>
                    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                </div>
                <div>
                    <h4 style="margin-bottom:5px;">Customer Info</h4>
                    <p><strong>Name:</strong> <span id="modalCustomer"></span></p>
                    <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
                    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                    <p><strong>Address:</strong> <span id="modalAddress"></span></p>
                </div>
            </div>

            <div id="modalReceiptArea" style="margin-bottom:20px; text-align:center;">
                <img id="modalReceiptImg" src="" alt="Receipt" style="max-width:300px; border:1px solid #ddd; display:none;">
                <p id="noReceiptMsg" style="color:#888;">No receipt uploaded</p>
            </div>

            <div style="background:#f8f9fa; padding:15px; border-radius:5px;">
                <h4 style="margin-top:0;">📝 Internal Notes</h4>
                <form method="POST">
                    <input type="hidden" name="note_order_id" id="noteOrderId">
                    <textarea name="note_content" id="noteContent" class="order-notes-area" placeholder="Add internal notes for this order..."></textarea>
                    <button type="submit" name="update_note" class="btn btn-primary" style="width:100%;">Save Notes</button>
                </form>
            </div>

            <div style="margin-top:20px; text-align:right;" class="no-print">
                <button class="btn btn-warning" onclick="printInvoice()">🖨 Print Invoice</button>
                <button class="btn" style="background:#eee; color:#333; margin-left:5px;" onclick="closeOrderModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Hidden Print Area for 4. Print Invoice -->
    <div id="printArea" style="display:none;">
        <div style="text-align:center; border-bottom:2px solid #000; padding-bottom:15px; margin-bottom:20px;">
            <h1 style="margin:0;">ALVENWEAR INVOICE</h1>
            <p style="margin:5px 0;">Order #<span id="printOrderId"></span> | Date: <span id="printDate"></span></p>
        </div>
        <table style="width:100%; margin-bottom:20px;">
            <tr>
                <td style="padding:5px; border:none;"><strong>Customer:</strong> <span id="printCustomer"></span></td>
                <td style="padding:5px; border:none; text-align:right;"><strong>Phone:</strong> <span id="printPhone"></span></td>
            </tr>
            <tr>
                <td colspan="2" style="padding:5px; border:none;"><strong>Address:</strong> <span id="printAddress"></span></td>
            </tr>
        </table>
        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; padding:8px; background:#eee;">Item</th>
                    <th style="border:1px solid #000; padding:8px; background:#eee; width:100px;">Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border:1px solid #000; padding:8px;"><span id="printProduct"></span> (Size: <span id="printSize"></span>)</td>
                    <td style="border:1px solid #000; padding:8px; text-align:right;">RM <span id="printPrice"></span></td>
                </tr>
            </tbody>
        </table>
        <div style="text-align:right; font-size:18px; font-weight:bold; margin-bottom:30px;">Total: RM <span id="printTotal"></span></div>
        <div style="border-top:1px solid #000; padding-top:10px; font-size:12px; color:#555;">
            <p>Thank you for shopping with Alvenwear! All sales are final unless defective.</p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <!-- Products Section -->
    <div id="products" class="section">
        <div class="section-header">
            <h2>Manage Products</h2><button class="btn btn-success" onclick="document.getElementById('add-product').classList.add('active'); showTab('add-product');">+ Add New Product</button>
        </div>
        <div class="search-box">
            <form method="GET"><input type="hidden" name="tab" value="products"><input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"><button type="submit" class="btn btn-primary">Search</button><a href="adminMenu.php#products" class="btn">Clear</a></form>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price (RM)</th>
                <th>Sizes</th>
                <th>Category</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search_term = $_GET['search'];
                $products_result = $conn->query("SELECT * FROM products WHERE name LIKE '%$search_term%'");
            } else {
                $products_result = $conn->query("SELECT * FROM products ORDER BY id DESC");
            }
            if ($products_result->num_rows > 0) {
                while ($row = $products_result->fetch_assoc()) {
                    echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>RM " . $row['price'] . "</td>
                    <td>" . htmlspecialchars($row['sizes']) . "</td>
                    <td><span class='badge badge-primary'>" . htmlspecialchars($row['category']) . "</span></td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td>
                        <a href='editProduct.php?id=" . $row['id'] . "' class='action-btn btn-success'>Edit</a>
                        <a href='adminMenu.php?delete_product=" . $row['id'] . "#products' class='action-btn btn-danger' onclick=\"return confirm('Delete this product?');\">Delete</a>
                    </td>
                  </tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>No products found</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Categories Section -->
    <div id="categories" class="section">
        <div class="section-header">
            <h2>Manage Categories</h2><button class="btn btn-success" onclick="openCategoryModal()">+ Add New Category</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Slug</th>
                <th>Product Count</th>
                <th>Actions</th>
            </tr>
            <?php if ($categories_result->num_rows > 0) {
                while ($row = $categories_result->fetch_assoc()) {
                    echo "<tr><td>" . $row['id'] . "</td><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['slug']) . "</td><td><span class='badge badge-success'>" . $row['product_count'] . " products</span></td><td><a href='adminMenu.php?delete_category=" . $row['id'] . "#categories' class='action-btn btn-danger' onclick=\"return confirm('Delete this category?');\">Delete</a></td></tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No categories found</td></tr>";
            } ?>
        </table>
    </div>

    <!-- Users Section -->
    <div id="users" class="section">
        <h2>Manage Users</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Total Orders</th>
                <th>Actions</th>
            </tr>
            <?php if ($users_result->num_rows > 0) {
                while ($row = $users_result->fetch_assoc()) {
                    echo "<tr><td>" . $row['id'] . "</td><td>" . htmlspecialchars($row['username']) . "</td><td>" . htmlspecialchars($row['email']) . "</td><td><span class='badge badge-primary'>" . $row['order_count'] . " orders</span></td><td><a href='adminMenu.php?delete_user=" . $row['id'] . "#users' class='action-btn btn-danger' onclick=\"return confirm('Delete this user?');\">Delete</a></td></tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No users found</td></tr>";
            } ?>
        </table>
    </div>

    <!-- Add Product Section -->
    <div id="add-product" class="section">
        <h2>Add New Product</h2>
        <form method="POST" class="add-product-form">
            <div class="form-group"><label>Product Name:</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Price (RM):</label><input type="number" step="0.01" name="price" required></div>
            <div class="form-group"><label>Sizes (comma separated):</label><input type="text" name="sizes" placeholder="S, M, L, XL" required></div>
            <div class="form-group"><label>Category:</label><input type="text" name="category" list="category-list" placeholder="Select or type category" required><datalist id="category-list"><?php $cats = $conn->query("SELECT name FROM categories");
                                                                                                                                                                                                while ($cat = $cats->fetch_assoc()) {
                                                                                                                                                                                                    echo "<option value='" . htmlspecialchars($cat['name']) . "'>";
                                                                                                                                                                                                } ?></datalist></div>
            <div class="form-group"><label>Image URL:</label><input type="file" name="image" placeholder="assets/images/product.jpg" required></div>
            <div class="form-group"><label>Description:</label><textarea name="description" required></textarea></div>
            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            <button type="button" class="btn" onclick="showTab('products')">Cancel</button>
        </form>
    </div>

    <!-- Add Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content"><span class="close-modal" onclick="closeCategoryModal()">&times;</span>
            <h2>Add New Category</h2>
            <form method="POST">
                <div class="form-group"><label>Category Name:</label><input type="text" name="category_name" placeholder="e.g., T-Shirts, Hoodies" required></div><button type="submit" name="add_category" class="btn btn-primary">Add Category</button><button type="button" class="btn" onclick="closeCategoryModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            var sections = document.getElementsByClassName('section');
            for (var i = 0; i < sections.length; i++) sections[i].classList.remove('active');
            var tabs = document.getElementsByClassName('tab-btn');
            for (var i = 0; i < tabs.length; i++) tabs[i].classList.remove('active');
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            window.location.hash = tabName;
        }

        function openCategoryModal() {
            document.getElementById('categoryModal').style.display = 'block';
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }
        window.onclick = function(event) {
            var modal = document.getElementById('categoryModal');
            if (event.target == modal) modal.style.display = 'none';
        }
        window.onload = function() {
            var hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                var tabs = document.getElementsByClassName('tab-btn');
                for (var i = 0; i < tabs.length; i++) {
                    if (tabs[i].textContent.toLowerCase().includes(hash.replace('-', ' '))) {
                        tabs[i].click();
                        break;
                    }
                }
            }
        }

        // Bulk Selection
        function toggleAll(source) {
            var checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            var checked = document.querySelectorAll('.row-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = checked > 0 ? checked + ' selected' : '';
        }

        // Order Modal
        function openOrderModal(id) {
            var row = document.querySelector('tr[data-id="' + id + '"]');
            if (!row) return;
            document.getElementById('modalOrderId').textContent = id;
            document.getElementById('modalProduct').textContent = row.dataset.product;
            document.getElementById('modalSize').textContent = row.dataset.size;
            document.getElementById('modalPrice').textContent = row.dataset.price;
            document.getElementById('modalStatus').textContent = row.dataset.status;
            document.getElementById('modalCustomer').textContent = row.dataset.customer;
            document.getElementById('modalPhone').textContent = row.dataset.phone;
            document.getElementById('modalEmail').textContent = row.dataset.email;
            document.getElementById('modalAddress').textContent = row.dataset.address;
            document.getElementById('noteOrderId').value = id;
            document.getElementById('noteContent').value = row.dataset.notes;

            var receipt = row.dataset.receipt;
            if (receipt) {
                document.getElementById('modalReceiptImg').src = 'assets/receipts/' + receipt;
                document.getElementById('modalReceiptImg').style.display = 'inline-block';
                document.getElementById('noReceiptMsg').style.display = 'none';
            } else {
                document.getElementById('modalReceiptImg').style.display = 'none';
                document.getElementById('noReceiptMsg').style.display = 'block';
            }
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('orderModal')) closeOrderModal();
            if (event.target == document.getElementById('categoryModal')) closeCategoryModal();
        }

        // Print Invoice
        function printInvoice() {
            document.getElementById('printOrderId').textContent = document.getElementById('modalOrderId').textContent;
            document.getElementById('printDate').textContent = new Date().toLocaleDateString();
            document.getElementById('printCustomer').textContent = document.getElementById('modalCustomer').textContent;
            document.getElementById('printPhone').textContent = document.getElementById('modalPhone').textContent;
            document.getElementById('printAddress').textContent = document.getElementById('modalAddress').textContent;
            document.getElementById('printProduct').textContent = document.getElementById('modalProduct').textContent;
            document.getElementById('printSize').textContent = document.getElementById('modalSize').textContent;
            document.getElementById('printPrice').textContent = document.getElementById('modalPrice').textContent;
            document.getElementById('printTotal').textContent = document.getElementById('modalPrice').textContent;

            var printArea = document.getElementById('printArea');
            printArea.style.display = 'block';
            window.print();
            printArea.style.display = 'none';
        }
    </script>
</body>


</html>