<?php
session_start();
include "db_connect.php";

// Security: Must be logged in and order ID must be provided
if (!isset($_SESSION["login_user"]) || !isset($_GET["id"])) {
    die("Access Denied");
}

$username = $_SESSION["login_user"];
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin');
$order_id = intval($_GET["id"]);

// Fetch order first
$order = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
if (!$order) {
    die("Order not found.");
}

// Security Check:
// If NOT admin, verify the order belongs to this user
if (!$is_admin) {
    $email_res = $conn->query("SELECT email FROM users WHERE username='$username'");
    if ($email_res->num_rows == 0) {
        die("User not found.");
    }
    $user_email = $email_res->fetch_assoc()['email'];

    if ($order['user_email'] != $user_email) {
        die("Access Denied: You do not own this order.");
    }
}

// Check if order is accepted
if ($order['status'] != 'Accepted') {
    die("Receipt not available. Orders must be accepted by admin first.");
}

$receipt_no = "ALV-" . $order['id'] . "-" . date('YmdHis');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?php echo $order['id']; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px 20px;
            background: #f4f4f4;
            margin: 0;
        }

        .receipt-box {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 2px;
        }

        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            color: #333;
            margin-top: 4px;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #333;
            color: white;
            font-weight: 600;
        }

        td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .total-section {
            text-align: right;
            font-size: 22px;
            font-weight: bold;
            margin: 20px 0;
            color: #000;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
            font-size: 13px;
            color: #666;
        }

        .btn-print {
            display: block;
            margin: 20px auto;
            padding: 12px 30px;
            background: #000;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .btn-print:hover {
            background: #333;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt-box {
                border: none;
                box-shadow: none;
                padding: 20px 0;
            }

            .btn-print {
                display: none !important;
            }

            @page {
                margin: 1.5cm;
            }
        }
    </style>
</head>

<body>
    <button class="btn-print" onclick="window.print()">🖨 Print Receipt</button>

    <div class="receipt-box">
        <div class="header">
            <h1>ALVENWEAR</h1>
            <p>Official Order Receipt</p>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-label">Receipt Number</div>
                <div class="info-value"><?php echo $receipt_no; ?></div>
                <div class="info-label" style="margin-top:10px;">Order Date</div>
                <div class="info-value"><?php echo date('d M Y, h:i A'); ?></div>
            </div>
            <div style="text-align: right;">
                <div class="info-label">Shipping To</div>
                <div class="info-value">
                    <?php echo htmlspecialchars($order['customer_name']); ?><br>
                    <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                    <?php echo nl2br(htmlspecialchars($order['customer_address'])); ?>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['size']); ?></td>
                    <td>RM <?php echo number_format($order['price'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            Total: RM <?php echo number_format($order['price'], 2); ?>
        </div>

        <div class="footer">
            <p>Thank you for supporting Alvenwear! 🖤</p>
            <p>This receipt is automatically generated upon admin approval.</p>
            <p>© <?php echo date('Y'); ?> Alvenwear. All rights reserved.</p>
        </div>
    </div>
</body>

</html>