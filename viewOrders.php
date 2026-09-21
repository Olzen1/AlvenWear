<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    die("Access Denied");
}

// Handle Actions
if (isset($_POST['action']) && $_POST['action'] == 'accept') {
    $order_id = $_POST['order_id'];
    $conn->query("UPDATE orders SET status='Accepted' WHERE id='$order_id'");
    header("Location: viewOrders.php");
    exit();
}
if (isset($_POST['action']) && $_POST['action'] == 'cancel') {
    $order_id = $_POST['order_id'];
    $reason = $_POST['cancel_reason'];
    $conn->query("UPDATE orders SET status='Cancelled', cancel_reason='$reason' WHERE id='$order_id'");
    header("Location: viewOrders.php");
    exit();
}
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $order_id = $_POST['order_id'];
    $conn->query("DELETE FROM orders WHERE id='$order_id'");
    header("Location: viewOrders.php");
    exit();
}

$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
$total_orders = $result->num_rows;
$pending_count = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='Pending'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>View Orders - Alvenwear Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar*/
        .navbar {
            background: #fff;
            padding: 12px 30px;
            border-bottom: 1px solid #e1e8ed;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar .container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            width: 100px;
        }

        .nav-scl ul {
            display: flex;
            gap: 25px;
            list-style: none;
            align-items: center;
        }

        .nav-scl a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .nav-scl a:hover {
            color: #000;
        }

        .btn-back {
            background: #333;
            color: white;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-back:hover {
            background: #555;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
            width: 100%;
        }

        /* Header Stats */
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            font-size: 24px;
            color: #1a202c;
        }

        .stats-bar {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-badge {
            background: #f7fafc;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-badge.pending {
            background: #fff7ed;
            color: #c05621;
        }

        .stat-badge.total {
            background: #edf2f7;
            color: #4a5568;
        }

        /* Table */
        .table-container {
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

        /* Info Columns */
        .info-group strong {
            display: block;
            color: #718096;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .info-group span {
            color: #2d3748;
            font-weight: 500;
        }

        .price-tag {
            color: #e53e3e;
            font-weight: bold;
            font-size: 15px;
        }

        /* Receipt Image */
        .receipt-img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .receipt-img:hover {
            transform: scale(1.05);
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
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

        /* Action Buttons */
        .action-form {
            margin-bottom: 8px;
        }

        .btn-action {
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-accept {
            background: #48bb78;
            color: white;
        }

        .btn-accept:hover {
            background: #38a169;
        }

        .btn-cancel {
            background: #fc8181;
            color: white;
        }

        .btn-cancel:hover {
            background: #f56565;
        }

        .btn-delete {
            background: #718096;
            color: white;
            width: 100%;
            margin-top: 5px;
        }

        .btn-delete:hover {
            background: #4a5568;
        }

        .cancel-input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 12px;
            margin: 5px 0;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Footer */
        footer {
            background: #1a202c;
            color: #a0aec0;
            padding: 25px 30px;
            text-align: center;
            font-size: 13px;
            margin-top: auto;
        }

        footer a {
            color: #cbd5e0;
            text-decoration: none;
        }

        footer a:hover {
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="logo"><a href="index.php"><img src="/alvenwear/assets/ALVÉN.png" alt="Logo"></a></div>
            <div class="nav-scl">
                <a href="adminMenu.php" class="btn-back">← Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Customer Orders</h1>
            <div class="stats-bar">
                <span class="stat-badge total">Total: <?php echo $total_orders; ?></span>
                <span class="stat-badge pending">Pending: <?php echo $pending_count; ?></span>
            </div>
        </div>

        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th style="width:110px;">Receipt</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):
                            $statusClass = $row['status'] == 'Accepted' ? 'status-accepted' : ($row['status'] == 'Cancelled' ? 'status-cancelled' : 'status-pending');
                        ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td>
                                    <div class="info-group">
                                        <strong>Product</strong>
                                        <span><?php echo htmlspecialchars($row['product_name']); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <strong>Size</strong>
                                        <span><?php echo htmlspecialchars($row['size']); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <strong>Price</strong>
                                        <span class="price-tag">RM <?php echo number_format($row['price'], 2); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="info-group">
                                        <strong>Name</strong>
                                        <span><?php echo htmlspecialchars($row['customer_name']); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <strong>Phone</strong>
                                        <span><?php echo htmlspecialchars($row['customer_phone']); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <strong>Address</strong>
                                        <span style="font-size:13px;"><?php echo htmlspecialchars($row['customer_address']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($row['receipt_image']) && file_exists("assets/receipts/" . $row['receipt_image'])): ?>
                                        <a href="assets/receipts/<?php echo $row['receipt_image']; ?>" target="_blank">
                                            <img src="assets/receipts/<?php echo $row['receipt_image']; ?>" class="receipt-img" alt="Receipt">
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#a0aec0;font-size:12px;">No receipt</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <?php if ($row['status'] == 'Pending'): ?>
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn-action btn-accept">✓ Accept</button>
                                        </form>
                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="text" name="cancel_reason" class="cancel-input" placeholder="Cancel reason..." required>
                                            <button type="submit" class="btn-action btn-cancel">✕ Cancel</button>
                                        </form>
                                    <?php elseif ($row['status'] == 'Accepted'): ?>
                                        <span style="color:#276749;font-weight:600;">✓ Completed</span>
                                    <?php else: ?>
                                        <span style="color:#c53030;font-weight:600;">✕ Cancelled</span>
                                        <?php if (!empty($row['cancel_reason'])): ?>
                                            <div style="font-size:11px;color:#718096;margin-top:4px;">
                                                Reason: <?php echo htmlspecialchars($row['cancel_reason']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn-action btn-delete" onclick="return confirm('Delete this order?');">🗑 Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p>No orders yet. Orders will appear here when customers checkout.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Alvenwear Admin Panel. <a href="index.php">Back to Store</a></p>
    </footer>
</body>

</html>